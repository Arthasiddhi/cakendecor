<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CND\Baker\Test\Unit\Controller\Adminhtml\Index;

use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Model\AccountManagement;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Unit test for \CND\Baker\Controller\Adminhtml\Index controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResetPasswordTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Request mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Response mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * Instance of mocked tested object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Controller\Adminhtml\Index
     */
    protected $_testedObject;

    /**
     * ObjectManager mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Api\AccountManagementInterface
     */
    protected $_bakerAccountManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Api\BakerRepositoryInterface
     */
    protected $_bakerRepositoryMock;

    /**
     * Session mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Backend helper mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * Prepare required values
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->setMethods(
            ['setRedirect', 'getHeader', '__wakeup']
        )->getMock();

        $this->_response->expects(
            $this->any()
        )->method(
            'getHeader'
        )->with(
            $this->equalTo('X-Frame-Options')
        )->will(
            $this->returnValue(true)
        );

        $this->_objectManager = $this->getMockBuilder(
            \Magento\Framework\App\ObjectManager::class
        )->disableOriginalConstructor()->setMethods(
            ['get', 'create']
        )->getMock();
        $frontControllerMock = $this->getMockBuilder(
            \Magento\Framework\App\FrontController::class
        )->disableOriginalConstructor()->getMock();

        $actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder(
            \Magento\Backend\Model\Session::class
        )->disableOriginalConstructor()->setMethods(
            ['setIsUrlNotice', '__wakeup']
        )->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder(
            \Magento\Backend\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['getUrl']
        )->getMock();

        $this->messageManager = $this->getMockBuilder(
            \Magento\Framework\Message\Manager::class
        )->disableOriginalConstructor()->setMethods(
            ['addSuccess', 'addMessage', 'addException', 'addErrorMessage']
        )->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $contextArgs = [
            'getHelper',
            'getSession',
            'getAuthorization',
            'getTranslator',
            'getObjectManager',
            'getFrontController',
            'getActionFlag',
            'getMessageManager',
            'getLayoutFactory',
            'getEventManager',
            'getRequest',
            'getResponse',
            'getView',
            'getResultRedirectFactory'
        ];
        $contextMock = $this->getMockBuilder(
            \Magento\Backend\App\Action\Context::class
        )->disableOriginalConstructor()->setMethods(
            $contextArgs
        )->getMock();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->_request);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->_response);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->_objectManager);
        $contextMock->expects($this->any())->method('getFrontController')->willReturn($frontControllerMock);
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($actionFlagMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->_helper);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->_session);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);
        $viewMock =  $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)->getMock();
        $viewMock->expects($this->any())->method('loadLayout')->willReturnSelf();
        $contextMock->expects($this->any())->method('getView')->willReturn($viewMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->_bakerAccountManagementMock = $this->getMockBuilder(
            \CND\Baker\Api\AccountManagementInterface::class
        )->getMock();

        $this->_bakerRepositoryMock = $this->getMockBuilder(
            \CND\Baker\Api\BakerRepositoryInterface::class
        )->getMock();

        $args = [
            'context' => $contextMock,
            'bakerAccountManagement' => $this->_bakerAccountManagementMock,
            'bakerRepository' => $this->_bakerRepositoryMock,
        ];

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject(
            \CND\Baker\Controller\Adminhtml\Index\ResetPassword::class,
            $args
        );
    }

    public function testResetPasswordActionNoBaker()
    {
        $redirectLink = 'baker/index';
        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $this->equalTo('baker_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue(false)
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo($redirectLink));

        $this->assertInstanceOf(
             \Magento\Backend\Model\View\Result\Redirect::class,
            $this->_testedObject->execute()
        );
    }

    public function testResetPasswordActionInvalidBakerId()
    {
        $redirectLink = 'baker/index';
        $bakerId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $this->equalTo('baker_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($bakerId)
        );

        $this->_bakerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $bakerId
        )->will(
            $this->throwException(
                new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'bakerId', 'fieldValue' => $bakerId]
                    )
                )
            )
        );

        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('baker/index'),
            $this->equalTo([])
        )->will(
            $this->returnValue($redirectLink)
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo($redirectLink));

        $this->assertInstanceOf(
             \Magento\Backend\Model\View\Result\Redirect::class,
            $this->_testedObject->execute()
        );
    }

    public function testResetPasswordActionCoreException()
    {
        $bakerId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $this->equalTo('baker_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($bakerId)
        );

        // Setup a core exception to return
        $exception = new \Magento\Framework\Validator\Exception();
        $error = new \Magento\Framework\Message\Error('Something Bad happened');
        $exception->addMessage($error);

        $this->_bakerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $bakerId
        )->will(
            $this->throwException($exception)
        );

        // Verify error message is set
        $this->messageManager->expects($this->once())
            ->method('addMessage')
            ->with($error);

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionSecurityException()
    {
        $securityText = 'Security violation.';
        $exception = new \Magento\Framework\Exception\SecurityViolationException(__($securityText));
        $bakerId = 1;
        $email = 'some@example.com';
        $websiteId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $this->equalTo('baker_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($bakerId)
        );
        $baker = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\BakerInterface::class,
            ['getId', 'getEmail', 'getWebsiteId']
        );
        $baker->expects($this->once())->method('getEmail')->will($this->returnValue($email));
        $baker->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->_bakerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $bakerId
        )->will(
            $this->returnValue($baker)
        );
        $this->_bakerAccountManagementMock->expects(
            $this->once()
        )->method(
            'initiatePasswordReset'
        )->willThrowException($exception);

        $this->messageManager->expects(
            $this->once()
        )->method(
            'addErrorMessage'
        )->with(
            $this->equalTo($exception->getMessage())
        );

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionCoreExceptionWarn()
    {
        $warningText = 'Warning';
        $bakerId = 1;

        $this->_request->expects($this->once())
            ->method('getParam')
            ->with('baker_id', 0)
            ->willReturn($bakerId);

        // Setup a core exception to return
        $exception = new \Magento\Framework\Validator\Exception(__($warningText));

        $error = new \Magento\Framework\Message\Warning('Something Not So Bad happened');
        $exception->addMessage($error);

        $this->_bakerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($bakerId)
            ->willThrowException($exception);

        // Verify Warning is converted to an Error and message text is set to exception text
        $this->messageManager->expects($this->once())
            ->method('addMessage')
            ->with(new \Magento\Framework\Message\Error($warningText));

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionException()
    {
        $bakerId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $this->equalTo('baker_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($bakerId)
        );

        // Setup a core exception to return
        $exception = new \Exception('Something Really Bad happened');

        $this->_bakerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $bakerId
        )->will(
            $this->throwException($exception)
        );

        // Verify error message is set
        $this->messageManager->expects(
            $this->once()
        )->method(
            'addException'
        )->with(
            $this->equalTo($exception),
            $this->equalTo('Something went wrong while resetting baker password.')
        );

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionSendEmail()
    {
        $bakerId = 1;
        $email = 'test@example.com';
        $websiteId = 1;
        $redirectLink = 'baker/*/edit';

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $this->equalTo('baker_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($bakerId)
        );

        $baker = $this->getMockForAbstractClass(
            \CND\Baker\Api\Data\BakerInterface::class,
            ['getId', 'getEmail', 'getWebsiteId']
        );

        $baker->expects($this->once())->method('getEmail')->will($this->returnValue($email));
        $baker->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));

        $this->_bakerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $bakerId
        )->will(
            $this->returnValue($baker)
        );

        // verify initiatePasswordReset() is called
        $this->_bakerAccountManagementMock->expects(
            $this->once()
        )->method(
            'initiatePasswordReset'
        )->with(
            $email,
            AccountManagement::EMAIL_REMINDER,
            $websiteId
        );

        // verify success message
        $this->messageManager->expects(
            $this->once()
        )->method(
            'addSuccess'
        )->with(
            $this->equalTo('The baker will receive an email with a link to reset password.')
        );

        // verify redirect
        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('baker/*/edit'),
            $this->equalTo(['id' => $bakerId, '_current' => true])
        )->will(
            $this->returnValue($redirectLink)
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with(
                $redirectLink,
                ['id' => $bakerId, '_current' => true]
            );

        $this->assertInstanceOf(
             \Magento\Backend\Model\View\Result\Redirect::class,
            $this->_testedObject->execute()
        );
    }
}
