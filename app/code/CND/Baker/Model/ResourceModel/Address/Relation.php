<?php
/**
 * Baker address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class represents save operations for baker address relations
 */
class Relation implements RelationInterface
{
    /**
     * @var \CND\Baker\Model\BakerFactory
     */
    protected $bakerFactory;

    /**
     * @param \CND\Baker\Model\BakerFactory $bakerFactory
     */
    public function __construct(\CND\Baker\Model\BakerFactory $bakerFactory)
    {
        $this->bakerFactory = $bakerFactory;
    }

    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @var $object \CND\Baker\Model\Address
         */
        if (!$object->getIsBakerSaveTransaction() && $this->isAddressDefault($object)) {
            $baker = $this->bakerFactory->create()->load($object->getBakerId());
            $changedAddresses = [];

            if ($object->getIsDefaultBilling()) {
                $changedAddresses['default_billing'] = $object->getId();
            }

            if ($object->getIsDefaultShipping()) {
                $changedAddresses['default_shipping'] = $object->getId();
            }

            if ($changedAddresses) {
                $baker->getResource()->getConnection()->update(
                    $baker->getResource()->getTable('baker_entity'),
                    $changedAddresses,
                    $baker->getResource()->getConnection()->quoteInto('entity_id = ?', $baker->getId())
                );
            }
        }
    }

    /**
     * Checks if address has chosen as default and has had an id
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isAddressDefault(\Magento\Framework\Model\AbstractModel $object)
    {
        return $object->getId() && ($object->getIsDefaultBilling() || $object->getIsDefaultShipping());
    }
}
