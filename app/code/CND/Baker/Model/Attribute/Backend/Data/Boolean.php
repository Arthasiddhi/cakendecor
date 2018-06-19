<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Attribute\Backend\Data;

/**
 * Boolean customer attribute backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Boolean extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Prepare data before attribute save
     *
     * @param \CND\Baker\Model\Customer $customer
     * @return $this
     */
    public function beforeSave($customer)
    {
        $attributeName = $this->getAttribute()->getName();
        $inputValue = $customer->getData($attributeName);
        $inputValue = $inputValue === null ? $this->getAttribute()->getDefaultValue() : $inputValue;
        $sanitizedValue = !empty($inputValue) ? '1' : '0';
        $customer->setData($attributeName, $sanitizedValue);
        return $this;
    }
}
