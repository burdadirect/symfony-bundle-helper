<?php

namespace HBM\HelperBundle\Twig\Extension;

use HBM\HelperBundle\Twig\Runtime\HelperRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HelperExtension extends AbstractExtension
{
    /* DEFINITIONS */

    public function getFilters(): array
    {
        return [
          new TwigFilter('hbmRepairHtml', [HelperRuntime::class, 'repairHtml']),
        ];
    }
}
