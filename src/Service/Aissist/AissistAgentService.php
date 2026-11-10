<?php

namespace HBM\HelperBundle\Service\Aissist;

use HBM\BasicsBundle\Util\Pinpoint;
use HBM\HelperBundle\Util\Result\ResultAissist;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @phpstan-type agentMessageTextPart array{
 *     kind: 'text',
 *     text: string
 * }
 *
 * @phpstan-type agentMessageDataPart array{
 *     kind: 'data',
 *     data: array{
 *         type: 'file',
 *         attrs: array{
 *             uri: string,
 *             mediaType: string,
 *             filename: string
 *         }
 *     }
 * }
 *
 * @phpstan-type agentParams array{
 *      message: array{
 *          kind: 'message',
 *          role: 'user',
 *          messageId: string,
 *          parts: list<agentMessageTextPart|agentMessageDataPart>
 *      },
 *      metadata: array{
 *          templateId: string,
 *          skipGlobalSystemPrompt: bool
 *      }
 *  }
 */
class AissistAgentService extends AbstractAissistService
{

    /**
     * @param list<agentMessageTextPart|agentMessageDataPart> $messageParts
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function sendMessageToTemplate(string $templateId, array $messageParts, string $agentName = 'general-purpose'): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $body = [
          'jsonrpc' => '2.0',
          'id' => Uuid::uuid4()->toString(),
          'method' => 'message/send',
          'params' => [
            'message' => [
              'kind' => 'message',
              'role' => 'user',
              'messageId' => Uuid::uuid4()->toString(),
              'parts' => $messageParts,
            ],
            'metadata' => [
              'templateId' => $templateId,
                # The template is our complete instruction set, do not add the
                # platform-wide default system prompt on top.
              'skipGlobalSystemPrompt' => true,
            ]
          ],
        ];

        $response = $this->request('POST', ['/agents/%s/', [$agentName]], ['json' => $body], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

}
