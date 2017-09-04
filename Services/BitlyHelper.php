<?php

namespace HBM\HelperBundle\Services;

require_once __DIR__.'/third_party/bitly/bitly.php';

class BitlyHelper
{

  /** @var array */
  private $config;

  /** @var array */
  private $accessTokens;

  public function __construct($config) {
    $this->config = $config;
  }

  public function getConfig() {
    return $this->config;
  }

  public function get($endpoint, $params) {
    $paramsWithToken = array_merge($params, ['access_token' => $this->getAccessToken($params['domain'] ?? 'default')]);

    return bitly_get($endpoint, $paramsWithToken);
  }

  public function post($endpoint, $params) {
    $paramsWithToken = array_merge($params, ['access_token' => $this->getAccessToken($params['domain'] ?? 'default')]);

    return bitly_post($endpoint, $paramsWithToken);
  }

  public function getAccessToken($domain = 'default') {
    if (!isset($this->accessTokens[$domain])) {
      $configKey = $domain;
      if (!isset($this->config[$configKey])) {
        $configKey = 'default';
      }

      $result = bitly_oauth_access_token_via_password(
        $this->config[$configKey]['user_login'] ?? '',
        $this->config[$configKey]['user_password'] ?? '',
        $this->config[$configKey]['client_id'] ?? '',
        $this->config[$configKey]['client_secret'] ?? ''
      );

      if (isset($result['access_token'])) {
        $this->accessTokens[$domain] = $result['access_token'];
      }
    }

    return $this->accessTokens[$domain];
  }

}
