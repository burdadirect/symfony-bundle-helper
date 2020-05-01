<?php

namespace HBM\HelperBundle\Service;

class HmacHelper {

  /**
   * @var array
   */
  private $config;

  /**
   * HmacHelper constructor.
   *
   * @param $config
   */
  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * @param $varToSign
   * @param null $secret
   *
   * @return string
   */
  public function sign($varToSign, $secret = NULL) : string {
    $stringToSign = $varToSign;
    if (\is_array($stringToSign)) {
      $stringToSign = implode("\n", $varToSign)."\n";
    }

    $secretToUse = $secret;
    if ($secretToUse === NULL) {
      $secretToUse = $this->config['secret'];
    }

    return base64_encode(hash_hmac('sha256', $stringToSign, $secretToUse, true));
  }

}
