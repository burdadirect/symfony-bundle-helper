<?php

namespace HBM\HelperBundle\Service\Aissist;

use HBM\BasicsBundle\Util\Enum\SettingVarType;
use HBM\HelperBundle\Util\Result\ResultAissist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AissistChatHttpService extends AbstractAissistService
{

    private ?string $providerToUse = null;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getProviderToUse(): string
    {
        if (!isset($this->providerToUse)) {
            $this->providerToUse = $this->settingRepositoryInterface->getVarValueParsed('provider_chat_http', 'aissist');

            if (!$this->providerToUse) {
                $resultProviders = $this->listProviders();
                if ($resultProviders->getReturn()) {
                    foreach ($resultProviders->getData() as $providerName => $providerData) {
                        if ($providerData['default'] ?? false) {
                            $this->providerToUse = $providerName;
                            break;
                        }
                    }
                }

                if ($this->providerToUse) {
                    $this->settingRepositoryInterface->findOrCreateOne('provider_chat_http', 'aissist', SettingVarType::STRING, $this->providerToUse, persistAndFlush: true);
                }
            }

            if (!$this->providerToUse) {
                throw new \RuntimeException('No provider found');
            }
        }
        return $this->providerToUse;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function chatHttp(array $messages, array $inputAssetIds = [], ?string $provider = null, int $maxTokens = 4000): ResultAissist
    {
        $body = [
            'provider' => $provider ?? $this->getProviderToUse(),
            'messages' => $messages,

            // Deprecated but necessary settings.
            'temperature' => 0.75,
            'top_p' => 0.9,
            'presence_penalty' => 0,
            'frequency_penalty' => 0,
        ];

        if ($maxTokens) {
            $body['max_tokens'] = $maxTokens;
        }

        if (count($inputAssetIds) === 0) {
            $body['input_asset_ids'] = $inputAssetIds;
        }

        $response = $this->request('POST', '/v1/chat/http', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_OK], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function chatJson(array $messages, array $inputAssetIds = [], ?string $provider = null, int $maxTokens = 4000): ResultAissist
    {
        $body = [
            'provider' => $provider ?? $this->getProviderToUse(),
            'messages' => $messages,

            // Optional settings.
            'max_tokens' => $maxTokens,

            // Deprecated but necessary settings.
            'temperature' => 0.75,
            'top_p' => 0.9,
            'presence_penalty' => 0,
            'frequency_penalty' => 0,
        ];

        if (count($inputAssetIds) === 0) {
            $body['input_asset_ids'] = $inputAssetIds;
        }

        $response = $this->request('POST', '/v1/chat/json', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_OK], __CLASS__, __METHOD__, __LINE__);
    }

}
