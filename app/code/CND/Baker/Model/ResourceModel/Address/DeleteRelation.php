<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel\Address;

use CND\Baker\Api\Data\BakerInterface;

/**
 * Class DeleteRelation
 * @package CND\Baker\Model\ResourceModel\Address
 */
class DeleteRelation
{
    /**
     * Delete relation (billing and shipping) between baker and address
     *
     * @param \Magento\Framework\Model\AbstractModel $address
     * @param \CND\Baker\Model\Baker $baker
     * @return void
     */
    public function deleteRelation(
        \Magento\Framework\Model\AbstractModel $address,
        \CND\Baker\Model\Baker $baker
    ) {
        $toUpdate = $this->getDataToUpdate($address, $baker);

        if (!$address->getIsBakerSaveTransaction() && !empty($toUpdate)) {
            $address->getResource()->getConnection()->update(
                $address->getResource()->getTable('baker_entity'),
                $toUpdate,
                $address->getResource()->getConnection()->quoteInto('entity_id = ?', $baker->getId())
            );
        }
    }

    /**
     * Return address type (billing or shipping), or null if address is not default
     *
     * @param \CND\Baker\Api\Data\AddressInterface $address
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @return array
     */
    private function getDataToUpdate(
        \Magento\Framework\Model\AbstractModel $address,
        \CND\Baker\Model\Baker $baker
    ) {
        $toUpdate = [];
        if ($address->getId()) {
            if ($baker->getDefaultBilling() == $address->getId()) {
                $toUpdate[BakerInterface::DEFAULT_BILLING] = null;
            }

            if ($baker->getDefaultShipping() == $address->getId()) {
                $toUpdate[BakerInterface::DEFAULT_SHIPPING] = null;
            }
        }

        return $toUpdate;
    }
}
