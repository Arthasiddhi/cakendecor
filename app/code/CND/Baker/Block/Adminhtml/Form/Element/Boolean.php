<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Baker Widget Form Boolean Element Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace CND\Baker\Block\Adminhtml\Form\Element;

class Boolean extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Prepare default SELECT values
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setValues([['label' => __('No'), 'value' => '0'], ['label' => __('Yes'), 'value' => 1]]);
    }
}
