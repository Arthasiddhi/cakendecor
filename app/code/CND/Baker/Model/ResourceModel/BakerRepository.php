<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model\ResourceModel;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Model\Baker\NotificationStorage;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Baker repository.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BakerRepository implements \CND\Baker\Api\BakerRepositoryInterface
{
    /**
     * @var \CND\Baker\Model\BakerFactory
     */
    protected $bakerFactory;

    /**
     * @var \CND\Baker\Model\Data\BakerSecureFactory
     */
    protected $bakerSecureFactory;

    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * @var \CND\Baker\Model\ResourceModel\AddressRepository
     */
    protected $addressRepository;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker
     */
    protected $bakerResourceModel;

    /**
     * @var \CND\Baker\Api\BakerMetadataInterface
     */
    protected $bakerMetadata;

    /**
     * @var \CND\Baker\Api\Data\BakerSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @param \CND\Baker\Model\BakerFactory $bakerFactory
     * @param \CND\Baker\Model\Data\BakerSecureFactory $bakerSecureFactory
     * @param \CND\Baker\Model\BakerRegistry $bakerRegistry
     * @param \CND\Baker\Model\ResourceModel\AddressRepository $addressRepository
     * @param \CND\Baker\Model\ResourceModel\Baker $bakerResourceModel
     * @param \CND\Baker\Api\BakerMetadataInterface $bakerMetadata
     * @param \CND\Baker\Api\Data\BakerSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param DataObjectHelper $dataObjectHelper
     * @param ImageProcessorInterface $imageProcessor
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param NotificationStorage $notificationStorage
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \CND\Baker\Model\BakerFactory $bakerFactory,
        \CND\Baker\Model\Data\BakerSecureFactory $bakerSecureFactory,
        \CND\Baker\Model\BakerRegistry $bakerRegistry,
        \CND\Baker\Model\ResourceModel\AddressRepository $addressRepository,
        \CND\Baker\Model\ResourceModel\Baker $bakerResourceModel,
        \CND\Baker\Api\BakerMetadataInterface $bakerMetadata,
        \CND\Baker\Api\Data\BakerSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        DataObjectHelper $dataObjectHelper,
        ImageProcessorInterface $imageProcessor,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        NotificationStorage $notificationStorage
    ) {
        $this->bakerFactory = $bakerFactory;
        $this->bakerSecureFactory = $bakerSecureFactory;
        $this->bakerRegistry = $bakerRegistry;
        $this->addressRepository = $addressRepository;
        $this->bakerResourceModel = $bakerResourceModel;
        $this->bakerMetadata = $bakerMetadata;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->notificationStorage = $notificationStorage;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\CND\Baker\Api\Data\BakerInterface $baker, $passwordHash = null)
    {
        $prevBakerData = null;
        $prevBakerDataArr = null;
        if ($baker->getId()) {
            $prevBakerData = $this->getById($baker->getId());
            $prevBakerDataArr = $prevBakerData->__toArray();
        }
        /** @var $baker \CND\Baker\Model\Data\Baker */
        $bakerArr = $baker->__toArray();
        $baker = $this->imageProcessor->save(
            $baker,
            BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $prevBakerData
        );

        $origAddresses = $baker->getAddresses();
        $baker->setAddresses([]);
        $bakerData = $this->extensibleDataObjectConverter->toNestedArray(
            $baker,
            [],
            \CND\Baker\Api\Data\BakerInterface::class
        );

        $baker->setAddresses($origAddresses);
        $bakerModel = $this->bakerFactory->create(['data' => $bakerData]);
        $storeId = $bakerModel->getStoreId();
        if ($storeId === null) {
            $bakerModel->setStoreId($this->storeManager->getStore()->getId());
        }
        $bakerModel->setId($baker->getId());

        // Need to use attribute set or future updates can cause data loss
        if (!$bakerModel->getAttributeSetId()) {
            $bakerModel->setAttributeSetId(
                \CND\Baker\Api\BakerMetadataInterface::ATTRIBUTE_SET_ID_BAKER
            );
        }
        $this->populateBakerWithSecureData($bakerModel, $passwordHash);

        // If baker email was changed, reset RpToken info
        if ($prevBakerData
            && $prevBakerData->getEmail() !== $bakerModel->getEmail()
        ) {
            $bakerModel->setRpToken(null);
            $bakerModel->setRpTokenCreatedAt(null);
        }
        if (!array_key_exists('default_billing', $bakerArr) &&
            null !== $prevBakerDataArr &&
            array_key_exists('default_billing', $prevBakerDataArr)
        ) {
            $bakerModel->setDefaultBilling($prevBakerDataArr['default_billing']);
        }

        if (!array_key_exists('default_shipping', $bakerArr) &&
            null !== $prevBakerDataArr &&
            array_key_exists('default_shipping', $prevBakerDataArr)
        ) {
            $bakerModel->setDefaultShipping($prevBakerDataArr['default_shipping']);
        }

        $bakerModel->save();
        $this->bakerRegistry->push($bakerModel);
        $bakerId = $bakerModel->getId();

        if ($baker->getAddresses() !== null) {
            if ($baker->getId()) {
                $existingAddresses = $this->getById($baker->getId())->getAddresses();
                $getIdFunc = function ($address) {
                    return $address->getId();
                };
                $existingAddressIds = array_map($getIdFunc, $existingAddresses);
            } else {
                $existingAddressIds = [];
            }

            $savedAddressIds = [];
            foreach ($baker->getAddresses() as $address) {
                $address->setBakerId($bakerId)
                    ->setRegion($address->getRegion());
                $this->addressRepository->save($address);
                if ($address->getId()) {
                    $savedAddressIds[] = $address->getId();
                }
            }

            $addressIdsToDelete = array_diff($existingAddressIds, $savedAddressIds);
            foreach ($addressIdsToDelete as $addressId) {
                $this->addressRepository->deleteById($addressId);
            }
        }
        $this->bakerRegistry->remove($bakerId);
        $savedBaker = $this->get($baker->getEmail(), $baker->getWebsiteId());
        $this->eventManager->dispatch(
            'baker_save_after_data_object',
            ['baker_data_object' => $savedBaker, 'orig_baker_data_object' => $prevBakerData]
        );
        return $savedBaker;
    }

    /**
     * Set secure data to baker model
     *
     * @param \CND\Baker\Model\Baker $bakerModel
     * @param string|null $passwordHash
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return void
     */
    private function populateBakerWithSecureData($bakerModel, $passwordHash = null)
    {
        if ($bakerModel->getId()) {
            $bakerSecure = $this->bakerRegistry->retrieveSecureData($bakerModel->getId());

            $bakerModel->setRpToken($passwordHash ? null : $bakerSecure->getRpToken());
            $bakerModel->setRpTokenCreatedAt($passwordHash ? null : $bakerSecure->getRpTokenCreatedAt());
            $bakerModel->setPasswordHash($passwordHash ?: $bakerSecure->getPasswordHash());

            $bakerModel->setFailuresNum($bakerSecure->getFailuresNum());
            $bakerModel->setFirstFailure($bakerSecure->getFirstFailure());
            $bakerModel->setLockExpires($bakerSecure->getLockExpires());
        } elseif ($passwordHash) {
            $bakerModel->setPasswordHash($passwordHash);
        }

        if ($passwordHash && $bakerModel->getId()) {
            $this->bakerRegistry->remove($bakerModel->getId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($email, $websiteId = null)
    {
        $bakerModel = $this->bakerRegistry->retrieveByEmail($email, $websiteId);
        return $bakerModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($bakerId)
    {
        $bakerModel = $this->bakerRegistry->retrieve($bakerId);
        return $bakerModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \CND\Baker\Model\ResourceModel\Baker\Collection $collection */
        $collection = $this->bakerFactory->create()->getCollection();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \CND\Baker\Api\Data\BakerInterface::class
        );
        // This is needed to make sure all the attributes are properly loaded
        foreach ($this->bakerMetadata->getAllAttributesMetadata() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }
        // Needed to enable filtering on name as a whole
        $collection->addNameToSelect();
        // Needed to enable filtering based on billing address attributes
        $collection->joinAttribute('billing_postcode', 'baker_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'baker_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'baker_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'baker_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'baker_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('company', 'baker_address/company', 'default_billing', null, 'left');

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());

        $bakers = [];
        /** @var \CND\Baker\Model\Baker $bakerModel */
        foreach ($collection as $bakerModel) {
            $bakers[] = $bakerModel->getDataModel();
        }
        $searchResults->setItems($bakers);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\CND\Baker\Api\Data\BakerInterface $baker)
    {
        return $this->deleteById($baker->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($bakerId)
    {
        $bakerModel = $this->bakerRegistry->retrieve($bakerId);
        $bakerModel->delete();
        $this->bakerRegistry->remove($bakerId);
        $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $bakerId);

        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 100.2.0
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \CND\Baker\Model\ResourceModel\Baker\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \CND\Baker\Model\ResourceModel\Baker\Collection $collection
    ) {
        $fields = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = ['attribute' => $filter->getField(), $condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }
}
