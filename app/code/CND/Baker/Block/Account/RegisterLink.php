<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Account;

use CND\Baker\Model\Context;

/**
 * Baker register link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class RegisterLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Baker session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \CND\Baker\Model\Registration
     */
    protected $_registration;

    /**
     * @var \CND\Baker\Model\Url
     */
    protected $_bakerUrl;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \CND\Baker\Model\Registration $registration
     * @param \CND\Baker\Model\Url $bakerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \CND\Baker\Model\Registration $registration,
        \CND\Baker\Model\Url $bakerUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_registration = $registration;
        $this->_bakerUrl = $bakerUrl;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_bakerUrl->getRegisterUrl();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->_registration->isAllowed()
            || $this->httpContext->getValue(Context::CONTEXT_AUTH)
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
