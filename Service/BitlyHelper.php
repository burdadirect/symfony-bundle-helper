<?php

namespace HBM\HelperBundle\Service;

require_once __DIR__ . '/../vendor_third_party/bitly/bitly.php';

class BitlyHelper {

  /**
   * @var array
   */
  private $config;

  /**
   * @var array
   */
  private $accessTokens;

  /**
   * BitlyHelper constructor.
   *
   * @param array $config
   */
  public function __construct(array $config) {
    $this->config = $config;
  }

  /**
   * @return array
   */
  public function getConfig() : array {
    return $this->config;
  }

  /**
   * @param $endpoint
   * @param $params
   *
   * @return mixed
   */
  public function get($endpoint, $params) {
    $paramsWithToken = array_merge($params, ['access_token' => $this->getAccessToken($params['domain'] ?? 'default')]);

    return bitly_get($endpoint, $paramsWithToken);
  }

  /**
   * @param $endpoint
   * @param $params
   *
   * @return mixed
   */
  public function post($endpoint, $params) {
    $paramsWithToken = array_merge($params, ['access_token' => $this->getAccessToken($params['domain'] ?? 'default')]);

    return bitly_post($endpoint, $paramsWithToken);
  }

  /**
   * @param string $domain
   *
   * @return mixed
   */
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
