<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\Data\BakerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * @since 100.1.0
 */
interface EmailNotificationInterface
{
    /**
     * Constants for the type of new account email to be sent
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Send notification to baker when email and/or password changed
     *
     * @param BakerInterface $savedBaker
     * @param string $origBakerEmail
     * @param bool $isPasswordChanged
     * @return void
     * @since 100.1.0
     */
    public function credentialsChanged(
        BakerInterface $savedBaker,
        $origBakerEmail,
        $isPasswordChanged = false
    );

    /**
     * Send email with new baker password
     *
     * @param BakerInterface $baker
     * @return void
     * @since 100.1.0
     */
    public function passwordReminder(BakerInterface $baker);

    /**
     * Send email with reset password confirmation link
     *
     * @param BakerInterface $baker
     * @return void
     * @since 100.1.0
     */
    public function passwordResetConfirmation(BakerInterface $baker);

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
     * @since 100.1.0
     */
    public function newAccount(
        BakerInterface $baker,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    );
}
