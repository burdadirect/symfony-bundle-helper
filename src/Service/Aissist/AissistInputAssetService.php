<?php

namespace HBM\HelperBundle\Service\Aissist;

use HBM\HelperBundle\Util\Result\ResultAissist;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AissistInputAssetService extends AbstractAissistService
{

    /**
     * @return ResultAissist{
     *     input_asset_id: string,
     *     file_path: string,
     *     expires_in_seconds: int,
     *     upload_url: string
     * }
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function initUpload(string $filename, ?int $fileSize = null, ?string $contentType = null): ResultAissist
    {
        $body = [
          'filename' => basename($filename),
          'file_size' => $fileSize ?? filesize($filename),
          'content_type' => $contentType ?? mime_content_type($filename),
        ];

        $response = $this->request('POST', '/v1/input-asset/upload/init', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_CREATED], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function executeUpload(string $filepath, string $uploadUrl): ResultAissist {
        return $this->doUpload($uploadUrl, filesize($filepath), fopen($filepath, 'rb'));
    }

    /**
     * @return ResultAissist{
     *     transcript_id: string
     * }
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function completeUpload(string $inputAssetId): ResultAissist
    {
        $body = [
          'input_asset_id' => $inputAssetId,
        ];

        $response = $this->request('POST', '/v1/input-asset/upload/complete', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_OK], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function deleteUpload(string $inputAssetId): ResultAissist
    {
        $body = [
          'input_asset_id' => $inputAssetId,
        ];

        $response = $this->request('DELETE', '/v1/input-asset/upload/abort', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_NO_CONTENT], __CLASS__, __METHOD__, __LINE__);
    }

}
