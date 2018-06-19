<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model\ResourceModel\Baker;

/**
 * Class Relation
 */
class Relation implements \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface
{
    /**
     * Save relations for Baker
     *
     * @param \Magento\Framework\Model\AbstractModel $baker
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $baker)
    {
        $defaultBillingId = $baker->getData('default_billing');
        $defaultShippingId = $baker->getData('default_shipping');

        /** @var \CND\Baker\Model\Address $address */
        foreach ($baker->getAddresses() as $address) {
            if ($address->getData('_deleted')) {
                if ($address->getId() == $defaultBillingId) {
                    $baker->setData('default_billing', null);
                }

                if ($address->getId() == $defaultShippingId) {
                    $baker->setData('default_shipping', null);
                }

                $removedAddressId = $address->getId();
                $address->delete();

                // Remove deleted address from baker address collection
                $baker->getAddressesCollection()->removeItemByKey($removedAddressId);
            } else {
                $address->setParentId(
                    $baker->getId()
                )->setStoreId(
                    $baker->getStoreId()
                )->setIsBakerSaveTransaction(
                    true
                )->save();

                if (($address->getIsPrimaryBilling() ||
                        $address->getIsDefaultBilling()) && $address->getId() != $defaultBillingId
                ) {
                    $baker->setData('default_billing', $address->getId());
                }

                if (($address->getIsPrimaryShipping() ||
                        $address->getIsDefaultShipping()) && $address->getId() != $defaultShippingId
                ) {
                    $baker->setData('default_shipping', $address->getId());
                }
            }
        }

        $changedAddresses = [];

        $changedAddresses['default_billing'] = $baker->getData('default_billing');
        $changedAddresses['default_shipping'] = $baker->getData('default_shipping');

        $baker->getResource()->getConnection()->update(
            $baker->getResource()->getTable('baker_entity'),
            $changedAddresses,
            $baker->getResource()->getConnection()->quoteInto('entity_id = ?', $baker->getId())
        );
    }
}
