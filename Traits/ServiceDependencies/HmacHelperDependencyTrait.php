<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies;

use HBM\HelperBundle\Service\HmacHelper;

trait HmacHelperDependencyTrait {

  protected HmacHelper $hmacHelper;

  /**
   * @required
   *
   * @param HmacHelper $hmacHelper
   *
   * @return void
   */
  public function setHmacHelper(HmacHelper $hmacHelper): void {
    $this->hmacHelper = $hmacHelper;
  }

}
