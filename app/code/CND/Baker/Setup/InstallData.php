<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * baker setup factory
     *
     * @var BakerSetupFactory
     */
    private $bakerSetupFactory;

    /**
     * Init
     *
     * @param BakerSetupFactory $bakerSetupFactory
     */
    public function __construct(BakerSetupFactory $bakerSetupFactory)
    {
        $this->bakerSetupFactory = $bakerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var bakerSetup $bakerSetup */
        $bakerSetup = $this->bakerSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        // insert default baker groups
        $setup->getConnection()->insertForce(
            $setup->getTable('baker_group'),
            ['baker_group_id' => 0, 'baker_group_code' => 'NOT LOGGED IN', 'tax_class_id' => 3]
        );
        $setup->getConnection()->insertForce(
            $setup->getTable('baker_group'),
            ['baker_group_id' => 1, 'baker_group_code' => 'General', 'tax_class_id' => 3]
        );
        $setup->getConnection()->insertForce(
            $setup->getTable('baker_group'),
            ['baker_group_id' => 2, 'baker_group_code' => 'Wholesale', 'tax_class_id' => 3]
        );
        $setup->getConnection()->insertForce(
            $setup->getTable('baker_group'),
            ['baker_group_id' => 3, 'baker_group_code' => 'Retailer', 'tax_class_id' => 3]
        );

        $bakerSetup->installEntities();

        $bakerSetup->installbakerForms();

        $disableAGCAttribute = $bakerSetup->getEavConfig()->getAttribute('baker', 'disable_auto_group_change');
        $disableAGCAttribute->setData('used_in_forms', ['adminhtml_baker']);
        $disableAGCAttribute->save();

        $attributesInfo = [
            'vat_id' => [
                'label' => 'VAT number',
                'type' => 'static',
                'input' => 'text',
                'position' => 140,
                'visible' => true,
                'required' => false,
            ],
            'vat_is_valid' => [
                'label' => 'VAT number validity',
                'visible' => false,
                'required' => false,
                'type' => 'static',
            ],
            'vat_request_id' => [
                'label' => 'VAT number validation request ID',
                'type' => 'static',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_date' => [
                'label' => 'VAT number validation request date',
                'type' => 'static',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_success' => [
                'label' => 'VAT number validation request success',
                'visible' => false,
                'required' => false,
                'type' => 'static',
            ],
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $bakerSetup->addAttribute('baker_address', $attributeCode, $attributeParams);
        }

        $vatIdAttribute = $bakerSetup->getEavConfig()->getAttribute('baker_address', 'vat_id');
        $vatIdAttribute->setData(
            'used_in_forms',
            ['adminhtml_baker_address', 'baker_address_edit', 'baker_register_address']
        );
        $vatIdAttribute->save();

        $entities = $bakerSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $bakerSetup->addEntityType($entityName, $entity);
        }

        $bakerSetup->updateAttribute(
            'baker_address',
            'street',
            'backend_model',
            \Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class
        );

        $migrationSetup = $setup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'baker_eav_attribute',
            'data_model',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->doUpdateClassAliases();

        $setup->endSetup();
    }
}
