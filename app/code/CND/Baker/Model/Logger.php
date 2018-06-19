<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Baker log data logger.
 *
 * Saves and retrieves baker log data.
 */
class Logger
{
    /**
     * Resource instance.
     *
     * @var Resource
     */
    protected $resource;

    /**
     * @var \CND\Baker\Model\LogFactory
     */
    protected $logFactory;

    /**
     * @param ResourceConnection $resource
     * @param \CND\Baker\Model\LogFactory $logFactory
     */
    public function __construct(
        ResourceConnection $resource,
        \CND\Baker\Model\LogFactory $logFactory
    ) {
        $this->resource = $resource;
        $this->logFactory = $logFactory;
    }

    /**
     * Save (insert new or update existing) log.
     *
     * @param int $bakerId
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function log($bakerId, array $data)
    {
        $data = array_filter($data);

        if (!$data) {
            throw new \InvalidArgumentException("Log data is empty");
        }

        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $connection->insertOnDuplicate(
            $this->resource->getTableName('baker_log'),
            array_merge(['baker_id' => $bakerId], $data),
            array_keys($data)
        );

        return $this;
    }

    /**
     * Load log by Baker Id.
     *
     * @param int $bakerId
     * @return Log
     */
    public function get($bakerId = null)
    {
        $data = (null !== $bakerId) ? $this->loadLogData($bakerId) : [];

        return $this->logFactory->create(
            [
                'bakerId' => isset($data['baker_id']) ? $data['baker_id'] : null,
                'lastLoginAt' => isset($data['last_login_at']) ? $data['last_login_at'] : null,
                'lastLogoutAt' => isset($data['last_logout_at']) ? $data['last_logout_at'] : null,
                'lastVisitAt' => isset($data['last_visit_at']) ? $data['last_visit_at'] : null
            ]
        );
    }

    /**
     * Load baker log data by baker id
     *
     * @param int $bakerId
     * @return array
     */
    protected function loadLogData($bakerId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from(
                ['cl' => $this->resource->getTableName('baker_log')]
            )
            ->joinLeft(
                ['cv' => $this->resource->getTableName('baker_visitor')],
                'cv.baker_id = cl.baker_id',
                ['last_visit_at']
            )
            ->where(
                'cl.baker_id = ?',
                $bakerId
            )
            ->order(
                'cv.visitor_id DESC'
            )
            ->limit(1);

        return $connection->fetchRow($select);
    }
}
