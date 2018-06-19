<?php
/**
 * Created by PhpStorm.
 * User: ravi
 * Date: 8/6/18
 * Time: 4:25 PM
 */

namespace CND\HomeModule\Block;


class Home extends \Magento\Directory\Block\Data
{
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Cakes N Decor'));
        return parent::_prepareLayout();
    }

    public function getFormData()
    {
        $data = $this->getData('form_data');

        return $data;
    }
}