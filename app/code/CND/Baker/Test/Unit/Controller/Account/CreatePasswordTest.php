<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Controller\Account;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePasswordTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Controller\Account\CreatePassword */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $pageFactoryMock;

    /** @var \CND\Baker\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $accountManagementMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectFactoryMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMockBuilder(\CND\Baker\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRpToken', 'setRpBakerId', 'getRpToken', 'getRpBakerId'])
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountManagementMock = $this->getMockBuilder(\CND\Baker\Api\AccountManagementInterface::class)
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->redirectFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \CND\Baker\Controller\Account\CreatePassword::class,
            [
                'bakerSession' => $this->sessionMock,
                'resultPageFactory' => $this->pageFactoryMock,
                'accountManagement' => $this->accountManagementMock,
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->redirectFactoryMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    public function testExecuteWithLink()
    {
        $token = 'token';
        $bakerId = '11';

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['token', null, $token],
                    ['id', null, $bakerId],
                ]
            );

        $this->accountManagementMock->expects($this->once())
            ->method('validateResetPasswordLinkToken')
            ->with($bakerId, $token)
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('setRpToken')
            ->with($token);
        $this->sessionMock->expects($this->once())
            ->method('setRpBakerId')
            ->with($bakerId);

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/createpassword', [])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    public function testExecuteWithSession()
    {
        $token = 'token';
        $bakerId = '11';

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['token', null, null],
                    ['id', null, $bakerId],
                ]
            );

        $this->sessionMock->expects($this->once())
            ->method('getRpToken')
            ->willReturn($token);
        $this->sessionMock->expects($this->once())
            ->method('getRpBakerId')
            ->willReturn($bakerId);

        $this->accountManagementMock->expects($this->once())
            ->method('validateResetPasswordLinkToken')
            ->with($bakerId, $token)
            ->willReturn(true);

        /** @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject $pageMock */
        $pageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->with(false, [])
            ->willReturn($pageMock);

        /** @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject $layoutMock */
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        /** @var \CND\Baker\Block\Account\Resetpassword|\PHPUnit_Framework_MockObject_MockObject $layoutMock */
        $blockMock = $this->getMockBuilder(\CND\Baker\Block\Account\Resetpassword::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBakerId', 'setResetPasswordLinkToken'])
            ->getMock();

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('resetPassword')
            ->willReturn($blockMock);

        $blockMock->expects($this->once())
            ->method('setBakerId')
            ->with($bakerId)
            ->willReturnSelf();
        $blockMock->expects($this->once())
            ->method('setResetPasswordLinkToken')
            ->with($token)
            ->willReturnSelf();

        $this->assertEquals($pageMock, $this->model->execute());
    }

    public function testExecuteWithException()
    {
        $token = 'token';
        $bakerId = '11';

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['token', null, $token],
                    ['id', null, null],
                ]
            );

        $this->sessionMock->expects($this->once())
            ->method('getRpToken')
            ->willReturn($token);
        $this->sessionMock->expects($this->once())
            ->method('getRpBakerId')
            ->willReturn($bakerId);

        $this->accountManagementMock->expects($this->once())
            ->method('validateResetPasswordLinkToken')
            ->with($bakerId, $token)
            ->willThrowException(new \Exception('Exception.'));

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Your password reset link has expired.'))
            ->willReturnSelf();

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/forgotpassword', [])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }
}
