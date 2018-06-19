<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;
use CND\Baker\Block\Account\Dashboard\Info;

/**
 * Test class for \CND\Baker\Block\Account\Dashboard\Info.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /** Constant values used for testing */
    const CUSTOMER_ID = 1;

    const CHANGE_PASSWORD_URL = 'http://localhost/index.php/account/edit/changepass/1';

    const EMAIL_ADDRESS = 'john.doe@example.com';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\View\Element\Template\Context */
    private $_context;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Model\Session */
    private $_bakerSession;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Api\Data\BakerInterface */
    private $_baker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Helper\View
     */
    private $_helperView;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Newsletter\Model\Subscriber */
    private $_subscriber;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Newsletter\Model\SubscriberFactory */
    private $_subscriberFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Block\Form\Register */
    private $_formRegister;

    /** @var Info */
    private $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Helper\Session\CurrentBaker
     */
    protected $currentBaker;

    protected function setUp()
    {
        $this->currentBaker = $this->createMock(\CND\Baker\Helper\Session\CurrentBaker::class);

        $urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class, [], '', false);
        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnValue(self::CHANGE_PASSWORD_URL));

        $layout = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class, [], '', false);
        $this->_formRegister = $this->createMock(\CND\Baker\Block\Form\Register::class);
        $layout->expects(
                $this->any()
            )->method(
                'getBlockSingleton'
            )->with(
                \CND\Baker\Block\Form\Register::class
            )->will(
                $this->returnValue($this->_formRegister)
            );

        $this->_context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $this->_context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));

        $this->_bakerSession = $this->createMock(\CND\Baker\Model\Session::class);
        $this->_bakerSession->expects($this->any())->method('getId')->will($this->returnValue(self::CUSTOMER_ID));

        $this->_baker = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $this->_baker->expects($this->any())->method('getEmail')->will($this->returnValue(self::EMAIL_ADDRESS));
        $this->_helperView = $this->getMockBuilder(
            \CND\Baker\Helper\View::class
        )->disableOriginalConstructor()->getMock();
        $this->_subscriberFactory = $this->createPartialMock(\Magento\Newsletter\Model\SubscriberFactory::class, ['create']);
        $this->_subscriber = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $this->_subscriber->expects($this->any())->method('loadByEmail')->will($this->returnSelf());
        $this->_subscriberFactory->expects(
            $this->any()
        )->method(
                'create'
            )->will(
                $this->returnValue($this->_subscriber)
            );

        $this->_block = new \CND\Baker\Block\Account\Dashboard\Info(
            $this->_context,
            $this->currentBaker,
            $this->_subscriberFactory,
            $this->_helperView
        );
    }

    public function testGetBaker()
    {
        $this->currentBaker->expects(
            $this->once()
        )->method(
                'getBaker'
            )->will(
                $this->returnValue($this->_baker)
            );

        $baker = $this->_block->getBaker();
        $this->assertEquals($baker, $this->_baker);
    }

    public function testGetBakerException()
    {
        $this->currentBaker->expects($this->once())
            ->method('getBaker')
            ->will(
                $this->throwException(new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'bakerId', 'fieldValue' => 1]
                    )
                ))
            );

        $this->assertNull($this->_block->getBaker());
    }

    public function testGetName()
    {
        $expectedValue = 'John Q Doe Jr';

        $this->currentBaker->expects(
            $this->once()
        )->method(
                'getBaker'
            )->will(
                $this->returnValue($this->_baker)
            );

        /**
         * Called three times, once for each attribute (i.e. prefix, middlename, and suffix)
         */
        $this->_helperView->expects($this->any())->method('getBakerName')->will($this->returnValue($expectedValue));

        $this->assertEquals($expectedValue, $this->_block->getName());
    }

    public function testGetChangePasswordUrl()
    {
        $this->assertEquals(self::CHANGE_PASSWORD_URL, $this->_block->getChangePasswordUrl());
    }

    public function testGetSubscriptionObject()
    {
        $this->assertSame($this->_subscriber, $this->_block->getSubscriptionObject());
    }

    /**
     * @param bool $isSubscribed Is the subscriber subscribed?
     * @param bool $expectedValue The expected value - Whether the subscriber is subscribed or not.
     *
     * @dataProvider getIsSubscribedProvider
     */
    public function testGetIsSubscribed($isSubscribed, $expectedValue)
    {
        $this->_subscriber->expects($this->once())->method('isSubscribed')->will($this->returnValue($isSubscribed));
        $this->assertEquals($expectedValue, $this->_block->getIsSubscribed());
    }

    /**
     * @return array
     */
    public function getIsSubscribedProvider()
    {
        return [[true, true], [false, false]];
    }

    /**
     * @param bool $isNewsletterEnabled Determines if the newsletter is enabled
     * @param bool $expectedValue The expected value - Whether the newsletter is enabled or not
     *
     * @dataProvider isNewsletterEnabledProvider
     */
    public function testIsNewsletterEnabled($isNewsletterEnabled, $expectedValue)
    {
        $this->_formRegister->expects(
            $this->once()
        )->method(
                'isNewsletterEnabled'
            )->will(
                $this->returnValue($isNewsletterEnabled)
            );
        $this->assertEquals($expectedValue, $this->_block->isNewsletterEnabled());
    }

    public function isNewsletterEnabledProvider()
    {
        return [[true, true], [false, false]];
    }
}
