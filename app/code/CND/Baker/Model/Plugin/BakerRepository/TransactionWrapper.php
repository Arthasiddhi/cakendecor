<?php
/**
 * Plugin for \CND\Baker\Api\BakerRepositoryInterface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Plugin\BakerRepository;

class TransactionWrapper
{
    /**
     * @var \CND\Baker\Model\ResourceModel\Baker
     */
    protected $resourceModel;

    /**
     * @param \CND\Baker\Model\ResourceModel\Baker $resourceModel
     */
    public function __construct(
        \CND\Baker\Model\ResourceModel\Baker $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param \CND\Baker\Api\BakerRepositoryInterface $subject
     * @param callable $proceed
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @param string $passwordHash
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \CND\Baker\Api\BakerRepositoryInterface $subject,
        \Closure $proceed,
        \CND\Baker\Api\Data\BakerInterface $baker,
        $passwordHash = null
    ) {
        $this->resourceModel->beginTransaction();
        try {
            /** @var $result \CND\Baker\Api\Data\BakerInterface */
            $result = $proceed($baker, $passwordHash);
            $this->resourceModel->commit();
            return $result;
        } catch (\Exception $e) {
            $this->resourceModel->rollBack();
            throw $e;
        }
    }
}
