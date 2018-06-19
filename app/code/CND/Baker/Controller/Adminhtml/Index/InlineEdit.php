<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use CND\Baker\Model\EmailNotificationInterface;
use CND\Baker\Test\Block\Form\Login;
use CND\Baker\Ui\Component\Listing\AttributeRepository;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Api\BakerRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CND_Baker::manage';

    /**
     * @var \CND\Baker\Api\Data\BakerInterface
     */
    private $baker;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \CND\Baker\Model\Baker\Mapper
     */
    protected $bakerMapper;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \CND\Baker\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @param Action\Context $context
     * @param BakerRepositoryInterface $bakerRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \CND\Baker\Model\Baker\Mapper $bakerMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        BakerRepositoryInterface $bakerRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \CND\Baker\Model\Baker\Mapper $bakerMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->bakerRepository = $bakerRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->bakerMapper = $bakerMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $bakerId) {
            $this->setBaker($this->bakerRepository->getById($bakerId));
            $currentBaker = clone $this->getBaker();

            if ($this->getBaker()->getDefaultBilling()) {
                $this->updateDefaultBilling($this->getData($postItems[$bakerId]));
            }
            $this->updateBaker($this->getData($postItems[$bakerId], true));
            $this->saveBaker($this->getBaker());

            $this->getEmailNotification()->credentialsChanged($this->getBaker(), $currentBaker->getEmail());
        }

        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists()
        ]);
    }

    /**
     * Receive entity(baker|baker_address) data from request
     *
     * @param array $data
     * @param null $isBakerData
     * @return array
     */
    protected function getData(array $data, $isBakerData = null)
    {
        $addressKeys = preg_grep(
            '/^(' . AttributeRepository::BILLING_ADDRESS_PREFIX . '\w+)/',
            array_keys($data),
            $isBakerData
        );
        $result = array_intersect_key($data, array_flip($addressKeys));
        if ($isBakerData === null) {
            foreach ($result as $key => $value) {
                if (strpos($key, AttributeRepository::BILLING_ADDRESS_PREFIX) !== false) {
                    unset($result[$key]);
                    $result[str_replace(AttributeRepository::BILLING_ADDRESS_PREFIX, '', $key)] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Update baker data
     *
     * @param array $data
     * @return void
     */
    protected function updateBaker(array $data)
    {
        $baker = $this->getBaker();
        $bakerData = array_merge(
            $this->bakerMapper->toFlatArray($baker),
            $data
        );
        $this->dataObjectHelper->populateWithArray(
            $baker,
            $bakerData,
            \CND\Baker\Api\Data\BakerInterface::class
        );
    }

    /**
     * Update baker address data
     *
     * @param array $data
     * @return void
     */
    protected function updateDefaultBilling(array $data)
    {
        $addresses = $this->getBaker()->getAddresses();
        /** @var \CND\Baker\Api\Data\AddressInterface $address */
        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $this->dataObjectHelper->populateWithArray(
                    $address,
                    $this->processAddressData($data),
                    \CND\Baker\Api\Data\AddressInterface::class
                );
                break;
            }
        }
    }

    /**
     * Save baker with error catching
     *
     * @param BakerInterface $baker
     * @return void
     */
    protected function saveBaker(BakerInterface $baker)
    {
        try {
            $this->bakerRepository->save($baker);
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->getMessageManager()->addError($this->getErrorWithBakerId($e->getMessage()));
            $this->logger->critical($e);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getMessageManager()->addError($this->getErrorWithBakerId($e->getMessage()));
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->getMessageManager()->addError($this->getErrorWithBakerId('We can\'t save the baker.'));
            $this->logger->critical($e);
        }
    }

    /**
     * Parse street field
     *
     * @param array $data
     * @return array
     */
    protected function processAddressData(array $data)
    {
        foreach (['firstname', 'lastname'] as $requiredField) {
            if (empty($data[$requiredField])) {
                $data[$requiredField] =  $this->getBaker()->{'get' . ucfirst($requiredField)}();
            }
        }
        return $data;
    }

    /**
     * Get array with errors
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    /**
     * Check if errors exists
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCount();
    }

    /**
     * Set baker
     *
     * @param BakerInterface $baker
     * @return $this
     */
    protected function setBaker(BakerInterface $baker)
    {
        $this->baker = $baker;
        return $this;
    }

    /**
     * Receive baker
     *
     * @return BakerInterface
     */
    protected function getBaker()
    {
        return $this->baker;
    }

    /**
     * Add page title to error message
     *
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBakerId($errorText)
    {
        return '[Baker ID: ' . $this->getBaker()->getId() . '] ' . __($errorText);
    }
}
