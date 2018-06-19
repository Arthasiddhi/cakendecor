<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api;

/**
 * Interface for managing bakers accounts.
 * @api
 * @since 100.0.2
 */
interface AccountManagementInterface
{
    /**#@+
     * Constant for confirmation status
     */
    const ACCOUNT_CONFIRMED = 'account_confirmed';
    const ACCOUNT_CONFIRMATION_REQUIRED = 'account_confirmation_required';
    const ACCOUNT_CONFIRMATION_NOT_REQUIRED = 'account_confirmation_not_required';
    const MAX_PASSWORD_LENGTH = 256;
    /**#@-*/

    /**
     * Create baker account. Perform necessary business operations like sending email.
     *
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @param string $password
     * @param string $redirectUrl
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAccount(
        \CND\Baker\Api\Data\BakerInterface $baker,
        $password = null,
        $redirectUrl = ''
    );

    /**
     * Create baker account using provided hashed password. Should not be exposed as a webapi.
     *
     * @api
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @param string $hash Password hash that we can save directly
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the baker to a product they were looking at after pressing confirmation link.
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAccountWithPasswordHash(
        \CND\Baker\Api\Data\BakerInterface $baker,
        $hash,
        $redirectUrl = ''
    );

    /**
     * Validate baker data.
     *
     * @param \CND\Baker\Api\Data\BakerInterface $baker
     * @return \CND\Baker\Api\Data\ValidationResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\CND\Baker\Api\Data\BakerInterface $baker);

    /**
     * Check if baker can be deleted.
     *
     * @api
     * @param int $bakerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isReadonly($bakerId);

    /**
     * Activate a baker account using a key that was sent in a confirmation email.
     *
     * @param string $email
     * @param string $confirmationKey
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activate($email, $confirmationKey);

    /**
     * Activate a baker account using a key that was sent in a confirmation email.
     *
     * @api
     * @param int $bakerId
     * @param string $confirmationKey
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activateById($bakerId, $confirmationKey);

    /**
     * Authenticate a baker by username and password
     *
     * @param string $email
     * @param string $password
     * @return \CND\Baker\Api\Data\BakerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authenticate($email, $password);

    /**
     * Change baker password.
     *
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePassword($email, $currentPassword, $newPassword);

    /**
     * Change baker password.
     *
     * @param int $bakerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePasswordById($bakerId, $currentPassword, $newPassword);

    /**
     * Send an email to the baker with a password reset link.
     *
     * @param string $email
     * @param string $template
     * @param int $websiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initiatePasswordReset($email, $template, $websiteId = null);

    /**
     * Reset baker password.
     *
     * @param string $email
     * @param string $resetToken
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetPassword($email, $resetToken, $newPassword);

    /**
     * Check if password reset token is valid.
     *
     * @param int $bakerId
     * @param string $resetPasswordLinkToken
     * @return bool True if the token is valid
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or baker id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If baker doesn't exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateResetPasswordLinkToken($bakerId, $resetPasswordLinkToken);

    /**
     * Gets the account confirmation status.
     *
     * @param int $bakerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfirmationStatus($bakerId);

    /**
     * Resend confirmation email.
     *
     * @param string $email
     * @param int $websiteId
     * @param string $redirectUrl
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resendConfirmation($email, $websiteId, $redirectUrl = '');

    /**
     * Check if given email is associated with a baker account in given website.
     *
     * @param string $bakerEmail
     * @param int $websiteId If not set, will use the current websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEmailAvailable($bakerEmail, $websiteId = null);


    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password);
}
