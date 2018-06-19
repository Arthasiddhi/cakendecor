<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Api\GroupManagementInterface;
use Magento\Framework\App\RequestInterface;

class BakerExtractor
{
    /**
     * @var \CND\Baker\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \CND\Baker\Api\Data\BakerInterfaceFactory
     */
    protected $bakerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupManagementInterface
     */
    protected $bakerGroupManagement;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param Metadata\FormFactory $formFactory
     * @param \CND\Baker\Api\Data\BakerInterfaceFactory $bakerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GroupManagementInterface $bakerGroupManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \CND\Baker\Model\Metadata\FormFactory $formFactory,
        \CND\Baker\Api\Data\BakerInterfaceFactory $bakerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $bakerGroupManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->formFactory = $formFactory;
        $this->bakerFactory = $bakerFactory;
        $this->storeManager = $storeManager;
        $this->bakerGroupManagement = $bakerGroupManagement;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param string $formCode
     * @param RequestInterface $request
     * @param array $attributeValues
     * @return BakerInterface
     */
    public function extract(
        $formCode,
        RequestInterface $request,
        array $attributeValues = []
    ) {
        $bakerForm = $this->formFactory->create(
            BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $formCode,
            $attributeValues
        );

        $bakerData = $bakerForm->extractData($request);
        $bakerData = $bakerForm->compactData($bakerData);

        $allowedAttributes = $bakerForm->getAllowedAttributes();
        $isGroupIdEmpty = isset($allowedAttributes['group_id']);

        $bakerDataObject = $this->bakerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $bakerDataObject,
            $bakerData,
            \CND\Baker\Api\Data\BakerInterface::class
        );
        $store = $this->storeManager->getStore();
        if ($isGroupIdEmpty) {
            $bakerDataObject->setGroupId(
                $this->bakerGroupManagement->getDefaultGroup($store->getId())->getId()
            );
        }

        $bakerDataObject->setWebsiteId($store->getWebsiteId());
        $bakerDataObject->setStoreId($store->getId());

        return $bakerDataObject;
    }
}
