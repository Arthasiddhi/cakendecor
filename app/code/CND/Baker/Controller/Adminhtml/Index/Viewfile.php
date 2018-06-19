<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\AddressRepositoryInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\AddressInterfaceFactory;
use CND\Baker\Api\Data\BakerInterfaceFactory;
use CND\Baker\Model\Address\Mapper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObjectFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Viewfile extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \CND\Baker\Model\BakerFactory $bakerFactory
     * @param \CND\Baker\Model\AddressFactory $addressFactory
     * @param \CND\Baker\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \CND\Baker\Helper\View $viewHelper
     * @param \Magento\Framework\Math\Random $random
     * @param BakerRepositoryInterface $bakerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $bakerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param BakerInterfaceFactory $bakerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param \CND\Baker\Model\Baker\Mapper $bakerMapper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \CND\Baker\Model\BakerFactory $bakerFactory,
        \CND\Baker\Model\AddressFactory $addressFactory,
        \CND\Baker\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \CND\Baker\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $random,
        BakerRepositoryInterface $bakerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $bakerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        BakerInterfaceFactory $bakerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \CND\Baker\Model\Baker\Mapper $bakerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        DataObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $bakerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $bakerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $bakerAccountManagement,
            $addressRepository,
            $bakerDataFactory,
            $addressDataFactory,
            $bakerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
        $this->resultRawFactory = $resultRawFactory;
        $this->urlDecoder  = $urlDecoder;
    }

    /**
     * Baker view file action
     *
     * @return \Magento\Framework\Controller\ResultInterface|void
     * @throws NotFoundException
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute()
    {
        list($file, $plain) = $this->getFileParams();

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = BakerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);
        if (mb_strpos($path, '..') !== false
            || (!$directory->isFile($fileName)
                && !$this->_objectManager->get(
                    \Magento\MediaStorage\Helper\File\Storage::class
                )->processStorageFile($path))
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        if ($plain) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }
            $stat = $directory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($fileName));
            return $resultRaw;
        } else {
            $name = pathinfo($path, PATHINFO_BASENAME);
            $this->_fileFactory->create(
                $name,
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }

    /**
     * Get parameters from request.
     *
     * @return array
     * @throws NotFoundException
     */
    private function getFileParams()
    {
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );

            return [$file, false];
        } elseif ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('image')
            );

            return [$file, true];
        } else {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
