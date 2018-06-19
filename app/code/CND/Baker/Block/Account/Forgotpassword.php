<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Account;

use CND\Baker\Model\Url;
use Magento\Framework\View\Element\Template;

/**
 * Baker account navigation sidebar
 *
 * @api
 * @since 100.0.2
 */
class Forgotpassword extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Url
     */
    protected $bakerUrl;

    /**
     * @param Template\Context $context
     * @param Url $bakerUrl
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Url $bakerUrl,
        array $data = []
    ) {
        $this->bakerUrl = $bakerUrl;
        parent::__construct($context, $data);
    }

    /**
     * Get login URL
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->bakerUrl->getLoginUrl();
    }
}
