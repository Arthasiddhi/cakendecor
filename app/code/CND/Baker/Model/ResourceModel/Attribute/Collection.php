<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Baker EAV additional attribute resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace CND\Baker\Model\ResourceModel\Attribute;

class Collection extends \Magento\Eav\Model\ResourceModel\Attribute\Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'baker';

    /**
     * Default attribute entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return $this->_entityTypeCode;
    }

    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     */
    protected function _getEavWebsiteTable()
    {
        return $this->getTable('baker_eav_attribute_website');
    }
}
