<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Controller\Adminhtml\Index;

use CND\Baker\Api\Data\BakerInterface;
use Magento\Framework\Message\Error;

class Validate extends \CND\Baker\Controller\Adminhtml\Index
{
    /**
     * Baker validation
     *
     * @param \Magento\Framework\DataObject $response
     * @return BakerInterface|null
     */
    protected function _validateBaker($response)
    {
        $baker = null;
        $errors = [];

        try {
            /** @var BakerInterface $baker */
            $baker = $this->bakerDataFactory->create();

            $bakerForm = $this->_formFactory->create(
                'baker',
                'adminhtml_baker',
                [],
                true
            );
            $bakerForm->setInvisibleIgnored(true);

            $data = $bakerForm->extractData($this->getRequest(), 'baker');

            if ($baker->getWebsiteId()) {
                unset($data['website_id']);
            }

            $this->dataObjectHelper->populateWithArray(
                $baker,
                $data,
                \CND\Baker\Api\Data\BakerInterface::class
            );
            $submittedData = $this->getRequest()->getParam('baker');
            if (isset($submittedData['entity_id'])) {
                $entity_id = $submittedData['entity_id'];
                $baker->setId($entity_id);
            }
            $errors = $this->bakerAccountManagement->validate($baker)->getMessages();
        } catch (\Magento\Framework\Validator\Exception $exception) {
            /* @var $error Error */
            foreach ($exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR) as $error) {
                $errors[] = $error->getText();
            }
        }

        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }

        return $baker;
    }

    /**
     * Baker address validation.
     *
     * @param \Magento\Framework\DataObject $response
     * @return void
     */
    protected function _validateBakerAddress($response)
    {
        $addresses = $this->getRequest()->getPost('address');
        if (!is_array($addresses)) {
            return;
        }
        foreach (array_keys($addresses) as $index) {
            if ($index == '_template_') {
                continue;
            }

            $addressForm = $this->_formFactory->create('baker_address', 'adminhtml_baker_address');

            $requestScope = sprintf('address/%s', $index);
            $formData = $addressForm->extractData($this->getRequest(), $requestScope);

            $errors = $addressForm->validateData($formData);
            if ($errors !== true) {
                $messages = $response->hasMessages() ? $response->getMessages() : [];
                foreach ($errors as $error) {
                    $messages[] = $error;
                }
                $response->setMessages($messages);
                $response->setError(1);
            }
        }
    }

    /**
     * AJAX baker validation action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        $baker = $this->_validateBaker($response);
        if ($baker) {
            $this->_validateBakerAddress($response);
        }
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
