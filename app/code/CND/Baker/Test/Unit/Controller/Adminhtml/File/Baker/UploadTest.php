<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Test\Unit\Controller\Adminhtml\File\Baker;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Controller\Adminhtml\File\Baker\Upload;
use Magento\Framework\Controller\ResultFactory;

class UploadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Upload
     */
    private $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \CND\Baker\Model\FileUploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileUploaderFactory;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var BakerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bakerMetadataService;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->fileUploaderFactory = $this->getMockBuilder(\CND\Baker\Model\FileUploaderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->bakerMetadataService = $this->getMockBuilder(\CND\Baker\Api\BakerMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->controller = new Upload(
            $this->context,
            $this->fileUploaderFactory,
            $this->bakerMetadataService,
            $this->logger
        );
    }

    public function testExecuteEmptyFiles()
    {
        $this->markTestSkipped();
        $exception = new \Exception('$_FILES array is empty.');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturnSelf();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'error' => __('Something went wrong while saving file.'),
                'errorcode' => 0,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    public function testExecute()
    {
        $attributeCode = 'attribute_code';

        $_FILES = [
            'baker' => [
                'name' => [
                    $attributeCode => 'filename',
                ],
            ],
        ];

        $resultFileName = '/filename.ext1';
        $resultFilePath = 'filepath';
        $resultFileUrl = 'viewFileUrl';

        $result = [
            'name' => $resultFileName,
            'file' => $resultFileName,
            'path' => $resultFilePath,
            'tmp_name' => $resultFilePath . $resultFileName,
            'url' => $resultFileUrl,
        ];

        $attributeMetadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->bakerMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $fileUploader = $this->getMockBuilder(\CND\Baker\Model\FileUploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploader->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $fileUploader->expects($this->once())
            ->method('upload')
            ->willReturn($result);

        $this->fileUploaderFactory->expects($this->once())
            ->method('create')
            ->with([
                'attributeMetadata' => $attributeMetadataMock,
                'entityTypeCode' => BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'scope' => 'baker',
            ])
            ->willReturn($fileUploader);

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    public function testExecuteWithErrors()
    {
        $attributeCode = 'attribute_code';

        $_FILES = [
            'baker' => [
                'name' => [
                    $attributeCode => 'filename',
                ],
            ],
        ];

        $errors = [
            'error1',
            'error2',
        ];

        $attributeMetadataMock = $this->getMockBuilder(\CND\Baker\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->bakerMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $fileUploader = $this->getMockBuilder(\CND\Baker\Model\FileUploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploader->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->fileUploaderFactory->expects($this->once())
            ->method('create')
            ->with([
                'attributeMetadata' => $attributeMetadataMock,
                'entityTypeCode' => BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'scope' => 'baker',
            ])
            ->willReturn($fileUploader);

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'error' => implode('</br>', $errors),
                'errorcode' => 0,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }
}
