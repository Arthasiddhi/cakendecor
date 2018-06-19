<?php
/**
 * Unit test for session \CND\Baker\Model\Session
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpContextMock;

    /**
     * @var \Magento\Framework\UrlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFactoryMock;

    /**
     * @var \CND\Baker\Model\BakerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerFactoryMock;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepositoryMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \CND\Baker\Model\Session
     */
    protected $_model;

    protected function setUp()
    {
        $this->_storageMock = $this->createPartialMock(
            \CND\Baker\Model\Session\Storage::class,
            ['getIsBakerEmulated', 'getData', 'unsIsBakerEmulated', '__sleep', '__wakeup']
        );
        $this->_eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->urlFactoryMock = $this->createMock(\Magento\Framework\UrlFactory::class);
        $this->bakerFactoryMock = $this->getMockBuilder(\CND\Baker\Model\BakerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->bakerRepositoryMock = $this->createMock(\CND\Baker\Api\BakerRepositoryInterface::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->_model = $helper->getObject(
            \CND\Baker\Model\Session::class,
            [
                'bakerFactory' => $this->bakerFactoryMock,
                'storage' => $this->_storageMock,
                'eventManager' => $this->_eventManagerMock,
                'httpContext' => $this->_httpContextMock,
                'urlFactory' => $this->urlFactoryMock,
                'bakerRepository' => $this->bakerRepositoryMock,
                'response' => $this->responseMock,
            ]
        );
    }

    public function testSetBakerAsLoggedIn()
    {
        $baker = $this->createMock(\CND\Baker\Model\Baker::class);
        $bakerDto = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $baker->expects($this->any())
            ->method('getDataModel')
            ->will($this->returnValue($bakerDto));

        $this->_eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('baker_login', ['baker' => $baker]);
        $this->_eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('baker_data_object_login', ['baker' => $bakerDto]);

        $_SESSION = [];
        $this->_model->setBakerAsLoggedIn($baker);
        $this->assertSame($baker, $this->_model->getBaker());
    }

    public function testSetBakerDataAsLoggedIn()
    {
        $baker = $this->createMock(\CND\Baker\Model\Baker::class);
        $bakerDto = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);

        $this->bakerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($baker));
        $baker->expects($this->once())
            ->method('updateData')
            ->with($bakerDto)
            ->will($this->returnSelf());

        $this->_eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('baker_login', ['baker' => $baker]);
        $this->_eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('baker_data_object_login', ['baker' => $bakerDto]);

        $this->_model->setBakerDataAsLoggedIn($bakerDto);
        $this->assertSame($baker, $this->_model->getBaker());
    }

    public function testAuthenticate()
    {
        $urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlMock->expects($this->exactly(2))
            ->method('getUrl')
            ->will($this->returnValue(''));
        $urlMock->expects($this->once())
            ->method('getRebuiltUrl')
            ->will($this->returnValue(''));
        $this->urlFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->will($this->returnValue($urlMock));

        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('')
            ->will($this->returnValue(''));

        $this->assertFalse($this->_model->authenticate());
    }

    public function testLoginById()
    {
        $bakerId = 1;

        $bakerDataMock = $this->prepareLoginDataMock($bakerId);

        $this->bakerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($bakerId))
            ->will($this->returnValue($bakerDataMock));

        $this->assertTrue($this->_model->loginById($bakerId));
    }

    /**
     * @param $bakerId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareLoginDataMock($bakerId)
    {
        $bakerDataMock = $this->createMock(\CND\Baker\Api\Data\BakerInterface::class);
        $bakerDataMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($bakerId));

        $bakerMock = $this->createPartialMock(
            \CND\Baker\Model\Baker::class,
            ['getId', 'isConfirmationRequired', 'getConfirmation', 'updateData', 'getGroupId']
        );
        $bakerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($bakerId));
        $bakerMock->expects($this->once())
            ->method('isConfirmationRequired')
            ->will($this->returnValue(true));
        $bakerMock->expects($this->never())
            ->method('getConfirmation')
            ->will($this->returnValue($bakerId));

        $this->bakerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($bakerMock));
        $bakerMock->expects($this->once())
            ->method('updateData')
            ->with($bakerDataMock)
            ->will($this->returnSelf());

        $this->_httpContextMock->expects($this->exactly(3))
            ->method('setValue');
        return $bakerDataMock;
    }

    /**
     * @param bool $expectedResult
     * @param bool $isBakerIdValid
     * @param bool $isBakerEmulated
     * @dataProvider getIsLoggedInDataProvider
     */
    public function testIsLoggedIn($expectedResult, $isBakerIdValid, $isBakerEmulated)
    {
        $bakerId = 1;
        $this->_storageMock->expects($this->any())->method('getData')->with('baker_id')
            ->will($this->returnValue($bakerId));

        if ($isBakerIdValid) {
            $this->bakerRepositoryMock->expects($this->once())
                ->method('getById')
                ->with($bakerId);
        } else {
            $this->bakerRepositoryMock->expects($this->once())
                ->method('getById')
                ->with($bakerId)
                ->will($this->throwException(new \Exception('Baker ID is invalid.')));
        }
        $this->_storageMock->expects($this->any())->method('getIsBakerEmulated')
            ->will($this->returnValue($isBakerEmulated));
        $this->assertEquals($expectedResult, $this->_model->isLoggedIn());
    }

    /**
     * @return array
     */
    public function getIsLoggedInDataProvider()
    {
        return [
            ['expectedResult' => true, 'isBakerIdValid' => true, 'isBakerEmulated' => false],
            ['expectedResult' => false, 'isBakerIdValid' => true, 'isBakerEmulated' => true],
            ['expectedResult' => false, 'isBakerIdValid' => false, 'isBakerEmulated' => false],
            ['expectedResult' => false, 'isBakerIdValid' => false, 'isBakerEmulated' => true],
        ];
    }

    public function testSetBakerRemovesFlagThatShowsIfBakerIsEmulated()
    {
        $bakerMock = $this->createMock(\CND\Baker\Model\Baker::class);
        $this->_storageMock->expects($this->once())->method('unsIsBakerEmulated');
        $this->_model->setBaker($bakerMock);
    }
}
