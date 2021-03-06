<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Observer\Visitor;

use Magento\Framework\Event\Observer;

/**
 * Visitor Observer
 */
class BindQuoteCreateObserver extends AbstractVisitorObserver
{
    /**
     * bindQuoteDestroy
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->visitor->bindQuoteCreate($observer);
    }
}
