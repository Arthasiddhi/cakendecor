<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Edit;

use CND\Baker\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 * @package CND\Baker\Block\Adminhtml\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
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
        $canModify = !$bakerId || !$this->bakerAccountManagement->isReadonly($this->getBakerId());
        $data = [];
        if ($canModify) {
            $data = [
                'label' => __('Save Baker'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 90,
            ];
        }
        return $data;
    }
}
