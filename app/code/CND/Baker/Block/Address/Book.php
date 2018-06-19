<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Address;

use CND\Baker\Api\AddressRepositoryInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Model\Address\Mapper;

/**
 * Baker address book block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Book extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \CND\Baker\Helper\Session\CurrentBaker
     */
    protected $currentBaker;

    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \CND\Baker\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param BakerRepositoryInterface $bakerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \CND\Baker\Helper\Session\CurrentBaker $currentBaker
     * @param \CND\Baker\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        BakerRepositoryInterface $bakerRepository,
        AddressRepositoryInterface $addressRepository,
        \CND\Baker\Helper\Session\CurrentBaker $currentBaker,
        \CND\Baker\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = []
    ) {
        $this->bakerRepository = $bakerRepository;
        $this->currentBaker = $currentBaker;
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Address Book'));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl('baker/address/new', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('baker/account/', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('baker/address/delete');
    }

    /**
     * @param int $addressId
     * @return string
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->getUrl('baker/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    /**
     * @return bool
     */
    public function hasPrimaryAddress()
    {
        return $this->getDefaultBilling() || $this->getDefaultShipping();
    }

    /**
     * @return \CND\Baker\Api\Data\AddressInterface[]|bool
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->bakerRepository->getById($this->currentBaker->getBakerId())->getAddresses();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
            if (!in_array($address->getId(), $primaryAddressIds)) {
                $additional[] = $address;
            }
        }
        return empty($additional) ? false : $additional;
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \CND\Baker\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressHtml(\CND\Baker\Api\Data\AddressInterface $address = null)
    {
        if ($address !== null) {
            /** @var \CND\Baker\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($this->addressMapper->toFlatArray($address));
        }
        return '';
    }

    /**
     * @return \CND\Baker\Api\Data\BakerInterface|null
     */
    public function getBaker()
    {
        $baker = $this->getData('baker');
        if ($baker === null) {
            try {
                $baker = $this->bakerRepository->getById($this->currentBaker->getBakerId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
            $this->setData('baker', $baker);
        }
        return $baker;
    }

    /**
     * @return int|null
     */
    public function getDefaultBilling()
    {
        $baker = $this->getBaker();
        if ($baker === null) {
            return null;
        } else {
            return $baker->getDefaultBilling();
        }
    }

    /**
     * @param int $addressId
     * @return \CND\Baker\Api\Data\AddressInterface|null
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return int|null
     */
    public function getDefaultShipping()
    {
        $baker = $this->getBaker();
        if ($baker === null) {
            return null;
        } else {
            return $baker->getDefaultShipping();
        }
    }
}
