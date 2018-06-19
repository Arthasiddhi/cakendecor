<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\BakerData;

use CND\Baker\Helper\Session\CurrentBaker;
use CND\Baker\Helper\View;

/**
 * Baker section
 */
class Baker implements SectionSourceInterface
{
    /**
     * @var CurrentBaker
     */
    protected $currentBaker;

    /**
     * @var View
     */
    private $bakerViewHelper;

    /**
     * @param CurrentBaker $currentBaker
     * @param View $bakerViewHelper
     */
    public function __construct(
        CurrentBaker $currentBaker,
        View $bakerViewHelper
    ) {
        $this->currentBaker = $currentBaker;
        $this->bakerViewHelper = $bakerViewHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentBaker->getBakerId()) {
            return [];
        }

        $baker = $this->currentBaker->getBaker();
        return [
            'fullname' => $this->bakerViewHelper->getBakerName($baker),
            'firstname' => $baker->getFirstname(),
            'websiteId' => $baker->getWebsiteId(),
        ];
    }
}
