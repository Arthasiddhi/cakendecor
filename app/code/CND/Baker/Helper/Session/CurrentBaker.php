<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Helper\Session;

use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\BakerInterfaceFactory;
use CND\Baker\Model\Session as BakerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\LayoutInterface;

/**
 * Class CurrentBaker
 */
class CurrentBaker
{
    /**
     * @var \CND\Baker\Model\Session
     */
    protected $bakerSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \CND\Baker\Api\Data\BakerInterfaceFactory
     */
    protected $bakerFactory;

    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @param BakerSession $bakerSession
     * @param LayoutInterface $layout
     * @param BakerInterfaceFactory $bakerFactory
     * @param BakerRepositoryInterface $bakerRepository
     * @param RequestInterface $request
     * @param ModuleManager $moduleManager
     * @param ViewInterface $view
     */
    public function __construct(
        BakerSession $bakerSession,
        LayoutInterface $layout,
        BakerInterfaceFactory $bakerFactory,
        BakerRepositoryInterface $bakerRepository,
        RequestInterface $request,
        ModuleManager $moduleManager,
        ViewInterface $view
    ) {
        $this->bakerSession = $bakerSession;
        $this->layout = $layout;
        $this->bakerFactory = $bakerFactory;
        $this->bakerRepository = $bakerRepository;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->view = $view;
    }

    /**
     * Returns baker Data with baker group only
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    protected function getDepersonalizedBaker()
    {
        $baker = $this->bakerFactory->create();
        $baker->setGroupId($this->bakerSession->getBakerGroupId());
        return $baker;
    }

    /**
     * Returns baker Data from service
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    protected function getBakerFromService()
    {
        return $this->bakerRepository->getById($this->bakerSession->getId());
    }

    /**
     * Returns current baker according to session and context
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    public function getBaker()
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && !$this->request->isAjax()
            && $this->view->isLayoutLoaded()
            && $this->layout->isCacheable()
        ) {
            return $this->getDepersonalizedBaker();
        } else {
            return $this->getBakerFromService();
        }
    }

    /**
     * Returns baker id from session
     *
     * @return int|null
     */
    public function getBakerId()
    {
        return $this->bakerSession->getId();
    }

    /**
     * Set baker id
     *
     * @param int|null $bakerId
     * @return void
     */
    public function setBakerId($bakerId)
    {
        $this->bakerSession->setId($bakerId);
    }
}
