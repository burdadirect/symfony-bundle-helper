<?php

namespace HBM\HelperBundle\Service\Screenshot;

class WebshrinkerHelper {

  /**
   * @var array
   */
  private $config;

  /**
   * WebshrinkerHelper constructor.
   *
   * @param $config
   */
  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * @param $url
   * @param $path
   * @param array $options
   * @param null $statusCode
   *
   * @return bool|string
   */
  public function screenshot($url, $path, array $options = [], &$statusCode = NULL) {
    $requestUrl = $this->buildRequestUrlScreenshot($url, $options);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($statusCode === 200) {
      file_put_contents($path, $response);
    }

    return $response;
  }

  /**
   * @param $url
   * @param array $options
   * @param null $statusCode
   *
   * @return mixed
   */
  public function info($url, array $options = [], &$statusCode = NULL) {
    $requestUrl = $this->buildRequestUrlInfo($url, $options);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    return json_decode($response, TRUE);
  }

  /**
   * @param $statusCode
   *
   * @return string
   */
  public function statusCodeToError($statusCode) : string {
    switch($statusCode) {
      case 200:
        // $image_data contains the screenshot of the site
        // Do something with the image
        return 'The screenshot was saved.';
      case 202:
        // The website is being visited and the screenshot created
        // $image_data contains the placeholder loading image
        return 'The screenshot is being created, the placeholder image was saved.';
      case 400:
        // Bad or malformed HTTP request
        return 'Bad or malformed HTTP request.';
      case 401:
        // Unauthorized
        return 'Unauthorized - check your access and secret key permissions.';
      case 402:
        // Request limit reached
        return 'Account request limit reached.';
    }

    return 'n/a';
  }

  /**
   * @param $url
   * @param $options
   *
   * @return string
   */
  private function buildRequestUrlScreenshot($url, $options) : string {
    return $this->buildRequestUrl('thumbnails/v2/%s?%s', $url, $options);
  }

  /**
   * @param $url
   * @param $options
   *
   * @return string
   */
  private function buildRequestUrlInfo($url, $options) : string {
    return $this->buildRequestUrl('thumbnails/v2/%s/info?%s', $url, $options);
  }

  /**
   * @param $requestPath
   * @param $url
   * @param $options
   *
   * @return string
   */
  private function buildRequestUrl($requestPath, $url, $options) : string {
    $accessKey = $this->config['access_key'];
    $secretKey = $this->config['secret_key'];

    $options['key'] = $accessKey;

    $parameters = http_build_query($options);

    $request = sprintf($requestPath, base64_encode($url), $parameters);
    $hash = md5(sprintf('%s:%s', $secretKey, $request));

    return 'https://api.webshrinker.com/'.$request.'&hash='.$hash;
  }

}
