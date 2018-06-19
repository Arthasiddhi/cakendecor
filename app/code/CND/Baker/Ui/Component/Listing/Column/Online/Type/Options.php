<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Ui\Component\Listing\Column\Online\Type;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => \CND\Baker\Model\Visitor::VISITOR_TYPE_VISITOR,
                    'label' => __('Visitor'),
                ],
                [
                    'value' => \CND\Baker\Model\Visitor::VISITOR_TYPE_CUSTOMER,
                    'label' => __('Baker'),
                ]
            ];
        }
        return $this->options;
    }
}
