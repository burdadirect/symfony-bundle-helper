<?php

namespace HBM\HelperBundle\Services;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class S3
{

  use ContainerAwareTrait;

  /** @var array */
  private $config;

  /** @var S3Client */
  private $s3client;

  public function __construct($config) {
    $this->config = $config;
  }

  public function getClient() {
    if ($this->s3client === NULL) {
      $this->s3client = new S3Client([
        'credentials' => [
          'key' => $this->config['key'],
          'secret' => $this->config['secret'],
        ],
        'region' => $this->config['region'],
        'version' => 'latest',
        //'ACL' => 'public-read',
      ]);
    }

    return $this->s3client;
  }

  public function getUploadRoot() {
    if ($this->config['key'] && $this->config['secret']) {
      return $this->getUploadRootS3();
    } else {
      return $this->getUploadRootLocal();
    }
  }

  public function getUploadRootLocal() {
    return $this->config['local'];
  }

  public function getUploadRootS3() {
    $this->getClient()->registerStreamWrapper();

    return 's3://'.$this->config['bucket'].'/';
  }

  public function makeFilePublic($path) {
    $this->getClient()->putObjectAcl([
      'Bucket' => $this->config['bucket'],
      'Key'    => $path,
      'ACL'    => 'public-read',
    ]);
  }

  public function makeFilePrivate($path) {
    $this->getClient()->putObjectAcl([
      'Bucket' => $this->config['bucket'],
      'Key'    => $path,
      'ACL'    => 'private',
    ]);
  }

  public function getPreSignedUrlForReading($path, $duration = '+20 minutes') {
    return $this->getPreSignedUrl($path, 'GetObject', $duration);
  }

  public function getPreSignedUrlForWriting($path, $duration = '+20 minutes') {
    return $this->getPreSignedUrl($path, 'PutObject', $duration);
  }

  public function getPreSignedUrl($path, $action, $duration = '+20 minutes') {
    $cmd = $this->getClient()->getCommand($action, [
      'Bucket' => $this->config['bucket'],
      'Key'    => $path
    ]);

    $request = $this->getClient()->createPresignedRequest($cmd, $duration);

    return (string) $request->getUri();
  }

}
