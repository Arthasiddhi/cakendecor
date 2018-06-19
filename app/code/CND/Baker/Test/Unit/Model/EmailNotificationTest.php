<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\Model;

use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Model\EmailNotification;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class EmailNotificationTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \CND\Baker\Model\BakerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerRegistryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilderMock;

    /**
     * @var \CND\Baker\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerViewHelperMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\CND\Baker\Model\Data\BakerSecure
     */
    private $bakerSecureMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    private $storeMock;

    /**
     * @var \CND\Baker\Model\EmailNotification
     */
    private $model;

    /**
     * @var SenderResolverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $senderResolverMock;

    public function setUp()
    {
        $this->bakerRegistryMock = $this->createMock(\CND\Baker\Model\BakerRegistry::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->transportBuilderMock = $this->createMock(\Magento\Framework\Mail\Template\TransportBuilder::class);

        $this->bakerViewHelperMock = $this->createMock(\CND\Baker\Helper\View::class);

        $this->dataProcessorMock = $this->createMock(\Magento\Framework\Reflection\DataObjectProcessor::class);

        $contextMock = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);

        $this->scopeConfigMock = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->bakerSecureMock = $this->createMock(\CND\Baker\Model\Data\BakerSecure::class);

        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->senderResolverMock = $this->getMockBuilder(SenderResolverInterface::class)
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            EmailNotification::class,
            [
                'bakerRegistry' => $this->bakerRegistryMock,
                'storeManager' => $this->storeManagerMock,
                'transportBuilder' => $this->transportBuilderMock,
                'bakerViewHelper' => $this->bakerViewHelperMock,
                'dataProcessor' => $this->dataProcessorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'senderResolver' => $this->senderResolverMock
            ]
        );
    }

    /**
     * @param int $testNumber
     * @param string $oldEmail
     * @param string $newEmail
     * @param bool $isPasswordChanged
     *
     * @dataProvider sendNotificationEmailsDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCredentialsChanged($testNumber, $oldEmail, $newEmail, $isPasswordChanged)
    {
        $bakerId = 1;
        $bakerStoreId = 2;
        $bakerWebsiteId = 1;
        $bakerData = ['key' => 'value'];
        $bakerName = 'Baker Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];

        $expects = $this->once();
        $xmlPathTemplate = EmailNotification::XML_PATH_RESET_PASSWORD_TEMPLATE;
        switch ($testNumber) {
            case 1:
                $xmlPathTemplate = EmailNotification::XML_PATH_RESET_PASSWORD_TEMPLATE;
                $expects = $this->once();
                break;
            case 2:
                $xmlPathTemplate = \CND\Baker\Model\EmailNotification::XML_PATH_CHANGE_EMAIL_TEMPLATE;
                $expects = $this->exactly(2);
                break;
            case 3:
                $xmlPathTemplate = EmailNotification::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE;
                $expects = $this->exactly(2);
                break;
        }

        $this->senderResolverMock
            ->expects($expects)
            ->method('resolve')
            ->with($sender, $bakerStoreId)
            ->willReturn($senderValues);

        /** @var \PHPUnit_Framework_MockObject_MockObject $origBaker */
        $origBaker = $this->createMock(BakerInterface::class);
        $origBaker->expects($this->any())
            ->method('getStoreId')
            ->willReturn(0);
        $origBaker->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);
        $origBaker->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($bakerWebsiteId);

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($bakerStoreId);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getStore')
            ->willReturn($storeMock);

        $websiteMock = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getStoreIds']);
        $websiteMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([$bakerStoreId]);

        $this->storeManagerMock->expects(clone $expects)
            ->method('getWebsite')
            ->with($bakerWebsiteId)
            ->willReturn($websiteMock);

        $bakerSecureMock = $this->createMock(\CND\Baker\Model\Data\BakerSecure::class);
        $this->bakerRegistryMock->expects(clone $expects)
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($bakerSecureMock);

        $this->dataProcessorMock->expects(clone $expects)
            ->method('buildOutputDataArray')
            ->with($origBaker, BakerInterface::class)
            ->willReturn($bakerData);

        $this->bakerViewHelperMock->expects($this->any())
            ->method('getBakerName')
            ->with($origBaker)
            ->willReturn($bakerName);

        $bakerSecureMock->expects(clone $expects)
            ->method('addData')
            ->with($bakerData)
            ->willReturnSelf();
        $bakerSecureMock->expects(clone $expects)
            ->method('setData')
            ->with('name', $bakerName)
            ->willReturnSelf();

        /** @var BakerInterface | \PHPUnit_Framework_MockObject_MockObject $savedBaker */
        $savedBaker = clone $origBaker;

        $origBaker->expects($this->any())
            ->method('getEmail')
            ->willReturn($oldEmail);

        $savedBaker->expects($this->any())
            ->method('getEmail')
            ->willReturn($newEmail);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->withConsecutive(
                [$xmlPathTemplate, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $bakerStoreId],
                [
                    \CND\Baker\Model\EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $bakerStoreId
                ],
                [$xmlPathTemplate, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $bakerStoreId],
                [
                    \CND\Baker\Model\EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $bakerStoreId
                ]
            )
            ->willReturnOnConsecutiveCalls($templateIdentifier, $sender, $templateIdentifier, $sender);

        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateOptions')
            ->with(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $bakerStoreId])
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setTemplateVars')
            ->with(['baker' => $bakerSecureMock, 'store' => $storeMock])
            ->willReturnSelf();
        $this->transportBuilderMock->expects(clone $expects)
            ->method('setFrom')
            ->with($senderValues)
            ->willReturnSelf();

        $this->transportBuilderMock->expects(clone $expects)
            ->method('addTo')
            ->withConsecutive([$oldEmail, $bakerName], [$newEmail, $bakerName])
            ->willReturnSelf();

        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $this->transportBuilderMock->expects(clone $expects)
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects(clone $expects)
            ->method('sendMessage');

        $this->model->credentialsChanged($savedBaker, $oldEmail, $isPasswordChanged);
    }

    /**
     * @return array
     */
    public function sendNotificationEmailsDataProvider()
    {
        return [
            [
                'test_number' => 1,
                'old_email' => 'test@example.com',
                'new_email' => 'test@example.com',
                'is_password_changed' => true
            ],
            [
                'test_number' => 2,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => false
            ],
            [
                'test_number' => 3,
                'old_email' => 'test1@example.com',
                'new_email' => 'test2@example.com',
                'is_password_changed' => true
            ]
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordReminder()
    {
        $bakerId = 1;
        $bakerStoreId = 2;
        $bakerEmail = 'email@email.com';
        $bakerData = ['key' => 'value'];
        $bakerName = 'Baker Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($sender, $bakerStoreId)
            ->willReturn($senderValues);

        /** @var BakerInterface | \PHPUnit_Framework_MockObject_MockObject $baker */
        $baker = $this->createMock(BakerInterface::class);
        $baker->expects($this->any())
            ->method('getStoreId')
            ->willReturn($bakerStoreId);
        $baker->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->any())
            ->method('getEmail')
            ->willReturn($bakerEmail);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($bakerStoreId);

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeManagerMock->expects($this->at(1))
            ->method('getStore')
            ->with($bakerStoreId)
            ->willReturn($this->storeMock);

        $this->bakerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($baker, BakerInterface::class)
            ->willReturn($bakerData);

        $this->bakerViewHelperMock->expects($this->any())
            ->method('getBakerName')
            ->with($baker)
            ->willReturn($bakerName);

        $this->bakerSecureMock->expects($this->once())
            ->method('addData')
            ->with($bakerData)
            ->willReturnSelf();
        $this->bakerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', $bakerName)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_REMIND_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($sender);

        $this->mockDefaultTransportBuilder(
            $templateIdentifier,
            $bakerStoreId,
            $senderValues,
            $bakerEmail,
            $bakerName,
            ['baker' => $this->bakerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordReminder($baker);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPasswordResetConfirmation()
    {
        $bakerId = 1;
        $bakerStoreId = 2;
        $bakerEmail = 'email@email.com';
        $bakerData = ['key' => 'value'];
        $bakerName = 'Baker Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($sender, $bakerStoreId)
            ->willReturn($senderValues);

        /** @var BakerInterface | \PHPUnit_Framework_MockObject_MockObject $baker */
        $baker = $this->createMock(BakerInterface::class);
        $baker->expects($this->any())
            ->method('getStoreId')
            ->willReturn($bakerStoreId);
        $baker->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->any())
            ->method('getEmail')
            ->willReturn($bakerEmail);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($bakerStoreId);

        $this->storeManagerMock->expects($this->at(0))
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeManagerMock->expects($this->at(1))
            ->method('getStore')
            ->with($bakerStoreId)
            ->willReturn($this->storeMock);

        $this->bakerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($baker, BakerInterface::class)
            ->willReturn($bakerData);

        $this->bakerViewHelperMock->expects($this->any())
            ->method('getBakerName')
            ->with($baker)
            ->willReturn($bakerName);

        $this->bakerSecureMock->expects($this->once())
            ->method('addData')
            ->with($bakerData)
            ->willReturnSelf();
        $this->bakerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', $bakerName)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($sender);

        $this->mockDefaultTransportBuilder(
            $templateIdentifier,
            $bakerStoreId,
            $senderValues,
            $bakerEmail,
            $bakerName,
            ['baker' => $this->bakerSecureMock, 'store' => $this->storeMock]
        );

        $this->model->passwordResetConfirmation($baker);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testNewAccount()
    {
        $bakerId = 1;
        $bakerStoreId = 2;
        $bakerEmail = 'email@email.com';
        $bakerData = ['key' => 'value'];
        $bakerName = 'Baker Name';
        $templateIdentifier = 'Template Identifier';
        $sender = 'Sender';
        $senderValues = ['name' => $sender, 'email' => $sender];

        $this->senderResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($sender, $bakerStoreId)
            ->willReturn($senderValues);

        /** @var BakerInterface | \PHPUnit_Framework_MockObject_MockObject $baker */
        $baker = $this->createMock(BakerInterface::class);
        $baker->expects($this->any())
            ->method('getStoreId')
            ->willReturn($bakerStoreId);
        $baker->expects($this->any())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->any())
            ->method('getEmail')
            ->willReturn($bakerEmail);

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($bakerStoreId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($bakerStoreId)
            ->willReturn($this->storeMock);

        $this->bakerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($bakerId)
            ->willReturn($this->bakerSecureMock);

        $this->dataProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($baker, BakerInterface::class)
            ->willReturn($bakerData);

        $this->bakerViewHelperMock->expects($this->any())
            ->method('getBakerName')
            ->with($baker)
            ->willReturn($bakerName);

        $this->bakerSecureMock->expects($this->once())
            ->method('addData')
            ->with($bakerData)
            ->willReturnSelf();
        $this->bakerSecureMock->expects($this->once())
            ->method('setData')
            ->with('name', $bakerName)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($templateIdentifier);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(EmailNotification::XML_PATH_REGISTER_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE, $bakerStoreId)
            ->willReturn($sender);

        $this->mockDefaultTransportBuilder(
            $templateIdentifier,
            $bakerStoreId,
            $senderValues,
            $bakerEmail,
            $bakerName,
            ['baker' => $this->bakerSecureMock, 'back_url' => '', 'store' => $this->storeMock]
        );

        $this->model->newAccount($baker, EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED, '', $bakerStoreId);
    }

    /**
     * Create defaul mock for $this->transportBuilderMock
     *
     * @param string $templateIdentifier
     * @param int $bakerStoreId
     * @param array $senderValues
     * @param string $bakerEmail
     * @param string $bakerName
     * @param array $templateVars
     */
    protected function mockDefaultTransportBuilder(
        $templateIdentifier,
        $bakerStoreId,
        array $senderValues,
        $bakerEmail,
        $bakerName,
        array $templateVars = []
    ) {
        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => Area::AREA_FRONTEND, 'store' => $bakerStoreId])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with($templateVars)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with($senderValues)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->with($bakerEmail, $bakerName)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage');
    }
}
