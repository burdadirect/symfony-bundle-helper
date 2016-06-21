<?php

namespace HBM\HelperBundle\Twig;

use HBM\HelperBundle\Services\StringSanitizer;

class SanitizerExtension extends \Twig_Extension
{

  private $stringSanitizer;

  public function __construct(StringSanitizer $stringSanitizer)
  {
    $this->stringSanitizer = $stringSanitizer;
  }

  public function getFilters()
  {
    return array(
      new \Twig_SimpleFilter('tidy', array($this, 'tidy')),
    );
  }

  public function getName()
  {
    return 'hbm_twig_extensions_string_sanitizer';
  }

  /****************************************************************************/
  /* FILTERS                                                                  */
  /****************************************************************************/

  public function tidy($html) {
    return $this->stringSanitizer->repairHtml($html);
  }

}
