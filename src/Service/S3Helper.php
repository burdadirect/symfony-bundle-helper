<?php

namespace HBM\HelperBundle\Service;

use Aws\S3\S3Client;

class S3Helper
{
    private array $config;
    private S3Client $s3client;
    private string $name = 'default';

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(?string $name): string
    {
        if ($name === null) {
            $name = $this->name;
        }

        if (!isset($this->config[$name])) {
            return 'default';
        }

        return $name;
    }

    public function getConfig(string $name): mixed
    {
        return $this->config[$this->getName($name)] ?? null;
    }

    public function getConfigValue(string $name, string $key): mixed
    {
        return $this->config[$this->getName($name)][$key] ?? null;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getClient(?string $name = null): S3Client
    {
        if (!isset($this->s3client)) {
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
     * @throws \InvalidArgumentException
     */
    public function getUploadRoot(?string $name = null): ?string
    {
        if ($this->getConfigValue($name, 'key') && $this->getConfigValue($name, 'secret')) {
            return $this->getUploadRootS3($name);
        }

        return $this->getUploadRootLocal($name);
    }

    public function getUploadRootLocal(?string $name = null): ?string
    {
        return $this->getConfigValue($name, 'local');
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getUploadRootS3(?string $name = null): string
    {
        $this->getClient()->registerStreamWrapper();

        return 's3://' . $this->getConfigValue($name, 'bucket') . '/';
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function makeFilePublic(string $path, ?string $name = null): void
    {
        $this->getClient()->putObjectAcl([
          'Bucket' => $this->getConfigValue($name, 'bucket'),
          'Key'    => $path,
          'ACL'    => 'public-read',
        ]);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function makeFilePrivate(string $path, ?string $name = null): void
    {
        $this->getClient()->putObjectAcl([
          'Bucket' => $this->getConfigValue($name, 'bucket'),
          'Key'    => $path,
          'ACL'    => 'private',
        ]);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getPreSignedUrlForReading(string $path, string $duration = '+20 minutes', ?string $name = null): string
    {
        return $this->getPreSignedUrl($path, 'GetObject', $duration, $name);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getPreSignedUrlForWriting(string $path, string $duration = '+20 minutes', ?string $name = null): string
    {
        return $this->getPreSignedUrl($path, 'PutObject', $duration, $name);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getPreSignedUrl(string $path, string $action, string $duration = '+20 minutes', ?string $name = null): string
    {
        $cmd = $this->getClient()->getCommand($action, [
          'Bucket' => $this->getConfigValue($name, 'bucket'),
          'Key'    => $path,
        ]);

        $request = $this->getClient()->createPresignedRequest($cmd, $duration);

        return (string) $request->getUri();
    }
}
