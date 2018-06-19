<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\BakerData;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Section pool
 *
 * @api
 * @since 100.0.2
 */
class SectionPool implements SectionPoolInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Section map. Key is section name, value is section source object class
     *
     * @var array
     */
    protected $sectionSourceMap;

    /**
     * @var \CND\Baker\BakerData\Section\Identifier
     */
    protected $identifier;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param \CND\Baker\BakerData\Section\Identifier $identifier
     * @param array $sectionSourceMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \CND\Baker\BakerData\Section\Identifier $identifier,
        array $sectionSourceMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->identifier = $identifier;
        $this->sectionSourceMap = $sectionSourceMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionsData(array $sectionNames = null, $updateIds = false)
    {
        $sectionsData = $sectionNames ? $this->getSectionDataByNames($sectionNames) : $this->getAllSectionData();
        $sectionsData = $this->identifier->markSections($sectionsData, $sectionNames, $updateIds);
        return $sectionsData;
    }

    /**
     * Get section sources by section names
     *
     * @param array $sectionNames
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSectionDataByNames($sectionNames)
    {
        $data = [];
        foreach ($sectionNames as $sectionName) {
            if (!isset($this->sectionSourceMap[$sectionName])) {
                throw new LocalizedException(__('"%1" section source is not supported', $sectionName));
            }
            $data[$sectionName] = $this->get($this->sectionSourceMap[$sectionName])->getSectionData();
        }
        return $data;
    }

    /**
     * Get all section sources
     *
     * @return array
     */
    protected function getAllSectionData()
    {
        $data = [];
        foreach ($this->sectionSourceMap as $sectionName => $sectionClass) {
            $data[$sectionName] = $this->get($sectionClass)->getSectionData();
        }
        return $data;
    }

    /**
     * Get section source by name
     *
     * @param string $name
     * @return SectionSourceInterface
     * @throws LocalizedException
     */
    protected function get($name)
    {
        $sectionSource = $this->objectManager->get($name);

        if (!$sectionSource instanceof SectionSourceInterface) {
            throw new LocalizedException(
                __('%1 doesn\'t extend \CND\Baker\BakerData\SectionSourceInterface', $name)
            );
        }
        return $sectionSource;
    }
}
