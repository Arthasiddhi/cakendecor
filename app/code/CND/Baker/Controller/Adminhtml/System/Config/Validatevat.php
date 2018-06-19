<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\System\Config;

/**
 * VAT validation controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Validatevat extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CND_Baker::manage';

    /**
     * Perform baker VAT ID validation
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _validate()
    {
        return $this->_objectManager->get(\CND\Baker\Model\Vat::class)
            ->checkVatNumber(
                $this->getRequest()->getParam('country'),
                $this->getRequest()->getParam('vat')
            );
    }
}
