<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Edit;

use CND\Baker\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package CND\Baker\Block\Adminhtml\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $bakerAccountManagement;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AccountManagementInterface $bakerAccountManagement
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        AccountManagementInterface $bakerAccountManagement
    ) {
        parent::__construct($context, $registry);
        $this->bakerAccountManagement = $bakerAccountManagement;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $bakerId = $this->getBakerId();
        $canModify = $bakerId && !$this->bakerAccountManagement->isReadonly($this->getBakerId());
        $data = [];
        if ($bakerId && $canModify) {
            $data = [
                'label' => __('Delete Baker'),
                'class' => 'delete',
                'id' => 'baker-edit-delete-button',
                'data_attribute' => [
                    'url' => $this->getDeleteUrl()
                ],
                'on_click' => '',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getBakerId()]);
    }
}
