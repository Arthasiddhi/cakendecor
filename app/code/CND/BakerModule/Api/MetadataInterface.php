<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api;

/**
 * Interface for getting attributes metadata. Note that this interface should not be used directly, use its children.
 * @api
 * @since 100.0.2
 */
interface MetadataInterface extends \Magento\Framework\Api\MetadataServiceInterface
{
    /**
     * Retrieve all attributes filtered by form code
     *
     * @param string $formCode
     * @return \CND\Baker\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributes($formCode);

    /**
     * Retrieve attribute metadata.
     *
     * @param string $attributeCode
     * @return \CND\Baker\Api\Data\AttributeMetadataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeMetadata($attributeCode);

    /**
     * Get all attribute metadata.
     *
     * @return \CND\Baker\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllAttributesMetadata();

    /**
     *  Get custom attributes metadata for the given data interface.
     *
     * @param string $dataInterfaceName
     * @return \CND\Baker\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomAttributesMetadata($dataInterfaceName = '');
}
