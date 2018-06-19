<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Account;

use CND\Baker\Block\Account\SortLinkInterface;

/**
 * Class Link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * @var \CND\Baker\Model\Url
     */
    protected $_bakerUrl;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \CND\Baker\Model\Url $bakerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \CND\Baker\Model\Url $bakerUrl,
        array $data = []
    ) {
        $this->_bakerUrl = $bakerUrl;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_bakerUrl->getAccountUrl();
    }

    /**
     * {@inheritdoc}
     * @since 100.2.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
