<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Group;

use CND\Baker\Model\Session;

/**
 * Class for getting current customer group from customer session.
 */
class Retriever implements RetrieverInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Session $customerSession
     */
    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function getBakerGroupId()
    {
        return $this->customerSession->getBakerGroupId();
    }
}
