<?php
/**
 * Baker address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel;

use CND\Baker\Controller\Adminhtml\Group\Delete;
use CND\Baker\Model\BakerRegistry;
use CND\Baker\Model\ResourceModel\Address\DeleteRelation;
use Magento\Framework\App\ObjectManager;

/**
 * Class Address
 * @package CND\Baker\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends \Magento\Eav\Model\Entity\VersionControl\AbstractEntity
{
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @var \CND\Baker\Api\BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \CND\Baker\Api\BakerRepositoryInterface $bakerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \CND\Baker\Api\BakerRepositoryInterface $bakerRepository,
        $data = []
    ) {
        $this->bakerRepository = $bakerRepository;
        $this->_validatorFactory = $validatorFactory;
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $data);
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->connectionName = 'baker';
    }

    /**
     * Getter and lazy loader for _type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            $this->setType('baker_address');
        }
        return parent::getEntityType();
    }

    /**
     * Check baker address before saving
     *
     * @param \Magento\Framework\DataObject $address
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\DataObject $address)
    {
        parent::_beforeSave($address);

        $this->_validate($address);

        return $this;
    }

    /**
     * Validate baker address entity
     *
     * @param \Magento\Framework\DataObject $address
     * @return void
     * @throws \Magento\Framework\Validator\Exception When validation failed
     */
    protected function _validate($address)
    {
        $validator = $this->_validatorFactory->createValidator('baker_address', 'save');

        if (!$validator->isValid($address)) {
            throw new \Magento\Framework\Validator\Exception(
                null,
                null,
                $validator->getMessages()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $result = parent::delete($object);
        $object->setData([]);
        return $result;
    }

    /**
     * @deprecated 100.2.0
     * @return DeleteRelation
     */
    private function getDeleteRelation()
    {
        return ObjectManager::getInstance()->get(DeleteRelation::class);
    }

    /**
     * @deprecated 100.2.0
     * @return BakerRegistry
     */
    private function getRegistry()
    {
        return ObjectManager::getInstance()->get(BakerRegistry::class);
    }

    /**
     * @param \CND\Baker\Model\Address $address
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\DataObject $address)
    {
        $baker = $this->getBakerRegistry()->retrieve($address->getBakerId());

        $this->getDeleteRelation()->deleteRelation($address, $baker);
        return parent::_afterDelete($address);
    }
}
