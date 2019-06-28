<?php

namespace HBM\HelperBundle\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class ScreenshotLayerHelper {

  /** @var array */
  private $config;

  /** @var Client */
  private $client;

  public function __construct($config) {
    $this->config = $config;
  }

  private function getClient() : Client {
    if ($this->client === NULL) {
      $this->client = new Client(['base_uri' => 'https://api.screenshotlayer.com/api']);
    }
    return $this->client;
  }

  /**
   * @param $url
   * @param array $options
   * @param array $data
   *
   * @return null|mixed|ResponseInterface
   */
  public function capture($url, array $options = []) {
    $query = array_merge($options, [
      'access_key' => $this->config['accesskey'],
      'url' => $url
    ]);

    $response = NULL;
    try {
      $response = $this->getClient()->request('GET', 'capture',
        [
          'query' => $query
        ]
      );
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
    }

    return $response;
  }

}
