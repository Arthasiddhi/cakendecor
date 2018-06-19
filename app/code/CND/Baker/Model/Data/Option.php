<?php
/**
 * Eav attribute option
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Model\Data;

/**
 * Class Option
 */
class Option extends \Magento\Framework\Api\AbstractSimpleObject implements
    \CND\Baker\Api\Data\OptionInterface
{
    /**
     * Get option label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_get(self::LABEL);
    }

    /**
     * Get option value
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Get nested options
     *
     * @return \CND\Baker\Api\Data\OptionInterface[]|null
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }

    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * Set option value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Set nested options
     *
     * @param \CND\Baker\Api\Data\OptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        return $this->setData(self::OPTIONS, $options);
    }
}
