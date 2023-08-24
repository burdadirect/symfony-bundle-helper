<?php

namespace HBM\HelperBundle\Traits\ServiceDependencies;

use HBM\HelperBundle\Service\SanitizingHelper;
use Symfony\Contracts\Service\Attribute\Required;

trait SanitizingHelperDependencyTrait {

  protected SanitizingHelper $sanitizingHelper;

  /**
   * @param SanitizingHelper $sanitizingHelper
   *
   * @return void
   */
  #[Required]
  public function setSanitizingHelper(SanitizingHelper $sanitizingHelper): void {
    $this->sanitizingHelper = $sanitizingHelper;
  }

}
