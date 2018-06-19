<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use CND\Baker\Api\BakerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Ui\Component\MassAction\Filter;
use CND\Baker\Model\ResourceModel\Baker\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class MassUnsubscribe
 */
class MassUnsubscribe extends AbstractMassAction
{
    /**
     * @var BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param BakerRepositoryInterface $bakerRepository
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BakerRepositoryInterface $bakerRepository,
        SubscriberFactory $subscriberFactory
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->bakerRepository = $bakerRepository;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Baker mass unsubscribe action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $bakersUpdated = 0;
        foreach ($collection->getAllIds() as $bakerId) {
            // Verify baker exists
            $this->bakerRepository->getById($bakerId);
            $this->subscriberFactory->create()->unsubscribeBakerById($bakerId);
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
