<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Widget;

use CND\Baker\Api\AddressMetadataInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Helper\Address as AddressHelper;
use CND\Baker\Model\Options;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for showing baker name.
 *
 * @method BakerInterface getObject()
 * @method Name setObject(BakerInterface $baker)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Name extends AbstractWidget
{
    /**
     * @var AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @param Context $context
     * @param AddressHelper $addressHelper
     * @param BakerMetadataInterface $bakerMetadata
     * @param Options $options
     * @param AddressMetadataInterface $addressMetadata
     * @param array $data
     */
    public function __construct(
        Context $context,
        AddressHelper $addressHelper,
        BakerMetadataInterface $bakerMetadata,
        Options $options,
        AddressMetadataInterface $addressMetadata,
        array $data = []
    ) {
        $this->options = $options;
        parent::__construct($context, $addressHelper, $bakerMetadata, $data);
        $this->addressMetadata = $addressMetadata;
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('widget/name.phtml');
    }

    /**
     * Can show config value
     *
     * @param string $key
     * @return bool
     */
    protected function _showConfig($key)
    {
        return (bool)$this->getConfig($key);
    }

    /**
     * Can show prefix
     *
     * @return bool
     */
    public function showPrefix()
    {
        return $this->_isAttributeVisible('prefix');
    }

    /**
     * Define if prefix attribute is required
     *
     * @return bool
     */
    public function isPrefixRequired()
    {
        return $this->_isAttributeRequired('prefix');
    }

    /**
     * Retrieve name prefix drop-down options
     *
     * @return array|bool
     */
    public function getPrefixOptions()
    {
        $prefixOptions = $this->options->getNamePrefixOptions();

        if ($this->getObject() && !empty($prefixOptions)) {
            $prefixOption = $this->getObject()->getPrefix();
            $oldPrefix = $this->escapeHtml(trim($prefixOption));
            if ($prefixOption !== null && !isset($prefixOptions[$oldPrefix]) && !isset($prefixOptions[$prefixOption])) {
                $prefixOptions[$oldPrefix] = $oldPrefix;
            }
        }
        return $prefixOptions;
    }

    /**
     * Define if middle name attribute can be shown
     *
     * @return bool
     */
    public function showMiddlename()
    {
        return $this->_isAttributeVisible('middlename');
    }

    /**
     * Define if middlename attribute is required
     *
     * @return bool
     */
    public function isMiddlenameRequired()
    {
        return $this->_isAttributeRequired('middlename');
    }

    /**
     * Define if suffix attribute can be shown
     *
     * @return bool
     */
    public function showSuffix()
    {
        return $this->_isAttributeVisible('suffix');
    }

    /**
     * Define if suffix attribute is required
     *
     * @return bool
     */
    public function isSuffixRequired()
    {
        return $this->_isAttributeRequired('suffix');
    }

    /**
     * Retrieve name suffix drop-down options
     *
     * @return array|bool
     */
    public function getSuffixOptions()
    {
        $suffixOptions = $this->options->getNameSuffixOptions();
        if ($this->getObject() && !empty($suffixOptions)) {
            $suffixOption = $this->getObject()->getSuffix();
            $oldSuffix = $this->escapeHtml(trim($suffixOption));
            if ($suffixOption !== null && !isset($suffixOptions[$oldSuffix]) && !isset($suffixOptions[$suffixOption])) {
                $suffixOptions[$oldSuffix] = $oldSuffix;
            }
        }
        return $suffixOptions;
    }

    /**
     * Class name getter
     *
     * @return string
     */
    public function getClassName()
    {
        if (!$this->hasData('class_name')) {
            $this->setData('class_name', 'baker-name');
        }
        return $this->getData('class_name');
    }

    /**
     * Container class name getter
     *
     * @return string
     */
    public function getContainerClassName()
    {
        $class = $this->getClassName();
        $class .= $this->showPrefix() ? '-prefix' : '';
        $class .= $this->showMiddlename() ? '-middlename' : '';
        $class .= $this->showSuffix() ? '-suffix' : '';
        return $class;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getAttribute($attributeCode)
    {
        if ($this->getForceUseBakerAttributes() || $this->getObject() instanceof BakerInterface) {
            return parent::_getAttribute($attributeCode);
        }

        try {
            $attribute = $this->addressMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        if ($this->getForceUseBakerRequiredAttributes() && $attribute && !$attribute->isRequired()) {
            $bakerAttribute = parent::_getAttribute($attributeCode);
            if ($bakerAttribute && $bakerAttribute->isRequired()) {
                $attribute = $bakerAttribute;
            }
        }

        return $attribute;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeValidationClass($attributeCode)
    {
        return $this->_addressHelper->getAttributeValidationClass($attributeCode);
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    private function _isAttributeRequired($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isRequired() : false;
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    private function _isAttributeVisible($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }
}
