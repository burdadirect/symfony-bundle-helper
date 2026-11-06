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

abstract class AbstractAissistChatService extends AbstractAissistService
{

    protected ?string $providerToUse = null;

    protected array $defaults = [
        // Optional settings.
        'max_tokens' => 4000,
        'reasoning_effort' => 'low',

        // Deprecated but necessary settings.
        'temperature' => 0.75,
        'top_p' => 0.9,
        'presence_penalty' => 0,
        'frequency_penalty' => 0,
    ];

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
            $this->providerToUse = $this->settingRepositoryInterface->getVarValueParsed(static::class, 'aissist');

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
                    $this->settingRepositoryInterface->findOrCreateOne(static::class, 'aissist', SettingVarType::STRING, $this->providerToUse, persistAndFlush: true);
                }
            }

            if (!$this->providerToUse) {
                throw new \RuntimeException('No provider found');
            }
        }
        return $this->providerToUse;
    }
}
