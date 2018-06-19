<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use CND\Baker\Api\AddressMetadataInterface;
use CND\Baker\Api\BakerMetadataInterface;
use CND\Baker\Api\Data\BakerInterface;
use CND\Baker\Controller\RegistryConstants;
use CND\Baker\Model\EmailNotificationInterface;
use CND\Baker\Model\Metadata\Form;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * Reformat baker account data to be compatible with baker service interface
     *
     * @return array
     */
    protected function _extractBakerData()
    {
        $bakerData = [];
        if ($this->getRequest()->getPost('baker')) {
            $additionalAttributes = [
                BakerInterface::DEFAULT_BILLING,
                BakerInterface::DEFAULT_SHIPPING,
                'confirmation',
                'sendemail_store_id',
                'extension_attributes',
            ];

            $bakerData = $this->_extractData(
                'adminhtml_baker',
                BakerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $additionalAttributes,
                'baker'
            );
        }

        if (isset($bakerData['disable_auto_group_change'])) {
            $bakerData['disable_auto_group_change'] = (int) filter_var(
                $bakerData['disable_auto_group_change'],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return $bakerData;
    }

    /**
     * Perform baker data filtration based on form code and form object
     *
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param string $entityType entity type for the form
     * @param string[] $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @return array
     */
    protected function _extractData(
        $formCode,
        $entityType,
        $additionalAttributes = [],
        $scope = null
    ) {
        $metadataForm = $this->getMetadataForm($entityType, $formCode, $scope);
        $formData = $metadataForm->extractData($this->getRequest(), $scope);
        $formData = $metadataForm->compactData($formData);

        // Initialize additional attributes
        /** @var \Magento\Framework\DataObject $object */
        $object = $this->_objectFactory->create(['data' => $this->getRequest()->getPostValue()]);
        $requestData = $object->getData($scope);
        foreach ($additionalAttributes as $attributeCode) {
            $formData[$attributeCode] = isset($requestData[$attributeCode]) ? $requestData[$attributeCode] : false;
        }

        // Unset unused attributes
        $formAttributes = $metadataForm->getAttributes();
        foreach ($formAttributes as $attribute) {
            /** @var \CND\Baker\Api\Data\AttributeMetadataInterface $attribute */
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getFrontendInput() != 'boolean'
                && $formData[$attributeCode] === false
            ) {
                unset($formData[$attributeCode]);
            }
        }

        if (empty($formData['extension_attributes'])) {
            unset($formData['extension_attributes']);
        }

        return $formData;
    }

    /**
     * Saves default_billing and default_shipping flags for baker address
     *
     * @param array $addressIdList
     * @param array $extractedBakerData
     * @return array
     */
    protected function saveDefaultFlags(array $addressIdList, array & $extractedBakerData)
    {
        $result = [];
        $extractedBakerData[BakerInterface::DEFAULT_BILLING] = null;
        $extractedBakerData[BakerInterface::DEFAULT_SHIPPING] = null;
        foreach ($addressIdList as $addressId) {
            $scope = sprintf('address/%s', $addressId);
            $addressData = $this->_extractData(
                'adminhtml_baker_address',
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                ['default_billing', 'default_shipping'],
                $scope
            );

            if (is_numeric($addressId)) {
                $addressData['id'] = $addressId;
            }
            // Set default billing and shipping flags to baker
            if (!empty($addressData['default_billing']) && $addressData['default_billing'] === 'true') {
                $extractedBakerData[BakerInterface::DEFAULT_BILLING] = $addressId;
                $addressData['default_billing'] = true;
            } else {
                $addressData['default_billing'] = false;
            }
            if (!empty($addressData['default_shipping']) && $addressData['default_shipping'] === 'true') {
                $extractedBakerData[BakerInterface::DEFAULT_SHIPPING] = $addressId;
                $addressData['default_shipping'] = true;
            } else {
                $addressData['default_shipping'] = false;
            }
            $result[] = $addressData;
        }
        return $result;
    }

    /**
     * Reformat baker addresses data to be compatible with baker service interface
     *
     * @param array $extractedBakerData
     * @return array
     */
    protected function _extractBakerAddressData(array & $extractedBakerData)
    {
        $addresses = $this->getRequest()->getPost('address');
        $result = [];
        if (is_array($addresses)) {
            if (isset($addresses['_template_'])) {
                unset($addresses['_template_']);
            }

            $addressIdList = array_keys($addresses);
            $result = $this->saveDefaultFlags($addressIdList, $extractedBakerData);
        }

        return $result;
    }

    /**
     * Save baker action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();

        $bakerId = $this->getCurrentBakerId();

        if ($originalRequestData) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $bakerData = $this->_extractBakerData();
                $addressesData = $this->_extractBakerAddressData($bakerData);

                if ($bakerId) {
                    $currentBaker = $this->_bakerRepository->getById($bakerId);
                    $bakerData = array_merge(
                        $this->bakerMapper->toFlatArray($currentBaker),
                        $bakerData
                    );
                    $bakerData['id'] = $bakerId;
                }

                /** @var BakerInterface $baker */
                $baker = $this->bakerDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $baker,
                    $bakerData,
                    \CND\Baker\Api\Data\BakerInterface::class
                );
                $addresses = [];
                foreach ($addressesData as $addressData) {
                    $region = isset($addressData['region']) ? $addressData['region'] : null;
                    $regionId = isset($addressData['region_id']) ? $addressData['region_id'] : null;
                    $addressData['region'] = [
                        'region' => $region,
                        'region_id' => $regionId,
                    ];
                    $addressDataObject = $this->addressDataFactory->create();
                    $this->dataObjectHelper->populateWithArray(
                        $addressDataObject,
                        $addressData,
                        \CND\Baker\Api\Data\AddressInterface::class
                    );
                    $addresses[] = $addressDataObject;
                }

                $this->_eventManager->dispatch(
                    'adminhtml_baker_prepare_save',
                    ['baker' => $baker, 'request' => $this->getRequest()]
                );
                $baker->setAddresses($addresses);
                if (isset($bakerData['sendemail_store_id'])) {
                    $baker->setStoreId($bakerData['sendemail_store_id']);
                }

                // Save baker
                if ($bakerId) {
                    $this->_bakerRepository->save($baker);

                    $this->getEmailNotification()->credentialsChanged($baker, $currentBaker->getEmail());
                } else {
                    $baker = $this->bakerAccountManagement->createAccount($baker);
                    $bakerId = $baker->getId();
                }

                $isSubscribed = null;
                if ($this->_authorization->isAllowed(null)) {
                    $isSubscribed = $this->getRequest()->getPost('subscription');
                }
                if ($isSubscribed !== null) {
                    if ($isSubscribed !== '0') {
                        $this->_subscriberFactory->create()->subscribeBakerById($bakerId);
                    } else {
                        $this->_subscriberFactory->create()->unsubscribeBakerById($bakerId);
                    }
                }

                // After save
                $this->_eventManager->dispatch(
                    'adminhtml_baker_save_after',
                    ['baker' => $baker, 'request' => $this->getRequest()]
                );
                $this->_getSession()->unsBakerFormData();
                // Done Saving baker, finish save action
                $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $bakerId);
                $this->messageManager->addSuccess(__('You saved the baker.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setBakerFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Magento\Framework\Exception\AbstractAggregateException $exception) {
                $errors = $exception->getErrors();
                $messages = [];
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setBakerFormData($originalRequestData);
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setBakerFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('Something went wrong while saving the baker.'));
                $this->_getSession()->setBakerFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($bakerId) {
                $resultRedirect->setPath(
                    'baker/*/edit',
                    ['id' => $bakerId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'baker/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('baker/index');
        }
        return $resultRedirect;
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
     * Get metadata form
     *
     * @param string $entityType
     * @param string $formCode
     * @param string $scope
     * @return Form
     */
    private function getMetadataForm($entityType, $formCode, $scope)
    {
        $attributeValues = [];

        if ($entityType == BakerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $bakerId = $this->getCurrentBakerId();
            if ($bakerId) {
                $baker = $this->_bakerRepository->getById($bakerId);
                $attributeValues = $this->bakerMapper->toFlatArray($baker);
            }
        }

        if ($entityType == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $scopeData = explode('/', $scope);
            if (isset($scopeData[1]) && is_numeric($scopeData[1])) {
                $bakerAddress = $this->addressRepository->getById($scopeData[1]);
                $attributeValues = $this->addressMapper->toFlatArray($bakerAddress);
            }
        }

        $metadataForm = $this->_formFactory->create(
            $entityType,
            $formCode,
            $attributeValues,
            false,
            Form::DONT_IGNORE_INVISIBLE
        );

        return $metadataForm;
    }

    /**
     * Retrieve current baker ID
     *
     * @return int
     */
    private function getCurrentBakerId()
    {
        $originalRequestData = $this->getRequest()->getPostValue(BakerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $bakerId = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $bakerId;
    }
}
