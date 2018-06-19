<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use CND\Baker\Model\Session as BakerSession;

/**
 * Session-based customer user context
 */
class BakerSessionUserContext implements UserContextInterface
{
    /**
     * @var BakerSession
     */
    protected $_bakerSession;

    /**
     * Initialize dependencies.
     *
     * @param BakerSession $bakerSession
     */
    public function __construct(
        BakerSession $bakerSession
    ) {
        $this->_bakerSession = $bakerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->_bakerSession->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_BAKER;
    }
}
