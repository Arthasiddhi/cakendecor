<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Adminhtml\Edit\Tab;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ViewTest
 * @package CND\Baker\Block\Adminhtml\Edit\Tab
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Block\Adminhtml\Edit\Tab\View
     */
    protected $view;

    protected function setUp()
    {
        $registry = $this->createMock(\Magento\Framework\Registry::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $objectManagerHelper->getObject(
            \CND\Baker\Block\Adminhtml\Edit\Tab\View::class,
            [
                'registry' => $registry
            ]
        );
    }

    public function testGetTabLabel()
    {
        $this->assertEquals('Baker View', $this->view->getTabLabel());
    }

    public function testGetTabTitle()
    {
        $this->assertEquals('Baker View', $this->view->getTabTitle());
    }
}
