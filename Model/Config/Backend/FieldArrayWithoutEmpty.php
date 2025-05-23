<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;
use Magento\Framework\Exception\LocalizedException;

class FieldArrayWithoutEmpty extends Serialized
{
    /**
     * Processing object before save data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
