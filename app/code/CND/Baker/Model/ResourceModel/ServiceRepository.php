<?php
/**
 * Baker service entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel;

use CND\Baker\Model\Service as BakerServiceModel;
use CND\Baker\Model\Baker as BakerModel;
use CND\Baker\Model\ResourceModel\Service\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceRepository implements \CND\Baker\Api\ServiceRepositoryInterface
{
    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var \CND\Baker\Model\ServiceFactory
     */
    protected $serviceFactory;

    /**
     * @var \CND\Baker\Model\ServiceRegistry
     */
    protected $serviceRegistry;

    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\Service
     */
    protected $serviceResourceModel;

    /**
     * @var \CND\Baker\Api\Data\ServiceSearchResultsInterfaceFactory
     */
    protected $serviceSearchResultsFactory;

    /**
     * @var \CND\Baker\Model\ResourceModel\Service\CollectionFactory
     */
    protected $serviceCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \CND\Baker\Model\ServiceFactory $serviceFactory
     * @param \CND\Baker\Model\ServiceRegistry $serviceRegistry
     * @param \CND\Baker\Model\BakerRegistry $bakerRegistry
     * @param \CND\Baker\Model\ResourceModel\Service $serviceResourceModel
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \CND\Baker\Api\Data\ServiceSearchResultsInterfaceFactory $serviceSearchResultsFactory
     * @param \CND\Baker\Model\ResourceModel\Service\CollectionFactory $serviceCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \CND\Baker\Model\ServiceFactory $serviceFactory,
        \CND\Baker\Model\ServiceRegistry $serviceRegistry,
        \CND\Baker\Model\BakerRegistry $bakerRegistry,
        \CND\Baker\Model\ResourceModel\Service $serviceResourceModel,
        \Magento\Directory\Helper\Data $directoryData,
        \CND\Baker\Api\Data\ServiceSearchResultsInterfaceFactory $serviceSearchResultsFactory,
        \CND\Baker\Model\ResourceModel\Service\CollectionFactory $serviceCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->serviceRegistry = $serviceRegistry;
        $this->bakerRegistry = $bakerRegistry;
        $this->serviceResourceModel = $serviceResourceModel;
        $this->directoryData = $directoryData;
        $this->serviceSearchResultsFactory = $serviceSearchResultsFactory;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save baker service.
     *
     * @param \CND\Baker\Api\Data\ServiceInterface $service
     * @return \CND\Baker\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\CND\Baker\Api\Data\ServiceInterface $service)
    {
        $serviceModel = null;
        $bakerModel = $this->bakerRegistry->retrieve($service->getBakerId());
        if ($service->getId()) {
            $serviceModel = $this->serviceRegistry->retrieve($service->getId());
        }

        if ($serviceModel === null) {
            /** @var \CND\Baker\Model\Service $serviceModel */
            $serviceModel = $this->serviceFactory->create();
            $serviceModel->updateData($service);
            $serviceModel->setBaker($bakerModel);
        } else {
            $serviceModel->updateData($service);
        }

        $errors = $serviceModel->validate();
        if ($errors !== true) {
            $inputException = new InputException();
            foreach ($errors as $error) {
                $inputException->addError($error);
            }
            throw $inputException;
        }
        $serviceModel->save();
        $service->setId($serviceModel->getId());
        // Clean up the baker registry since the Service save has a
        // side effect on baker : \CND\Baker\Model\ResourceModel\Service::_afterSave
        $this->serviceRegistry->push($serviceModel);
        $this->updateServiceCollection($bakerModel, $serviceModel);

        return $serviceModel->getDataModel();
    }

    /**
     * @param Baker $baker
     * @param Service $service
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function updateServiceCollection(BakerModel $baker, BakerServiceModel $service)
    {
        $baker->getServiceesCollection()->removeItemByKey($service->getId());
        $baker->getServiceesCollection()->addItem($service);
    }

    /**
     * Retrieve baker service.
     *
     * @param int $serviceId
     * @return \CND\Baker\Api\Data\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($serviceId)
    {
        $service = $this->serviceRegistry->retrieve($serviceId);
        return $service->getDataModel();
    }

    /**
     * Retrieve bakers servicees matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \CND\Baker\Api\Data\ServiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->serviceCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \CND\Baker\Api\Data\ServiceInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \CND\Baker\Api\Data\ServiceInterface[] $servicees */
        $servicees = [];
        /** @var \CND\Baker\Model\Service $service */
        foreach ($collection->getItems() as $service) {
            $servicees[] = $this->getById($service->getId());
        }

        /** @var \CND\Baker\Api\Data\ServiceSearchResultsInterface $searchResults */
        $searchResults = $this->serviceSearchResultsFactory->create();
        $searchResults->setItems($servicees);
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 100.2.0
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = ['attribute' => $filter->getField(), $condition => $filter->getValue()];
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Delete baker service.
     *
     * @param \CND\Baker\Api\Data\ServiceInterface $service
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\Baker\Api\Data\ServiceInterface $service)
    {
        $serviceId = $service->getId();
        $service = $this->serviceRegistry->retrieve($serviceId);
        $bakerModel = $this->bakerRegistry->retrieve($service->getBakerId());
        $bakerModel->getServiceesCollection()->clear();
        $this->serviceResourceModel->delete($service);
        $this->serviceRegistry->remove($serviceId);
        return true;
    }

    /**
     * Delete baker service by ID.
     *
     * @param int $serviceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($serviceId)
    {
        $service = $this->serviceRegistry->retrieve($serviceId);
        $bakerModel = $this->bakerRegistry->retrieve($service->getBakerId());
        $bakerModel->getServiceesCollection()->removeItemByKey($serviceId);
        $this->serviceResourceModel->delete($service);
        $this->serviceRegistry->remove($serviceId);
        return true;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 100.2.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
