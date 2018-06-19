<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Group\Edit;

use CND\Baker\Controller\RegistryConstants;

/**
 * Adminhtml baker groups edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Baker
     */
    protected $_taxBaker;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var \CND\Baker\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \CND\Baker\Api\Data\GroupInterfaceFactory
     */
    protected $groupDataFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\TaxClass\Source\Baker $taxBaker
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \CND\Baker\Api\GroupRepositoryInterface $groupRepository
     * @param \CND\Baker\Api\Data\GroupInterfaceFactory $groupDataFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\TaxClass\Source\Baker $taxBaker,
        \Magento\Tax\Helper\Data $taxHelper,
        \CND\Baker\Api\GroupRepositoryInterface $groupRepository,
        \CND\Baker\Api\Data\GroupInterfaceFactory $groupDataFactory,
        array $data = []
    ) {
        $this->_taxBaker = $taxBaker;
        $this->_taxHelper = $taxHelper;
        $this->_groupRepository = $groupRepository;
        $this->groupDataFactory = $groupDataFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form for render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $groupId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        /** @var \CND\Baker\Api\Data\GroupInterface $bakerGroup */
        if ($groupId === null) {
            $bakerGroup = $this->groupDataFactory->create();
            $defaultBakerTaxClass = $this->_taxHelper->getDefaultBakerTaxClass();
        } else {
            $bakerGroup = $this->_groupRepository->getById($groupId);
            $defaultBakerTaxClass = $bakerGroup->getTaxClassId();
        }

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Group Information')]);

        $validateClass = sprintf(
            'required-entry validate-length maximum-length-%d',
            \CND\Baker\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
        );
        $name = $fieldset->addField(
            'baker_group_code',
            'text',
            [
                'name' => 'code',
                'label' => __('Group Name'),
                'title' => __('Group Name'),
                'note' => __(
                    'Maximum length must be less then %1 characters.',
                    \CND\Baker\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        if ($bakerGroup->getId() == 0 && $bakerGroup->getCode()) {
            $name->setDisabled(true);
        }

        $fieldset->addField(
            'tax_class_id',
            'select',
            [
                'name' => 'tax_class',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->_taxBaker->toOptionArray(),
            ]
        );

        if ($bakerGroup->getId() !== null) {
            // If edit add id
            $form->addField('id', 'hidden', ['name' => 'id', 'value' => $bakerGroup->getId()]);
        }

        if ($this->_backendSession->getBakerGroupData()) {
            $form->addValues($this->_backendSession->getBakerGroupData());
            $this->_backendSession->setBakerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $bakerGroup->getId(),
                    'baker_group_code' => $bakerGroup->getCode(),
                    'tax_class_id' => $defaultBakerTaxClass,
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('baker/*/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}
