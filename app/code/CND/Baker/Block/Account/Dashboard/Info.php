<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Dashboard Baker Info
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Cached subscription object
     *
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscription;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \CND\Baker\Helper\View
     */
    protected $_helperView;

    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker
     */
    protected $currentBaker;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \CND\Baker\Helper\Session\CurrentBaker $currentBaker
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \CND\Baker\Helper\View $helperView
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CND\Baker\Helper\Session\CurrentBaker $currentBaker,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \CND\Baker\Helper\View $helperView,
        array $data = []
    ) {
        $this->currentBaker = $currentBaker;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_helperView = $helperView;
        parent::__construct($context, $data);
    }

    /**
     * Returns the Magento Baker Model for this block
     *
     * @return \CND\Baker\Api\Data\BakerInterface|null
     */
    public function getBaker()
    {
        try {
            return $this->currentBaker->getBaker();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get the full name of a baker
     *
     * @return string full name
     */
    public function getName()
    {
        return $this->_helperView->getBakerName($this->getBaker());
    }

    /**
     * @return string
     */
    public function getChangePasswordUrl()
    {
        return $this->_urlBuilder->getUrl('baker/account/edit/changepass/1');
    }

    /**
     * Get Baker Subscription Object Information
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function getSubscriptionObject()
    {
        if (!$this->_subscription) {
            $this->_subscription = $this->_createSubscriber();
            $baker = $this->getBaker();
            if ($baker) {
                $this->_subscription->loadByEmail($baker->getEmail());
            }
        }
        return $this->_subscription;
    }

    /**
     * Gets Baker subscription status
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return $this->getLayout()
            ->getBlockSingleton(\CND\Baker\Block\Form\Register::class)
            ->isNewsletterEnabled();
    }

    /**
     * @return \Magento\Newsletter\Model\Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->currentBaker->getBakerId() ? parent::_toHtml() : '';
    }
}
