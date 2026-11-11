<?php

namespace HBM\HelperBundle\Service\Aissist;

use HBM\BasicsBundle\Util\Pinpoint;
use HBM\HelperBundle\Util\Result\ResultAissist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @phpstan-type message array{
 *     role: string,
 *     content: string
 * }
 */
class AissistChatHttpService extends AbstractAissistChatService
{

    /**
     * @param list<message> $messages
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function chatHttp(array $messages, array $inputAssetIds = [], ?string $provider = null, array $options = []): ResultAissist
    {
        $body = [
            'provider' => $provider ?? $this->getProviderToUse(),
            'messages' => $messages,
        ];

        if (count($inputAssetIds) > 0) {
            $body['input_asset_ids'] = $inputAssetIds;
        }

        $body = array_merge($this->defaults, $options, $body);
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('POST', '/v1/chat/http', ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

    /**
     *
     * @param message[] $messages
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function chatJson(array $messages, array $inputAssetIds = [], ?string $provider = null, array $options = []): ResultAissist
    {
        $body = [
            'provider' => $provider ?? $this->getProviderToUse(),
            'messages' => $messages,
        ];

        if (count($inputAssetIds) === 0) {
            $body['input_asset_ids'] = $inputAssetIds;
        }

        $body = array_merge($this->defaults, $options, $body);
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('POST', '/v1/chat/json', ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

}
