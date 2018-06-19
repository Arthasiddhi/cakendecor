<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class InvalidateTokenButton
 * @package CND\Baker\Block\Adminhtml\Edit
 */
class InvalidateTokenButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $bakerId = $this->getBakerId();
        $data = [];
        if ($bakerId) {
            $deleteConfirmMsg = __("Are you sure you want to revoke the baker's tokens?");
            $data = [
                'label' => __('Force Sign-In'),
                'class' => 'invalidate-token',
                'on_click' => 'deleteConfirm("' . $deleteConfirmMsg . '", "' . $this->getInvalidateTokenUrl() . '")',
                'sort_order' => 65,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getInvalidateTokenUrl()
    {
        return $this->getUrl('baker/baker/invalidateToken', ['baker_id' => $this->getBakerId()]);
    }
}
