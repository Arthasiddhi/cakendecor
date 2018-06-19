<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use CND\Baker\Helper\View as BakerViewHelper;
use CND\Baker\Api\Data\BakerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotification implements EmailNotificationInterface
{
    /**#@+
     * Configuration paths for email templates and identities
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'baker/password/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'baker/password/reset_password_template';

    const XML_PATH_CHANGE_EMAIL_TEMPLATE = 'baker/account_information/change_email_template';

    const XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE =
        'baker/account_information/change_email_and_password_template';

    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'baker/password/forgot_email_template';

    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'baker/password/remind_email_template';

    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'baker/create_account/email_identity';

    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'baker/create_account/email_template';

    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'baker/create_account/email_no_password_template';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'baker/create_account/email_confirmation_template';

    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'baker/create_account/email_confirmed_template';

    /**
     * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
     *                                                  and password is not set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
     */
    const TEMPLATE_TYPES = [
        self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
    ];

    /**#@-*/

    /**#@-*/
    private $bakerRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var BakerViewHelper
     */
    protected $bakerViewHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @param BakerRegistry $bakerRegistry
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param BakerViewHelper $bakerViewHelper
     * @param DataObjectProcessor $dataProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param SenderResolverInterface|null $senderResolver
     */
    public function __construct(
        BakerRegistry $bakerRegistry,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        BakerViewHelper $bakerViewHelper,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        SenderResolverInterface $senderResolver = null
    ) {
        $this->bakerRegistry = $bakerRegistry;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->bakerViewHelper = $bakerViewHelper;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver ?: ObjectManager::getInstance()->get(SenderResolverInterface::class);
    }

    /**
     * Send notification to baker when email or/and password changed
     *
     * @param BakerInterface $savedBaker
     * @param string $origBakerEmail
     * @param bool $isPasswordChanged
     * @return void
     */
    public function credentialsChanged(
        BakerInterface $savedBaker,
        $origBakerEmail,
        $isPasswordChanged = false
    ) {
        if ($origBakerEmail != $savedBaker->getEmail()) {
            if ($isPasswordChanged) {
                $this->emailAndPasswordChanged($savedBaker, $origBakerEmail);
                $this->emailAndPasswordChanged($savedBaker, $savedBaker->getEmail());
                return;
            }

            $this->emailChanged($savedBaker, $origBakerEmail);
            $this->emailChanged($savedBaker, $savedBaker->getEmail());
            return;
        }

        if ($isPasswordChanged) {
            $this->passwordReset($savedBaker);
        }
    }

    /**
     * Send email to baker when his email and password is changed
     *
     * @param BakerInterface $baker
     * @param string $email
     * @return void
     */
    private function emailAndPasswordChanged(BakerInterface $baker, $email)
    {
        $storeId = $baker->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker);
        }

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            self::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId,
            $email
        );
    }

    /**
     * Send email to baker when his email is changed
     *
     * @param BakerInterface $baker
     * @param string $email
     * @return void
     */
    private function emailChanged(BakerInterface $baker, $email)
    {
        $storeId = $baker->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker);
        }

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            self::XML_PATH_CHANGE_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId,
            $email
        );
    }

    /**
     * Send email to baker when his password is reset
     *
     * @param BakerInterface $baker
     * @return void
     */
    private function passwordReset(BakerInterface $baker)
    {
        $storeId = $baker->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker);
        }

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            self::XML_PATH_RESET_PASSWORD_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send corresponding email template
     *
     * @param BakerInterface $baker
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    private function sendEmailTemplate(
        $baker,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, 'store', $storeId);
        if ($email === null) {
            $email = $baker->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, 'store', $storeId),
            $storeId
        );

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email, $this->bakerViewHelper->getBakerName($baker))
            ->getTransport();

        $transport->sendMessage();
    }

    /**
     * Create an object with data merged from Baker and BakerSecure
     *
     * @param BakerInterface $baker
     * @return \CND\Baker\Model\Data\BakerSecure
     */
    private function getFullBakerObject($baker)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedBakerData = $this->bakerRegistry->retrieveSecureData($baker->getId());
        $bakerData = $this->dataProcessor
            ->buildOutputDataArray($baker, \CND\Baker\Api\Data\BakerInterface::class);
        $mergedBakerData->addData($bakerData);
        $mergedBakerData->setData('name', $this->bakerViewHelper->getBakerName($baker));
        return $mergedBakerData;
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param BakerInterface $baker
     * @param int|string|null $defaultStoreId
     * @return int
     */
    private function getWebsiteStoreId($baker, $defaultStoreId = null)
    {
        if ($baker->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($baker->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Send email with new baker password
     *
     * @param BakerInterface $baker
     * @return void
     */
    public function passwordReminder(BakerInterface $baker)
    {
        $storeId = $this->getWebsiteStoreId($baker);
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param BakerInterface $baker
     * @return void
     */
    public function passwordResetConfirmation(BakerInterface $baker)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker);
        }

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send email with new account related information
     *
     * @param BakerInterface $baker
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    public function newAccount(
        BakerInterface $baker,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        $types = self::TEMPLATE_TYPES;

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($baker, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($baker->getStoreId());

        $bakerEmailData = $this->getFullBakerObject($baker);

        $this->sendEmailTemplate(
            $baker,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['baker' => $bakerEmailData, 'back_url' => $backUrl, 'store' => $store],
            $storeId
        );
    }
}
