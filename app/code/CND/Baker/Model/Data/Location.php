<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Data;

use CND\Baker\Api\Data\LocationInterface;
use CND\Baker\Api\Data\RegionInterface;
use \Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Address
 *
 *
 * @api
 * @since 100.0.2
 */
class Location extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \CND\Baker\Api\Data\LocationInterface
{
    /**
     * @var \CND\Baker\Api\AddressMetadataInterface
     */
    protected $metadataService;

    /**
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param \CND\Baker\Api\AddressMetadataInterface $metadataService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        \CND\Baker\Api\AddressMetadataInterface $metadataService,
        $data = []
    ) {
        $this->metadataService = $metadataService;
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomAttributesCodes()
    {
        if ($this->customAttributesCodes === null) {
            $this->customAttributesCodes = $this->getEavAttributesCodes($this->metadataService);
        }
        return $this->customAttributesCodes;
    }

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get region
     *
     * @return \CND\Baker\Api\Data\RegionInterface|null
     */

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }


    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
    /**
     * Get Baker
     *
     * @return \CND\Baker\Api\Data\BakerInterface[]|null
     */
    public function getBakers()
    {
        return $this->_get(self::KEY_BAKERS);
    }
    /**
     * Set customer addresses.
     *
     * @param \CND\Baker\Api\Data\BakerInterface[] $addresses
     * @return $this
     */
    public function setBakers(array $baker = null)
    {
        return $this->setData(self::KEY_BAKERS, $baker);
    }

       /**
     * {@inheritdoc}
     *
     * @return \CND\Baker\Api\Data\LocationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \CND\Baker\Api\Data\LocationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\CND\Baker\Api\Data\LocationExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }


    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
}
