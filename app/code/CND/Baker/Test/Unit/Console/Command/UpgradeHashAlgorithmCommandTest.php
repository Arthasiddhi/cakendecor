<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Console\Command;

use CND\Baker\Console\Command\UpgradeHashAlgorithmCommand;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use CND\Baker\Model\ResourceModel\Baker\CollectionFactory;

class UpgradeHashAlgorithmCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpgradeHashAlgorithmCommand
     */
    private $command;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerCollectionFactory;

    protected function setUp()
    {
        $this->bakerCollectionFactory = $this->getMockBuilder(
            \CND\Baker\Model\ResourceModel\Baker\CollectionFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->command = $this->objectManager->getObject(
            \CND\Baker\Console\Command\UpgradeHashAlgorithmCommand::class,
            [
                'bakerCollectionFactory' => $this->bakerCollectionFactory
            ]
        );
    }

    public function testConfigure()
    {
        $this->assertEquals('baker:hash:upgrade', $this->command->getName());
        $this->assertEquals(
            'Upgrade baker\'s hash according to the latest algorithm',
            $this->command->getDescription()
        );
    }
}
