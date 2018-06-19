<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Controller\Adminhtml\Index;

use CND\Baker\Api\AddressMetadataInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\Data\AttributeMetadataInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Controller\RegistryConstants;
use CND\Baker\Model\EmailNotificationInterface;
use CND\Baker\Model\Metadata\Form;
use Magento\Framework\Controller\Result\Redirect;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @covers \CND\Baker\Controller\Adminhtml\Index\Save
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Controller\Adminhtml\Index\Save
     */
    protected $model;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \CND\Baker\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Framework\DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var \CND\Baker\Api\Data\BakerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerDataFactoryMock;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerRepositoryMock;

    /**
     * @var \CND\Baker\Model\Baker\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerMapperMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectFactoryMock;

    /**
     * @var \CND\Baker\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managementMock;

    /**
     * @var \CND\Baker\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDataFactoryMock;

    /**
     * @var EmailNotificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailNotificationMock;

    /**
     * @var \CND\Baker\Model\Address\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerAddressMapperMock;

    /**
     * @var \CND\Baker\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerAddressRepositoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['unsBakerFormData', 'setBakerFormData'])
            ->getMock();
        $this->formFactoryMock = $this->getMockBuilder(\CND\Baker\Model\Metadata\FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->bakerDataFactoryMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\BakerInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->bakerRepositoryMock = $this->getMockBuilder(\CND\Baker\Api\BakerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bakerAddressRepositoryMock = $this->getMockBuilder(
            \CND\Baker\Api\AddressRepositoryInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->bakerMapperMock = $this->getMockBuilder(
            \CND\Baker\Model\Baker\Mapper::class
        )->disableOriginalConstructor()->getMock();
        $this->bakerAddressMapperMock = $this->getMockBuilder(
            \CND\Baker\Model\Address\Mapper::class
        )->disableOriginalConstructor()->getMock();
        $this->dataHelperMock = $this->getMockBuilder(
            \Magento\Framework\Api\DataObjectHelper::class
        )->disableOriginalConstructor()->getMock();
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriberFactoryMock = $this->getMockBuilder(\Magento\Newsletter\Model\SubscriberFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactoryMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->managementMock = $this->getMockBuilder(\CND\Baker\Model\AccountManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAccount'])
            ->getMock();
        $this->addressDataFactoryMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->emailNotificationMock = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            \CND\Baker\Controller\Adminhtml\Index\Save::class,
            [
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'formFactory' => $this->formFactoryMock,
                'objectFactory' => $this->objectFactoryMock,
                'bakerDataFactory' => $this->bakerDataFactoryMock,
                'bakerRepository' => $this->bakerRepositoryMock,
                'bakerMapper' => $this->bakerMapperMock,
                'dataObjectHelper' => $this->dataHelperMock,
                'subscriberFactory' => $this->subscriberFactoryMock,
                'coreRegistry' => $this->registryMock,
                'bakerAccountManagement' => $this->managementMock,
                'addressDataFactory' => $this->addressDataFactoryMock,
                'request' => $this->requestMock,
                'session' => $this->sessionMock,
                'authorization' => $this->authorizationMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->redirectFactoryMock,
                'addressRepository' => $this->bakerAddressRepositoryMock,
                'addressMapper' => $this->bakerAddressMapperMock,
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'emailNotification',
            $this->emailNotificationMock
        );
    }

    /**
     * @covers \CND\Baker\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithExistentBaker()
    {
        $bakerId = 22;
        $addressId = 11;
        $subscription = 'true';
        $postValue = [
            'baker' => [
                'entity_id' => $bakerId,
                'code' => 'value',
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'address' => [
                '_template_' => '_template_',
                $addressId => [
                    'entity_id' => $addressId,
                    'default_billing' => 'true',
                    'default_shipping' => 'true',
                    'code' => 'value',
                    'coolness' => false,
                    'region' => 'region',
                    'region_id' => 'region_id',
                ],
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'entity_id' => $bakerId,
            'code' => 'value',
            'coolness' => false,
            'disable_auto_group_change' => 'false',
        ];
        $compactedData = [
            'entity_id' => $bakerId,
            'code' => 'value',
            'coolness' => false,
            'disable_auto_group_change' => 'false',
            BakerInterface::DEFAULT_BILLING => 2,
            BakerInterface::DEFAULT_SHIPPING => 2
        ];
        $addressExtractedData = [
            'entity_id' => $addressId,
            'code' => 'value',
            'coolness' => false,
            'region' => 'region',
            'region_id' => 'region_id',
        ];
        $addressCompactedData = [
            'entity_id' => $addressId,
            'default_billing' => 'true',
            'default_shipping' => 'true',
            'code' => 'value',
            'coolness' => false,
            'region' => 'region',
            'region_id' => 'region_id',
        ];
        $savedData = [
            'entity_id' => $bakerId,
            'darkness' => true,
            'name' => 'Name',
            BakerInterface::DEFAULT_BILLING => false,
            BakerInterface::DEFAULT_SHIPPING => false,
        ];
        $savedAddressData = [
            'entity_id' => $addressId,
            'default_billing' => true,
            'default_shipping' => true,
        ];
        $mergedData = [
            'entity_id' => $bakerId,
            'darkness' => true,
            'name' => 'Name',
            'code' => 'value',
            'disable_auto_group_change' => 0,
            BakerInterface::DEFAULT_BILLING => $addressId,
            BakerInterface::DEFAULT_SHIPPING => $addressId,
            'confirmation' => false,
            'sendemail_store_id' => '1',
            'id' => $bakerId,
        ];
        $mergedAddressData = [
            'entity_id' => $addressId,
            'default_billing' => true,
            'default_shipping' => true,
            'code' => 'value',
            'region' => [
                    'region' => 'region',
                    'region_id' => 'region_id',
                ],
            'region_id' => 'region_id',
            'id' => $addressId,
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $bakerFormMock */
        $attributeMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap([
                [null, null, $postValue],
                [BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['baker']],
                ['address/' . $addressId, null, $postValue['address'][$addressId]],
            ]);
        $this->requestMock->expects($this->exactly(3))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['baker', null, $postValue['baker']],
                    ['address', null, $postValue['address']],
                    ['subscription', null, $subscription],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap(
                [
                    ['baker', null, $postValue['baker']],
                    ['address/' . $addressId, null, $postValue['address'][$addressId]],
                ]
            );

        $this->objectFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $bakerFormMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $bakerFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'baker')
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($compactedData);
        $bakerFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $bakerAddressFormMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $bakerAddressFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'address/' . $addressId)
            ->willReturn($addressExtractedData);
        $bakerAddressFormMock->expects($this->once())
            ->method('compactData')
            ->with($addressExtractedData)
            ->willReturn($addressCompactedData);
        $bakerAddressFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [
                        BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                        'adminhtml_baker',
                        $savedData,
                        false,
                        Form::DONT_IGNORE_INVISIBLE,
                        [],
                        $bakerFormMock
                    ],
                    [
                        AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                        'adminhtml_baker_address',
                        $savedAddressData,
                        false,
                        Form::DONT_IGNORE_INVISIBLE,
                        [],
                        $bakerAddressFormMock
                    ],
                ]
            );

        /** @var BakerInterface|\PHPUnit_Framework_MockObject_MockObject $bakerMock */
        $bakerMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\BakerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->bakerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bakerMock);

        $this->bakerRepositoryMock->expects($this->exactly(2))
            ->method('getById')
            ->with($bakerId)
            ->willReturn($bakerMock);

        $this->bakerMapperMock->expects($this->exactly(2))
            ->method('toFlatArray')
            ->with($bakerMock)
            ->willReturn($savedData);

        $addressMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bakerAddressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($addressMock);

        $this->bakerAddressMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($addressMock)
            ->willReturn($savedAddressData);

        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($addressMock);

        $this->dataHelperMock->expects($this->exactly(2))
            ->method('populateWithArray')
            ->willReturnMap(
                [
                    [
                        $bakerMock,
                        $mergedData, \CND\Baker\Api\Data\BakerInterface::class,
                        $this->dataHelperMock
                    ],
                    [
                        $addressMock,
                        $mergedAddressData, \CND\Baker\Api\Data\AddressInterface::class,
                        $this->dataHelperMock
                    ],
                ]
            );

        $bakerMock->expects($this->once())
            ->method('setAddresses')
            ->with([$addressMock])
            ->willReturnSelf();

        $this->bakerRepositoryMock->expects($this->once())
            ->method('save')
            ->with($bakerMock)
            ->willReturnSelf();

        $bakerEmail = 'baker@email.com';
        $bakerMock->expects($this->once())->method('getEmail')->willReturn($bakerEmail);

        $this->emailNotificationMock->expects($this->once())
            ->method('credentialsChanged')
            ->with($bakerMock, $bakerEmail)
            ->willReturnSelf();

        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with(null)
            ->willReturn(true);

        /** @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(\Magento\Newsletter\Model\Subscriber::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriberFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($subscriberMock);

        $subscriberMock->expects($this->once())
            ->method('subscribeBakerById')
            ->with($bakerId);
        $subscriberMock->expects($this->never())
            ->method('unsubscribeBakerById');

        $this->sessionMock->expects($this->once())
            ->method('unsBakerFormData');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with(RegistryConstants::CURRENT_CUSTOMER_ID, $bakerId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the baker.'))
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('back', false)
            ->willReturn(true);

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
            ->with('baker/*/edit', ['id' => $bakerId, '_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \CND\Baker\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewBaker()
    {
        $bakerId = 22;
        $addressId = 11;
        $subscription = '0';
        $postValue = [
            'baker' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'address' => [
                '_template_' => '_template_',
                $addressId => [
                    'entity_id' => $addressId,
                    'code' => 'value',
                    'coolness' => false,
                    'region' => 'region',
                    'region_id' => 'region_id',
                ],
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
        ];
        $addressExtractedData = [
            'entity_id' => $addressId,
            'code' => 'value',
            'coolness' => false,
            'region' => 'region',
            'region_id' => 'region_id',
        ];
        $mergedData = [
            'disable_auto_group_change' => 0,
            BakerInterface::DEFAULT_BILLING => null,
            BakerInterface::DEFAULT_SHIPPING => null,
            'confirmation' => false,
        ];
        $mergedAddressData = [
            'entity_id' => $addressId,
            'default_billing' => false,
            'default_shipping' => false,
            'code' => 'value',
            'region' => [
                    'region' => 'region',
                    'region_id' => 'region_id',
                ],
            'region_id' => 'region_id',
            'id' => $addressId,
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $bakerFormMock */
        $attributeMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap([
                [null, null, $postValue],
                [BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['baker']],
                ['address/' . $addressId, null, $postValue['address'][$addressId]],
            ]);
        $this->requestMock->expects($this->exactly(3))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['baker', null, $postValue['baker']],
                    ['address', null, $postValue['address']],
                    ['subscription', null, $subscription],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap(
                [
                    ['baker', null, $postValue['baker']],
                    ['address/' . $addressId, null, $postValue['address'][$addressId]],
                ]
            );

        $this->objectFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $bakerFormMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $bakerFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'baker')
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $bakerAddressFormMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $bakerAddressFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'address/' . $addressId)
            ->willReturn($addressExtractedData);
        $bakerAddressFormMock->expects($this->once())
            ->method('compactData')
            ->with($addressExtractedData)
            ->willReturn($addressExtractedData);
        $bakerAddressFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [
                        BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                        'adminhtml_baker',
                        [],
                        false,
                        Form::DONT_IGNORE_INVISIBLE,
                        [],
                        $bakerFormMock
                    ],
                    [
                        AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                        'adminhtml_baker_address',
                        [],
                        false,
                        Form::DONT_IGNORE_INVISIBLE,
                        [],
                        $bakerAddressFormMock
                    ],
                ]
            );

        /** @var BakerInterface|\PHPUnit_Framework_MockObject_MockObject $bakerMock */
        $bakerMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\BakerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->bakerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bakerMock);

        $addressMock = $this->getMockBuilder(\CND\Baker\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($addressMock);

        $this->bakerAddressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($addressMock);

        $this->bakerAddressMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($addressMock)
            ->willReturn([]);

        $this->dataHelperMock->expects($this->exactly(2))
            ->method('populateWithArray')
            ->willReturnMap(
                [
                    [
                        $bakerMock,
                        $mergedData, \CND\Baker\Api\Data\BakerInterface::class,
                        $this->dataHelperMock
                    ],
                    [
                        $addressMock,
                        $mergedAddressData, \CND\Baker\Api\Data\AddressInterface::class,
                        $this->dataHelperMock
                    ],
                ]
            );

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($bakerMock, null, '')
            ->willReturn($bakerMock);

        $bakerMock->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);

        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with(null)
            ->willReturn(true);

        /** @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(\Magento\Newsletter\Model\Subscriber::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriberFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($subscriberMock);

        $subscriberMock->expects($this->once())
            ->method('unsubscribeBakerById')
            ->with($bakerId);
        $subscriberMock->expects($this->never())
            ->method('subscribeBakerById');

        $this->sessionMock->expects($this->once())
            ->method('unsBakerFormData');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with(RegistryConstants::CURRENT_CUSTOMER_ID, $bakerId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the baker.'))
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('back', false)
            ->willReturn(false);

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('baker/index', [])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \CND\Baker\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewBakerAndValidationException()
    {
        $subscription = '0';
        $postValue = [
            'baker' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $bakerFormMock */
        $attributeMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap([
                [null, null, $postValue],
                [BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['baker']],
            ]);
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['baker', null, $postValue['baker']],
                    ['address', null, null],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->once())
            ->method('getData')
            ->with('baker')
            ->willReturn($postValue['baker']);

        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $bakerFormMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $bakerFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'baker')
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_baker',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($bakerFormMock);

        /** @var BakerInterface|\PHPUnit_Framework_MockObject_MockObject $bakerMock */
        $bakerMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\BakerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->bakerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bakerMock);

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($bakerMock, null, '')
            ->willThrowException(new \Magento\Framework\Validator\Exception(__('Validator Exception')));

        $bakerMock->expects($this->never())
            ->method('getId');

        $this->authorizationMock->expects($this->never())
            ->method('isAllowed');

        $this->subscriberFactoryMock->expects($this->never())
            ->method('create');

        $this->sessionMock->expects($this->never())
            ->method('unsBakerFormData');

        $this->registryMock->expects($this->never())
            ->method('register');

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');

        $this->messageManagerMock->expects($this->once())
            ->method('addMessage')
            ->with(new \Magento\Framework\Message\Error('Validator Exception'));

        $this->sessionMock->expects($this->once())
            ->method('setBakerFormData')
            ->with($postValue);

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
            ->with('baker/*/new', ['_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \CND\Baker\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewBakerAndLocalizedException()
    {
        $subscription = '0';
        $postValue = [
            'baker' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $bakerFormMock */
        $attributeMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap([
                [null, null, $postValue],
                [BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['baker']],
            ]);
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['baker', null, $postValue['baker']],
                    ['address', null, null],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->once())
            ->method('getData')
            ->with('baker')
            ->willReturn($postValue['baker']);

        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        /** @var Form|\PHPUnit_Framework_MockObject_MockObject $formMock */
        $bakerFormMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $bakerFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'baker')
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_baker',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($bakerFormMock);

        $bakerMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\BakerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->bakerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bakerMock);

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($bakerMock, null, '')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Localized Exception')));

        $bakerMock->expects($this->never())
            ->method('getId');

        $this->authorizationMock->expects($this->never())
            ->method('isAllowed');

        $this->subscriberFactoryMock->expects($this->never())
            ->method('create');

        $this->sessionMock->expects($this->never())
            ->method('unsBakerFormData');

        $this->registryMock->expects($this->never())
            ->method('register');

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');

        $this->messageManagerMock->expects($this->once())
            ->method('addMessage')
            ->with(new \Magento\Framework\Message\Error('Localized Exception'));

        $this->sessionMock->expects($this->once())
            ->method('setBakerFormData')
            ->with($postValue);

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
            ->with('baker/*/new', ['_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \CND\Baker\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewBakerAndException()
    {
        $subscription = '0';
        $postValue = [
            'baker' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $bakerFormMock */
        $attributeMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap([
                [null, null, $postValue],
                [BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['baker']],
            ]);
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['baker', null, $postValue['baker']],
                    ['address', null, null],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->once())
            ->method('getData')
            ->with('baker')
            ->willReturn($postValue['baker']);

        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $bakerFormMock = $this->getMockBuilder(
            \CND\Baker\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $bakerFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'baker')
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $bakerFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_baker',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($bakerFormMock);

        /** @var BakerInterface|\PHPUnit_Framework_MockObject_MockObject $bakerMock */
        $bakerMock = $this->getMockBuilder(
            \CND\Baker\Api\Data\BakerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->bakerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bakerMock);

        $exception = new \Exception(__('Exception'));
        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($bakerMock, null, '')
            ->willThrowException($exception);

        $bakerMock->expects($this->never())
            ->method('getId');

        $this->authorizationMock->expects($this->never())
            ->method('isAllowed');

        $this->subscriberFactoryMock->expects($this->never())
            ->method('create');

        $this->sessionMock->expects($this->never())
            ->method('unsBakerFormData');

        $this->registryMock->expects($this->never())
            ->method('register');

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');

        $this->messageManagerMock->expects($this->once())
            ->method('addException')
            ->with($exception, __('Something went wrong while saving the baker.'));

        $this->sessionMock->expects($this->once())
            ->method('setBakerFormData')
            ->with($postValue);

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
            ->with('baker/*/new', ['_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }
}
