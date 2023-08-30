<?php

namespace HBM\HelperBundle\Service;

class HmacHelper
{
    /** @var array */
    private $config;

    /**
     * HmacHelper constructor.
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param array|string $varToSign
     */
    public function sign($varToSign, string $secret = null, ?string $sep = "\n"): string
    {
        $stringToSign = $varToSign;

        if (\is_array($stringToSign)) {
            $stringToSign = implode($sep, $varToSign) . $sep;
        }

        $secretToUse = $secret;

        if ($secretToUse === null) {
            $secretToUse = $this->config['secret'];
        }

        return base64_encode(hash_hmac('sha256', $stringToSign, $secretToUse, true));
    }

    /**
     * @param array|string varToSign
     * @param array|string $secretData
     */
    public function signWithSecretData($varToSign, $secretData): string
    {
        if (is_array($secretData)) {
            $secret = $secretData['secret'];
            $sep    = $secretData['separator'] ?? $secretData['sep'] ?? "\n";
        } else {
            $secret = $secretData;
            $sep    = "\n";
        }

        return $this->sign($varToSign, $secret, $sep);
    }
}
