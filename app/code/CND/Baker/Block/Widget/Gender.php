<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Widget;

use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Api\Data\OptionInterface;

/**
 * Block to render baker's gender attribute
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Gender extends AbstractWidget
{
    /**
     * @var \CND\Baker\Model\Session
     */
    protected $_bakerSession;

    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * Create an instance of the Gender widget
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \CND\Baker\Helper\Address $addressHelper
     * @param BakerMetadataInterface $bakerMetadata
     * @param BakerRepositoryInterface $bakerRepository
     * @param \CND\Baker\Model\Session $bakerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CND\Baker\Helper\Address $addressHelper,
        BakerMetadataInterface $bakerMetadata,
        BakerRepositoryInterface $bakerRepository,
        \CND\Baker\Model\Session $bakerSession,
        array $data = []
    ) {
        $this->_bakerSession = $bakerSession;
        $this->bakerRepository = $bakerRepository;
        parent::__construct($context, $addressHelper, $bakerMetadata, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/gender.phtml');
    }

    /**
     * Check if gender attribute enabled in system
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isVisible() : false;
    }

    /**
     * Check if gender attribute marked as required
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isRequired() : false;
    }

    /**
     * Get current baker from session
     *
     * @return BakerInterface
     */
    public function getBaker()
    {
        return $this->bakerRepository->getById($this->_bakerSession->getBakerId());
    }

    /**
     * Returns options from gender attribute
     * @return OptionInterface[]
     */
    public function getGenderOptions()
    {
        return $this->_getAttribute('gender')->getOptions();
    }
}
