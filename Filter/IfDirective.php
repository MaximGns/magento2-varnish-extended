<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Filter;

/**
 * Fine-tuned IfDirective with better whitespace output
 */
class IfDirective extends \Magento\Framework\Filter\DirectiveProcessor\IfDirective
{
    public const CONSTRUCTION_IF_PATTERN = '/{{if\s*(.*?)}}\n?(.*?)\s*({{else}}\n?(.*?))?\s*{{\\/if\s*}}/si';

    public function getRegularExpression(): string
    {
        return self::CONSTRUCTION_IF_PATTERN;
    }
}
