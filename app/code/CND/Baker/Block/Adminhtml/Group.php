<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml bakers group page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace CND\Baker\Block\Adminhtml;

/**
 * @api
 * @since 100.0.2
 */
class Group extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify header & button labels
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'baker_group';
        $this->_headerText = __('Baker Groups');
        $this->_addButtonLabel = __('Add New Baker Group');
        parent::_construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-baker-groups';
    }
}
