<?php

namespace HBM\HelperBundle\Services;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

require_once('./third_party/bitly/bitly.php');

class BitlyHelper
{

  use ContainerAwareTrait;

  /** @var array */
  private $config;

  /** @var string */
  private $accessToken;

  public function __construct($config) {
    $this->config = $config;
  }

  public function bitly_get($endpoint, $params) {
    $paramsWithToken = array_merge($params, ['access_token' => $this->getAccessToken()]);

    return bitly_get($endpoint, $paramsWithToken);
  }

  public function bitly_post($endpoint, $params) {
    $paramsWithToken = array_merge($params, ['access_token' => $this->getAccessToken()]);

    return bitly_post($endpoint, $paramsWithToken);
  }

  public function getAccessToken() {
    if ($this->accessToken === NULL) {
      $result = bitly_oauth_access_token_via_password(
        $this->config['user_login'],
        $this->config['user_password'],
        $this->config['client_id'],
        $this->config['client_secret']
      );

      if (isset($result['access_token'])) {
        $this->accessToken = $result['access_token'];
      }
    }

    return $this->accessToken;
  }

}
