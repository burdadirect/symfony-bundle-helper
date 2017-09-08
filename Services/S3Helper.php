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

  public function getName($name) {
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

  public function getConfigValue($name, $key) {
    return $this->config[$this->getName($name)][$key] ?? NULL;
  }

  public function getClient($name = NULL) {
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

  public function getUploadRoot($name = NULL) {
    if ($this->getConfigValue($name, 'key') && $this->getConfigValue($name, 'secret')) {
      return $this->getUploadRootS3($name);
    }

    return $this->getUploadRootLocal($name);
  }

  public function getUploadRootLocal($name = NULL) {
    return $this->getConfigValue($name, 'local');
  }

  public function getUploadRootS3($name = NULL) {
    $this->getClient()->registerStreamWrapper();

    return 's3://'.$this->getConfigValue($name, 'bucket').'/';
  }

  public function makeFilePublic($path, $name = NULL) {
    $this->getClient()->putObjectAcl([
      'Bucket' => $this->getConfigValue($name, 'bucket'),
      'Key'    => $path,
      'ACL'    => 'public-read',
    ]);
  }

  public function makeFilePrivate($path, $name = NULL) {
    $this->getClient()->putObjectAcl([
      'Bucket' => $this->getConfigValue($name, 'bucket'),
      'Key'    => $path,
      'ACL'    => 'private',
    ]);
  }

  public function getPreSignedUrlForReading($path, $duration = '+20 minutes', $name = NULL) {
    return $this->getPreSignedUrl($path, 'GetObject', $duration, $name);
  }

  public function getPreSignedUrlForWriting($path, $duration = '+20 minutes', $name = NULL) {
    return $this->getPreSignedUrl($path, 'PutObject', $duration, $name);
  }

  public function getPreSignedUrl($path, $action, $duration = '+20 minutes', $name = NULL) {
    $cmd = $this->getClient()->getCommand($action, [
      'Bucket' => $this->getConfigValue($name, 'bucket'),
      'Key'    => $path
    ]);

    $request = $this->getClient()->createPresignedRequest($cmd, $duration);

    return (string) $request->getUri();
  }

}
