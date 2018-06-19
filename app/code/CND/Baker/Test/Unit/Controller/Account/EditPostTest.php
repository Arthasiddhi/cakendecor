<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Controller\Account;

use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Controller\Account\EditPost;
use CND\Baker\Model\AuthenticationInterface;
use CND\Baker\Model\BakerExtractor;
use CND\Baker\Model\EmailNotificationInterface;
use CND\Baker\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EditPost
     */
    protected $model;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSession;

    /**
     * @var \CND\Baker\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerAccountManagement;

    /**
     * @var BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepository;

    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var BakerExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerExtractor;

    /**
     * @var EmailNotificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailNotification;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var AuthenticationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationMock;

    /**
     * @var \CND\Baker\Model\Baker\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerMapperMock;

    protected function setUp()
    {
        $this->prepareContext();

        $this->bakerSession = $this->getMockBuilder(\CND\Baker\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBakerId', 'setBakerFormData', 'logout', 'start'])
            ->getMock();

        $this->bakerAccountManagement = $this->getMockBuilder(\CND\Baker\Model\AccountManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bakerRepository = $this->getMockBuilder(\CND\Baker\Api\BakerRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->validator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bakerExtractor = $this->getMockBuilder(\CND\Baker\Model\BakerExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailNotification = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bakerMapperMock = $this->getMockBuilder(\CND\Baker\Model\Baker\Mapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new EditPost(
            $this->context,
            $this->bakerSession,
            $this->bakerAccountManagement,
            $this->bakerRepository,
            $this->validator,
            $this->bakerExtractor
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'emailNotification',
            $this->emailNotification
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'authentication',
            $this->authenticationMock
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'bakerMapper',
            $this->bakerMapperMock
        );
    }

    public function testInvalidFormKey()
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    public function testNoPostValues()
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    public function testGeneralSave()
    {
        $bakerId = 1;
        $currentPassword = '1234567';
        $bakerEmail = 'baker@example.com';

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();
        $currentBakerMock = $this->getCurrentBakerMock($bakerId, $address);
        $newBakerMock = $this->getNewBakerMock($bakerId, $address);

        $currentBakerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($bakerEmail);

        $this->bakerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentBakerMock)
            ->willReturn([]);

        $this->bakerSession->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);

        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($currentBakerMock);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['change_email'],
                ['change_email'],
                ['change_password']
            )
            ->willReturnOnConsecutiveCalls(true, true, false);

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('current_password')
            ->willReturn($currentPassword);

        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($currentBakerMock);

        $this->bakerRepository->expects($this->once())
            ->method('save')
            ->with($newBakerMock)
            ->willReturnSelf();

        $this->bakerExtractor->expects($this->once())
            ->method('extract')
            ->with('baker_account_edit', $this->request)
            ->willReturn($newBakerMock);

        $this->emailNotification->expects($this->once())
            ->method('credentialsChanged')
            ->with($currentBakerMock, $bakerEmail, false)
            ->willReturnSelf();

        $newBakerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($bakerEmail);

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'baker_account_edited',
                ['email' => $bakerEmail]
            );

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the account information.'))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('baker/account')
            ->willReturnSelf();

        $this->authenticationMock->expects($this->once())
            ->method('authenticate')
            ->willReturn(true);

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @param int $testNumber
     * @param string $exceptionClass
     * @param string $errorMessage
     *
     * @dataProvider changeEmailExceptionDataProvider
     */
    public function testChangeEmailException($testNumber, $exceptionClass, $errorMessage)
    {
        $bakerId = 1;
        $password = '1234567';

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $currentBakerMock = $this->getCurrentBakerMock($bakerId, $address);
        $newBakerMock = $this->getNewBakerMock($bakerId, $address);

        $this->bakerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentBakerMock)
            ->willReturn([]);

        $this->bakerExtractor->expects($this->once())
            ->method('extract')
            ->with('baker_account_edit', $this->request)
            ->willReturn($newBakerMock);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->bakerSession->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);

        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($currentBakerMock);

        $this->request->expects($this->any())
            ->method('getParam')
            ->with('change_email')
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('current_password')
            ->willReturn($password);

        $exception = new $exceptionClass($errorMessage);
        $this->authenticationMock->expects($this->once())
            ->method('authenticate')
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($errorMessage)
            ->willReturnSelf();

        if ($testNumber==1) {
            $this->resultRedirect->expects($this->once())
                ->method('setPath')
                ->with('*/*/edit')
                ->willReturnSelf();
        }

        if ($testNumber==2) {
            $this->bakerSession->expects($this->once())
                ->method('logout');

            $this->bakerSession->expects($this->once())
                ->method('start');

            $this->resultRedirect->expects($this->once())
                ->method('setPath')
                ->with('baker/account/login')
                ->willReturnSelf();
        }

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return array
     */
    public function changeEmailExceptionDataProvider()
    {
        return [
            [
                'testNumber' => 1,
                'exceptionClass' => \Magento\Framework\Exception\InvalidEmailOrPasswordException::class,
                'errorMessage' => __('The password doesn\'t match this account.')
            ],
            [
                'testNumber' => 2,
                'exceptionClass' => \Magento\Framework\Exception\State\UserLockedException::class,
                'errorMessage' => __('You did not sign in correctly or your account is temporarily disabled.')
            ]
        ];
    }

    /**
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmationPassword
     * @param [] $errors
     *
     * @dataProvider changePasswordDataProvider
     */
    public function testChangePassword(
        $currentPassword,
        $newPassword,
        $confirmationPassword,
        $errors
    ) {
        $bakerId = 1;
        $bakerEmail = 'user1@example.com';

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $currentBakerMock = $this->getCurrentBakerMock($bakerId, $address);
        $newBakerMock = $this->getNewBakerMock($bakerId, $address);

        $this->bakerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentBakerMock)
            ->willReturn([]);

        $this->bakerSession->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);

        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($currentBakerMock);

        $this->bakerExtractor->expects($this->once())
            ->method('extract')
            ->with('baker_account_edit', $this->request)
            ->willReturn($newBakerMock);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['change_email'],
                ['change_email'],
                ['change_password']
            )
            ->willReturnOnConsecutiveCalls(false, false, true);

        $this->request->expects($this->any())
            ->method('getPostValue')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getPost')
            ->willReturnMap([
                ['current_password', null, $currentPassword],
                ['password', null, $newPassword],
                ['password_confirmation', null, $confirmationPassword],
            ]);

        $currentBakerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($bakerEmail);

        // Prepare errors processing
        if ($errors['counter'] > 0) {
            $this->mockChangePasswordErrors($currentPassword, $newPassword, $errors, $bakerEmail);
        } else {
            $this->bakerAccountManagement->expects($this->once())
                ->method('changePassword')
                ->with($bakerEmail, $currentPassword, $newPassword)
                ->willReturnSelf();

            $this->bakerRepository->expects($this->once())
                ->method('save')
                ->with($newBakerMock)
                ->willReturnSelf();

            $this->messageManager->expects($this->once())
                ->method('addSuccess')
                ->with(__('You saved the account information.'))
                ->willReturnSelf();

            $this->resultRedirect->expects($this->once())
                ->method('setPath')
                ->with('baker/account')
                ->willReturnSelf();
        }

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return array
     */
    public function changePasswordDataProvider()
    {
        return [
            [
                'current_password' => '',
                'new_password' => '',
                'confirmation_password' => '',
                'errors' => [
                    'counter' => 1,
                    'message' => __('Please enter new password.'),
                ]
            ],
            [
                'current_password' => '',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user3@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => __('Password confirmation doesn\'t match entered password.'),
                ]
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 0,
                    'message' => '',
                ]
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => 'AuthenticationException',
                    'exception' => \Magento\Framework\Exception\AuthenticationException::class,
                ]
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => 'Exception',
                    'exception' => '\Exception',
                ]
            ]
        ];
    }

    /**
     * @param string $message
     * @param string $exception
     *
     * @dataProvider exceptionDataProvider
     */
    public function testGeneralException(
        $message,
        $exception
    ) {
        $bakerId = 1;

        $address = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $currentBakerMock = $this->getCurrentBakerMock($bakerId, $address);
        $newBakerMock = $this->getNewBakerMock($bakerId, $address);

        $this->bakerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentBakerMock)
            ->willReturn([]);

        $exception = new $exception(__($message));

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['change_email'],
                ['change_email'],
                ['change_password']
            )
            ->willReturn(false);

        $this->request->expects($this->any())
            ->method('getPostValue')
            ->willReturn(true);

        $this->bakerSession->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);
        $this->bakerSession->expects($this->once())
            ->method('setBakerFormData')
            ->with(true)
            ->willReturnSelf();

        $this->bakerRepository->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willReturn($currentBakerMock);
        $this->bakerRepository->expects($this->once())
            ->method('save')
            ->with($newBakerMock)
            ->willThrowException($exception);

        $this->bakerExtractor->expects($this->once())
            ->method('extract')
            ->with('baker_account_edit', $this->request)
            ->willReturn($newBakerMock);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return array
     */
    public function exceptionDataProvider()
    {
        return [
            [
                'message' => 'LocalizedException',
                'exception' => \Magento\Framework\Exception\LocalizedException::class,
            ],
            [
                'message' => 'Exception',
                'exception' => '\Exception',
            ],
        ];
    }

    protected function prepareContext()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManager);

        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);
    }

    /**
     * @param int $bakerId
     * @param \PHPUnit_Framework_MockObject_MockObject $address
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getNewBakerMock($bakerId, $address)
    {
        $newBakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();

        $newBakerMock->expects($this->once())
            ->method('setId')
            ->with($bakerId)
            ->willReturnSelf();
        $newBakerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn(null);
        $newBakerMock->expects($this->once())
            ->method('setAddresses')
            ->with([$address])
            ->willReturn(null);

        return $newBakerMock;
    }

    /**
     * @param int $bakerId
     * @param \PHPUnit_Framework_MockObject_MockObject $address
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCurrentBakerMock($bakerId, $address)
    {
        $currentBakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMockForAbstractClass();

        $currentBakerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $currentBakerMock->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);

        return $currentBakerMock;
    }

    /**
     * @param string $currentPassword
     * @param string $newPassword
     * @param [] $errors
     * @param string $bakerEmail
     * @return void
     */
    protected function mockChangePasswordErrors($currentPassword, $newPassword, $errors, $bakerEmail)
    {
        if (!empty($errors['exception'])) {
            $exception = new $errors['exception'](__($errors['message']));

            $this->bakerAccountManagement->expects($this->once())
                ->method('changePassword')
                ->with($bakerEmail, $currentPassword, $newPassword)
                ->willThrowException($exception);

            $this->messageManager->expects($this->any())
                ->method('addException')
                ->with($exception, __('We can\'t save the baker.'))
                ->willReturnSelf();
        }

        $this->bakerSession->expects($this->once())
            ->method('setBakerFormData')
            ->with(true)
            ->willReturnSelf();

        $this->messageManager->expects($this->any())
            ->method('addError')
            ->with($errors['message'])
            ->willReturnSelf();

        $this->resultRedirect->expects($this->any())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();
    }
}
