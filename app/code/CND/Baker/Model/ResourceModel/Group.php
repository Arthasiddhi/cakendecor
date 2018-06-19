<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;

/**
 * Baker group resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Group extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb
{
    /**
     * Group Management
     *
     * @var \CND\Baker\Api\GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker\CollectionFactory
     */
    protected $_bakersFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Snapshot $entitySnapshot,
     * @param RelationComposite $entityRelationComposite,
     * @param \CND\Baker\Api\GroupManagementInterface $groupManagement
     * @param Baker\CollectionFactory $bakersFactory
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        \CND\Baker\Api\GroupManagementInterface $groupManagement,
        \CND\Baker\Model\ResourceModel\Baker\CollectionFactory $bakersFactory,
        $connectionName = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_bakersFactory = $bakersFactory;
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('baker_group', 'baker_group_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => 'baker_group_code', 'title' => __('Baker Group')]];

        return $this;
    }

    /**
     * Check if group uses as default
     *
     * @param  \Magento\Framework\Model\AbstractModel $group
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $group)
    {
        if ($group->usesAsDefault()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t delete group "%1".', $group->getCode())
            );
        }
        return parent::_beforeDelete($group);
    }

    /**
     * Method set default group id to the bakers collection
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $group)
    {
        $bakerCollection = $this->_createBakersCollection()->addAttributeToFilter(
            'group_id',
            $group->getId()
        )->load();
        foreach ($bakerCollection as $baker) {
            /** @var $baker \CND\Baker\Model\Baker */
            $baker->load($baker->getId());
            $defaultGroupId = $this->_groupManagement->getDefaultGroup($baker->getStoreId())->getId();
            $baker->setGroupId($defaultGroupId);
            $baker->save();
        }
        return parent::_afterDelete($group);
    }

    /**
     * @return \CND\Baker\Model\ResourceModel\Baker\Collection
     */
    protected function _createBakersCollection()
    {
        return $this->_bakersFactory->create();
    }

    /**
     * Prepare data before save
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $group)
    {
        /** @var \CND\Baker\Model\Group $group */
        $group->setCode(substr($group->getCode(), 0, $group::GROUP_CODE_MAX_LENGTH));
        return parent::_beforeSave($group);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId() == \CND\Baker\Model\Group::CUST_GROUP_ALL) {
            $this->skipReservedId($object);
        }

        return $this;
    }

    /**
     * Here we do not allow to save systems reserved ID.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function skipReservedId(\Magento\Framework\Model\AbstractModel $object)
    {
        $tableFieldsWithoutIdField = $this->getTableFieldsWithoutIdField();
        $select = $this->getConnection()->select();
        $select->from(
            [$this->getMainTable()],
            $tableFieldsWithoutIdField
        )
            ->where('baker_group_id = ?', \CND\Baker\Model\Group::CUST_GROUP_ALL);

        $query = $this->getConnection()->insertFromSelect(
            $select,
            $this->getMainTable(),
            $tableFieldsWithoutIdField
        );
        $this->getConnection()->query($query);
        $lastInsertId = $this->getConnection()->lastInsertId();

        $query = $this->getConnection()->deleteFromSelect(
            $select,
            $this->getMainTable()
        );
        $this->getConnection()->query($query);

        $object->setId($lastInsertId);
    }

    /**
     * Get main table fields except of ID field.
     *
     * @return array
     */
    private function getTableFieldsWithoutIdField()
    {
        $fields = $this->getConnection()->describeTable($this->getMainTable());
        if (isset($fields['baker_group_id'])) {
            unset($fields['baker_group_id']);
        }

        return array_keys($fields);
    }
}
