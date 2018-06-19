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
class ResetPasswordPostTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\Controller\Account\ResetPasswordPost */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $pageFactoryMock;

    /** @var \CND\Baker\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $accountManagementMock;

    /** @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bakerRepositoryMock;

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
            ->setMethods(['unsRpToken', 'unsRpBakerId'])
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountManagementMock = $this->getMockBuilder(\CND\Baker\Api\AccountManagementInterface::class)
            ->getMockForAbstractClass();
        $this->bakerRepositoryMock = $this->getMockBuilder(\CND\Baker\Api\BakerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getQuery', 'getPost'])
            ->getMockForAbstractClass();
        $this->redirectFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \CND\Baker\Controller\Account\ResetPasswordPost::class,
            [
                'bakerSession' => $this->sessionMock,
                'resultPageFactory' => $this->pageFactoryMock,
                'accountManagement' => $this->accountManagementMock,
                'bakerRepository' => $this->bakerRepositoryMock,
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->redirectFactoryMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    public function testExecute()
    {
        $token = 'token';
        $bakerId = '11';
        $password = 'password';
        $passwordConfirmation = 'password';
        $email = 'email@email.com';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $bakerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        /** @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject $bakerMock */
        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();

        $this->bakerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($bakerMock);

        $bakerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->accountManagementMock->expects($this->once())
            ->method('resetPassword')
            ->with($email, $token, $password)
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('unsRpToken');
        $this->sessionMock->expects($this->once())
            ->method('unsRpBakerId');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You updated your password.'))
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
            ->with('*/*/login', [])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    public function testExecuteWithException()
    {
        $token = 'token';
        $bakerId = '11';
        $password = 'password';
        $passwordConfirmation = 'password';
        $email = 'email@email.com';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $bakerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        /** @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject $bakerMock */
        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();

        $this->bakerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($bakerMock);

        $bakerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->accountManagementMock->expects($this->once())
            ->method('resetPassword')
            ->with($email, $token, $password)
            ->willThrowException(new \Exception('Exception.'));

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Something went wrong while saving the new password.'))
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
            ->with('*/*/createPassword', ['id' => $bakerId, 'token' => $token])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * Test for InputException
     */
    public function testExecuteWithInputException()
    {
        $token = 'token';
        $bakerId = '11';
        $password = 'password';
        $passwordConfirmation = 'password';
        $email = 'email@email.com';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $bakerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        /** @var \CND\Baker\Api\Data\BakerInterface|\PHPUnit_Framework_MockObject_MockObject $bakerMock */
        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();

        $this->bakerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($bakerMock);

        $bakerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->accountManagementMock->expects($this->once())
            ->method('resetPassword')
            ->with($email, $token, $password)
            ->willThrowException(new \Magento\Framework\Exception\InputException(__('InputException.')));

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('InputException.'))
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
            ->with('*/*/createPassword', ['id' => $bakerId, 'token' => $token])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    public function testExecuteWithWrongConfirmation()
    {
        $token = 'token';
        $bakerId = '11';
        $password = 'password';
        $passwordConfirmation = 'wrong_password';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $bakerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('New Password and Confirm New Password values didn\'t match.'))
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
            ->with('*/*/createPassword', ['id' => $bakerId, 'token' => $token])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    public function testExecuteWithEmptyPassword()
    {
        $token = 'token';
        $bakerId = '11';
        $password = '';
        $passwordConfirmation = '';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $bakerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Please enter a new password.'))
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
            ->with('*/*/createPassword', ['id' => $bakerId, 'token' => $token])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }
}
