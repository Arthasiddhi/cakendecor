<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Account;

use CND\Baker\Api\AccountManagementInterface;
use CND\Baker\Api\BakerRepositoryInterface;

/**
 * Baker dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Dashboard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $subscription;

    /**
     * @var \CND\Baker\Model\Session
     */
    protected $bakerSession;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $bakerAccountManagement;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \CND\Baker\Model\Session $bakerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param BakerRepositoryInterface $bakerRepository
     * @param AccountManagementInterface $bakerAccountManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CND\Baker\Model\Session $bakerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        BakerRepositoryInterface $bakerRepository,
        AccountManagementInterface $bakerAccountManagement,
        array $data = []
    ) {
        $this->bakerSession = $bakerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->bakerRepository = $bakerRepository;
        $this->bakerAccountManagement = $bakerAccountManagement;
        parent::__construct($context, $data);
    }

    /**
     * Return the Baker given the baker Id stored in the session.
     *
     * @return \CND\Baker\Api\Data\BakerInterface
     */
    public function getBaker()
    {
        return $this->bakerRepository->getById($this->bakerSession->getBakerId());
    }

    /**
     * Retrieve the Url for editing the baker's account.
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_urlBuilder->getUrl('baker/account/edit', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for baker addresses.
     *
     * @return string
     */
    public function getAddressesUrl()
    {
        return $this->_urlBuilder->getUrl('baker/address/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for editing the specified address.
     *
     * @param \CND\Baker\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressEditUrl($address)
    {
        return $this->_urlBuilder->getUrl(
            'baker/address/edit',
            ['_secure' => true, 'id' => $address->getId()]
        );
    }

    /**
     * Retrieve the Url for baker orders.
     *
     * @return string
     */
    public function getOrdersUrl()
    {
        return $this->_urlBuilder->getUrl('baker/order/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for baker reviews.
     *
     * @return string
     */
    public function getReviewsUrl()
    {
        return $this->_urlBuilder->getUrl('review/baker/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for managing baker wishlist.
     *
     * @return string
     */
    public function getWishlistUrl()
    {
        return $this->_urlBuilder->getUrl('baker/wishlist/index', ['_secure' => true]);
    }

    /**
     * Retrieve the subscription object (i.e. the subscriber).
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function getSubscriptionObject()
    {
        if ($this->subscription === null) {
            $this->subscription =
                $this->_createSubscriber()->loadByBakerId($this->bakerSession->getBakerId());
        }

        return $this->subscription;
    }

    /**
     * Retrieve the Url for managing newsletter subscriptions.
     *
     * @return string
     */
    public function getManageNewsletterUrl()
    {
        return $this->getUrl('newsletter/manage');
    }

    /**
     * Retrieve subscription text, either subscribed or not.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSubscriptionText()
    {
        if ($this->getSubscriptionObject()->isSubscribed()) {
            return __('You are subscribed to our newsletter.');
        }

        return __('You aren\'t subscribed to our newsletter.');
    }

    /**
     * Retrieve the baker's primary addresses (i.e. default billing and shipping).
     *
     * @return \CND\Baker\Api\Data\AddressInterface[]|bool
     */
    public function getPrimaryAddresses()
    {
        $addresses = [];
        $bakerId = $this->getBaker()->getId();

        if ($defaultBilling = $this->bakerAccountManagement->getDefaultBillingAddress($bakerId)) {
            $addresses[] = $defaultBilling;
        }

        if ($defaultShipping = $this->bakerAccountManagement->getDefaultShippingAddress($bakerId)) {
            if ($defaultBilling) {
                if ($defaultBilling->getId() != $defaultShipping->getId()) {
                    $addresses[] = $defaultShipping;
                }
            } else {
                $addresses[] = $defaultShipping;
            }
        }

        return empty($addresses) ? false : $addresses;
    }

    /**
     * Get back Url in account dashboard.
     *
     * This method is copy/pasted in:
     * \Magento\Wishlist\Block\Baker\Wishlist  - Because of strange inheritance
     * \CND\Baker\Block\Address\Book - Because of secure Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('baker/account/');
    }

    /**
     * Create an instance of a subscriber.
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->subscriberFactory->create();
    }
}
