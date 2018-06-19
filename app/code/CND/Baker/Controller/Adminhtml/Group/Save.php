<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Group;

use CND\Baker\Api\Data\GroupInterfaceFactory;
use CND\Baker\Api\Data\GroupInterface;
use CND\Baker\Api\GroupRepositoryInterface;

class Save extends \CND\Baker\Controller\Adminhtml\Group
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupDataFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupDataFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        parent::__construct(
            $context,
            $coreRegistry,
            $groupRepository,
            $groupDataFactory,
            $resultForwardFactory,
            $resultPageFactory
        );
    }

    /**
     * Store Baker Group Data to session
     *
     * @param array $bakerGroupData
     * @return void
     */
    protected function storeBakerGroupDataToSession($bakerGroupData)
    {
        if (array_key_exists('code', $bakerGroupData)) {
            $bakerGroupData['baker_group_code'] = $bakerGroupData['code'];
            unset($bakerGroupData['code']);
        }
        $this->_getSession()->setBakerGroupData($bakerGroupData);
    }

    /**
     * Create or save baker group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $taxClass = (int)$this->getRequest()->getParam('tax_class');

        /** @var \CND\Baker\Api\Data\GroupInterface $bakerGroup */
        $bakerGroup = null;
        if ($taxClass) {
            $id = $this->getRequest()->getParam('id');
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $bakerGroupCode = (string)$this->getRequest()->getParam('code');
                if ($id !== null) {
                    $bakerGroup = $this->groupRepository->getById((int)$id);
                    $bakerGroupCode = $bakerGroupCode ?: $bakerGroup->getCode();
                } else {
                    $bakerGroup = $this->groupDataFactory->create();
                }
                $bakerGroup->setCode(!empty($bakerGroupCode) ? $bakerGroupCode : null);
                $bakerGroup->setTaxClassId($taxClass);

                $this->groupRepository->save($bakerGroup);

                $this->messageManager->addSuccess(__('You saved the baker group.'));
                $resultRedirect->setPath('baker/group');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                if ($bakerGroup != null) {
                    $this->storeBakerGroupDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $bakerGroup,
                            \CND\Baker\Api\Data\GroupInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('baker/group/edit', ['id' => $id]);
            }
            return $resultRedirect;
        } else {
            return $this->resultForwardFactory->create()->forward('new');
        }
    }
}
