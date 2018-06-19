<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \CND\Baker\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var \CND\Baker\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerSessionMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \CND\Baker\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDataFactoryMock;

    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentBakerMock;

    /**
     * @var \CND\Baker\Block\Address\Edit
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();

        $this->addressRepositoryMock = $this->getMockBuilder(\CND\Baker\Api\AddressRepositoryInterface::class)
            ->getMock();

        $this->bakerSessionMock = $this->getMockBuilder(\CND\Baker\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAddressFormData', 'getBakerId'])
            ->getMock();

        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressDataFactoryMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->currentBakerMock = $this->getMockBuilder(\CND\Baker\Helper\Session\CurrentBaker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \CND\Baker\Block\Address\Edit::class,
            [
                'request' => $this->requestMock,
                'addressRepository' => $this->addressRepositoryMock,
                'bakerSession' => $this->bakerSessionMock,
                'pageConfig' => $this->pageConfigMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'addressDataFactory' => $this->addressDataFactoryMock,
                'currentBaker' => $this->currentBakerMock,
            ]
        );
    }

    /**
     * @param array $postedData
     * @dataProvider postedDataProvider
     */
    public function testSetLayoutWithOwnAddressAndPostedData(array $postedData)
    {
        $addressId = 1;
        $bakerId = 1;
        $title = __('Edit Address');
        $newPostedData = $postedData;
        $newPostedData['region'] = $postedData;

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($addressId);

        $addressMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($addressMock);

        $addressMock->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);

        $this->bakerSessionMock->expects($this->at(0))
            ->method('getBakerId')
            ->willReturn($bakerId);

        $addressMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($addressId);

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->bakerSessionMock->expects($this->at(1))
            ->method('getAddressFormData')
            ->with(true)
            ->willReturn($postedData);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $addressMock,
                $newPostedData,
                \CND\Baker\Api\Data\AddressInterface::class
            )->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    /**
     * @return array
     */
    public function postedDataProvider()
    {
        return [
            [
                ['region_id' => 1, 'region' => 'region']
            ],
            [
                ['region' => 'region without id']
            ]
        ];
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetLayoutWithAlienAddress()
    {
        $addressId = 1;
        $bakerId = 1;
        $bakerPrefix = 'prefix';
        $bakerFirstName = 'firstname';
        $bakerMiddlename = 'middlename';
        $bakerLastname = 'lastname';
        $bakerSuffix = 'suffix';
        $title = __('Add New Address');

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($addressId);

        $addressMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($addressMock);

        $addressMock->expects($this->once())
            ->method('getBakerId')
            ->willReturn($bakerId);

        $this->bakerSessionMock->expects($this->at(0))
            ->method('getBakerId')
            ->willReturn($bakerId + 1);

        $newAddressMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newAddressMock);

        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMock();
        $this->currentBakerMock->expects($this->once())
            ->method('getBaker')
            ->willReturn($bakerMock);

        $bakerMock->expects($this->once())
            ->method('getPrefix')
            ->willReturn($bakerPrefix);
        $bakerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($bakerFirstName);
        $bakerMock->expects($this->once())
            ->method('getMiddlename')
            ->willReturn($bakerMiddlename);
        $bakerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($bakerLastname);
        $bakerMock->expects($this->once())
            ->method('getSuffix')
            ->willReturn($bakerSuffix);

        $newAddressMock->expects($this->once())
            ->method('setPrefix')
            ->with($bakerPrefix)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setFirstname')
            ->with($bakerFirstName)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setMiddlename')
            ->with($bakerMiddlename)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setLastname')
            ->with($bakerLastname)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setSuffix')
            ->with($bakerSuffix)
            ->willReturnSelf();

        $newAddressMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithoutAddressId()
    {
        $bakerPrefix = 'prefix';
        $bakerFirstName = 'firstname';
        $bakerMiddlename = 'middlename';
        $bakerLastname = 'lastname';
        $bakerSuffix = 'suffix';
        $title = 'title';

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn('');

        $newAddressMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newAddressMock);

        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMock();
        $this->currentBakerMock->expects($this->once())
            ->method('getBaker')
            ->willReturn($bakerMock);

        $bakerMock->expects($this->once())
            ->method('getPrefix')
            ->willReturn($bakerPrefix);
        $bakerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($bakerFirstName);
        $bakerMock->expects($this->once())
            ->method('getMiddlename')
            ->willReturn($bakerMiddlename);
        $bakerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($bakerLastname);
        $bakerMock->expects($this->once())
            ->method('getSuffix')
            ->willReturn($bakerSuffix);

        $newAddressMock->expects($this->once())
            ->method('setPrefix')
            ->with($bakerPrefix)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setFirstname')
            ->with($bakerFirstName)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setMiddlename')
            ->with($bakerMiddlename)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setLastname')
            ->with($bakerLastname)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setSuffix')
            ->with($bakerSuffix)
            ->willReturnSelf();

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $this->model->setData('title', $title);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithoutAddress()
    {
        $addressId = 1;
        $bakerPrefix = 'prefix';
        $bakerFirstName = 'firstname';
        $bakerMiddlename = 'middlename';
        $bakerLastname = 'lastname';
        $bakerSuffix = 'suffix';
        $title = 'title';

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($addressId);

        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willThrowException(
                \Magento\Framework\Exception\NoSuchEntityException::singleField('addressId', $addressId)
            );

        $newAddressMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newAddressMock);

        $bakerMock = $this->getMockBuilder(\CND\Baker\Api\Data\BakerInterface::class)
            ->getMock();
        $this->currentBakerMock->expects($this->once())
            ->method('getBaker')
            ->willReturn($bakerMock);

        $bakerMock->expects($this->once())
            ->method('getPrefix')
            ->willReturn($bakerPrefix);
        $bakerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($bakerFirstName);
        $bakerMock->expects($this->once())
            ->method('getMiddlename')
            ->willReturn($bakerMiddlename);
        $bakerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($bakerLastname);
        $bakerMock->expects($this->once())
            ->method('getSuffix')
            ->willReturn($bakerSuffix);

        $newAddressMock->expects($this->once())
            ->method('setPrefix')
            ->with($bakerPrefix)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setFirstname')
            ->with($bakerFirstName)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setMiddlename')
            ->with($bakerMiddlename)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setLastname')
            ->with($bakerLastname)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setSuffix')
            ->with($bakerSuffix)
            ->willReturnSelf();

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $this->model->setData('title', $title);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }
}
