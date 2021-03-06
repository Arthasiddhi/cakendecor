<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class MassSubscribeTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassSubscribeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Controller\Adminhtml\Index\MassSubscribe
     */
    protected $massAction;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerCollectionMock;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepositoryMock;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectManagerMock = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create']
        );
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->bakerCollectionMock =
            $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bakerCollectionFactoryMock =
            $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $redirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirectMock);
        $this->subscriberMock = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $subscriberFactoryMock = $this->getMockBuilder(\Magento\Newsletter\Model\SubscriberFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $subscriberFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->subscriberMock);

        $this->resultRedirectMock = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirectMock);

        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultFactoryMock);

        $this->filterMock = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->bakerCollectionMock)
            ->willReturnArgument(0);
        $this->bakerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->bakerCollectionMock);
        $this->bakerRepositoryMock = $this->getMockBuilder(\CND\Baker\Api\BakerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->massAction = $objectManagerHelper->getObject(
            \CND\Baker\Controller\Adminhtml\Index\MassSubscribe::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->bakerCollectionFactoryMock,
                'bakerRepository' => $this->bakerRepositoryMock,
                'subscriberFactory' => $subscriberFactoryMock,
            ]
        );
    }

    public function testExecute()
    {
        $bakersIds = [10, 11, 12];

        $this->bakerCollectionMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn($bakersIds);

        $this->bakerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturnMap([[10, true], [11, true], [12, true]]);

        $this->subscriberMock->expects($this->any())
            ->method('subscribeBakerById')
            ->willReturnMap([[10, true], [11, true], [12, true]]);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('A total of %1 record(s) were updated.', count($bakersIds)));

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('baker/*/index')
            ->willReturnSelf();

        $this->massAction->execute();
    }

    public function testExecuteWithException()
    {
        $bakersIds = [10, 11, 12];

        $this->bakerCollectionMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn($bakersIds);

        $this->bakerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willThrowException(new \Exception('Some message.'));

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Some message.');

        $this->massAction->execute();
    }
}
