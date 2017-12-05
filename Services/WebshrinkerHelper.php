<?php

namespace HBM\HelperBundle\Services;

class WebshrinkerHelper {

  /** @var array */
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  public function screenshot($url, $path, $options = []) {
    $requestUrl = $this->buildRequestUrlScreenshot($url, $options);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($statusCode === 200) {
      file_put_contents($path, $response);
    }

    return $statusCode;
  }

  public function info($url, $options = []) {
    $requestUrl = $this->buildRequestUrlInfo($url, $options);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($statusCode === 200) {
      return json_decode($response, TRUE);
    }

    return $statusCode;
  }

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

  private function buildRequestUrlScreenshot($url, $options) {
    return $this->buildRequestUrl('thumbnails/v2/%s?%s', $url, $options);
  }

  private function buildRequestUrlInfo($url, $options) {
    return $this->buildRequestUrl('thumbnails/v2/%s/info?%s', $url, $options);
  }

  private function buildRequestUrl($requestPath, $url, $options) {
    $accessKey = $this->config['access_key'];
    $secretKey = $this->config['secret_key'];

    $options['key'] = $accessKey;

    $parameters = http_build_query($options);

    $request = sprintf($requestPath, base64_encode($url), $parameters);
    $hash = md5(sprintf('%s:%s', $secretKey, $request));

    return 'https://api.webshrinker.com/'.$request.'&hash='.$hash;
  }

}
