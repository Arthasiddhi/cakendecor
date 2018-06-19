<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Baker;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;

class NotificationStorage
{
    const UPDATE_CUSTOMER_SESSION = 'update_baker_session';

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @param FrontendInterface $cache
     */

    /**
     * @param FrontendInterface $cache
     */
    private $serializer;

    /**
     * NotificationStorage constructor.
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Add notification in cache
     *
     * @param string $notificationType
     * @param string $bakerId
     * @return void
     */
    public function add($notificationType, $bakerId)
    {
        $this->cache->save(
            $this->getSerializer()->serialize([
                'baker_id' => $bakerId,
                'notification_type' => $notificationType
            ]),
            $this->getCacheKey($notificationType, $bakerId)
        );
    }

    /**
     * Check whether notification is exists in cache
     *
     * @param string $notificationType
     * @param string $bakerId
     * @return bool
     */
    public function isExists($notificationType, $bakerId)
    {
        return $this->cache->test($this->getCacheKey($notificationType, $bakerId));
    }

    /**
     * Remove notification from cache
     *
     * @param string $notificationType
     * @param string $bakerId
     * @return void
     */
    public function remove($notificationType, $bakerId)
    {
        $this->cache->remove($this->getCacheKey($notificationType, $bakerId));
    }

    /**
     * Retrieve cache key
     *
     * @param string $notificationType
     * @param string $bakerId
     * @return string
     */
    private function getCacheKey($notificationType, $bakerId)
    {
        return 'notification_' . $notificationType . '_' . $bakerId;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated 100.2.0
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(SerializerInterface::class);
        }
        return $this->serializer;
    }
}
