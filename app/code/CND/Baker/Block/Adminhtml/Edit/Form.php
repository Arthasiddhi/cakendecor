<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Edit;

use CND\Baker\Controller\RegistryConstants;

/**
 * Adminhtml baker edit form block
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Baker Repository.
     *
     * @var \CND\Baker\Api\BakerRepositoryInterface
     */
    protected $_bakerRepository;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \CND\Baker\Api\BakerRepositoryInterface $bakerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \CND\Baker\Api\BakerRepositoryInterface $bakerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        array $data = []
    ) {
        $this->_bakerRepository = $bakerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare the form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('baker/*/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $bakerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        if ($bakerId) {
            $form->addField('id', 'hidden', ['name' => 'baker_id']);
            $baker = $this->_bakerRepository->getById($bakerId);
            $form->setValues(
                $this->_extensibleDataObjectConverter->toFlatArray(
                    $baker,
                    [],
                    \CND\Baker\Api\Data\BakerInterface::class
                )
            )->addValues(
                ['baker_id' => $bakerId]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
