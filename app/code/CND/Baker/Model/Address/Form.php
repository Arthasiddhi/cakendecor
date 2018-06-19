<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Address Form Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace CND\Baker\Model\Address;

class Form extends \CND\Baker\Model\Form
{
    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'entity_address';
}
