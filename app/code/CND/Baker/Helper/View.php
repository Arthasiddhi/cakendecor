<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Helper;

use CND\Baker\Api\BakerNameGenerationInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\Data\BakerInterface;

/**
 * Baker helper for view.
 */
class View extends \Magento\Framework\App\Helper\AbstractHelper implements BakerNameGenerationInterface
{
    /**
     * @var BakerMetadataInterface
     */
    protected $_bakerMetadataService;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param BakerMetadataInterface $bakerMetadataService
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        BakerMetadataInterface $bakerMetadataService
    ) {
        $this->_bakerMetadataService = $bakerMetadataService;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getBakerName(BakerInterface $bakerData)
    {
        $name = '';
        $prefixMetadata = $this->_bakerMetadataService->getAttributeMetadata('prefix');
        if ($prefixMetadata->isVisible() && $bakerData->getPrefix()) {
            $name .= $bakerData->getPrefix() . ' ';
        }

        $name .= $bakerData->getFirstname();

        $middleNameMetadata = $this->_bakerMetadataService->getAttributeMetadata('middlename');
        if ($middleNameMetadata->isVisible() && $bakerData->getMiddlename()) {
            $name .= ' ' . $bakerData->getMiddlename();
        }

        $name .= ' ' . $bakerData->getLastname();

        $suffixMetadata = $this->_bakerMetadataService->getAttributeMetadata('suffix');
        if ($suffixMetadata->isVisible() && $bakerData->getSuffix()) {
            $name .= ' ' . $bakerData->getSuffix();
        }
        return $name;
    }
}
