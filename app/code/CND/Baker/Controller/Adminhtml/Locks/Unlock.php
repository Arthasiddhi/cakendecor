<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Locks;

use CND\Baker\Model\AuthenticationInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Unlock Baker Controller
 */
class Unlock extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CND_Baker::manage';

    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * Unlock constructor.
     *
     * @param Action\Context $context
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        Action\Context $context,
        AuthenticationInterface $authentication
    ) {
        parent::__construct($context);
        $this->authentication = $authentication;
    }

    /**
     * Unlock specified baker
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $bakerId = $this->getRequest()->getParam('baker_id');
        try {
            // unlock baker
            if ($bakerId) {
                $this->authentication->unlock($bakerId);
                $this->getMessageManager()->addSuccess(__('Baker has been unlocked successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            'baker/index/edit',
            ['id' => $bakerId]
        );
    }
}
