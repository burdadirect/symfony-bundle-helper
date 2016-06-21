<?php

namespace HBM\HelperBundle\Services;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Routing\Router;

class Blitline
{

  use ContainerAwareTrait;

  /** @var array */
  private $config;

  /** @var \HBM\HelperBundle\Services\S3 */
  private $s3;

  /** @var \HBM\HelperBundle\Services\Hmac */
  private $hmac;

  /** @var \Symfony\Component\Routing\Router */
  private $router;

  public function __construct($config, S3 $s3, Hmac $hmac, Router $router) {
    $this->config = $config;
    $this->s3 = $s3;
    $this->hmac = $hmac;
    $this->router = $router;
  }

  public function screenshot($postbackData, $url, $path, $viewport = '1200x800', $delay = 2000) {
    $request = [
      'src' => $url,
      'src_type' => "screen_shot_url",
      'src_data' => [
        'viewport' => $viewport,
        'delay' => $delay
      ],
      'functions' => [
        [
          'name' => 'no_op',
          'params' => [
            'quality' => 100
          ],
          'save' => [
            'image_identifier' => md5($path),
            's3_destination' => [
              'signed_url' => $this->s3->getPreSignedUrlForWriting($path),
              /*
              'headers' => [
                'x-amx-acl' => 'public-read'
              ]
              */
            ],
          ]
        ]
      ]
    ];

    $result = $this->process($postbackData, $request);

    return [
      'request' => $request,
      'result' => $result
    ];
  }

  public function process($postbackData, &$request) {
    $request['application_id'] = $this->config['appid'];
    if ($this->config['postback']['url'] && $this->config['postback']['route']) {
      $request['postback_url'] = $this->config['postback']['url'].$this->router->generate($this->config['postback']['route'], [
        'data' => json_encode($postbackData),
        'hmac' => urlencode($this->hmac->sign(json_encode($postbackData)))
      ]);
    }

    $http_query = http_build_query(['json' => json_encode($request)]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://api.blitline.com/job');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $http_query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return json_decode(curl_exec($ch), TRUE);
  }

}
