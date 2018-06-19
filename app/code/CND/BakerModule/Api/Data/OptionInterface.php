<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Api\Data;

/**
 * Option interface.
 * @api
 * @since 100.0.2
 */
interface OptionInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const LABEL = 'label';
    const VALUE = 'value';
    const OPTIONS = 'options';
    /**#@-*/

    /**
     * Get option label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get option value
     *
     * @return string|null
     */
    public function getValue();

    /**
     * Set option value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get nested options
     *
     * @return \CND\Baker\Api\Data\OptionInterface[]|null
     */
    public function getOptions();

    /**
     * Set nested options
     *
     * @param \CND\Baker\Api\Data\OptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null);
}
