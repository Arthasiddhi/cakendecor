<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model;

/**
 * Baker Authentication update model.
 */
class BakerAuthUpdate
{
    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker
     */
    protected $bakerResourceModel;

    /**
     * @param \CND\Baker\Model\BakerRegistry $bakerRegistry
     * @param \CND\Baker\Model\ResourceModel\Baker $bakerResourceModel
     */
    public function __construct(
        \CND\Baker\Model\BakerRegistry $bakerRegistry,
        \CND\Baker\Model\ResourceModel\Baker $bakerResourceModel
    ) {
        $this->bakerRegistry = $bakerRegistry;
        $this->bakerResourceModel = $bakerResourceModel;
    }

    /**
     * Reset Authentication data for baker.
     *
     * @param int $bakerId
     * @return $this
     */
    public function saveAuth($bakerId)
    {
        $bakerSecure = $this->bakerRegistry->retrieveSecureData($bakerId);

        $this->bakerResourceModel->getConnection()->update(
            $this->bakerResourceModel->getTable('baker_entity'),
            [
                'failures_num' => $bakerSecure->getData('failures_num'),
                'first_failure' => $bakerSecure->getData('first_failure'),
                'lock_expires' => $bakerSecure->getData('lock_expires'),
            ],
            $this->bakerResourceModel->getConnection()->quoteInto('entity_id = ?', $bakerId)
        );

        return $this;
    }
}
