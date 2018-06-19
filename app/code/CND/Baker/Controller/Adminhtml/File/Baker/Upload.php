<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\File\Baker;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Model\FileUploader;
use CND\Baker\Model\FileUploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CND_Baker::manage';

    /**
     * @var FileUploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var BakerMetadataInterface
     */
    private $bakerMetadataService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param FileUploaderFactory $fileUploaderFactory
     * @param BakerMetadataInterface $bakerMetadataService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $fileUploaderFactory,
        BakerMetadataInterface $bakerMetadataService,
        LoggerInterface $logger
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->bakerMetadataService = $bakerMetadataService;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            if (empty($_FILES)) {
                throw new \Exception('$_FILES array is empty.');
            }

            $attributeCode = key($_FILES['baker']['name']);
            $attributeMetadata = $this->bakerMetadataService->getAttributeMetadata($attributeCode);

            /** @var FileUploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create([
                'attributeMetadata' => $attributeMetadata,
                'entityTypeCode' => BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'scope' => 'baker',
            ]);

            $errors = $fileUploader->validate();
            if (true !== $errors) {
                $errorMessage = implode('</br>', $errors);
                throw new LocalizedException(__($errorMessage));
            }

            $result = $fileUploader->upload();
        } catch (LocalizedException $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => __('Something went wrong while saving file.'),
                'errorcode' => $e->getCode(),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
