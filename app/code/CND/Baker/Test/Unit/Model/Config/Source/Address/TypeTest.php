<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Config\Source\Address;

class TypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\Config\Source\Address\Type
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \CND\Baker\Model\Config\Source\Address\Type();
    }

    public function testToOptionArray()
    {
        $expected = ['billing' => 'Billing Address','shipping' => 'Shipping Address'];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
