<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Interface \CND\Baker\Model\AuthenticationInterface
 *
 */
interface AuthenticationInterface
{
    /**
     * Process baker authentication failure
     *
     * @param int $bakerId
     * @return void
     */
    public function processAuthenticationFailure($bakerId);

    /**
     * Unlock baker
     *
     * @param int $bakerId
     * @return void
     */
    public function unlock($bakerId);

    /**
     * Check if a baker is locked
     *
     * @param int $bakerId
     * @return boolean
     */
    public function isLocked($bakerId);

    /**
     * Authenticate baker
     *
     * @param int $bakerId
     * @param string $password
     * @return boolean
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    public function authenticate($bakerId, $password);
}
