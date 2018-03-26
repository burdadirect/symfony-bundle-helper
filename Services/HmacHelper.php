<?php

namespace HBM\HelperBundle\Services;

class HmacHelper {

  /** @var array */
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

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
