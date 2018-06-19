<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CND\Baker\Test\Unit\BakerData;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SectionConfigConverterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \CND\Baker\BakerData\SectionConfigConverter */
    protected $converter;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \DOMDocument */
    protected $source;

    protected function setUp()
    {
        $this->source = new \DOMDocument();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->converter = $this->objectManagerHelper->getObject(
            \CND\Baker\BakerData\SectionConfigConverter::class
        );
    }

    public function testConvert()
    {
        $this->source->loadXML(file_get_contents(__DIR__ . '/_files/sections.xml'));

        $this->assertEquals(
            [
                'sections' => [
                    'baker/account/logout' => '*',
                    'baker/account/editpost' => ['account'],
                ],
            ],
            $this->converter->convert($this->source)
        );
    }
}
