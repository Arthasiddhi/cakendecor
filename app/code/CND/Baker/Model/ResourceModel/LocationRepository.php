<?php
/**
 * Baker location entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel;

use CND\Baker\Model\Location as BakerLocationModel;
use CND\Baker\Model\Baker as BakerModel;
use CND\Baker\Model\ResourceModel\Location\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LocationRepository implements \CND\Baker\Api\LocationRepositoryInterface
{
    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var \CND\Baker\Model\LocationFactory
     */
    protected $locationFactory;

    /**
     * @var \CND\Baker\Model\LocationRegistry
     */
    protected $locationRegistry;

    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\Location
     */
    protected $locationResourceModel;

    /**
     * @var \CND\Baker\Api\Data\LocationSearchResultsInterfaceFactory
     */
    protected $locationSearchResultsFactory;

    /**
     * @var \CND\Baker\Model\ResourceModel\Location\CollectionFactory
     */
    protected $locationCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \CND\Baker\Model\LocationFactory $locationFactory
     * @param \CND\Baker\Model\LocationRegistry $locationRegistry
     * @param \CND\Baker\Model\BakerRegistry $bakerRegistry
     * @param \CND\Baker\Model\ResourceModel\Location $locationResourceModel
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \CND\Baker\Api\Data\LocationSearchResultsInterfaceFactory $locationSearchResultsFactory
     * @param \CND\Baker\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \CND\Baker\Model\LocationFactory $locationFactory,
        \CND\Baker\Model\LocationRegistry $locationRegistry,
        \CND\Baker\Model\BakerRegistry $bakerRegistry,
        \CND\Baker\Model\ResourceModel\Location $locationResourceModel,
        \Magento\Directory\Helper\Data $directoryData,
        \CND\Baker\Api\Data\LocationSearchResultsInterfaceFactory $locationSearchResultsFactory,
        \CND\Baker\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->locationFactory = $locationFactory;
        $this->locationRegistry = $locationRegistry;
        $this->bakerRegistry = $bakerRegistry;
        $this->locationResourceModel = $locationResourceModel;
        $this->directoryData = $directoryData;
        $this->locationSearchResultsFactory = $locationSearchResultsFactory;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save baker location.
     *
     * @param \CND\Baker\Api\Data\LocationInterface $location
     * @return \CND\Baker\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function getItems()
    {
        return $this->locationCollectionFactory->create()->getItems();
    }
    public function save(\CND\Baker\Api\Data\LocationInterface $location)
    {
        $locationModel = null;
        $bakerModel = $this->bakerRegistry->retrieve($location->getBakerId());
        if ($location->getId()) {
            $locationModel = $this->locationRegistry->retrieve($location->getId());
        }

        if ($locationModel === null) {
            /** @var \CND\Baker\Model\Location $locationModel */
            $locationModel = $this->locationFactory->create();
            $locationModel->updateData($location);
            $locationModel->setBaker($bakerModel);
        } else {
            $locationModel->updateData($location);
        }

        $errors = $locationModel->validate();
        if ($errors !== true) {
            $inputException = new InputException();
            foreach ($errors as $error) {
                $inputException->addError($error);
            }
            throw $inputException;
        }
        $locationModel->save();
        $location->setId($locationModel->getId());
        // Clean up the baker registry since the Location save has a
        // side effect on baker : \CND\Baker\Model\ResourceModel\Location::_afterSave
        $this->locationRegistry->push($locationModel);
        $this->updateLocationCollection($bakerModel, $locationModel);

        return $locationModel->getDataModel();
    }

    /**
     * @param Baker $baker
     * @param Location $location
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function updateLocationCollection(BakerModel $baker, BakerLocationModel $location)
    {
        $baker->getLocationesCollection()->removeItemByKey($location->getId());
        $baker->getLocationesCollection()->addItem($location);
    }

    /**
     * Retrieve baker location.
     *
     * @param int $locationId
     * @return \CND\Baker\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($locationId)
    {
        $location = $this->locationRegistry->retrieve($locationId);
        return $location->getDataModel();
    }

    /**
     * Retrieve bakers locationes matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \CND\Baker\Api\Data\LocationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->locationCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \CND\Baker\Api\Data\LocationInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \CND\Baker\Api\Data\LocationInterface[] $locationes */
        $locationes = [];
        /** @var \CND\Baker\Model\Location $location */
        foreach ($collection->getItems() as $location) {
            $locationes[] = $this->getById($location->getId());
        }

        /** @var \CND\Baker\Api\Data\LocationSearchResultsInterface $searchResults */
        $searchResults = $this->locationSearchResultsFactory->create();
        $searchResults->setItems($locationes);
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
     * Delete baker location.
     *
     * @param \CND\Baker\Api\Data\LocationInterface $location
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\Baker\Api\Data\LocationInterface $location)
    {
        $locationId = $location->getId();
        $location = $this->locationRegistry->retrieve($locationId);
        $bakerModel = $this->bakerRegistry->retrieve($location->getBakerId());
        $bakerModel->getLocationesCollection()->clear();
        $this->locationResourceModel->delete($location);
        $this->locationRegistry->remove($locationId);
        return true;
    }

    /**
     * Delete baker location by ID.
     *
     * @param int $locationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($locationId)
    {
        $location = $this->locationRegistry->retrieve($locationId);
        $bakerModel = $this->bakerRegistry->retrieve($location->getBakerId());
        $bakerModel->getLocationesCollection()->removeItemByKey($locationId);
        $this->locationResourceModel->delete($location);
        $this->locationRegistry->remove($locationId);
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
