<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class TrackingParameters extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('param', ['label' => __('Tracking Parameters'), 'class' => 'required-entry']);
        $this->_addAfter = true;
        $this->_addButtonLabel = __('Add Tracking Param');
    }
}
