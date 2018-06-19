<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Plugin;

use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Model\Baker\NotificationStorage;
use CND\Baker\Model\Session;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class BakerNotification
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var BakerRepositoryInterface
     */
    private $bakerRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param BakerRepositoryInterface $bakerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        BakerRepositoryInterface $bakerRepository,
        LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->bakerRepository = $bakerRepository;
        $this->logger = $logger;
    }

    /**
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $bakerId = $this->session->getBakerId();

        if ($this->state->getAreaCode() == Area::AREA_FRONTEND && $request->isPost()
            && $this->notificationStorage->isExists(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                $bakerId
            )
        ) {
            try {
                $baker = $this->bakerRepository->getById($bakerId);
                $this->session->setBakerData($baker);
                $this->session->setBakerGroupId($baker->getGroupId());
                $this->session->regenerateId();
                $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $bakerId);
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e);
            }
        }
    }
}
