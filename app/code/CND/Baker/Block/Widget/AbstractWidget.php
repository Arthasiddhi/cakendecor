<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Block\Widget;

use CND\Baker\Api\BakerMetadataInterface;

class AbstractWidget extends \Magento\Framework\View\Element\Template
{
    /**
     * @var BakerMetadataInterface
     */
    protected $bakerMetadata;

    /**
     * @var \CND\Baker\Helper\Address
     */
    protected $_addressHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \CND\Baker\Helper\Address $addressHelper
     * @param BakerMetadataInterface $bakerMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CND\Baker\Helper\Address $addressHelper,
        BakerMetadataInterface $bakerMetadata,
        array $data = []
    ) {
        $this->_addressHelper = $addressHelper;
        $this->bakerMetadata = $bakerMetadata;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function getConfig($key)
    {
        return $this->_addressHelper->getConfig($key);
    }

    /**
     * @return string
     */
    public function getFieldIdFormat()
    {
        if (!$this->hasData('field_id_format')) {
            $this->setData('field_id_format', '%s');
        }
        return $this->getData('field_id_format');
    }

    /**
     * @return string
     */
    public function getFieldNameFormat()
    {
        if (!$this->hasData('field_name_format')) {
            $this->setData('field_name_format', '%s');
        }
        return $this->getData('field_name_format');
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFieldId($field)
    {
        return sprintf($this->getFieldIdFormat(), $field);
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFieldName($field)
    {
        return sprintf($this->getFieldNameFormat(), $field);
    }

    /**
     * Retrieve baker attribute instance
     *
     * @param string $attributeCode
     * @return \CND\Baker\Api\Data\AttributeMetadataInterface|null
     */
    protected function _getAttribute($attributeCode)
    {
        try {
            return $this->bakerMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
