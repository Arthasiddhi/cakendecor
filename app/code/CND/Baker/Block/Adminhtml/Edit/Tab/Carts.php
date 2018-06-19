<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Edit\Tab;

/**
 * Obtain all carts contents for specified client
 *
 * @api
 * @since 100.0.2
 */
class Carts extends \Magento\Backend\Block\Template
{
    /**
     * @var \CND\Baker\Model\Config\Share
     */
    protected $_shareConfig;

    /**
     * @var \CND\Baker\Api\Data\BakerInterfaceFactory
     */
    protected $bakerDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context          $context
     * @param \CND\Baker\Model\Config\Share             $shareConfig
     * @param \CND\Baker\Api\Data\BakerInterfaceFactory $bakerDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \CND\Baker\Model\Config\Share $shareConfig,
        \CND\Baker\Api\Data\BakerInterfaceFactory $bakerDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->_shareConfig = $shareConfig;
        $this->bakerDataFactory = $bakerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $data);
    }

    /**
     * Add shopping cart grid of each website
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $sharedWebsiteIds = $this->_shareConfig->getSharedWebsiteIds($this->_getBaker()->getWebsiteId());
        $isShared = count($sharedWebsiteIds) > 1;
        foreach ($sharedWebsiteIds as $websiteId) {
            $blockName = 'baker_cart_' . $websiteId;
            $block = $this->getLayout()->createBlock(
                \CND\Baker\Block\Adminhtml\Edit\Tab\Cart::class,
                $blockName,
                ['data' => ['website_id' => $websiteId]]
            );
            if ($isShared) {
                $websiteName = $this->_storeManager->getWebsite($websiteId)->getName();
                $block->setCartHeader(__('Shopping Cart from %1', $websiteName));
            }
            $this->setChild($blockName, $block);
        }
        return parent::_prepareLayout();
    }

    /**
     * Just get child blocks html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('adminhtml_block_html_before', ['block' => $this]);
        return $this->getChildHtml();
    }

    /**
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    protected function _getBaker()
    {
        $bakerDataObject = $this->bakerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $bakerDataObject,
            $this->_backendSession->getBakerData()['account'],
            \CND\Baker\Api\Data\BakerInterface::class
        );
        return $bakerDataObject;
    }
}
