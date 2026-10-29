<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies\Aissist;

use HBM\HelperBundle\Service\Aissist\AissistInputAssetService;
use Symfony\Contracts\Service\Attribute\Required;

trait AissistInputAssetServiceDependencyTrait {

    protected AissistInputAssetService $aissistInputAssetService;

    #[Required]
    public function setAissistInputAssetService(AissistInputAssetService $aissistInputAssetService): void
    {
        $this->aissistInputAssetService = $aissistInputAssetService;
    }

}
