<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies;

use HBM\HelperBundle\Service\Aissist\AissistAgentService;
use HBM\HelperBundle\Service\Aissist\AissistChatHttpService;
use HBM\HelperBundle\Service\Aissist\AissistInputAssetService;
use HBM\HelperBundle\Service\Aissist\AissistPromptTemplateService;
use HBM\HelperBundle\Service\Aissist\AissistTranscriptionService;
use HBM\HelperBundle\Util\Aissist;
use Symfony\Contracts\Service\Attribute\Required;

trait AissistDependencyTrait {

    protected Aissist $aissist;

    #[Required]
    public function setAissist(
      AissistAgentService $aissistAgentService,
      AissistChatHttpService $aissistChatHttpService,
      AissistInputAssetService $aissistInputAssetService,
      AissistTranscriptionService $aissistTranscriptionService,
      AissistPromptTemplateService $aissistPromptTemplateService,
    ): void
    {
        $this->aissist = new Aissist(
            $aissistAgentService,
            $aissistChatHttpService,
            $aissistInputAssetService,
            $aissistTranscriptionService,
            $aissistPromptTemplateService,
        );
    }

}
