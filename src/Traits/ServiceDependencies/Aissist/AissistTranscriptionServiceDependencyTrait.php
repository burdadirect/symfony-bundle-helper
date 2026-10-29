<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies\Aissist;

use HBM\HelperBundle\Service\Aissist\AissistTranscriptionService;
use Symfony\Contracts\Service\Attribute\Required;

trait AissistTranscriptionServiceDependencyTrait {

    protected AissistTranscriptionService $aissistTranscriptionService;

    #[Required]
    public function setAissistTranscriptionService(AissistTranscriptionService $aissistTranscriptionService): void
    {
        $this->aissistTranscriptionService = $aissistTranscriptionService;
    }

}
