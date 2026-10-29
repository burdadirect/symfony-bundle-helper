<?php

namespace HBM\HelperBundle\Service\Aissist;

use HBM\BasicsBundle\Entity\Repository\Interfaces\SettingRepositoryInterface;
use HBM\BasicsBundle\Traits\ServiceDependencies\ParameterBagDependencyTrait;
use HBM\BasicsBundle\Util\Total;
use HBM\HelperBundle\Util\Result\ResultAissist;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractAissistService
{

    use ParameterBagDependencyTrait;

    protected HttpClientInterface $apiClient;
    protected ?Total $total = null;

    public function __construct(
      protected readonly array $config,
      protected readonly SettingRepositoryInterface $settingRepositoryInterface,
    ) {
    }

    protected function getClient(): HttpClientInterface {
        if (!isset($this->apiClient)) {
            $jwtTokenFromSetting = $this->settingRepositoryInterface->getVarValueParsed('jwt', 'aissist');

            $httpOptions = new HttpOptions();
            $httpOptions->setBaseUri($this->config['uri']);
            $httpOptions->verifyHost(false);
            $httpOptions->verifyPeer(false);
            $httpOptions->setHeaders([
              'Accept' => 'application/json',
              'Content-Type' => 'application/json',
              'Authorization' => 'Bearer '.($jwtTokenFromSetting ?: $this->config['jwt'])
            ]);

            $this->apiClient = HttpClient::create($httpOptions->toArray());
        }

        return $this->apiClient;
    }

    public function getTotal(): ?Total
    {
        return $this->total;
    }

    public function setTotal(?Total $total): void
    {
        $this->total = $total;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function request(string $method, string|array $url, array $options = []): ResponseInterface {
        $requestUrl = $totalUrl = $url;
        if (is_array($url)) {
            $requestUrl = vsprintf($url[0], $url[1]);
            $totalUrl = $url[0];
        }

        // Log requests.
        if ($this->total instanceof Total) {
            $this->total->note(1, $totalUrl);
            $this->total->note(1, get_class($this));
            $this->total->note(1, 'all');
        }

        return $this->getClient()->request($method, $requestUrl, $options);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getProvider(string $name): ResultAissist
    {
        $response = $this->request('GET', '/v1/chat/providers/'.$name);

        return $this->handleResponse($response, [Response::HTTP_OK], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function listProviders(): ResultAissist
    {
        $response = $this->request('GET', '/v1/chat/providers/');

        return $this->handleResponse($response, [Response::HTTP_OK], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function doUpload(string $uploadUrl, int|string $length, $resource): ResultAissist {
        $client = HttpClient::create();
        $response = $client->request('PUT', $uploadUrl, [
          'headers' => [
            'Content-Length' => (string) $length,
          ],
          'body' => $resource,
        ]);

        $successStatusCodes = fn (ResponseInterface $res) => ($res->getStatusCode() >= 200 && $res->getStatusCode() < 300);

        return $this->handleResponse($response, $successStatusCodes, __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    protected function handleResponse(ResponseInterface $response, array|callable $successStatusCodes, ?string $class = null, ?string $method = null, ?int $line = null, ?ResultAissist $result = null, ?string $pinpoint = null): ResultAissist {
        $this->logResponseToFile($response, $pinpoint ?? sprintf('%s::%s[%s]', $class, $method, $line));

        $result = $result ?? new ResultAissist();
        $result->setResponse($response);

        if (is_array($successStatusCodes) && !in_array($response->getStatusCode(), $successStatusCodes, true)) {
            return $result->setError($response->getContent(false))->setReturn(false);
        }
        if (is_callable($successStatusCodes) && !$successStatusCodes($response)) {
            return $result->setError($response->getContent(false))->setReturn(false);
        }

        $contentType = $response->getHeaders()['content-type'][0] ?? null;
        if ($contentType === 'application/json') {
            $result->setData($response->toArray(false));
        } else {
            $result->setText($response->getContent(false));
        }

        return $result->setReturn(true);
    }

    /**
     * Simple helper to log to local file. Used for debugging/development purposes.
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function logResponseToFile(ResponseInterface $response, string $pinpoint, ?string $filePath = null): void
    {
        if (!$this->pb->get('kernel.debug')) {
            return;
        }

        if ($filePath === null) {
            $filePath = $this->pb->get('kernel.logs_dir') . '/aissist-service-requests.log';
        }

        $timestamp = new \DateTime()->format('Y-m-d H:i:s');
        $resource = fopen($filePath, 'ab');
        fwrite($resource, sprintf('--- Response Start (%s)---: %s%s', $pinpoint, $timestamp, PHP_EOL));
        fwrite($resource, sprintf('Status Code: %s%s', $response->getStatusCode(), PHP_EOL));
        fwrite($resource, sprintf('Body: %s%s', $response->getContent(false), PHP_EOL));
        fwrite($resource, sprintf('--- Response End (%s)---: %s%s', $pinpoint, $timestamp, PHP_EOL));
        fwrite($resource, PHP_EOL);
        fclose($resource);
    }
}
