<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

/**
 * Baker log model.
 *
 * Contains baker log data.
 */
class Log
{
    /**
     * Baker ID.
     *
     * @var int
     */
    protected $bakerId;

    /**
     * Date and time of baker's last login.
     *
     * @var string
     */
    protected $lastLoginAt;

    /**
     * Date and time of baker's last logout.
     *
     * @var string
     */
    protected $lastVisitAt;

    /**
     * Date and time of baker's last visit.
     *
     * @var string
     */
    protected $lastLogoutAt;

    /**
     * @param int $bakerId
     * @param string $lastLoginAt
     * @param string $lastVisitAt
     * @param string $lastLogoutAt
     */
    public function __construct($bakerId = null, $lastLoginAt = null, $lastVisitAt = null, $lastLogoutAt = null)
    {
        $this->bakerId = $bakerId;
        $this->lastLoginAt = $lastLoginAt;
        $this->lastVisitAt = $lastVisitAt;
        $this->lastLogoutAt = $lastLogoutAt;
    }

    /**
     * Retrieve baker id
     *
     * @return int
     */
    public function getBakerId()
    {
        return $this->bakerId;
    }

    /**
     * Retrieve last login date as string
     *
     * @return string
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * Retrieve last visit date as string
     *
     * @return string
     */
    public function getLastVisitAt()
    {
        return $this->lastVisitAt;
    }

    /**
     * Retrieve last logout date as string
     *
     * @return string
     */
    public function getLastLogoutAt()
    {
        return $this->lastLogoutAt;
    }
}
