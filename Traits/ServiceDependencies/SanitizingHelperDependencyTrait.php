<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies;

use HBM\HelperBundle\Service\SanitizingHelper;

trait SanitizingHelperDependencyTrait {

  protected SanitizingHelper $sanitizingHelper;

  /**
   * @required
   *
   * @param SanitizingHelper $sanitizingHelper
   *
   * @return void
   */
  public function setSanitizingHelper(SanitizingHelper $sanitizingHelper): void {
    $this->sanitizingHelper = $sanitizingHelper;
  }

}
