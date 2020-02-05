<?php

namespace HBM\HelperBundle\Twig;

use HBM\HelperBundle\Services\SanitizingHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SanitizingExtension extends AbstractExtension {

  /** @var SanitizingHelper */
  private $sanitizingHelper;

  public function __construct(SanitizingHelper $sanitizingHelper)
  {
    $this->sanitizingHelper = $sanitizingHelper;
  }

  public function getFilters()
  {
    return array(
      new TwigFilter('tidy', [$this, 'tidy']),
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
