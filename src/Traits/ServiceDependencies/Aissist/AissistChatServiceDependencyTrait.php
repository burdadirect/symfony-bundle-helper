<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies\Aissist;

use HBM\HelperBundle\Service\Aissist\AissistChatService;
use Symfony\Contracts\Service\Attribute\Required;

trait AissistChatServiceDependencyTrait {

    protected AissistChatService $aissistChatService;

    #[Required]
    public function setAissistChatService(AissistChatService $aissistChatService): void
    {
        $this->aissistChatService = $aissistChatService;
    }

}
