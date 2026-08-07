<?php

namespace HBM\HelperBundle\Service\Screenshot;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class ScreenshotLayerHelper
{
    private array $config;
    private Client $client;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function getClient(): Client
    {
        if (!isset($this->client)) {
            $this->client = new Client(['base_uri' => 'https://api.screenshotlayer.com/api']);
        }

        return $this->client;
    }

    public function capture(string $url, array $options = []): ?ResponseInterface
    {
        $query = array_merge($options, [
          'access_key' => $this->config['accesskey'],
          'url'        => $url,
        ]);

        $response = null;

        try {
            $response = $this->getClient()->request(
                'GET',
                'capture',
                [
                'query' => $query,
        ]
            );
        } catch (\GuzzleHttp\Exception\GuzzleException) {
        }

        return $response;
    }
}
