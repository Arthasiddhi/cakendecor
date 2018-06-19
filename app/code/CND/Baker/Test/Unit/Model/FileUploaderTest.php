<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Model;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Model\FileProcessor;
use CND\Baker\Model\FileUploader;

class FileUploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BakerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerMetadataService;

    /**
     * @var \CND\Baker\Api\AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMetadataService;

    /**
     * @var \CND\Baker\Model\Metadata\ElementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementFactory;

    /**
     * @var \CND\Baker\Model\FileProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessorFactory;

    /**
     * @var \CND\Baker\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMetadata;

    protected function setUp()
    {
        $this->bakerMetadataService = $this->getMockBuilder(\CND\Baker\Api\BakerMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->addressMetadataService = $this->getMockBuilder(\CND\Baker\Api\AddressMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->elementFactory = $this->getMockBuilder(\CND\Baker\Model\Metadata\ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessorFactory = $this->getMockBuilder(\CND\Baker\Model\FileProcessorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->attributeMetadata = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();
    }

    protected function tearDown()
    {
        $_FILES = [];
    }

    /**
     * @param string $entityTypeCode
     * @param string $scope
     * @return FileUploader
     */
    private function getModel($entityTypeCode, $scope)
    {
        $model = new FileUploader(
            $this->bakerMetadataService,
            $this->addressMetadataService,
            $this->elementFactory,
            $this->fileProcessorFactory,
            $this->attributeMetadata,
            $entityTypeCode,
            $scope
        );
        return $model;
    }

    public function testValidate()
    {
        $attributeCode = 'attribute_code';

        $filename = 'filename.ext1';

        $_FILES = [
            'baker' => [
                'name' => [
                    $attributeCode => $filename,
                ],
            ],
        ];

        $formElement = $this->getMockBuilder(\CND\Baker\Model\Metadata\Form\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formElement->expects($this->once())
            ->method('validateValue')
            ->with(['name' => $filename])
            ->willReturn(true);

        $this->elementFactory->expects($this->once())
            ->method('create')
            ->with($this->attributeMetadata, null, BakerMetadataInterface::ENTITY_TYPE_CUSTOMER)
            ->willReturn($formElement);

        $model = $this->getModel(BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'baker');
        $this->assertTrue($model->validate());
    }

    public function testUpload()
    {
        $attributeCode = 'attribute_code';
        $attributeFrontendInput = 'image';

        $resultFileName = '/filename.ext1';
        $resultFilePath = 'filepath';
        $resultFileUrl = 'viewFileUrl';

        $allowedExtensions = 'ext1,EXT2 , eXt3';    // Added spaces, commas and upper-cases
        $expectedAllowedExtensions = [
            'ext1',
            'ext2',
            'ext3',
        ];

        $_FILES = [
            'baker' => [
                'name' => [
                    $attributeCode => 'filename',
                ],
            ],
        ];

        $expectedResult = [
            'name' => $resultFileName,
            'file' => $resultFileName,
            'path' => $resultFilePath,
            'tmp_name' => ltrim($resultFileName, '/'),
            'url' => $resultFileUrl,
        ];

        $fileProcessor = $this->getMockBuilder(\CND\Baker\Model\FileProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileProcessor->expects($this->once())
            ->method('saveTemporaryFile')
            ->with('baker[' . $attributeCode . ']')
            ->willReturn([
                'name' => $resultFileName,
                'path' => $resultFilePath,
                'file' => $resultFileName,
            ]);
        $fileProcessor->expects($this->once())
            ->method('getViewUrl')
            ->with(FileProcessor::TMP_DIR . '/filename.ext1', $attributeFrontendInput)
            ->willReturn($resultFileUrl);

        $this->fileProcessorFactory->expects($this->once())
            ->method('create')
            ->with([
                'entityTypeCode' => BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'allowedExtensions' => $expectedAllowedExtensions,
            ])
            ->willReturn($fileProcessor);

        $validationRuleMock = $this->getMockBuilder(\CND\Baker\Api\Data\ValidationRuleInterface::class)
            ->getMockForAbstractClass();
        $validationRuleMock->expects($this->once())
            ->method('getName')
            ->willReturn('file_extensions');
        $validationRuleMock->expects($this->once())
            ->method('getValue')
            ->willReturn($allowedExtensions);

        $this->attributeMetadata->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn($attributeFrontendInput);
        $this->attributeMetadata->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);

        $model = $this->getModel(BakerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'baker');
        $this->assertEquals($expectedResult, $model->upload());
    }
}
