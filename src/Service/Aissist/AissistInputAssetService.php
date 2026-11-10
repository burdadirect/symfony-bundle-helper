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

/**
 * @phpstan-type inputAsset array{
 *     id: string,
 *     session_id: string,
 *     modified_at: string,
 *     created_at: string,
 *     type: string,
 *     dest: string,
 *     origin: string,
 *     source: string,
 *     sort: int,
 *     filename: string,
 *     content_type: string,
 *     size: int,
 *     checksum: string,
 *     created_by: string,
 *     previews: array<string, string>,
 *     source_meta_data: array<string, mixed>,
 *     attachment_status: string,
 *     sequence_ids: list<string>
 * }
 */
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
     *     input_asset_id: string
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

    /**************************************************************************/

    /**
     * @return ResultAissist{inputAsset}
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getInputAsset(string $inputAssetId): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('GET', ['/v1/input-asset/%s', [$inputAssetId]], [], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

    /**
     * @return ResultAissist{inputAsset}
     *
     * pending_upload -> in_validation -> processing -> ready_for_context
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function pollInputAsset(string $inputAssetId, int $retriesRemaining = 10, int $retryDelay = 3, ?callable $retryCallback = null): ResultAissist {
        $result = $this->getInputAsset($inputAssetId);
        if (($retriesRemaining <= 0) || !$result->getReturn() || ($result['attachment_status'] === 'ready_for_context')) {
            return $result;
        }

        if ($retryCallback) {
            $retryCallback($retriesRemaining, $retryDelay);
        }

        sleep($retryDelay);
        return $this->pollInputAsset($inputAssetId, $retriesRemaining - 1, $retryDelay, $retryCallback);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function deleteInputAsset(string $inputAssetId): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('DELETE', ['/v1/input-asset/%s', [$inputAssetId]], [], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_NO_CONTENT], $pinpoint->setLine(__LINE__));
    }

}
