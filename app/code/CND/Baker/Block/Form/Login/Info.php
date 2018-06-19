<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Form\Login;

/**
 * Baker login info block
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \CND\Baker\Model\Url
     */
    protected $_bakerUrl;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $coreUrl;

    /**
     * Registration
     *
     * @var \CND\Baker\Model\Registration
     */
    protected $registration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \CND\Baker\Model\Registration $registration
     * @param \CND\Baker\Model\Url $bakerUrl
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CND\Baker\Model\Registration $registration,
        \CND\Baker\Model\Url $bakerUrl,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registration = $registration;
        $this->_bakerUrl = $bakerUrl;
        $this->checkoutData = $checkoutData;
        $this->coreUrl = $coreUrl;
    }

    /**
     * Return registration
     *
     * @return \CND\Baker\Model\Registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }


    /**
     * Retrieve create new account url
     *
     * @return string
     */
    public function getCreateAccountUrl()
    {
        $url = $this->getData('create_account_url');
        if ($url === null) {
            $url = $this->_bakerUrl->getRegisterUrl();
        }
        if ($this->checkoutData->isContextCheckout()) {
            $url = $this->coreUrl->addRequestParam($url, ['context' => 'checkout']);
        }
        return $url;
    }
}
