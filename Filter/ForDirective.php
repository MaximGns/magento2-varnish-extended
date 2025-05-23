<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Filter;

use Magento\Framework\DataObject;
use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Fine-tuned ForDirective with better whitespace output
 */
class ForDirective extends \Magento\Framework\Filter\DirectiveProcessor\ForDirective
{
    public const LOOP_PATTERN = '/{{for(?P<loopItem>.*? )(in)(?P<loopData>.*?)}}\n*(?P<loopBody>.*?)\s*{{\/for}}/si';

    public function getRegularExpression(): string
    {
        return self::LOOP_PATTERN;
    }
}
