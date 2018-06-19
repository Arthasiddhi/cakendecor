<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

class ResetPassword extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Reset password handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $bakerId = (int)$this->getRequest()->getParam('baker_id', 0);
        if (!$bakerId) {
            $resultRedirect->setPath('baker/index');
            return $resultRedirect;
        }

        try {
            $baker = $this->_bakerRepository->getById($bakerId);
            $this->bakerAccountManagement->initiatePasswordReset(
                $baker->getEmail(),
                \CND\Baker\Model\AccountManagement::EMAIL_REMINDER,
                $baker->getWebsiteId()
            );
            $this->messageManager->addSuccess(__('The baker will receive an email with a link to reset password.'));
        } catch (NoSuchEntityException $exception) {
            $resultRedirect->setPath('baker/index');
            return $resultRedirect;
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $messages = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            if (!count($messages)) {
                $messages = $exception->getMessage();
            }
            $this->_addSessionErrorMessages($messages);
        } catch (SecurityViolationException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addException(
                $exception,
                __('Something went wrong while resetting baker password.')
            );
        }
        $resultRedirect->setPath(
            'baker/*/edit',
            ['id' => $bakerId, '_current' => true]
        );
        return $resultRedirect;
    }
}
