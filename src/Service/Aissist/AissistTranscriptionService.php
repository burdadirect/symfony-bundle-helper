<?php

namespace HBM\HelperBundle\Service\Aissist;

use HBM\HelperBundle\Util\Result\ResultAissist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AissistTranscriptionService extends AbstractAissistService
{

    /**
     * @return ResultAissist{
     *     transcript_id: string,
     *     upload_mode: string,
     *     file_path: string,
     *     expires_in_seconds: int,
     *     upload_url: string,
     *     upload_id: string,
     *     part_urls: list<array{
     *         part_number: int,
     *         url: string
     *     }>,
     *     part_size: int
     * }
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function initUpload(string $filename, int $fileSize, string $contentType, ?int $numSpeakers = null, ?string $language = null, ?bool $diarization = true, ?bool $tagAudioEvents = true): ResultAissist
    {
        $body = [
          'filename' => $filename,
          'file_size' => $fileSize,
          'content_type' => $contentType,
          'language' => $language,
          'diarization' => $diarization,
          'tag_audio_events' => $tagAudioEvents,
          'num_speakers' => $numSpeakers,
        ];

        $response = $this->request('POST', '/v1/transcript/upload/init', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_CREATED], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function executeUpload(string $filepath, string $uploadMode, ?string $uploadUrl, ?array $partUrls, ?int $partSize): ResultAissist
    {
        assert(is_file($filepath));

        if ($uploadMode === 'single') {
            return $this->executeUploadSingle($filepath, $uploadUrl);
        }

        if ($uploadMode === 'multipart') {
            return $this->executeUploadMultipart($filepath, $partUrls, $partSize);
        }

        throw new \RuntimeException('No valid upload mode used.');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function executeUploadSingle(string $filepath, string $uploadUrl): ResultAissist {
        return $this->doUpload($uploadUrl, filesize($filepath), fopen($filepath, 'rb'));
    }

    /**
     * TODO: verify multipart upload functionality, it should work like this but it is not tested
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function executeUploadMultipart(string $filepath, array $partUrls, int $partSize): ResultAissist {
        //execute s3 multipart upload using given part urls
        // if successful each part gets an etag that is needed to follow up the upload complete api call
        // [["part_number": 1, "etag": "\"abc123def456\""], [...]]

        $results = new ResultAissist();

        try {
            $fileSize = filesize($filepath);
            $file = fopen($filepath, 'rb');

            foreach ($partUrls as $part) {
                assert(array_key_exists('part_number', $part));
                assert(array_key_exists('url', $part));

                $partNumber = $part['part_number'];
                $remainingBytes = $fileSize - (($partNumber - 1) * $partSize);
                $currentPartSize = min($partSize, $remainingBytes);

                $partStream = fopen('php://temp', 'w+b');

                while ($remainingBytes > 0 && !feof($file)) {
                    $chunk = fread($file, $currentPartSize);
                    fwrite($partStream, $chunk);
                }

                rewind($partStream);

                $resultPartial = $this->doUpload($part['url'], $currentPartSize, $partStream);
                $resultPartial->setData([
                  'part_number' => $part['part_number'],
                  'etag' => $resultPartial->getResponse()->getHeaders(false)['etag'][0] ?? null,
                ]);

                $results[] = $resultPartial;
            }
        } finally {
            fclose($partStream);
        }

        if (array_any($results->getData(), static fn(ResultAissist $resultPartial) => !$resultPartial->getReturn())) {
            return $results->setReturn(false);
        }

        return $results->setReturn(true);
    }

    /**
     * @return ResultAissist{
     *     transcript_id: string,
     *     upload_id: string,
     *     parts: list<array{
     *         part_number: int,
     *         etag: string
     *     }>
     * }
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function completeUpload(string $transcriptId, string $uploadMode, ?string $uploadId, ?array $partEtags): ResultAissist
    {
        $body = [
          'transcript_id' => $transcriptId,
        ];

        if ($uploadMode === 'multipart') {
            assert(is_string($uploadId));
            assert(is_array($partEtags));
            $body['upload_id'] = $uploadId;
            //expecting array with multiple entries containing part_number(int) and etag(string) keys
            $body['parts'] = $partEtags;
        }

        $response = $this->request('POST', '/v1/transcript/upload/complete', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_OK], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @return ResultAissist{
     *     id: string,
     *     user: string,
     *     status: string,
     *     provider: string,
     *     language: string,
     *     file_path: string,
     *     status_url: string,
     *     remote_url: string,
     *     diarization: bool,
     *     num_speakers: int,
     *     tag_audio_events: bool,
     *     text: string,
     *     phrases: list<array{
     *         type: string,
     *         speaker: int,
     *         offset: float|int,
     *         duration: float|int,
     *         text: string
     *     }>,
     *     error_details: string,
     *     created_by: string,
     *     modified_by: string,
     *     created_at: string,
     *     modified_at: string
     * }
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getTranscript(string $transcriptId): ResultAissist
    {
        $response = $this->request('GET', ['/v1/transcript/%s', [$transcriptId]]);

        return $this->handleResponse($response, [Response::HTTP_OK], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @return ResultAissist{
     *      id: string,
     *      user: string,
     *      status: string,
     *      provider: string,
     *      language: string,
     *      file_path: string,
     *      status_url: string,
     *      remote_url: string,
     *      diarization: bool,
     *      num_speakers: int,
     *      tag_audio_events: bool,
     *      text: string,
     *      phrases: list<array{
     *          type: string,
     *          speaker: int,
     *          offset: float|int,
     *          duration: float|int,
     *          text: string
     *      }>,
     *      error_details: string,
     *      created_by: string,
     *      modified_by: string,
     *      created_at: string,
     *      modified_at: string
     *  }
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function pollTranscript(string $transcriptId, int $retriesRemaining = 10, int $retryDelay = 3, ?callable $retryCallback = null): ResultAissist {
        $result = $this->getTranscript($transcriptId);
        if (($retriesRemaining <= 0) || !$result->getReturn() || ($result['status'] === 'Succeeded')) {
            return $result;
        }

        if ($retryCallback) {
            $retryCallback($retriesRemaining, $retryDelay);
        }

        sleep($retryDelay);
        return $this->pollTranscript($transcriptId, $retriesRemaining - 1, $retryDelay, $retryCallback);
    }

    /**
     * Only needed to be called for multipart uploads (single uploads don't receive an upload_id)
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deleteUpload(string $transcriptId, string $uploadId): ResultAissist
    {
        $body = [
          'transcript_id' => $transcriptId,
          'upload_id' => $uploadId,
        ];

        $response = $this->request('DELETE', '/v1/transcript/upload/abort', ['json' => $body]);

        return $this->handleResponse($response, [Response::HTTP_NO_CONTENT], __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function deleteTranscript(string $transcriptId): ResultAissist
    {
        $response = $this->request('DELETE', ['/v1/transcript/%s', [$transcriptId]]);

        return $this->handleResponse($response, [Response::HTTP_NO_CONTENT], __CLASS__, __METHOD__, __LINE__);
    }

}
