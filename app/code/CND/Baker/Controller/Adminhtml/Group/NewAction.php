<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Group;

use CND\Baker\Controller\RegistryConstants;

class NewAction extends \CND\Baker\Controller\Adminhtml\Group
{
    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initGroup()
    {
        $groupId = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_GROUP_ID, $groupId);

        return $groupId;
    }

    /**
     * Edit or create baker group.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $groupId = $this->_initGroup();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CND_Baker::baker_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Baker Groups'));
        $resultPage->addBreadcrumb(__('Bakers'), __('Bakers'));
        $resultPage->addBreadcrumb(__('Baker Groups'), __('Baker Groups'), $this->getUrl('baker/group'));

        if ($groupId === null) {
            $resultPage->addBreadcrumb(__('New Group'), __('New Baker Groups'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Baker Group'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Group'), __('Edit Baker Groups'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->groupRepository->getById($groupId)->getCode()
            );
        }

        $resultPage->getLayout()->addBlock(\CND\Baker\Block\Adminhtml\Group\Edit::class, 'group', 'content')
            ->setEditMode((bool)$groupId);

        return $resultPage;
    }
}
