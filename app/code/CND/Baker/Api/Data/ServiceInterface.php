<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api\Data;

/**
 * Customer address interface.
 * @api
 * @since 100.0.2
 */
interface ServiceInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const NAME = 'name';
    const KEY_BAKERS = 'bakers';

    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get customer ID
     *
     * @return int|null
     */


    public function getName();

    /**
     * Set first name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);


    /**
     * Get Baker addresses.
     *
     * @return \CND\Baker\Api\Data\BakerInterface[]|null
     */
    public function getBakers();

    /**
     * Set Baker addresses.
     *
     * @param \CND\Baker\Api\Data\BakerInterface[] $bakers
     * @return $this
     */
    public function setBakers(array $bakers = null);



    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \CND\Baker\Api\Data\AddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \CND\Baker\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\CND\Baker\Api\Data\AddressExtensionInterface $extensionAttributes);
}
