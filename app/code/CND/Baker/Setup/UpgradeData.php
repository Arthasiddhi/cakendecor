<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Setup;

use CND\Baker\Model\Baker;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Baker setup factory
     *
     * @var BakerSetupFactory
     */
    protected $bakerSetupFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @param BakerSetupFactory $bakerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param FieldDataConverterFactory|null $fieldDataConverterFactory
     */
    public function __construct(
        BakerSetupFactory $bakerSetupFactory,
        IndexerRegistry $indexerRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        FieldDataConverterFactory $fieldDataConverterFactory = null
    ) {
        $this->bakerSetupFactory = $bakerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;

        $this->fieldDataConverterFactory = $fieldDataConverterFactory ?: ObjectManager::getInstance()->get(
            FieldDataConverterFactory::class
        );
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var BakerSetup $bakerSetup */
        $bakerSetup = $this->bakerSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $this->upgradeVersionTwoZeroSix($bakerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeVersionTwoZeroOne($bakerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $this->upgradeVersionTwoZeroTwo($bakerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->upgradeVersionTwoZeroThree($bakerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $this->upgradeVersionTwoZeroFour($bakerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->upgradeVersionTwoZeroFive($bakerSetup, $setup);
        }

        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $setup->getConnection()->delete(
                $setup->getTable('baker_form_attribute'),
                ['form_code = ?' => 'checkout_register']
            );
        }

        if (version_compare($context->getVersion(), '2.0.8', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => \CND\Baker\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE],
                ['path = ?' => 'general/restriction/autocomplete_on_storefront']
            );
        }

        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $this->upgradeVersionTwoZeroSeven($bakerSetup);
            $this->upgradeBakerPasswordResetlinkExpirationPeriodConfig($setup);
        }

        if (version_compare($context->getVersion(), '2.0.9', '<')) {
            $setup->getConnection()->beginTransaction();

            try {
                $this->migrateStoresAllowedCountriesToWebsite($setup);
                $setup->getConnection()->commit();
            } catch (\Exception $e) {
                $setup->getConnection()->rollBack();
                throw $e;
            }
        }
        if (version_compare($context->getVersion(), '2.0.11', '<')) {
            $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
            $fieldDataConverter->convert(
                $setup->getConnection(),
                $setup->getTable('baker_eav_attribute'),
                'attribute_id',
                'validate_rules'
            );
        }

        if (version_compare($context->getVersion(), '2.0.12', '<')) {
            $this->upgradeVersionTwoZeroTwelve($bakerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.13', '<')) {
            $this->upgradeVersionTwoZeroThirteen($bakerSetup);
        }

        $indexer = $this->indexerRegistry->get(Baker::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();
        $setup->endSetup();
    }

    /**
     * Retrieve Store Manager
     *
     * @deprecated 100.1.3
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (!$this->storeManager) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;
    }

    /**
     * Retrieve Allowed Countries Reader
     *
     * @deprecated 100.1.3
     * @return AllowedCountries
     */
    private function getAllowedCountriesReader()
    {
        if (!$this->allowedCountriesReader) {
            $this->allowedCountriesReader = ObjectManager::getInstance()->get(AllowedCountries::class);
        }

        return $this->allowedCountriesReader;
    }

    /**
     * Merge allowed countries between different scopes
     *
     * @param array $countries
     * @param array $newCountries
     * @param string $identifier
     * @return array
     */
    private function mergeAllowedCountries(array $countries, array $newCountries, $identifier)
    {
        if (!isset($countries[$identifier])) {
            $countries[$identifier] = $newCountries;
        } else {
            $countries[$identifier] =
                array_replace($countries[$identifier], $newCountries);
        }

        return $countries;
    }

    /**
     * Retrieve countries not depending on global scope
     *
     * @param string $scope
     * @param int $scopeCode
     * @return array
     */
    private function getAllowedCountries($scope, $scopeCode)
    {
        $reader = $this->getAllowedCountriesReader();
        return $reader->makeCountriesUnique($reader->getCountriesFromConfig($scope, $scopeCode));
    }

    /**
     * Merge allowed countries from stores to websites
     *
     * @param SetupInterface $setup
     * @return void
     */
    private function migrateStoresAllowedCountriesToWebsite(SetupInterface $setup)
    {
        $allowedCountries = [];
        //Process Websites
        foreach ($this->getStoreManager()->getStores() as $store) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_STORE, $store->getId()),
                $store->getWebsiteId()
            );
        }
        //Process stores
        foreach ($this->getStoreManager()->getWebsites() as $website) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId()),
                $website->getId()
            );
        }

        $connection = $setup->getConnection();

        //Remove everything from stores scope
        $connection->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                'scope = ?' => ScopeInterface::SCOPE_STORES
            ]
        );

        //Update websites
        foreach ($allowedCountries as $scopeId => $countries) {
            $connection->update(
                $setup->getTable('core_config_data'),
                [
                    'value' => implode(',', $countries)
                ],
                [
                    'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                    'scope_id = ?' => $scopeId,
                    'scope = ?' => ScopeInterface::SCOPE_WEBSITES
                ]
            );
        }
    }

    /**
     * @param array $entityAttributes
     * @param BakerSetup $bakerSetup
     * @return void
     */
    protected function upgradeAttributes(array $entityAttributes, BakerSetup $bakerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $bakerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeHash($setup)
    {
        $bakerEntityTable = $setup->getTable('baker_entity');

        $select = $setup->getConnection()->select()->from(
            $bakerEntityTable,
            ['entity_id', 'password_hash']
        );

        $bakers = $setup->getConnection()->fetchAll($select);
        foreach ($bakers as $baker) {
            if ($baker['password_hash'] === null) {
                continue;
            }
            list($hash, $salt) = explode(Encryptor::DELIMITER, $baker['password_hash']);

            $newHash = $baker['password_hash'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password_hash' => $newHash];
            $where = ['entity_id = ?' => (int)$baker['entity_id']];
            $setup->getConnection()->update($bakerEntityTable, $bind, $where);
        }
    }

    /**
     * @param BakerSetup $bakerSetup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function upgradeVersionTwoZeroOne($bakerSetup)
    {
        $entityAttributes = [
            'baker' => [
                'website_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'created_in' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'email' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'group_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'dob' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'taxvat' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'confirmation' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'created_at' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'gender' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
            ],
            'baker_address' => [
                'company' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'street' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'city' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'country_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'region' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'region_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'postcode' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'telephone' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'fax' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $bakerSetup);
    }

    /**
     * @param BakerSetup $bakerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroTwo($bakerSetup)
    {
        $entityTypeId = $bakerSetup->getEntityTypeId(Baker::ENTITY);
        $attributeId = $bakerSetup->getAttributeId($entityTypeId, 'gender');

        $option = ['attribute_id' => $attributeId, 'values' => [3 => 'Not Specified']];
        $bakerSetup->addAttributeOption($option);
    }

    /**
     * @param BakerSetup $bakerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroThree($bakerSetup)
    {
        $entityAttributes = [
            'baker_address' => [
                'region_id' => [
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                ],
                'firstname' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'lastname' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $bakerSetup);
    }

    /**
     * @param BakerSetup $bakerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroFour($bakerSetup)
    {
        $bakerSetup->addAttribute(
            Baker::ENTITY,
            'updated_at',
            [
                'type' => 'static',
                'label' => 'Updated At',
                'input' => 'date',
                'required' => false,
                'sort_order' => 87,
                'visible' => false,
                'system' => false,
            ]
        );
    }

    /**
     * @param BakerSetup $bakerSetup
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeVersionTwoZeroFive($bakerSetup, $setup)
    {
        $this->upgradeHash($setup);
        $entityAttributes = [
            'baker_address' => [
                'fax' => [
                    'is_visible' => false,
                    'is_system' => false,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $bakerSetup);
    }

    /**
     * @param BakerSetup $bakerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroSix($bakerSetup)
    {
        $bakerSetup->updateEntityType(
            \CND\Baker\Model\Baker::ENTITY,
            'entity_model',
            \CND\Baker\Model\ResourceModel\Baker::class
        );
        $bakerSetup->updateEntityType(
            \CND\Baker\Model\Baker::ENTITY,
            'increment_model',
            \Magento\Eav\Model\Entity\Increment\NumericValue::class
        );
        $bakerSetup->updateEntityType(
            \CND\Baker\Model\Baker::ENTITY,
            'entity_attribute_collection',
            \CND\Baker\Model\ResourceModel\Attribute\Collection::class
        );
        $bakerSetup->updateEntityType(
            'baker_address',
            'entity_model',
            \CND\Baker\Model\ResourceModel\Address::class
        );
        $bakerSetup->updateEntityType(
            'baker_address',
            'entity_attribute_collection',
            \CND\Baker\Model\ResourceModel\Address\Attribute\Collection::class
        );
        $bakerSetup->updateAttribute(
            'baker_address',
            'country_id',
            'source_model',
            \CND\Baker\Model\ResourceModel\Address\Attribute\Source\Country::class
        );
        $bakerSetup->updateAttribute(
            'baker_address',
            'region',
            'backend_model',
            \CND\Baker\Model\ResourceModel\Address\Attribute\Backend\Region::class
        );
        $bakerSetup->updateAttribute(
            'baker_address',
            'region_id',
            'source_model',
            \CND\Baker\Model\ResourceModel\Address\Attribute\Source\Region::class
        );
    }

    /**
     * @param BakerSetup $bakerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroSeven($bakerSetup)
    {
        $bakerSetup->addAttribute(
            Baker::ENTITY,
            'failures_num',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'hidden',
                'required' => false,
                'sort_order' => 100,
                'visible' => false,
                'system' => true,
            ]
        );

        $bakerSetup->addAttribute(
            Baker::ENTITY,
            'first_failure',
            [
                'type' => 'static',
                'label' => 'First Failure Date',
                'input' => 'date',
                'required' => false,
                'sort_order' => 110,
                'visible' => false,
                'system' => true,
            ]
        );

        $bakerSetup->addAttribute(
            Baker::ENTITY,
            'lock_expires',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'date',
                'required' => false,
                'sort_order' => 120,
                'visible' => false,
                'system' => true,
            ]
        );
    }

    /**
     * @param BakerSetup $bakerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroTwelve(BakerSetup $bakerSetup)
    {
        $bakerSetup->updateAttribute('baker_address', 'vat_id', 'frontend_label', 'VAT Number');
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeBakerPasswordResetlinkExpirationPeriodConfig($setup)
    {
        $configTable = $setup->getTable('core_config_data');

        $setup->getConnection()->update(
            $configTable,
            ['value' => new \Zend_Db_Expr('value*24')],
            ['path = ?' => \CND\Baker\Model\Baker::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD]
        );
    }

    /**
     * @param BakerSetup $bakerSetup
     */
    private function upgradeVersionTwoZeroThirteen(BakerSetup $bakerSetup)
    {
        $entityAttributes = [
            'baker_address' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
            'baker' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $bakerSetup);
    }
}
