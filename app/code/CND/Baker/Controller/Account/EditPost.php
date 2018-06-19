<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Account;

use CND\Baker\Model\AuthenticationInterface;
use CND\Baker\Model\Baker\Mapper;
use CND\Baker\Model\EmailNotificationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Model\BakerExtractor;
use CND\Baker\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends \CND\Baker\Controller\AbstractAccount
{
    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'baker_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $bakerAccountManagement;

    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var BakerExtractor
     */
    protected $bakerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \CND\Baker\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $bakerMapper;

    /**
     * @param Context $context
     * @param Session $bakerSession
     * @param AccountManagementInterface $bakerAccountManagement
     * @param BakerRepositoryInterface $bakerRepository
     * @param Validator $formKeyValidator
     * @param BakerExtractor $bakerExtractor
     */
    public function __construct(
        Context $context,
        Session $bakerSession,
        AccountManagementInterface $bakerAccountManagement,
        BakerRepositoryInterface $bakerRepository,
        Validator $formKeyValidator,
        BakerExtractor $bakerExtractor
    ) {
        parent::__construct($context);
        $this->session = $bakerSession;
        $this->bakerAccountManagement = $bakerAccountManagement;
        $this->bakerRepository = $bakerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->bakerExtractor = $bakerExtractor;
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(
                \CND\Baker\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Change baker email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentBakerDataObject = $this->getBakerDataObject($this->session->getBakerId());
            $bakerCandidateDataObject = $this->populateNewBakerDataObject(
                $this->_request,
                $currentBakerDataObject
            );

            try {
                // whether a baker enabled change email option
                $this->processChangeEmailRequest($currentBakerDataObject);

                // whether a baker enabled change password option
                $isPasswordChanged = $this->changeBakerPassword($currentBakerDataObject->getEmail());

                $this->bakerRepository->save($bakerCandidateDataObject);
                $this->getEmailNotification()->credentialsChanged(
                    $bakerCandidateDataObject,
                    $currentBakerDataObject->getEmail(),
                    $isPasswordChanged
                );
                $this->dispatchSuccessEvent($bakerCandidateDataObject);
                $this->messageManager->addSuccess(__('You saved the account information.'));
                return $resultRedirect->setPath('baker/account');
            } catch (InvalidEmailOrPasswordException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (UserLockedException $e) {
                $message = __(
                    'You did not sign in correctly or your account is temporarily disabled.'
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($message);
                return $resultRedirect->setPath('baker/account/login');
            } catch (InputException $e) {
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the baker.'));
            }

            $this->session->setBakerFormData($this->getRequest()->getPostValue());
        }

        return $resultRedirect->setPath('*/*/edit');
    }

    /**
     * Account editing action completed successfully event
     *
     * @param \CND\Baker\Api\Data\BakerInterface $bakerCandidateDataObject
     * @return void
     */
    private function dispatchSuccessEvent(\CND\Baker\Api\Data\BakerInterface $bakerCandidateDataObject)
    {
        $this->_eventManager->dispatch(
            'baker_account_edited',
            ['email' => $bakerCandidateDataObject->getEmail()]
        );
    }

    /**
     * Get baker data object
     *
     * @param int $bakerId
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    private function getBakerDataObject($bakerId)
    {
        return $this->bakerRepository->getById($bakerId);
    }

    /**
     * Create Data Transfer Object of baker candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \CND\Baker\Api\Data\BakerInterface $currentBakerData
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    private function populateNewBakerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \CND\Baker\Api\Data\BakerInterface $currentBakerData
    ) {
        $attributeValues = $this->getBakerMapper()->toFlatArray($currentBakerData);
        $bakerDto = $this->bakerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $bakerDto->setId($currentBakerData->getId());
        if (!$bakerDto->getAddresses()) {
            $bakerDto->setAddresses($currentBakerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $bakerDto->setEmail($currentBakerData->getEmail());
        }

        return $bakerDto;
    }

    /**
     * Change baker password
     *
     * @param string $email
     * @return boolean
     * @throws InvalidEmailOrPasswordException|InputException
     */
    protected function changeBakerPassword($email)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('password_confirmation');
            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $isPasswordChanged = $this->bakerAccountManagement->changePassword($email, $currPass, $newPass);
        }

        return $isPasswordChanged;
    }

    /**
     * Process change email request
     *
     * @param \CND\Baker\Api\Data\BakerInterface $currentBakerDataObject
     * @return void
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function processChangeEmailRequest(\CND\Baker\Api\Data\BakerInterface $currentBakerDataObject)
    {
        if ($this->getRequest()->getParam('change_email')) {
            // authenticate user for changing email
            try {
                $this->getAuthentication()->authenticate(
                    $currentBakerDataObject->getId(),
                    $this->getRequest()->getPost('current_password')
                );
            } catch (InvalidEmailOrPasswordException $e) {
                throw new InvalidEmailOrPasswordException(__('The password doesn\'t match this account.'));
            }
        }
    }

    /**
     * Get Baker Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getBakerMapper()
    {
        if ($this->bakerMapper === null) {
            $this->bakerMapper = ObjectManager::getInstance()->get(\CND\Baker\Model\Baker\Mapper::class);
        }
        return $this->bakerMapper;
    }
}
