<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Account;

use CND\Baker\Model\Url;
use Magento\Framework\App\Action\Context;
use CND\Baker\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use CND\Baker\Api\AccountManagementInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;

class Confirmation extends \CND\Baker\Controller\AbstractAccount
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \CND\Baker\Api\AccountManagementInterface
     */
    protected $bakerAccountManagement;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Url
     */
    private $bakerUrl;

    /**
     * @param Context $context
     * @param Session $bakerSession
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $bakerAccountManagement
     * @param Url $bakerUrl
     */
    public function __construct(
        Context $context,
        Session $bakerSession,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $bakerAccountManagement,
        Url $bakerUrl = null
    ) {
        $this->session = $bakerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->bakerAccountManagement = $bakerAccountManagement;
        $this->bakerUrl = $bakerUrl ?: ObjectManager::getInstance()->get(Url::class);
        parent::__construct($context);
    }

    /**
     * Send confirmation link to specified email
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            try {
                $this->bakerAccountManagement->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccess(__('Please check your email for confirmation key.'));
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccess(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Wrong email.'));
                $resultRedirect->setPath('*/*/*', ['email' => $email, '_secure' => true]);
                return $resultRedirect;
            }
            $this->session->setUsername($email);
            $resultRedirect->setPath('*/*/index', ['_secure' => true]);
            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('accountConfirmation')->setEmail(
            $this->getRequest()->getParam('email', $email)
        )->setLoginUrl(
            $this->bakerUrl->getLoginUrl()
        );
        return $resultPage;
    }
}
