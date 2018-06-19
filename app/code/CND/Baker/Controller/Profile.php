<?php
/**
 * Created by PhpStorm.
 * User: Vivek Ramasha
 * Date: 15-06-2018
 * Time: 01:31 PM
 */

namespace CND\BakerModule\Controller\Account;
use Magento\Framework\Controller\ResultFactory;


class Profile extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}