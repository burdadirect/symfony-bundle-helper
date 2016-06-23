<?php

namespace HBM\HelperBundle\Twig;

use HBM\HelperBundle\Services\SanitizingHelper;

class SanitizingExtension extends \Twig_Extension
{

  /** @var SanitizingHelper */
  private $sanitizingHelper;

  public function __construct(SanitizingHelper $sanitizingHelper)
  {
    $this->sanitizingHelper = $sanitizingHelper;
  }

  public function getFilters()
  {
    return array(
      new \Twig_SimpleFilter('tidy', array($this, 'tidy')),
    );
  }

  public function getName()
  {
    return 'hbm_twig_extensions_sanitizing';
  }

  /****************************************************************************/
  /* FILTERS                                                                  */
  /****************************************************************************/

  public function tidy($html) {
    return $this->sanitizingHelper->repairHtml($html);
  }

}
