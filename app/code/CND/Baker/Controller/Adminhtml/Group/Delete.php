<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Group;

use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \CND\Baker\Controller\Adminhtml\Group
{
    /**
     * Delete baker group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->groupRepository->deleteById($id);
                $this->messageManager->addSuccess(__('You deleted the baker group.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('The baker group no longer exists.'));
                return $resultRedirect->setPath('baker/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('baker/group/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('baker/group');
    }
}
