<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Account\Dashboard;

use CND\Baker\Api\Data\AddressInterface;
use CND\Baker\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to manage baker dashboard addresses section
 *
 * @api
 * @since 100.0.2
 */
class Address extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \CND\Baker\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker
     */
    protected $currentBaker;

    /**
     * @var \CND\Baker\Helper\Session\CurrentBakerAddress
     */
    protected $currentBakerAddress;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \CND\Baker\Helper\Session\CurrentBaker $currentBaker
     * @param \CND\Baker\Helper\Session\CurrentBakerAddress $currentBakerAddress
     * @param \CND\Baker\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CND\Baker\Helper\Session\CurrentBaker $currentBaker,
        \CND\Baker\Helper\Session\CurrentBakerAddress $currentBakerAddress,
        \CND\Baker\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = []
    ) {
        $this->currentBaker = $currentBaker;
        $this->currentBakerAddress = $currentBakerAddress;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
        $this->addressMapper = $addressMapper;
    }

    /**
     * Get the logged in baker
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
     * HTML for Shipping Address
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getPrimaryShippingAddressHtml()
    {
        try {
            $address = $this->currentBakerAddress->getDefaultShippingAddress();
        } catch (NoSuchEntityException $e) {
            return __('You have not set a default shipping address.');
        }

        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return __('You have not set a default shipping address.');
        }
    }

    /**
     * HTML for Billing Address
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getPrimaryBillingAddressHtml()
    {
        try {
            $address = $this->currentBakerAddress->getDefaultBillingAddress();
        } catch (NoSuchEntityException $e) {
            return __('You have not set a default billing address.');
        }

        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return __('You have not set a default billing address.');
        }
    }

    /**
     * @return string
     */
    public function getPrimaryShippingAddressEditUrl()
    {
        if (!$this->getBaker()) {
            return '';
        } else {
            $address = $this->currentBakerAddress->getDefaultShippingAddress();
            $addressId = $address ? $address->getId() : null;
            return $this->_urlBuilder->getUrl(
                'baker/address/edit',
                ['id' => $addressId]
            );
        }
    }

    /**
     * @return string
     */
    public function getPrimaryBillingAddressEditUrl()
    {
        if (!$this->getBaker()) {
            return '';
        } else {
            $address = $this->currentBakerAddress->getDefaultBillingAddress();
            $addressId = $address ? $address->getId() : null;
            return $this->_urlBuilder->getUrl(
                'baker/address/edit',
                ['id' => $addressId]
            );
        }
    }

    /**
     * @return string
     */
    public function getAddressBookUrl()
    {
        return $this->getUrl('baker/address/');
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param AddressInterface $address
     * @return string
     */
    protected function _getAddressHtml($address)
    {
        /** @var \CND\Baker\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }
}
