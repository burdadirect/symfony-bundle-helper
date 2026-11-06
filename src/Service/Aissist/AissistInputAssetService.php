<?php

namespace HBM\HelperBundle\Service\Aissist;

use HBM\BasicsBundle\Util\Pinpoint;
use HBM\HelperBundle\Util\Result\ResultAissist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
    public function initUpload(string $filename, ?int $fileSize = null, ?string $contentType = null, ?string $sessionId = null): ResultAissist
    {
        $body = [
          'filename' => basename($filename),
          'file_size' => $fileSize ?? filesize($filename),
          'content_type' => $contentType ?? mime_content_type($filename),
          'session_id' => $sessionId ?? null,
        ];

        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('POST', '/v1/input-asset/upload/init', ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_CREATED], $pinpoint->setLine(__LINE__));
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

        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('POST', '/v1/input-asset/upload/complete', ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
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

        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('DELETE', '/v1/input-asset/upload/abort', ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_NO_CONTENT], $pinpoint->setLine(__LINE__));
    }

}
