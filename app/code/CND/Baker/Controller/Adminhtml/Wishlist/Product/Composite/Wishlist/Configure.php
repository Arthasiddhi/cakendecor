<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;

use Exception;

class Configure extends \CND\Baker\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist
{
    /**
     * Ajax handler to response configuration fieldset of composite product in baker's wishlist.
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $configureResult = new \Magento\Framework\DataObject();
        try {
            $this->_initData();

            $configureResult->setProductId($this->_wishlistItem->getProductId());
            $configureResult->setBuyRequest($this->_wishlistItem->getBuyRequest());
            $configureResult->setCurrentStoreId($this->_wishlistItem->getStoreId());
            $configureResult->setCurrentBakerId($this->_wishlist->getBakerId());

            $configureResult->setOk(true);
        } catch (Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        return $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class)
            ->renderConfigureResult($configureResult);
    }
}
