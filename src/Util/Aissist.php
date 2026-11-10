<?php

namespace HBM\HelperBundle\Util;

use HBM\HelperBundle\Service\Aissist\AissistAgentService;
use HBM\HelperBundle\Service\Aissist\AissistChatHttpService;
use HBM\HelperBundle\Service\Aissist\AissistInputAssetService;
use HBM\HelperBundle\Service\Aissist\AissistPromptTemplateService;
use HBM\HelperBundle\Service\Aissist\AissistTranscriptionService;

class Aissist {

    public function __construct(
      public AissistAgentService $agent,
      public AissistChatHttpService $chatHttp,
      public AissistInputAssetService $inputAsset,
      public AissistTranscriptionService $transcription,
      public AissistPromptTemplateService $promptTemplate
    ) {
    }

}
