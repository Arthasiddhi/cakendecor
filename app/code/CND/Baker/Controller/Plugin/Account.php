<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Plugin;

use CND\Baker\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\Controller\ResultInterface;

class Account
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var array
     */
    private $allowedActions = [];

    /**
     * @param Session $bakerSession
     * @param array $allowedActions List of actions that are allowed for not authorized users
     */
    public function __construct(
        Session $bakerSession,
        array $allowedActions = []
    ) {
        $this->session = $bakerSession;
        $this->allowedActions = $allowedActions;
    }

    /**
     * Dispatch actions allowed for not authorized users
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $action = strtolower($request->getActionName());
        $pattern = '/^(' . implode('|', $this->allowedActions) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->session->authenticate()) {
                $subject->getActionFlag()->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            }
        } else {
            $this->session->setNoReferer(true);
        }
    }

    /**
     * Remove No-referer flag from baker session
     *
     * @param AbstractAction $subject
     * @param ResponseInterface|ResultInterface $result
     * @param RequestInterface $request
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(AbstractAction $subject, $result, RequestInterface $request)
    {
        $this->session->unsNoReferer(false);
        return $result;
    }
}
