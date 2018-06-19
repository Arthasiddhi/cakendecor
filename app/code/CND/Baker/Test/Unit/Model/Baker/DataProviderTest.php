<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model\Baker;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Model\Config\Share;
use CND\Baker\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use CND\Baker\Model\ResourceModel\Baker\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;

/**
 * Class DataProviderTest
 *
 * Test for class \CND\Baker\Model\Baker\DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    const ATTRIBUTE_CODE = 'test-code';
    const OPTIONS_RESULT = 'test-options';

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bakerCollectionFactoryMock;

    /**
     * @var EavValidationRules|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavValidationRulesMock;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \CND\Baker\Model\FileProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProcessorFactory;

    /**
     * @var \CND\Baker\Model\FileProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProcessor;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bakerCollectionFactoryMock = $this->createPartialMock(
            \CND\Baker\Model\ResourceModel\Baker\CollectionFactory::class,
            ['create']
        );
        $this->eavValidationRulesMock = $this
            ->getMockBuilder(\Magento\Ui\DataProvider\EavValidationRules::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this
            ->getMockBuilder(\Magento\Framework\Session\SessionManagerInterface::class)
            ->setMethods(['getBakerFormData', 'unsBakerFormData'])
            ->getMockForAbstractClass();

        $this->fileProcessor = $this->getMockBuilder(\CND\Baker\Model\FileProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessorFactory = $this->getMockBuilder(\CND\Baker\Model\FileProcessorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    /**
     * Run test getAttributesMeta method
     *
     * @param array $expected
     * @return void
     *
     * @dataProvider getAttributesMetaDataProvider
     */
    public function testGetAttributesMetaWithOptions(array $expected)
    {
        $helper = new ObjectManager($this);
        /** @var \CND\Baker\Model\Baker\DataProvider $dataProvider */
        $dataProvider = $helper->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->getBakerCollectionFactoryMock(),
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($expected, $meta);
    }

    /**
     * Data provider for testGetAttributesMeta
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAttributesMetaDataProvider()
    {
        return [
            [
                'expected' => [
                    'baker' => [
                        'children' => [
                            self::ATTRIBUTE_CODE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'frontend_input',
                                            'formElement' => 'frontend_input',
                                            'options' => 'test-options',
                                            'visible' => null,
                                            'required' => 'is_required',
                                            'label' => __('frontend_label'),
                                            'sortOrder' => 'sort_order',
                                            'notice' => 'note',
                                            'default' => 'default_value',
                                            'size' => 'multiline_count',
                                            'componentType' => Field::NAME,
                                        ],
                                    ],
                                ],
                            ],
                            'test-code-boolean' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'frontend_input',
                                            'formElement' => 'frontend_input',
                                            'visible' => null,
                                            'required' => 'is_required',
                                            'label' => __('frontend_label'),
                                            'sortOrder' => 'sort_order',
                                            'notice' => 'note',
                                            'default' => 'default_value',
                                            'size' => 'multiline_count',
                                            'componentType' => Field::NAME,
                                            'prefer' => 'toggle',
                                            'valueMap' => [
                                                'true' => 1,
                                                'false' => 0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'address' => [
                        'children' => [
                            self::ATTRIBUTE_CODE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'frontend_input',
                                            'formElement' => 'frontend_input',
                                            'options' => 'test-options',
                                            'visible' => null,
                                            'required' => 'is_required',
                                            'label' => __('frontend_label'),
                                            'sortOrder' => 'sort_order',
                                            'notice' => 'note',
                                            'default' => 'default_value',
                                            'size' => 'multiline_count',
                                            'componentType' => Field::NAME,
                                        ],
                                    ],
                                ],
                            ],
                            'test-code-boolean' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'frontend_input',
                                            'formElement' => 'frontend_input',
                                            'visible' => null,
                                            'required' => 'is_required',
                                            'label' => 'frontend_label',
                                            'sortOrder' => 'sort_order',
                                            'notice' => 'note',
                                            'default' => 'default_value',
                                            'size' => 'multiline_count',
                                            'componentType' => Field::NAME,
                                            'prefer' => 'toggle',
                                            'valueMap' => [
                                                'true' => 1,
                                                'false' => 0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'country_id' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'frontend_input',
                                            'formElement' => 'frontend_input',
                                            'options' => 'test-options',
                                            'visible' => null,
                                            'required' => 'is_required',
                                            'label' => __('frontend_label'),
                                            'sortOrder' => 'sort_order',
                                            'notice' => 'note',
                                            'default' => 'default_value',
                                            'size' => 'multiline_count',
                                            'componentType' => Field::NAME,
                                            'filterBy' => [
                                                'target' => '${ $.provider }:data.baker.website_id',
                                                'field' => 'website_ids'
                                            ]
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * @return CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBakerCollectionFactoryMock()
    {
        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->bakerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        return $this->bakerCollectionFactoryMock;
    }

    /**
     * @return Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEavConfigMock($bakerAttributes = [])
    {
        $this->eavConfigMock->expects($this->at(0))
            ->method('getEntityType')
            ->with('baker')
            ->willReturn($this->getTypeBakerMock($bakerAttributes));
        $this->eavConfigMock->expects($this->at(1))
            ->method('getEntityType')
            ->with('baker_address')
            ->willReturn($this->getTypeAddressMock());

        return $this->eavConfigMock;
    }

    /**
     * @return Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeBakerMock($bakerAttributes = [])
    {
        $typeBakerMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributesCollection = !empty($bakerAttributes) ? $bakerAttributes : $this->getAttributeMock();
        $typeBakerMock->expects($this->any())
            ->method('getEntityTypeCode')
            ->willReturn('baker');
        foreach ($attributesCollection as $attribute) {
            $attribute->expects($this->any())
                ->method('getEntityType')
                ->willReturn($typeBakerMock);
        }

        $typeBakerMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($attributesCollection);

        return $typeBakerMock;
    }

    /**
     * @return Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeAddressMock()
    {
        $typeAddressMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $typeAddressMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($this->getAttributeMock('address'));

        return $typeAddressMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $attributeMock
     * @param \PHPUnit_Framework_MockObject_MockObject $attributeBooleanMock
     * @param array $options
     */
    private function injectVisibilityProps(
        \PHPUnit_Framework_MockObject_MockObject $attributeMock,
        \PHPUnit_Framework_MockObject_MockObject $attributeBooleanMock,
        array $options = []
    ) {
        if (isset($options[self::ATTRIBUTE_CODE]['visible'])) {
            $attributeMock->expects($this->any())
                ->method('getIsVisible')
                ->willReturn($options[self::ATTRIBUTE_CODE]['visible']);
        }

        if (isset($options[self::ATTRIBUTE_CODE]['user_defined'])) {
            $attributeMock->expects($this->any())
                ->method('getIsUserDefined')
                ->willReturn($options[self::ATTRIBUTE_CODE]['user_defined']);
        }

        if (isset($options[self::ATTRIBUTE_CODE]['is_used_in_forms'])) {
            $attributeMock->expects($this->any())
                ->method('getUsedInForms')
                ->willReturn($options[self::ATTRIBUTE_CODE]['is_used_in_forms']);
        }

        if (isset($options['test-code-boolean']['visible'])) {
            $attributeBooleanMock->expects($this->any())
                ->method('getIsVisible')
                ->willReturn($options['test-code-boolean']['visible']);
        }

        if (isset($options['test-code-boolean']['user_defined'])) {
            $attributeBooleanMock->expects($this->any())
                ->method('getIsUserDefined')
                ->willReturn($options['test-code-boolean']['user_defined']);
        }

        if (isset($options['test-code-boolean']['is_used_in_forms'])) {
            $attributeBooleanMock->expects($this->any())
                ->method('getUsedInForms')
                ->willReturn($options['test-code-boolean']['is_used_in_forms']);
        }
    }

    /**
     * @return AbstractAttribute[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected function getAttributeMock($type = 'baker', $options = [])
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getDataUsingMethod',
                    'usesSource',
                    'getFrontendInput',
                    'getIsVisible',
                    'getSource',
                    'getIsUserDefined',
                    'getUsedInForms',
                    'getEntityType',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sourceMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeCode = self::ATTRIBUTE_CODE;
        if (isset($options[self::ATTRIBUTE_CODE]['specific_code_prefix'])) {
            $attributeCode = $attributeCode . $options[self::ATTRIBUTE_CODE]['specific_code_prefix'];
        }

        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $sourceMock->expects($this->any())
            ->method('getAllOptions')
            ->willReturn(self::OPTIONS_RESULT);

        $attributeMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback($this->attributeGetUsingMethodCallback());

        $attributeMock->expects($this->any())
            ->method('usesSource')
            ->willReturn(true);
        $attributeMock->expects($this->any())
            ->method('getSource')
            ->willReturn($sourceMock);

        $attributeBooleanMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getDataUsingMethod',
                    'usesSource',
                    'getFrontendInput',
                    'getIsVisible',
                    'getIsUserDefined',
                    'getUsedInForms',
                    'getSource',
                    'getEntityType',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeBooleanMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('boolean');
        $attributeBooleanMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback($this->attributeGetUsingMethodCallback());

        $attributeBooleanMock->expects($this->once())
            ->method('usesSource')
            ->willReturn(false);
        $booleanAttributeCode = 'test-code-boolean';
        if (isset($options['test-code-boolean']['specific_code_prefix'])) {
            $booleanAttributeCode = $booleanAttributeCode . $options['test-code-boolean']['specific_code_prefix'];
        }

        $attributeBooleanMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($booleanAttributeCode);

        $this->eavValidationRulesMock->expects($this->any())
            ->method('build')
            ->willReturnMap([
                [$attributeMock, $this->logicalNot($this->isEmpty()), []],
                [$attributeBooleanMock, $this->logicalNot($this->isEmpty()), []],
            ]);
        $mocks = [$attributeMock, $attributeBooleanMock];
        $this->injectVisibilityProps($attributeMock, $attributeBooleanMock, $options);
        if ($type == "address") {
            $mocks[] = $this->getCountryAttrMock();
        }
        return $mocks;
    }

    /**
     * Callback for ::getDataUsingMethod
     *
     * @return \Closure
     */
    private function attributeGetUsingMethodCallback()
    {
        return function ($origName) {
            return $origName;
        };
    }

    private function getCountryAttrMock()
    {
        $countryByWebsiteMock = $this->getMockBuilder(CountryWithWebsites::class)
            ->disableOriginalConstructor()
            ->getMock();
        $countryByWebsiteMock->expects($this->any())
            ->method('getAllOptions')
            ->willReturn('test-options');
        $shareMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [CountryWithWebsites::class, $countryByWebsiteMock],
                [Share::class, $shareMock],
            ]);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        $countryAttrMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['getAttributeCode', 'getDataUsingMethod', 'usesSource', 'getSource', 'getLabel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $countryAttrMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('country_id');

        $countryAttrMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback(
                function ($origName) {
                    return $origName;
                }
            );
        $countryAttrMock->expects($this->any())
            ->method('getLabel')
            ->willReturn(__('frontend_label'));
        $countryAttrMock->expects($this->any())
            ->method('usesSource')
            ->willReturn(true);
        $countryAttrMock->expects($this->any())
            ->method('getSource')
            ->willReturn(null);

        return $countryAttrMock;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetData()
    {
        $bakerData = [
            'email' => 'test@test.ua',
            'default_billing' => 2,
            'default_shipping' => 2,
            'password_hash' => 'password_hash',
            'rp_token' => 'rp_token',
            'confirmation' => 'confirmation',
        ];
        $addressData = [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'street' => "street\nstreet",
        ];

        $baker = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder(\CND\Baker\Model\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->bakerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$baker]);
        $baker->expects($this->once())
            ->method('getData')
            ->willReturn($bakerData);
        $baker->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $address->expects($this->once())
            ->method('load')
            ->with(2)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getData')
            ->willReturn($addressData);
        $address->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $helper = new ObjectManager($this);
        $dataProvider = $helper->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->bakerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $reflection = new \ReflectionClass(get_class($dataProvider));
        $reflectionProperty = $reflection->getProperty('session');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dataProvider, $this->sessionMock);

        $this->sessionMock->expects($this->once())
            ->method('getBakerFormData')
            ->willReturn(null);

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals(
            [
                '' => [
                    'baker' => [
                        'email' => 'test@test.ua',
                        'default_billing' => 2,
                        'default_shipping' => 2,
                    ],
                    'address' => [
                        2 => [
                            'firstname' => 'firstname',
                            'lastname' => 'lastname',
                            'street' => [
                                'street',
                                'street',
                            ],
                            'default_billing' => 2,
                            'default_shipping' => 2,
                        ]
                    ]
                ]
            ],
            $dataProvider->getData()
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetDataWithBakerFormData()
    {
        $bakerId = 11;
        $bakerFormData = [
            'baker' => [
                'email' => 'test1@test1.ua',
                'default_billing' => 3,
                'default_shipping' => 3,
                'entity_id' => $bakerId,
            ],
            'address' => [
                3 => [
                    'firstname' => 'firstname1',
                    'lastname' => 'lastname1',
                    'street' => [
                        'street1',
                        'street2',
                    ],
                    'default_billing' => 3,
                    'default_shipping' => 3,
                ],
            ],
        ];

        $baker = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder(\CND\Baker\Model\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->bakerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$baker]);
        $baker->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => 'test@test.ua',
                'default_billing' => 2,
                'default_shipping' => 2,
            ]);
        $baker->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);
        $baker->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $baker->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $address->expects($this->once())
            ->method('load')
            ->with(2)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getData')
            ->willReturn([
                'firstname' => 'firstname',
                'lastname' => 'lastname',
                'street' => "street\nstreet",
            ]);
        $address->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $helper = new ObjectManager($this);
        $dataProvider = $helper->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->bakerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $reflection = new \ReflectionClass(get_class($dataProvider));
        $reflectionProperty = $reflection->getProperty('session');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dataProvider, $this->sessionMock);

        $this->sessionMock->expects($this->once())
            ->method('getBakerFormData')
            ->willReturn($bakerFormData);
        $this->sessionMock->expects($this->once())
            ->method('unsBakerFormData');

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals([$bakerId => $bakerFormData], $dataProvider->getData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    public function testGetDataWithCustomAttributeImage()
    {
        $bakerId = 1;
        $bakerEmail = 'user1@example.com';

        $filename = '/filename.ext1';
        $viewUrl = 'viewUrl';
        $mime = 'image/png';

        $expectedData = [
            $bakerId => [
                'baker' => [
                    'email' => $bakerEmail,
                    'img1' => [
                        [
                            'file' => $filename,
                            'size' => 1,
                            'url' => $viewUrl,
                            'name' => 'filename.ext1',
                            'type' => $mime,
                        ],
                    ],
                ],
            ],
        ];

        $attributeMock = $this->getMockBuilder(\CND\Baker\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('img1');

        $entityTypeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(BakerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $bakerMock = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bakerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => $bakerEmail,
                'img1' => $filename,
            ]);
        $bakerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $bakerMock->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);
        $bakerMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn([$attributeMock]);
        $bakerMock->expects($this->once())
            ->method('getEntityType')
            ->willReturn($entityTypeMock);

        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$bakerMock]);

        $this->bakerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->sessionMock->expects($this->once())
            ->method('getBakerFormData')
            ->willReturn([]);

        $this->fileProcessorFactory->expects($this->any())
            ->method('create')
            ->with([
                'entityTypeCode' => BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            ])
            ->willReturn($this->fileProcessor);

        $this->fileProcessor->expects($this->once())
            ->method('isExist')
            ->with($filename)
            ->willReturn(true);
        $this->fileProcessor->expects($this->once())
            ->method('getStat')
            ->with($filename)
            ->willReturn(['size' => 1]);
        $this->fileProcessor->expects($this->once())
            ->method('getViewUrl')
            ->with('/filename.ext1', 'image')
            ->willReturn($viewUrl);
        $this->fileProcessor->expects($this->once())
            ->method('getMimeType')
            ->with($filename)
            ->willReturn($mime);

        $objectManager = new ObjectManager($this);
        $dataProvider = $objectManager->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->bakerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'session',
            $this->sessionMock
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals($expectedData, $dataProvider->getData());
    }

    public function testGetDataWithCustomAttributeImageNoData()
    {
        $bakerId = 1;
        $bakerEmail = 'user1@example.com';

        $expectedData = [
            $bakerId => [
                'baker' => [
                    'email' => $bakerEmail,
                    'img1' => [],
                ],
            ],
        ];

        $attributeMock = $this->getMockBuilder(\CND\Baker\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('img1');

        $entityTypeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(BakerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $bakerMock = $this->getMockBuilder(\CND\Baker\Model\Baker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bakerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => $bakerEmail,
            ]);
        $bakerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $bakerMock->expects($this->once())
            ->method('getId')
            ->willReturn($bakerId);
        $bakerMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn([$attributeMock]);
        $bakerMock->expects($this->once())
            ->method('getEntityType')
            ->willReturn($entityTypeMock);

        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$bakerMock]);

        $this->bakerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->sessionMock->expects($this->once())
            ->method('getBakerFormData')
            ->willReturn([]);

        $objectManager = new ObjectManager($this);
        $dataProvider = $objectManager->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->bakerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'session',
            $this->sessionMock
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals($expectedData, $dataProvider->getData());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetAttributesMetaWithCustomAttributeImage()
    {
        $maxFileSize = 1000;
        $allowedExtension = 'ext1 ext2';

        $attributeCode = 'img1';

        $collectionMock = $this->getMockBuilder(\CND\Baker\Model\ResourceModel\Baker\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->bakerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods([
                'getAttributeCode',
                'getFrontendInput',
                'getDataUsingMethod',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback(
                function ($origName) {
                    return $origName;
                }
            );

        $typeBakerMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeBakerMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn([$attributeMock]);
        $typeBakerMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(BakerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $typeAddressMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeAddressMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn([]);

        $this->eavConfigMock->expects($this->at(0))
            ->method('getEntityType')
            ->with('baker')
            ->willReturn($typeBakerMock);
        $this->eavConfigMock->expects($this->at(1))
            ->method('getEntityType')
            ->with('baker_address')
            ->willReturn($typeAddressMock);

        $this->eavValidationRulesMock->expects($this->once())
            ->method('build')
            ->with($attributeMock, [
                'dataType' => 'frontend_input',
                'formElement' => 'frontend_input',
                'visible' => 'is_visible',
                'required' => 'is_required',
                'sortOrder' => 'sort_order',
                'notice' => 'note',
                'default' => 'default_value',
                'size' => 'multiline_count',
                'label' => __('frontend_label'),
            ])
            ->willReturn([
                'max_file_size' => $maxFileSize,
                'file_extensions' => 'ext1, eXt2 ', // Added spaces and upper-cases
            ]);

        $this->fileProcessorFactory->expects($this->any())
            ->method('create')
            ->with([
                'entityTypeCode' => BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            ])
            ->willReturn($this->fileProcessor);

        $objectManager = new ObjectManager($this);
        $dataProvider = $objectManager->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->bakerCollectionFactoryMock,
                'eavConfig' => $this->eavConfigMock,
                'fileProcessorFactory' => $this->fileProcessorFactory,
            ]
        );

        $result = $dataProvider->getMeta();

        $this->assertNotEmpty($result);

        $expected = [
            'baker' => [
                'children' => [
                    $attributeCode => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'fileUploader',
                                    'componentType' => 'fileUploader',
                                    'maxFileSize' => $maxFileSize,
                                    'allowedExtensions' => $allowedExtension,
                                    'uploaderConfig' => [
                                        'url' => 'baker/file/baker_upload',
                                    ],
                                    'sortOrder' => 'sort_order',
                                    'required' => 'is_required',
                                    'visible' => null,
                                    'validation' => [
                                        'max_file_size' => $maxFileSize,
                                        'file_extensions' => 'ext1, eXt2 ',
                                    ],
                                    'label' => __('frontend_label'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'address' => [
                'children' => [],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetDataWithVisibleAttributes()
    {

        $firstAttributesBundle = $this->getAttributeMock(
            'baker',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_edit'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1"
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1"
                ]
            ]
        );
        $secondAttributesBundle = $this->getAttributeMock(
            'baker',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_create'],
                    'user_defined' => false,
                    'specific_code_prefix' => "_2"
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_2"
                ]
            ]
        );

        $helper = new ObjectManager($this);
        /** @var \CND\Baker\Model\Baker\DataProvider $dataProvider */
        $dataProvider = $helper->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->getBakerCollectionFactoryMock(),
                'eavConfig' => $this->getEavConfigMock(array_merge($firstAttributesBundle, $secondAttributesBundle))
            ]
        );

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($this->getExpectationForVisibleAttributes(), $meta);
    }

    /**
     * @return void
     */
    public function testGetDataWithVisibleAttributesWithAccountEdit()
    {
        $firstAttributesBundle = $this->getAttributeMock(
            'baker',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_edit'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1"
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1"
                ]
            ]
        );
        $secondAttributesBundle = $this->getAttributeMock(
            'baker',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_create'],
                    'user_defined' => false,
                    'specific_code_prefix' => "_2"
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['baker_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_2"
                ]
            ]
        );

        $helper = new ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->setMethods(['getRequestParam'])
            ->getMockforAbstractClass();
        $context->expects($this->any())
            ->method('getRequestParam')
            ->with('request-field-name')
            ->willReturn(1);
        /** @var \CND\Baker\Model\Baker\DataProvider $dataProvider */
        $dataProvider = $helper->getObject(
            \CND\Baker\Model\Baker\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'bakerCollectionFactory' => $this->getBakerCollectionFactoryMock(),
                'context' => $context,
                'eavConfig' => $this->getEavConfigMock(array_merge($firstAttributesBundle, $secondAttributesBundle))
            ]
        );
        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($this->getExpectationForVisibleAttributes(false), $meta);
    }

    /**
     * Retrieve all baker variations of attributes with all variations of visibility
     *
     * @param bool $isRegistration
     * @return array
     */
    private function getBakerAttributeExpectations($isRegistration)
    {
        return [
            self::ATTRIBUTE_CODE . "_1" => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'options' => 'test-options',
                            'visible' => !$isRegistration,
                            'required' => 'is_required',
                            'label' => __('frontend_label'),
                            'sortOrder' => 'sort_order',
                            'notice' => 'note',
                            'default' => 'default_value',
                            'size' => 'multiline_count',
                            'componentType' => Field::NAME,
                        ],
                    ],
                ],
            ],
            self::ATTRIBUTE_CODE . "_2" => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'options' => 'test-options',
                            'visible' => true,
                            'required' => 'is_required',
                            'label' => __('frontend_label'),
                            'sortOrder' => 'sort_order',
                            'notice' => 'note',
                            'default' => 'default_value',
                            'size' => 'multiline_count',
                            'componentType' => Field::NAME,
                        ],
                    ],
                ],
            ],
            'test-code-boolean_1' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'visible' => $isRegistration,
                            'required' => 'is_required',
                            'label' => __('frontend_label'),
                            'sortOrder' => 'sort_order',
                            'notice' => 'note',
                            'default' => 'default_value',
                            'size' => 'multiline_count',
                            'componentType' => Field::NAME,
                            'prefer' => 'toggle',
                            'valueMap' => [
                                'true' => 1,
                                'false' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            'test-code-boolean_2' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'visible' => $isRegistration,
                            'required' => 'is_required',
                            'label' => __('frontend_label'),
                            'sortOrder' => 'sort_order',
                            'notice' => 'note',
                            'default' => 'default_value',
                            'size' => 'multiline_count',
                            'componentType' => Field::NAME,
                            'prefer' => 'toggle',
                            'valueMap' => [
                                'true' => 1,
                                'false' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve all variations of attributes with all variations of visibility
     *
     * @param bool $isRegistration
     * @return  array
     */
    private function getExpectationForVisibleAttributes($isRegistration = true)
    {
        return [
            'baker' => [
                'children' => $this->getBakerAttributeExpectations($isRegistration),
            ],
            'address' => [
                'children' => [
                    self::ATTRIBUTE_CODE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'frontend_input',
                                    'formElement' => 'frontend_input',
                                    'options' => 'test-options',
                                    'visible' => null,
                                    'required' => 'is_required',
                                    'label' => __('frontend_label'),
                                    'sortOrder' => 'sort_order',
                                    'notice' => 'note',
                                    'default' => 'default_value',
                                    'size' => 'multiline_count',
                                    'componentType' => Field::NAME,
                                ],
                            ],
                        ],
                    ],
                    'test-code-boolean' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'frontend_input',
                                    'formElement' => 'frontend_input',
                                    'visible' => null,
                                    'required' => 'is_required',
                                    'label' => 'frontend_label',
                                    'sortOrder' => 'sort_order',
                                    'notice' => 'note',
                                    'default' => 'default_value',
                                    'size' => 'multiline_count',
                                    'componentType' => Field::NAME,
                                    'prefer' => 'toggle',
                                    'valueMap' => [
                                        'true' => 1,
                                        'false' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'country_id' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'frontend_input',
                                    'formElement' => 'frontend_input',
                                    'options' => 'test-options',
                                    'visible' => null,
                                    'required' => 'is_required',
                                    'label' => __('frontend_label'),
                                    'sortOrder' => 'sort_order',
                                    'notice' => 'note',
                                    'default' => 'default_value',
                                    'size' => 'multiline_count',
                                    'componentType' => Field::NAME,
                                    'filterBy' => [
                                        'target' => '${ $.provider }:data.baker.website_id',
                                        'field' => 'website_ids'
                                    ]
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ];
    }
}
