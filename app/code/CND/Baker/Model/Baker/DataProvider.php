<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Baker;

use CND\Baker\Api\AddressMetadataInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\Data\AddressInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Model\Address;
use CND\Baker\Model\Attribute;
use CND\Baker\Model\Baker;
use CND\Baker\Model\FileProcessor;
use CND\Baker\Model\FileProcessorFactory;
use CND\Baker\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use CND\Baker\Model\ResourceModel\Baker\Collection;
use CND\Baker\Model\ResourceModel\Baker\CollectionFactory as BakerCollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    const MAX_FILE_SIZE = 2097152;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var CountryWithWebsites
     */
    private $countryWithWebsiteSource;

    /**
     * @var \CND\Baker\Model\Config\Share
     */
    private $shareConfig;

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    protected $metaProperties = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
    ];

    /**
     * Form element mapping
     *
     * @var array
     */
    protected $formElement = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var SessionManagerInterface
     * @since 100.1.0
     */
    protected $session;

    /**
     * @var FileProcessorFactory
     */
    private $fileProcessorFactory;

    /**
     * File types allowed for file_uploader UI component
     *
     * @var array
     */
    private $fileUploaderTypes = [
        'image',
        'file',
    ];

    /**
     * Baker fields that must be removed
     *
     * @var array
     */
    private $forbiddenBakerFields = [
        'password_hash',
        'rp_token',
        'confirmation',
    ];

    /*
     * @var ContextInterface
     */
    private $context;

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param BakerCollectionFactory $bakerCollectionFactory
     * @param Config $eavConfig
     * @param FilterPool $filterPool
     * @param FileProcessorFactory $fileProcessorFactory
     * @param ContextInterface $context
     * @param array $meta
     * @param array $data
     * @param bool $allowToShowHiddenAttributes
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        BakerCollectionFactory $bakerCollectionFactory,
        Config $eavConfig,
        FilterPool $filterPool,
        FileProcessorFactory $fileProcessorFactory = null,
        array $meta = [],
        array $data = [],
        ContextInterface $context = null,
        $allowToShowHiddenAttributes = true
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $bakerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->filterPool = $filterPool;
        $this->fileProcessorFactory = $fileProcessorFactory ?: $this->getFileProcessorFactory();
        $this->context = $context ?: ObjectManager::getInstance()->get(ContextInterface::class);
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->meta['baker']['children'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('baker')
        );
        $this->meta['address']['children'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('baker_address')
        );
    }

    /**
     * Get session object
     *
     * @return SessionManagerInterface
     * @deprecated 100.1.3
     * @since 100.1.0
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()->get(
                \Magento\Framework\Session\SessionManagerInterface::class
            );
        }
        return $this->session;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Baker $baker */
        foreach ($items as $baker) {
            $result['baker'] = $baker->getData();

            $this->overrideFileUploaderData($baker, $result['baker']);

            $result['baker'] = array_diff_key(
                $result['baker'],
                array_flip($this->forbiddenBakerFields)
            );
            unset($result['address']);

            /** @var Address $address */
            foreach ($baker->getAddresses() as $address) {
                $addressId = $address->getId();
                $address->load($addressId);
                $result['address'][$addressId] = $address->getData();
                $this->prepareAddressData($addressId, $result['address'], $result['baker']);

                $this->overrideFileUploaderData($address, $result['address'][$addressId]);
            }
            $this->loadedData[$baker->getId()] = $result;
        }

        $data = $this->getSession()->getBakerFormData();
        if (!empty($data)) {
            $bakerId = isset($data['baker']['entity_id']) ? $data['baker']['entity_id'] : null;
            $this->loadedData[$bakerId] = $data;
            $this->getSession()->unsBakerFormData();
        }

        return $this->loadedData;
    }

    /**
     * Override file uploader UI component data
     *
     * Overrides data for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Baker|Address $entity
     * @param array $entityData
     * @return void
     */
    private function overrideFileUploaderData($entity, array &$entityData)
    {
        $attributes = $entity->getAttributes();
        foreach ($attributes as $attribute) {
            /** @var Attribute $attribute */
            if (in_array($attribute->getFrontendInput(), $this->fileUploaderTypes)) {
                $entityData[$attribute->getAttributeCode()] = $this->getFileUploaderData(
                    $entity->getEntityType(),
                    $attribute,
                    $entityData
                );
            }
        }
    }

    /**
     * Retrieve array of values required by file uploader UI component
     *
     * @param Type $entityType
     * @param Attribute $attribute
     * @param array $bakerData
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getFileUploaderData(
        Type $entityType,
        Attribute $attribute,
        array $bakerData
    ) {
        $attributeCode = $attribute->getAttributeCode();

        $file = isset($bakerData[$attributeCode])
            ? $bakerData[$attributeCode]
            : '';

        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->getFileProcessorFactory()->create([
            'entityTypeCode' => $entityType->getEntityTypeCode(),
        ]);

        if (!empty($file)
            && $fileProcessor->isExist($file)
        ) {
            $stat = $fileProcessor->getStat($file);
            $viewUrl = $fileProcessor->getViewUrl($file, $attribute->getFrontendInput());

            return [
                [
                    'file' => $file,
                    'size' => isset($stat) ? $stat['size'] : 0,
                    'url' => isset($viewUrl) ? $viewUrl : '',
                    'name' => basename($file),
                    'type' => $fileProcessor->getMimeType($file),
                ],
            ];
        }

        return [];
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $this->processFrontendInput($attribute, $meta);

            $code = $attribute->getAttributeCode();

            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code]['arguments']['data']['config'][$metaName] = ($metaName === 'label') ? __($value) : $value;
                if ('frontend_input' === $origName) {
                    $meta[$code]['arguments']['data']['config']['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }
            }

            if ($attribute->usesSource()) {
                if ($code == AddressInterface::COUNTRY_ID) {
                    $meta[$code]['arguments']['data']['config']['options'] = $this->getCountryWithWebsiteSource()
                        ->getAllOptions();
                } else {
                    $meta[$code]['arguments']['data']['config']['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]['arguments']['data']['config']);
            if (!empty($rules)) {
                $meta[$code]['arguments']['data']['config']['validation'] = $rules;
            }

            $meta[$code]['arguments']['data']['config']['componentType'] = Field::NAME;
            $meta[$code]['arguments']['data']['config']['visible'] = $this->canShowAttribute($attribute);

            $this->overrideFileUploaderMetadata($entityType, $attribute, $meta[$code]['arguments']['data']['config']);
        }

        $this->processWebsiteMeta($meta);
        return $meta;
    }

    /**
     * Check whether the specific attribute can be shown in form: baker registration, baker edit, etc...
     *
     * @param Attribute $bakerAttribute
     * @return bool
     */
    private function canShowAttributeInForm(AbstractAttribute $bakerAttribute)
    {
        $isRegistration = $this->context->getRequestParam($this->getRequestFieldName()) === null;

        if ($bakerAttribute->getEntityType()->getEntityTypeCode() === 'baker') {
            return is_array($bakerAttribute->getUsedInForms()) &&
                (
                    (in_array('baker_account_create', $bakerAttribute->getUsedInForms()) && $isRegistration) ||
                    (in_array('baker_account_edit', $bakerAttribute->getUsedInForms()) && !$isRegistration)
                );
        } else {
            return is_array($bakerAttribute->getUsedInForms()) &&
                in_array('baker_address_edit', $bakerAttribute->getUsedInForms());
        }
    }

    /**
     * Detect can we show attribute on specific form or not
     *
     * @param Attribute $bakerAttribute
     * @return bool
     */
    private function canShowAttribute(AbstractAttribute $bakerAttribute)
    {
        $userDefined = (bool) $bakerAttribute->getIsUserDefined();
        if (!$userDefined) {
            return $bakerAttribute->getIsVisible();
        }

        $canShowOnForm = $this->canShowAttributeInForm($bakerAttribute);

        return ($this->allowToShowHiddenAttributes && $canShowOnForm) ||
            (!$this->allowToShowHiddenAttributes && $canShowOnForm && $bakerAttribute->getIsVisible());
    }

    /**
     * Retrieve Country With Websites Source
     *
     * @return CountryWithWebsites
     * @deprecated 100.2.0
     */
    private function getCountryWithWebsiteSource()
    {
        if (!$this->countryWithWebsiteSource) {
            $this->countryWithWebsiteSource = ObjectManager::getInstance()->get(CountryWithWebsites::class);
        }

        return $this->countryWithWebsiteSource;
    }

    /**
     * Retrieve Baker Config Share
     *
     * @return \CND\Baker\Model\Config\Share
     * @deprecated 100.1.3
     */
    private function getShareConfig()
    {
        if (!$this->shareConfig) {
            $this->shareConfig = ObjectManager::getInstance()->get(\CND\Baker\Model\Config\Share::class);
        }

        return $this->shareConfig;
    }

    /**
     * Add global scope parameter and filter options to website meta
     *
     * @param array $meta
     * @return void
     */
    private function processWebsiteMeta(&$meta)
    {
        if (isset($meta[BakerInterface::WEBSITE_ID]) && $this->getShareConfig()->isGlobalScope()) {
            $meta[BakerInterface::WEBSITE_ID]['arguments']['data']['config']['isGlobalScope'] = 1;
        }

        if (isset($meta[AddressInterface::COUNTRY_ID]) && !$this->getShareConfig()->isGlobalScope()) {
            $meta[AddressInterface::COUNTRY_ID]['arguments']['data']['config']['filterBy'] = [
                'target' => '${ $.provider }:data.baker.website_id',
                'field' => 'website_ids'
            ];
        }
    }

    /**
     * Override file uploader UI component metadata
     *
     * Overrides metadata for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Type $entityType
     * @param AbstractAttribute $attribute
     * @param array $config
     * @return void
     */
    private function overrideFileUploaderMetadata(
        Type $entityType,
        AbstractAttribute $attribute,
        array &$config
    ) {
        if (in_array($attribute->getFrontendInput(), $this->fileUploaderTypes)) {
            $maxFileSize = self::MAX_FILE_SIZE;

            if (isset($config['validation']['max_file_size'])) {
                $maxFileSize = (int)$config['validation']['max_file_size'];
            }

            $allowedExtensions = [];

            if (isset($config['validation']['file_extensions'])) {
                $allowedExtensions = explode(',', $config['validation']['file_extensions']);
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });
            }

            $allowedExtensions = implode(' ', $allowedExtensions);

            $entityTypeCode = $entityType->getEntityTypeCode();
            $url = $this->getFileUploadUrl($entityTypeCode);

            $config = [
                'formElement' => 'fileUploader',
                'componentType' => 'fileUploader',
                'maxFileSize' => $maxFileSize,
                'allowedExtensions' => $allowedExtensions,
                'uploaderConfig' => [
                    'url' => $url,
                ],
                'label' => $this->getMetadataValue($config, 'label'),
                'sortOrder' => $this->getMetadataValue($config, 'sortOrder'),
                'required' => $this->getMetadataValue($config, 'required'),
                'visible' => $this->getMetadataValue($config, 'visible'),
                'validation' => $this->getMetadataValue($config, 'validation'),
            ];
        }
    }

    /**
     * Retrieve metadata value
     *
     * @param array $config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function getMetadataValue($config, $name, $default = null)
    {
        $value = isset($config[$name]) ? $config[$name] : $default;
        return $value;
    }

    /**
     * Retrieve URL to file upload
     *
     * @param string $entityTypeCode
     * @return string
     */
    private function getFileUploadUrl($entityTypeCode)
    {
        switch ($entityTypeCode) {
            case BakerMetadataInterface::ENTITY_TYPE_CUSTOMER:
                $url = 'baker/file/baker_upload';
                break;

            case AddressMetadataInterface::ENTITY_TYPE_ADDRESS:
                $url = 'baker/file/address_upload';
                break;

            default:
                $url = '';
                break;
        }
        return $url;
    }

    /**
     * Process attributes by frontend input type
     *
     * @param AttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function processFrontendInput(AttributeInterface $attribute, array &$meta)
    {
        $code = $attribute->getAttributeCode();
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta[$code]['arguments']['data']['config']['prefer'] = 'toggle';
            $meta[$code]['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }
    }

    /**
     * Prepare address data
     *
     * @param int $addressId
     * @param array $addresses
     * @param array $baker
     * @return void
     */
    protected function prepareAddressData($addressId, array &$addresses, array $baker)
    {
        if (isset($baker['default_billing'])
            && $addressId == $baker['default_billing']
        ) {
            $addresses[$addressId]['default_billing'] = $baker['default_billing'];
        }
        if (isset($baker['default_shipping'])
            && $addressId == $baker['default_shipping']
        ) {
            $addresses[$addressId]['default_shipping'] = $baker['default_shipping'];
        }
        if (isset($addresses[$addressId]['street']) && !is_array($addresses[$addressId]['street'])) {
            $addresses[$addressId]['street'] = explode("\n", $addresses[$addressId]['street']);
        }
    }

    /**
     * Get FileProcessorFactory instance
     *
     * @return FileProcessorFactory
     * @deprecated 100.1.3
     */
    private function getFileProcessorFactory()
    {
        if ($this->fileProcessorFactory === null) {
            $this->fileProcessorFactory = ObjectManager::getInstance()
                ->get(\CND\Baker\Model\FileProcessorFactory::class);
        }
        return $this->fileProcessorFactory;
    }
}
