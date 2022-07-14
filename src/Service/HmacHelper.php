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
   * @param string|array $varToSign
   * @param string|null $secret
   * @param string|null $sep
   *
   * @return string
   */
  public function sign($varToSign, ?string $secret = NULL, ?string $sep = "\n") : string {
    $stringToSign = $varToSign;
    if (\is_array($stringToSign)) {
      $stringToSign = implode($sep, $varToSign).$sep;
    }

    $secretToUse = $secret;
    if ($secretToUse === NULL) {
      $secretToUse = $this->config['secret'];
    }

    return base64_encode(hash_hmac('sha256', $stringToSign, $secretToUse, true));
  }

  /**
   * @param string|array varToSign
   * @param string|array $secretData
   *
   * @return string
   */
  public function signWithSecretData($varToSign, $secretData) : string {
    if (is_array($secretData)) {
      $secret = $secretData['secret'];
      $sep = $secretData['separator'] ?? $secretData['sep'] ?? "\n";
    } else {
      $secret = $secretData;
      $sep = "\n";
    }

    return $this->sign($varToSign, $secret, $sep);
  }

}
