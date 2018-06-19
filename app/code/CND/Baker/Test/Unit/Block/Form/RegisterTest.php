<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Block\Form;

use CND\Baker\Block\Form\Register;
use CND\Baker\Model\AccountManagement;

/**
 * Test class for \CND\Baker\Block\Form\Register.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegisterTest extends \PHPUnit\Framework\TestCase
{
    /** Constants used by the various unit tests */
    const POST_ACTION_URL = 'http://localhost/index.php/baker/account/createpost';

    const LOGIN_URL = 'http://localhost/index.php/baker/account/login';

    const COUNTRY_ID = 'US';

    const FORM_DATA = 'form_data';

    const REGION_ATTRIBUTE_VALUE = 'California';

    const REGION_ID_ATTRIBUTE_CODE = 'region_id';

    const REGION_ID_ATTRIBUTE_VALUE = '12';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Directory\Helper\Data */
    private $directoryHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Model\Session */
    private $_bakerSession;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Module\Manager */
    private $_moduleManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \CND\Baker\Model\Url */
    private $_bakerUrl;

    /** @var Register */
    private $_block;

    protected function setUp()
    {
        $this->_scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_moduleManager = $this->createMock(\Magento\Framework\Module\Manager::class);
        $this->directoryHelperMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->_bakerUrl = $this->createMock(\CND\Baker\Model\Url::class);
        $this->_bakerSession = $this->createPartialMock(
            \CND\Baker\Model\Session::class,
            ['getBakerFormData']
        );
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($this->_scopeConfig));

        $this->_block = new \CND\Baker\Block\Form\Register(
            $context,
            $this->directoryHelperMock,
            $this->getMockForAbstractClass(\Magento\Framework\Json\EncoderInterface::class, [], '', false),
            $this->createMock(\Magento\Framework\App\Cache\Type\Config::class),
            $this->createMock(\Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class),
            $this->createMock(\Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class),
            $this->_moduleManager,
            $this->_bakerSession,
            $this->_bakerUrl
        );
    }

    /**
     * @param string $path
     * @param mixed $configValue
     *
     * @dataProvider getConfigProvider
     */
    public function testGetConfig($path, $configValue)
    {
        $this->_scopeConfig->expects($this->once())->method('getValue')->will($this->returnValue($configValue));
        $this->assertEquals($configValue, $this->_block->getConfig($path));
    }

    /**
     * @return array
     */
    public function getConfigProvider()
    {
        return [
            ['/path/to/config/value', 'config value'],
            ['/path/to/config/value/that/does/not/exist', null]
        ];
    }

    public function testGetPostActionUrl()
    {
        $this->_bakerUrl->expects(
            $this->once()
        )->method(
            'getRegisterPostUrl'
        )->will(
            $this->returnValue(self::POST_ACTION_URL)
        );
        $this->assertEquals(self::POST_ACTION_URL, $this->_block->getPostActionUrl());
    }

    /**
     * Tests the use case where 'back_url' has not been set on the block.
     */
    public function testGetBackUrlNullData()
    {
        $this->_bakerUrl->expects(
            $this->once()
        )->method(
            'getLoginUrl'
        )->will(
            $this->returnValue(self::LOGIN_URL)
        );
        $this->assertEquals(self::LOGIN_URL, $this->_block->getBackUrl());
    }

    /**
     * Tests the use case where 'back_url' has been set on the block.
     */
    public function testGetBackUrlNotNullData()
    {
        $this->_block->setData('back_url', self::LOGIN_URL);
        $this->assertEquals(self::LOGIN_URL, $this->_block->getBackUrl());
    }

    /**
     * Form data has been set on the block so Form\Register::getFormData() simply returns it.
     */
    public function testGetFormDataNotNullFormData()
    {
        $data = new \Magento\Framework\DataObject();
        $this->_block->setData(self::FORM_DATA, $data);
        $this->assertSame($data, $this->_block->getFormData());
    }

    /**
     * Form data has not been set on the block and there is no baker data in the baker session. So
     * we expect an empty \Magento\Framework\DataObject.
     */
    public function testGetFormDataNullFormData()
    {
        $data = new \Magento\Framework\DataObject();
        $this->_bakerSession->expects($this->once())->method('getBakerFormData')->will($this->returnValue(null));
        $this->assertEquals($data, $this->_block->getFormData());
        $this->assertEquals($data, $this->_block->getData(self::FORM_DATA));
    }

    /**
     * Form data has not been set on the block, but there is baker data from the baker session.
     * The baker data is something other than 'region_id' so that code path is skipped.
     */
    public function testGetFormDataNullFormDataBakerFormData()
    {
        $data = new \Magento\Framework\DataObject();
        $data->setFirstname('John');
        $data->setBakerData(1);
        $bakerFormData = ['firstname' => 'John'];
        $this->_bakerSession->expects(
            $this->once()
        )->method(
            'getBakerFormData'
        )->will(
            $this->returnValue($bakerFormData)
        );
        $this->assertEquals($data, $this->_block->getFormData());
        $this->assertEquals($data, $this->_block->getData(self::FORM_DATA));
    }

    /**
     * Form data has not been set on the block, but there is baker data from the baker session.
     * The baker data is the 'region_id' so that code path is executed.
     */
    public function testGetFormDataBakerFormDataRegionId()
    {
        $data = new \Magento\Framework\DataObject();
        $data->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $data->setBakerData(1);
        $data[self::REGION_ID_ATTRIBUTE_CODE] = (int)self::REGION_ID_ATTRIBUTE_VALUE;
        $bakerFormData = [self::REGION_ID_ATTRIBUTE_CODE => self::REGION_ID_ATTRIBUTE_VALUE];
        $this->_bakerSession->expects(
            $this->once()
        )->method(
            'getBakerFormData'
        )->will(
            $this->returnValue($bakerFormData)
        );
        $formData = $this->_block->getFormData();
        $this->assertEquals($data, $formData);
        $this->assertTrue(isset($formData[self::REGION_ID_ATTRIBUTE_CODE]));
        $this->assertSame((int)self::REGION_ID_ATTRIBUTE_VALUE, $formData[self::REGION_ID_ATTRIBUTE_CODE]);
    }

    /**
     * Tests the Form\Register::getCountryId() use case where CountryId has been set on the form data
     * Object that has been set on the block.
     */
    public function testGetCountryIdFormData()
    {
        $formData = new \Magento\Framework\DataObject();
        $formData->setCountryId(self::COUNTRY_ID);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the default country use case of parent::getCountryId() where CountryId has not been set
     * and the 'country_id' attribute has also not been set.
     */
    public function testGetCountryIdParentNullData()
    {
        $this->directoryHelperMock->expects(
            $this->once()
        )->method(
            'getDefaultCountry'
        )->will(
            $this->returnValue(self::COUNTRY_ID)
        );
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the parent::getCountryId() use case where CountryId has not been set and the 'country_id'
     * attribute code has been set on the block.
     */
    public function testGetCountryIdParentNotNullData()
    {
        $this->_block->setData('country_id', self::COUNTRY_ID);
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the first if conditional of Form\Register::getRegion(), which checks to see if Region has
     * been set on the form data Object that's set on the block.
     */
    public function testGetRegionByRegion()
    {
        $formData = new \Magento\Framework\DataObject();
        $formData->setRegion(self::REGION_ATTRIBUTE_VALUE);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertSame(self::REGION_ATTRIBUTE_VALUE, $this->_block->getRegion());
    }

    /**
     * Tests the second if conditional of Form\Register::getRegion(), which checks to see if RegionId
     * has been set on the form data Object that's set on the block.
     */
    public function testGetRegionByRegionId()
    {
        $formData = new \Magento\Framework\DataObject();
        $formData->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertSame(self::REGION_ID_ATTRIBUTE_VALUE, $this->_block->getRegion());
    }

    /**
     * Neither Region, nor RegionId have been set on the form data Object that's set on the block so a
     * null value is expected.
     */
    public function testGetRegionNull()
    {
        $formData = new \Magento\Framework\DataObject();
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertNull($this->_block->getRegion());
    }

    /**
     * @param $isNewsletterEnabled
     * @param $expectedValue
     *
     * @dataProvider isNewsletterEnabledProvider
     */
    public function testIsNewsletterEnabled($isNewsletterEnabled, $expectedValue)
    {
        $this->_moduleManager->expects(
            $this->once()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Newsletter'
        )->will(
            $this->returnValue($isNewsletterEnabled)
        );
        $this->assertEquals($expectedValue, $this->_block->isNewsletterEnabled());
    }

    /**
     * @return array
     */
    public function isNewsletterEnabledProvider()
    {
        return [[true, true], [false, false]];
    }

    /**
     * This test is designed to execute all code paths of Form\Register::getFormData() when testing the
     * Form\Register::restoreSessionData() method.
     */
    public function testRestoreSessionData()
    {
        $data = new \Magento\Framework\DataObject();
        $data->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $data->setBakerData(1);
        $data[self::REGION_ID_ATTRIBUTE_CODE] = (int)self::REGION_ID_ATTRIBUTE_VALUE;
        $bakerFormData = [self::REGION_ID_ATTRIBUTE_CODE => self::REGION_ID_ATTRIBUTE_VALUE];
        $this->_bakerSession->expects(
            $this->once()
        )->method(
            'getBakerFormData'
        )->will(
            $this->returnValue($bakerFormData)
        );
        $form = $this->createMock(\CND\Baker\Model\Metadata\Form::class);
        $request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false);
        $formData = $this->_block->getFormData();
        $form->expects(
            $this->once()
        )->method(
            'prepareRequest'
        )->with(
            $formData->getData()
        )->will(
            $this->returnValue($request)
        );
        $form->expects(
            $this->once()
        )->method(
            'extractData'
        )->with(
            $request,
            null,
            false
        )->will(
            $this->returnValue($bakerFormData)
        );
        $form->expects($this->once())->method('restoreData')->will($this->returnValue($bakerFormData));
        $block = $this->_block->restoreSessionData($form, null, false);
        $this->assertSame($this->_block, $block);
        $this->assertEquals($data, $block->getData(self::FORM_DATA));
    }

    /**
     * Test get minimum password length
     */
    public function testGetMinimumPasswordLength()
    {
        $this->_scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH
        )->will(
            $this->returnValue(6)
        );
        $this->assertEquals(6, $this->_block->getMinimumPasswordLength());
    }

    /**
     * Test get required character classes number
     */
    public function testGetRequiredCharacterClassesNumber()
    {
        $this->_scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER
        )->will(
            $this->returnValue(3)
        );
        $this->assertEquals(3, $this->_block->getRequiredCharacterClassesNumber());
    }
}
