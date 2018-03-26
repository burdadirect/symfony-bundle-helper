<?php

namespace HBM\HelperBundle\Services;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class S3Helper
{

  use ContainerAwareTrait;

  /** @var array */
  private $config;

  /** @var S3Client */
  private $s3client;

  /** @var string */
  private $name = 'default';

  public function __construct($config) {
    $this->config = $config;
  }

  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  public function getName($name) : string {
    if ($name === NULL) {
      $name = $this->name;
    }

    if (!isset($this->config[$name])) {
      return 'default';
    }

    return $name;
  }

  public function getConfig($name) {
    return $this->config[$this->getName($name)] ?? NULL;
  }

  public function getConfigValue($name, $key) : ?string {
    return $this->config[$this->getName($name)][$key] ?? NULL;
  }

  /**
   * @param null $name
   *
   * @return S3Client
   *
   * @throws \InvalidArgumentException
   */
  public function getClient($name = NULL) : S3Client {
    if ($this->s3client === NULL) {
      $this->s3client = new S3Client([
        'credentials' => [
          'key' => $this->getConfigValue($name, 'key'),
          'secret' => $this->getConfigValue($name, 'secret'),
        ],
        'region' => $this->getConfigValue($name, 'region'),
        'version' => 'latest',
        'website' => ''
        //'ACL' => 'public-read',
      ]);
    }

    return $this->s3client;
  }

  /**
   * @param null $name
   *
   * @return null|string
   *
   * @throws \InvalidArgumentException
   */
  public function getUploadRoot($name = NULL) : ?string {
    if ($this->getConfigValue($name, 'key') && $this->getConfigValue($name, 'secret')) {
      return $this->getUploadRootS3($name);
    }

    return $this->getUploadRootLocal($name);
  }

  public function getUploadRootLocal($name = NULL) : ?string {
    return $this->getConfigValue($name, 'local');
  }

  /**
   * @param string|null $name
   *
   * @return string
   *
   * @throws \InvalidArgumentException
   */
  public function getUploadRootS3($name = NULL) : string {
    $this->getClient()->registerStreamWrapper();

    return 's3://'.$this->getConfigValue($name, 'bucket').'/';
  }

  /**
   * @param $path
   * @param string|null $name
   *
   * @throws \InvalidArgumentException
   */
  public function makeFilePublic($path, $name = NULL) : void {
    $this->getClient()->putObjectAcl([
      'Bucket' => $this->getConfigValue($name, 'bucket'),
      'Key'    => $path,
      'ACL'    => 'public-read',
    ]);
  }

  /**
   * @param $path
   * @param null $name
   *
   * @throws \InvalidArgumentException
   */
  public function makeFilePrivate($path, $name = NULL) : void {
    $this->getClient()->putObjectAcl([
      'Bucket' => $this->getConfigValue($name, 'bucket'),
      'Key'    => $path,
      'ACL'    => 'private',
    ]);
  }

  /**
   * @param $path
   * @param string $duration
   * @param string|null $name
   *
   * @return string
   *
   * @throws \InvalidArgumentException
   */
  public function getPreSignedUrlForReading($path, $duration = '+20 minutes', $name = NULL) : string {
    return $this->getPreSignedUrl($path, 'GetObject', $duration, $name);
  }

  /**
   * @param $path
   * @param string $duration
   * @param string|null $name
   *
   * @return string
   *
   * @throws \InvalidArgumentException
   */
  public function getPreSignedUrlForWriting($path, $duration = '+20 minutes', $name = NULL) : string {
    return $this->getPreSignedUrl($path, 'PutObject', $duration, $name);
  }

  /**
   * @param $path
   * @param $action
   * @param string $duration
   * @param string|null $name
   *
   * @return string
   *
   * @throws \InvalidArgumentException
   */
  public function getPreSignedUrl($path, $action, $duration = '+20 minutes', $name = NULL) : string {
    $cmd = $this->getClient()->getCommand($action, [
      'Bucket' => $this->getConfigValue($name, 'bucket'),
      'Key'    => $path
    ]);

    $request = $this->getClient()->createPresignedRequest($cmd, $duration);

    return (string) $request->getUri();
  }

}
