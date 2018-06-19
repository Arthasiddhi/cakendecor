<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Account;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use CND\Baker\Model\Baker\CredentialsValidator;
use Magento\Framework\App\ObjectManager;

class ResetPasswordPost extends \CND\Baker\Controller\AbstractAccount
{
    /**
     * @var \CND\Baker\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @param Context $context
     * @param Session $bakerSession
     * @param AccountManagementInterface $accountManagement
     * @param BakerRepositoryInterface $bakerRepository
     * @param CredentialsValidator|null $credentialsValidator
     */
    public function __construct(
        Context $context,
        Session $bakerSession,
        AccountManagementInterface $accountManagement,
        BakerRepositoryInterface $bakerRepository,
        CredentialsValidator $credentialsValidator = null
    ) {
        $this->session = $bakerSession;
        $this->accountManagement = $accountManagement;
        $this->bakerRepository = $bakerRepository;
        $this->credentialsValidator = $credentialsValidator ?: ObjectManager::getInstance()
            ->get(CredentialsValidator::class);
        parent::__construct($context);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $bakerId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');

        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            $resultRedirect->setPath('*/*/createPassword', ['id' => $bakerId, 'token' => $resetPasswordToken]);
            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('Please enter a new password.'));
            $resultRedirect->setPath('*/*/createPassword', ['id' => $bakerId, 'token' => $resetPasswordToken]);
            return $resultRedirect;
        }

        try {
            $bakerEmail = $this->bakerRepository->getById($bakerId)->getEmail();
            $this->credentialsValidator->checkPasswordDifferentFromEmail($bakerEmail, $password);
            $this->accountManagement->resetPassword($bakerEmail, $resetPasswordToken, $password);
            $this->session->unsRpToken();
            $this->session->unsRpBakerId();
            $this->messageManager->addSuccess(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');
            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Something went wrong while saving the new password.'));
        }
        $resultRedirect->setPath('*/*/createPassword', ['id' => $bakerId, 'token' => $resetPasswordToken]);
        return $resultRedirect;
    }
}
