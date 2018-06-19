<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model;

/**
 * System configuration operations for baker groups.
 */
class BakerGroupConfig implements \CND\Baker\Api\BakerGroupConfigInterface
{
    /**
     * @var \Magento\Config\Model\Config
     */
    private $config;

    /**
     * @var \CND\Baker\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param \Magento\Config\Model\Config $config
     * @param \CND\Baker\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Config\Model\Config $config,
        \CND\Baker\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->config = $config;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultBakerGroup($id)
    {
        if ($this->groupRepository->getById($id)) {
            $this->config->setDataByPath(
                \CND\Baker\Model\GroupManagement::XML_PATH_DEFAULT_ID,
                $id
            );
            $this->config->save();
        }

        return $id;
    }
}
