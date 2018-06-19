<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use CND\Baker\Model\BakerRegistry;

/**
 * Class UnlockButton
 */
class UnlockButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \CND\Baker\Model\BakerRegistry
     */
    protected $bakerRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \CND\Baker\Model\BakerRegistry $bakerRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        BakerRegistry $bakerRegistry
    ) {
        parent::__construct($context, $registry);
        $this->bakerRegistry = $bakerRegistry;
    }

    /**
     * Returns Unlock button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $bakerId = $this->getBakerId();
        $data = [];
        if ($bakerId) {
            $baker = $this->bakerRegistry->retrieve($bakerId);
            if ($baker->isBakerLocked()) {
                $data = [
                    'label' => __('Unlock'),
                    'class' => 'unlock unlock-baker',
                    'on_click' => sprintf("location.href = '%s';", $this->getUnlockUrl()),
                    'sort_order' => 50,
                ];
            }
        }
        return $data;
    }

    /**
     * Returns baker unlock action URL
     *
     * @return string
     */
    protected function getUnlockUrl()
    {
        return $this->getUrl('baker/locks/unlock', ['baker_id' => $this->getBakerId()]);
    }
}
