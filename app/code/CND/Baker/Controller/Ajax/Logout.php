<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Controller\Ajax;

/**
 * Logout controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class Logout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Initialize Logout controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \CND\Baker\Model\Session $bakerSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \CND\Baker\Model\Session $bakerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->bakerSession = $bakerSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Baker logout action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $lastBakerId = $this->bakerSession->getId();
        $this->bakerSession->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastBakerId($lastBakerId);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['message' => 'Logout Successful']);
    }
}
