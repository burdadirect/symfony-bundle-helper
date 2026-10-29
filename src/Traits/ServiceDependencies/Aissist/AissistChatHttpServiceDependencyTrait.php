<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies\Aissist;

use HBM\HelperBundle\Service\Aissist\AissistChatHttpService;
use Symfony\Contracts\Service\Attribute\Required;

trait AissistChatHttpServiceDependencyTrait {

    protected AissistChatHttpService $aissistChatHttpService;

    #[Required]
    public function setAissistChatHttpService(AissistChatHttpService $aissistChatHttpService): void
    {
        $this->aissistChatHttpService = $aissistChatHttpService;
    }

}
