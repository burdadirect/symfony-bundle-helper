<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies;

use HBM\HelperBundle\Service\HmacHelper;
use Symfony\Contracts\Service\Attribute\Required;

trait HmacHelperDependencyTrait {

  protected HmacHelper $hmacHelper;

  /**
   * @param HmacHelper $hmacHelper
   *
   * @return void
   */
  #[Required]
  public function setHmacHelper(HmacHelper $hmacHelper): void {
    $this->hmacHelper = $hmacHelper;
  }

}
