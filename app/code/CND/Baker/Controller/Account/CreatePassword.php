<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Account;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class CreatePassword extends \CND\Baker\Controller\AbstractAccount
{
    /**
     * @var \CND\Baker\Api\AccountManagementInterface
     */
    protected $accountManagement;

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
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        Context $context,
        Session $bakerSession,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement
    ) {
        $this->session = $bakerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        parent::__construct($context);
    }

    /**
     * Resetting password handler
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $bakerId = (int)$this->getRequest()->getParam('id');
        $isDirectLink = $resetPasswordToken != '' && $bakerId != 0;
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->session->getRpToken();
            $bakerId = (int)$this->session->getRpBakerId();
        }

        try {
            $this->accountManagement->validateResetPasswordLinkToken($bakerId, $resetPasswordToken);

            if ($isDirectLink) {
                $this->session->setRpToken($resetPasswordToken);
                $this->session->setRpBakerId($bakerId);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');
                return $resultRedirect;
            } else {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()->getBlock('resetPassword')->setBakerId($bakerId)
                    ->setResetPasswordLinkToken($resetPasswordToken);
                return $resultPage;
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');
            return $resultRedirect;
        }
    }
}
