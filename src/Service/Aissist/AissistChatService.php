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
 * WORK IN PROGRESS!
 * 
 * @phpstan-type options array{
 *     temperature: float|int,
 *     max_tokens: int,
 *     top_p: float|int,
 *     frequency_penalty: float|int,
 *     presence_penalty: float|int,
 *     reasoning_effort: string,
 *     extras: list<extra>,
 *     tools: string[],
 * }
 *
 * @phpstan-type chatSession array{
 *     id: string,
 *     type: string,
 *     user: string,
 *     label: string,
 *     provider: string,
 *     temperature: float|int,
 *     max_tokens: int,
 *     top_p: float|int,
 *     frequency_penalty: float|int,
 *     presence_penalty: float|int,
 *     reasoning_effort: string,
 *     system_prompt: string,
 *     extras: list<extra>,
 *     tools: list<tool>,
 *     usecase_id: string,
 *     input_assets: list<inputAsset>,
 *     knowledge_base_id: string,
 *     deprecated: bool,
 *     created_at: string,
 *     modified_at: string
 * }
 *
 * @phpstan-type extra array{
 *     key: string,
 *     value: string
 * }
 *
 * @phpstan-type tool array{
 *     id: string,
 *     name: string,
 *     description: string,
 *     binding: string,
 *     mcp_servers: list<array{
 *         name: string,
 *         whitelisted_mcp_tools: list<string>
 *     }>,
 *     created_by: string,
 *     created_at: string,
 *     modified_at: string,
 *     modified_by: string
 * }
 *
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
class AissistChatService extends AbstractAissistChatService
{

    /**
     * @param $options options
     *
     * @return ResultAissist{chatSession}
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function createChatSession(string $type = 'text', ?string $label = null, ?string $provider = null, ?string $systemPrompt = null, ?string $usecaseId = null, array $options = []): ResultAissist
    {
        $body = [
          'type' => $type,
          'label' => $label ?? 'Chat '.date('Y-m-d H:i:s'),
          'provider' => $provider ?? $this->getProviderToUse(),
          'system_prompt' => $systemPrompt,
          'usecase_id' => $usecaseId,
          'reasoning_effort' => 'low',
        ];

        $body = array_merge($options, $body);
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('POST', '/v1/chat-session/', ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

  /**
   * @return ResultAissist{list<chatSession>}
   *
   * @throws TransportExceptionInterface
   * @throws ServerExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws DecodingExceptionInterface
   * @throws ClientExceptionInterface
   */
  public function getChatSessions(int $skip = 0, int $limit = 100, int $sequenceLimit = 100): ResultAissist
  {
    $query = [
      'skip' => $skip,
      'limit' => $limit,
      'sequenceLimit' => $sequenceLimit,
    ];

    $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

    $response = $this->request('GET', '/v1/chat-session/', ['query' => $query], $pinpoint->setLine(__LINE__));

    return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
  }

    /**
     * @param $options options
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function postChatMessage(string $sessionId, string $role, string $message, array $options = []): ResultAissist
    {
        $body = [
          'role' => $role,
          'message' => $message,
        ];

        $body = array_merge($options, $body);
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('POST', ['/v1/chat/%s', [$sessionId]], ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getChatMessages(string $sessionId): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('GET', ['/v1/chat/%s', [$sessionId]], [], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

}
