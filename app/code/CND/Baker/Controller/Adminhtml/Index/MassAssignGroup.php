<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use CND\Baker\Model\ResourceModel\Baker\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use CND\Baker\Api\BakerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassAssignGroup
 */
class MassAssignGroup extends AbstractMassAction
{
    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param BakerRepositoryInterface $bakerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BakerRepositoryInterface $bakerRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->bakerRepository = $bakerRepository;
    }

    /**
     * Baker mass assign group action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $bakersUpdated = 0;
        foreach ($collection->getAllIds() as $bakerId) {
            // Verify baker exists
            $baker = $this->bakerRepository->getById($bakerId);
            $baker->setGroupId($this->getRequest()->getParam('group'));
            $this->bakerRepository->save($baker);
            $bakersUpdated++;
        }

        if ($bakersUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $bakersUpdated));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
