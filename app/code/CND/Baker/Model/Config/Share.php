<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Config;

/**
 * Baker sharing config model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Share extends \Magento\Framework\App\Config\Value implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Xml config path to Bakers sharing scope value
     *
     */
    const XML_PATH_Baker_ACCOUNT_SHARE = 'baker/account_share/scope';

    /**
     * Possible Baker sharing scopes
     *
     */
    const SHARE_GLOBAL = 0;

    const SHARE_WEBSITE = 1;

    /**
     * @var \CND\Baker\Model\ResourceModel\Baker
     */
    protected $_BakerResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \CND\Baker\Model\ResourceModel\Baker $bakerResource
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CND\Baker\Model\ResourceModel\Baker $bakerResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_BakerResource = $bakerResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Check whether current Bakers sharing scope is global
     *
     * @return bool
     */
    public function isGlobalScope()
    {
        return !$this->isWebsiteScope();
    }

    /**
     * Check whether current Bakers sharing scope is website
     *
     * @return bool
     */
    public function isWebsiteScope()
    {
        return $this->_config->getValue(
            self::XML_PATH_Baker_ACCOUNT_SHARE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == self::SHARE_WEBSITE;
    }

    /**
     * Get possible sharing configuration options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [self::SHARE_GLOBAL => __('Global'), self::SHARE_WEBSITE => __('Per Website')];
    }

    /**
     * Check for email duplicates before saving Bakers sharing options
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == self::SHARE_GLOBAL) {
            if ($this->_BakerResource->findEmailDuplicates()) {
                //@codingStandardsIgnoreStart
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'We can\'t share Baker accounts globally when the accounts share identical email addresses on more than one website.'
                    )
                );
                //@codingStandardsIgnoreEnd
            }
        }
        return $this;
    }

    /**
     * Returns shared website Ids.
     *
     * @param int $websiteId the ID to use if website scope is on
     * @return int[]
     */
    public function getSharedWebsiteIds($websiteId)
    {
        $ids = [];
        if ($this->isWebsiteScope()) {
            $ids[] = $websiteId;
        } else {
            foreach ($this->_storeManager->getWebsites() as $website) {
                $ids[] = $website->getId();
            }
        }
        return $ids;
    }
}
