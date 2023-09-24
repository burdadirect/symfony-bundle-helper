<?php

namespace HBM\HelperBundle\Service\Screenshot;

use HBM\HelperBundle\Service\HmacHelper;
use HBM\HelperBundle\Service\S3Helper;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class BlitlineHelper
{
    /** @var array */
    private $config;

    /** @var S3Helper */
    private $s3;

    /** @var HmacHelper */
    private $hmac;

    /** @var RouterInterface */
    private $router;

    /**
     * BlitlineHelper constructor.
     */
    public function __construct($config, S3Helper $s3, HmacHelper $hmac, RouterInterface $router)
    {
        $this->config = $config;
        $this->s3     = $s3;
        $this->hmac   = $hmac;
        $this->router = $router;
    }

    /**
     * @param string $viewport
     * @param int    $delay
     *
     * @throws InvalidParameterException
     * @throws RouteNotFoundException
     * @throws MissingMandatoryParametersException
     */
    public function screenshot($postbackData, $url, $path, $viewport = '1200x800', $delay = 2000): array
    {
        $request = [
          'src'      => $url,
          'src_type' => 'screen_shot_url',
          'src_data' => [
            'viewport' => $viewport,
            'delay'    => $delay,
          ],
          'functions' => [
            [
              'name'   => 'no_op',
              'params' => [
                'quality' => 100,
              ],
              'save' => [
                'image_identifier' => md5($path),
                's3_destination'   => [
                  'signed_url' => $this->s3->getPreSignedUrlForWriting($path),
                  /*
                  'headers' => [
                    'x-amx-acl' => 'public-read'
                  ]
                  */
                ],
              ],
            ],
          ],
        ];

        $result = $this->process($postbackData, $request);

        return [
          'request' => $request,
          'result'  => $result,
        ];
    }

    /**
     * @throws InvalidParameterException
     * @throws RouteNotFoundException
     * @throws MissingMandatoryParametersException
     */
    public function process($postbackData, &$request)
    {
        $request['application_id'] = $this->config['appid'];

        if ($this->config['postback']['url'] && $this->config['postback']['route']) {
            $request['postback_url'] = $this->config['postback']['url'] . $this->router->generate($this->config['postback']['route'], [
              'data' => json_encode($postbackData),
              'hmac' => urlencode($this->hmac->sign(json_encode($postbackData))),
            ]);
        }

        $http_query = http_build_query(['json' => json_encode($request)]);
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.blitline.com/job');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $http_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return json_decode(curl_exec($ch), true);
    }
}
