<?php

namespace HBM\HelperBundle\Twig\Runtime;

use HBM\HelperBundle\Service\SanitizingHelper;
use Twig\Extension\RuntimeExtensionInterface;

class HelperRuntime implements RuntimeExtensionInterface
{
    private SanitizingHelper $sanitizingHelper;

    public function __construct(SanitizingHelper $sanitizingHelper)
    {
        $this->sanitizingHelper = $sanitizingHelper;
    }

    public function repairHtml(?string $html, array $options = []): string
    {
        return $this->sanitizingHelper->repairHtml($html, $options);
    }
}
