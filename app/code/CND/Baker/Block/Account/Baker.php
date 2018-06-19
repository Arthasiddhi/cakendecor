<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Account;

use CND\Baker\Api\BakerRepositoryInterface;

/**
 * @api
 * @since 100.0.2
 */
class Baker extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var \CND\Baker\Helper\View
     */
    protected $_viewHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * Checking baker login status
     *
     * @return bool
     */
    public function bakerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\CND\Baker\Model\Context::CONTEXT_AUTH);
    }
}
