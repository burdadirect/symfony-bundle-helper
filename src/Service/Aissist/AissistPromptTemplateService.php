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
 * @phpstan-type promptTemplateData array{
 *     name: string,
 *     type: string,
 *     description?: string,
 *     hint?: string,
 *     system_prompt?: string,
 *     user_prompt?: string,
 *     output_example?: string,
 *     provider: string,
 *     temperature?: float,
 *     max_tokens?: int,
 *     top_p?: float,
 *     presence_penalty?: float,
 *     frequency_penalty?: float,
 *     reasoning_effort?: string,
 *     allowed_tools?: list<string>,
 *     knowledge_base_id?: string|null
 * }
 *
 * @phpstan-type promptTemplateInput array{
 *     id: string,
 *     template_id: string,
 *     name: string,
 *     type: string,
 *     destination: string,
 *     sort: int,
 *     enable_auto_suggest: bool,
 *     label: string,
 *     hint: string,
 *     example: string,
 *     created_by: string,
 *     modified_by: string,
 *     created_at: string,
 *     modified_at: string
 * }
 *
 * @phpstan-type promptTemplateTag array{
 *     id: string,
 *     name: string,
 *     sort: int,
 *     created_by: string,
 *     modified_by: string,
 *     created_at: string,
 *     modified_at: string,
 *     number_of_attached_templates: int
 * }
 *
 * @phpstan-type promptTemplateUsecase array{
 *     id: string,
 *     slug: string,
 *     label: string,
 *     idp_permission_group: string,
 *     type: string,
 *     sort: int,
 *     parameters: list<array{key: string, value: string}>,
 *     visible: bool,
 *     active: bool,
 *     created_at: string,
 *     modified_at: string
 * }
 *
 * @phpstan-type promptTemplate array{
 *     id: string,
 *     name: string,
 *     inputs: list<promptTemplateInput>,
 *     type: string,
 *     description: string,
 *     hint: string,
 *     system_prompt: string,
 *     user_prompt: string,
 *     output_example: string,
 *     usage: int,
 *     tags: list<promptTemplateTag>,
 *     provider: string,
 *     temperature: float,
 *     max_tokens: int,
 *     top_p: float,
 *     frequency_penalty: float,
 *     presence_penalty: float,
 *     reasoning_effort: string,
 *     created_by: string,
 *     modified_by: string,
 *     created_at: string,
 *     modified_at: string,
 *     allowed_tools: list<string>,
 *     knowledge_base_id: string,
 *     knowledge_base: string,
 *     ingest_file_ids: list<string>,
 *     usecases: list<promptTemplateUsecase>,
 *     scope: string
 * }
 */
class AissistPromptTemplateService extends AbstractAissistChatService
{

    /**
     * @return ResultAissist{promptTemplate}
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getPromptTemplate(string $templateId): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('GET', ['/v1/prompt-template/%s', [$templateId]], [], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

    /**
     * @param promptTemplateData $templateData
     *
     * @return ResultAissist{promptTemplate}
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function createPromptTemplate(array $templateData): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('POST', '/v1/prompt-template/', ['json' => $templateData], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

    /**
     * @param promptTemplateData $templateData
     *
     * @return ResultAissist{promptTemplate}
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function updatePromptTemplate(string $templateId, array $templateData): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('PATCH', ['/v1/prompt-template/%s', [$templateId]], ['json' => $templateData], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_OK], $pinpoint->setLine(__LINE__));
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function deletePromptTemplate(string $templateId): ResultAissist
    {
        $pinpoint = new Pinpoint(__CLASS__, __METHOD__);

        $response = $this->request('DELETE', ['/v1/prompt-template/%s', [$templateId]], [], $pinpoint->setLine(__LINE__));

        return $this->handleResponse($response, [Response::HTTP_NO_CONTENT], $pinpoint->setLine(__LINE__));
    }

}
