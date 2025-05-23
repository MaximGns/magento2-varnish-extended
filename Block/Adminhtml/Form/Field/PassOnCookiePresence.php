<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class PassOnCookiePresence extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('regex', ['label' => __('Cookie Regex'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Cookie Regex');
    }
}
