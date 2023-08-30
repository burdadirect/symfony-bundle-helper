<?php

namespace HBM\HelperBundle\Service;

use Aws\S3\S3Client;

class S3Helper
{
    /** @var array */
    private $config;

    /** @var S3Client */
    private $s3client;

    /** @var string */
    private $name = 'default';

    /**
     * S3Helper constructor.
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName($name): string
    {
        if ($name === null) {
            $name = $this->name;
        }

        if (!isset($this->config[$name])) {
            return 'default';
        }

        return $name;
    }

    /**
     * @return null|mixed
     */
    public function getConfig($name)
    {
        return $this->config[$this->getName($name)] ?? null;
    }

    public function getConfigValue($name, $key): ?string
    {
        return $this->config[$this->getName($name)][$key] ?? null;
    }

    /**
     * @param null $name
     *
     * @throws \InvalidArgumentException
     */
    public function getClient($name = null): S3Client
    {
        if ($this->s3client === null) {
            $this->s3client = new S3Client([
              'credentials' => [
                'key'    => $this->getConfigValue($name, 'key'),
                'secret' => $this->getConfigValue($name, 'secret'),
              ],
              'region'  => $this->getConfigValue($name, 'region'),
              'version' => 'latest',
              'website' => '',
              // 'ACL' => 'public-read',
            ]);
        }

        return $this->s3client;
    }

    /**
     * @param null $name
     *
     * @throws \InvalidArgumentException
     */
    public function getUploadRoot($name = null): ?string
    {
        if ($this->getConfigValue($name, 'key') && $this->getConfigValue($name, 'secret')) {
            return $this->getUploadRootS3($name);
        }

        return $this->getUploadRootLocal($name);
    }

    public function getUploadRootLocal($name = null): ?string
    {
        return $this->getConfigValue($name, 'local');
    }

    /**
     * @param null|string $name
     *
     * @throws \InvalidArgumentException
     */
    public function getUploadRootS3($name = null): string
    {
        $this->getClient()->registerStreamWrapper();

        return 's3://' . $this->getConfigValue($name, 'bucket') . '/';
    }

    /**
     * @param null|string $name
     *
     * @throws \InvalidArgumentException
     */
    public function makeFilePublic($path, $name = null): void
    {
        $this->getClient()->putObjectAcl([
          'Bucket' => $this->getConfigValue($name, 'bucket'),
          'Key'    => $path,
          'ACL'    => 'public-read',
        ]);
    }

    /**
     * @param null $name
     *
     * @throws \InvalidArgumentException
     */
    public function makeFilePrivate($path, $name = null): void
    {
        $this->getClient()->putObjectAcl([
          'Bucket' => $this->getConfigValue($name, 'bucket'),
          'Key'    => $path,
          'ACL'    => 'private',
        ]);
    }

    /**
     * @param string      $duration
     * @param null|string $name
     *
     * @throws \InvalidArgumentException
     */
    public function getPreSignedUrlForReading($path, $duration = '+20 minutes', $name = null): string
    {
        return $this->getPreSignedUrl($path, 'GetObject', $duration, $name);
    }

    /**
     * @param string      $duration
     * @param null|string $name
     *
     * @throws \InvalidArgumentException
     */
    public function getPreSignedUrlForWriting($path, $duration = '+20 minutes', $name = null): string
    {
        return $this->getPreSignedUrl($path, 'PutObject', $duration, $name);
    }

    /**
     * @param string      $duration
     * @param null|string $name
     *
     * @throws \InvalidArgumentException
     */
    public function getPreSignedUrl($path, $action, $duration = '+20 minutes', $name = null): string
    {
        $cmd = $this->getClient()->getCommand($action, [
          'Bucket' => $this->getConfigValue($name, 'bucket'),
          'Key'    => $path,
        ]);

        $request = $this->getClient()->createPresignedRequest($cmd, $duration);

        return (string) $request->getUri();
    }
}
