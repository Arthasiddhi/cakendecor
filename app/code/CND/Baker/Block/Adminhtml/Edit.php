<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Controller\RegistryConstants;

/**
 * @deprecated 100.2.0 for UiComponent replacement
 * @see app/code/Magento/Baker/view/base/ui_component/baker_form.xml
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var AccountManagementInterface
     */
    protected $bakerAccountManagement;

    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * Baker view helper
     *
     * @var \CND\Baker\Helper\View
     */
    protected $_viewHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AccountManagementInterface $bakerAccountManagement
     * @param BakerRepositoryInterface $bakerRepository
     * @param \CND\Baker\Helper\View $viewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        AccountManagementInterface $bakerAccountManagement,
        BakerRepositoryInterface $bakerRepository,
        \CND\Baker\Helper\View $viewHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->bakerAccountManagement = $bakerAccountManagement;
        $this->bakerRepository = $bakerRepository;
        $this->_viewHelper = $viewHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'CND_Baker';

        $bakerId = $this->getBakerId();

        if ($bakerId && $this->_authorization->isAllowed('Magento_Sales::create')) {
            $this->buttonList->add(
                'order',
                [
                    'label' => __('Create Order'),
                    'onclick' => 'setLocation(\'' . $this->getCreateOrderUrl() . '\')',
                    'class' => 'add'
                ],
                0
            );
        }

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Baker'));
        $this->buttonList->update('delete', 'label', __('Delete Baker'));

        if ($bakerId && $this->bakerAccountManagement->isReadonly($bakerId)) {
            $this->buttonList->remove('save');
            $this->buttonList->remove('reset');
        }

        if (!$bakerId || $this->bakerAccountManagement->isReadonly($bakerId)) {
            $this->buttonList->remove('delete');
        }

        if ($bakerId) {
            $url = $this->getUrl('baker/index/resetPassword', ['baker_id' => $bakerId]);
            $this->buttonList->add(
                'reset_password',
                [
                    'label' => __('Reset Password'),
                    'onclick' => 'setLocation(\'' . $url . '\')',
                    'class' => 'reset reset-password'
                ],
                0
            );
        }

        if ($bakerId) {
            $url = $this->getUrl('baker/baker/invalidateToken', ['baker_id' => $bakerId]);
            $deleteConfirmMsg = __("Are you sure you want to revoke the baker's tokens?");
            $this->buttonList->add(
                'invalidate_token',
                [
                    'label' => __('Force Sign-In'),
                    'onclick' => 'deleteConfirm(\'' . $this->escapeJs($this->escapeHtml($deleteConfirmMsg)) .
                        '\', \'' . $url . '\')',
                    'class' => 'invalidate-token'
                ],
                10
            );
        }
    }

    /**
     * Retrieve the Url for creating an order.
     *
     * @return string
     */
    public function getCreateOrderUrl()
    {
        return $this->getUrl('sales/order_create/start', ['baker_id' => $this->getBakerId()]);
    }

    /**
     * Return the baker Id.
     *
     * @return int|null
     */
    public function getBakerId()
    {
        $bakerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        return $bakerId;
    }

    /**
     * Retrieve the header text, either the name of an existing baker or 'New Baker'.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        $bakerId = $this->getBakerId();
        if ($bakerId) {
            $bakerData = $this->bakerRepository->getById($bakerId);
            return $this->escapeHtml($this->_viewHelper->getBakerName($bakerData));
        } else {
            return __('New Baker');
        }
    }

    /**
     * Prepare form Html. Add block for configurable product modification interface.
     *
     * @return string
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Composite\Configure::class
        )->toHtml();
        return $html;
    }

    /**
     * Retrieve baker validation Url.
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('baker/*/validate', ['_current' => true]);
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $bakerId = $this->getBakerId();
        if (!$bakerId || !$this->bakerAccountManagement->isReadonly($bakerId)) {
            $this->buttonList->add(
                'save_and_continue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                10
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve the save and continue edit Url.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'baker/index/save',
            ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}']
        );
    }
}
