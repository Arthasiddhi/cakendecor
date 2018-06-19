<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Account;

use CND\Baker\Api\BakerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use CND\Baker\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Edit extends \CND\Baker\Controller\AbstractAccount
{
    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $bakerSession
     * @param PageFactory $resultPageFactory
     * @param BakerRepositoryInterface $bakerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        Session $bakerSession,
        PageFactory $resultPageFactory,
        BakerRepositoryInterface $bakerRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->session = $bakerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->bakerRepository = $bakerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context);
    }

    /**
     * Forgot baker account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $block = $resultPage->getLayout()->getBlock('baker_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $data = $this->session->getBakerFormData(true);
        $bakerId = $this->session->getBakerId();
        $bakerDataObject = $this->bakerRepository->getById($bakerId);
        if (!empty($data)) {
            $this->dataObjectHelper->populateWithArray(
                $bakerDataObject,
                $data,
                \CND\Baker\Api\Data\BakerInterface::class
            );
        }
        $this->session->setBakerData($bakerDataObject);
        $this->session->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $resultPage->getConfig()->getTitle()->set(__('Account Information'));
        return $resultPage;
    }
}
