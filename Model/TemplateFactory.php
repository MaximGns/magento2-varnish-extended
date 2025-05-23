<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\Template\FilteringDepthMeter;
use Magento\Framework\Filter\Template\SignatureProvider;
use Magento\Framework\Filter\VariableResolverInterface;
use Magento\Framework\Stdlib\StringUtils;

class TemplateFactory
{
    /**
     * @param StringUtils $stringUtils
     * @param DirectiveProcessorInterface[] $directiveProcessors
     * @param VariableResolverInterface $variableResolver
     * @param SignatureProvider $signatureProvider
     * @param FilteringDepthMeter $filteringDepthMeter
     */
    public function __construct(
        private readonly StringUtils               $stringUtils,
        private readonly array                     $directiveProcessors,
        private readonly VariableResolverInterface $variableResolver,
        private readonly SignatureProvider         $signatureProvider,
        private readonly FilteringDepthMeter       $filteringDepthMeter,
    ) {}

    public function create(array $variables = []): Template
    {
        $arguments = [
            'string' => $this->stringUtils,
            'variableResolver' => $this->variableResolver,
            'signature' => $this->signatureProvider,
            'filteringDepth' => $this->filteringDepthMeter,
            'directiveProcessors' => $this->directiveProcessors,
            'variables' => $variables,
        ];
        return ObjectManager::getInstance()->create(Template::class, $arguments);
    }
}
