<?php
/**
 * Baker address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel;

use CND\Baker\Model\Address as BakerAddressModel;
use CND\Baker\Model\Baker as BakerModel;
use CND\Baker\Model\ResourceModel\Address\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepository implements \CND\Baker\Api\AddressRepositoryInterface
{
    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var \CND\Baker\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \CND\Baker\Model\AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\Address
     */
    protected $addressResourceModel;

    /**
     * @var \CND\Baker\Api\Data\AddressSearchResultsInterfaceFactory
     */
    protected $addressSearchResultsFactory;

    /**
     * @var \CND\Baker\Model\ResourceModel\Address\CollectionFactory
     */
    protected $addressCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \CND\Baker\Model\AddressFactory $addressFactory
     * @param \CND\Baker\Model\AddressRegistry $addressRegistry
     * @param \CND\Baker\Model\BakerRegistry $bakerRegistry
     * @param \CND\Baker\Model\ResourceModel\Address $addressResourceModel
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \CND\Baker\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory
     * @param \CND\Baker\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \CND\Baker\Model\AddressFactory $addressFactory,
        \CND\Baker\Model\AddressRegistry $addressRegistry,
        \CND\Baker\Model\BakerRegistry $bakerRegistry,
        \CND\Baker\Model\ResourceModel\Address $addressResourceModel,
        \Magento\Directory\Helper\Data $directoryData,
        \CND\Baker\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory,
        \CND\Baker\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressRegistry = $addressRegistry;
        $this->bakerRegistry = $bakerRegistry;
        $this->addressResourceModel = $addressResourceModel;
        $this->directoryData = $directoryData;
        $this->addressSearchResultsFactory = $addressSearchResultsFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save baker address.
     *
     * @param \CND\Baker\Api\Data\AddressInterface $address
     * @return \CND\Baker\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\CND\Baker\Api\Data\AddressInterface $address)
    {
        $addressModel = null;
        $bakerModel = $this->bakerRegistry->retrieve($address->getBakerId());
        if ($address->getId()) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
        }

        if ($addressModel === null) {
            /** @var \CND\Baker\Model\Address $addressModel */
            $addressModel = $this->addressFactory->create();
            $addressModel->updateData($address);
            $addressModel->setBaker($bakerModel);
        } else {
            $addressModel->updateData($address);
        }

        $errors = $addressModel->validate();
        if ($errors !== true) {
            $inputException = new InputException();
            foreach ($errors as $error) {
                $inputException->addError($error);
            }
            throw $inputException;
        }
        $addressModel->save();
        $address->setId($addressModel->getId());
        // Clean up the baker registry since the Address save has a
        // side effect on baker : \CND\Baker\Model\ResourceModel\Address::_afterSave
        $this->addressRegistry->push($addressModel);
        $this->updateAddressCollection($bakerModel, $addressModel);

        return $addressModel->getDataModel();
    }

    /**
     * @param Baker $baker
     * @param Address $address
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function updateAddressCollection(BakerModel $baker, BakerAddressModel $address)
    {
        $baker->getAddressesCollection()->removeItemByKey($address->getId());
        $baker->getAddressesCollection()->addItem($address);
    }

    /**
     * Retrieve baker address.
     *
     * @param int $addressId
     * @return \CND\Baker\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        return $address->getDataModel();
    }

    /**
     * Retrieve bakers addresses matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \CND\Baker\Api\Data\AddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->addressCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \CND\Baker\Api\Data\AddressInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \CND\Baker\Api\Data\AddressInterface[] $addresses */
        $addresses = [];
        /** @var \CND\Baker\Model\Address $address */
        foreach ($collection->getItems() as $address) {
            $addresses[] = $this->getById($address->getId());
        }

        /** @var \CND\Baker\Api\Data\AddressSearchResultsInterface $searchResults */
        $searchResults = $this->addressSearchResultsFactory->create();
        $searchResults->setItems($addresses);
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
     * Delete baker address.
     *
     * @param \CND\Baker\Api\Data\AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\CND\Baker\Api\Data\AddressInterface $address)
    {
        $addressId = $address->getId();
        $address = $this->addressRegistry->retrieve($addressId);
        $bakerModel = $this->bakerRegistry->retrieve($address->getBakerId());
        $bakerModel->getAddressesCollection()->clear();
        $this->addressResourceModel->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }

    /**
     * Delete baker address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        $bakerModel = $this->bakerRegistry->retrieve($address->getBakerId());
        $bakerModel->getAddressesCollection()->removeItemByKey($addressId);
        $this->addressResourceModel->delete($address);
        $this->addressRegistry->remove($addressId);
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
