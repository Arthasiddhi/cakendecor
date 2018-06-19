<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Ui\Component\Listing\Column\Online;

use Magento\Ui\Component\Listing\Columns\Column;
use CND\Baker\Model\Visitor;

/**
 * Class Type
 */
class Type extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $item[$this->getData('name')] == Visitor::VISITOR_TYPE_VISITOR
                    ? __('Visitor')
                    : __('Baker');
            }
        }

        return $dataSource;
    }
}
