<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model;

use CND\Baker\Api\BakerRepositoryInterface;
use CND\Baker\Api\Data\BakerInterface as BakerData;
use CND\Baker\Api\GroupManagementInterface;
use CND\Baker\Model\Config\Share;
use CND\Baker\Model\ResourceModel\Baker as ResourceBaker;

/**
 * Baker session model
 *
 * @api
 * @method string getNoReferer()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Baker object
     *
     * @var BakerData
     */
    protected $_customer;

    /**
     * @var ResourceBaker
     */
    protected $_customerResource;

    /**
     * Baker model
     *
     * @var Baker
     */
    protected $_bakerModel;

    /**
     * Flag with baker id validations result
     *
     * @var bool|null
     */
    protected $_isBakerIdChecked = null;

    /**
     * Baker URL
     *
     * @var \CND\Baker\Model\Url
     */
    protected $_bakerUrl;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data|null
     */
    protected $_coreUrl = null;

    /**
     * @var Share
     */
    protected $_configShare;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var  BakerRepositoryInterface
     */
    protected $bakerRepository;

    /**
     * @var BakerFactory
     */
    protected $_bakerFactory;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param Share $configShare
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param \CND\Baker\Model\Url $bakerUrl
     * @param ResourceBaker $bakerResource
     * @param BakerFactory $bakerFactory
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param BakerRepositoryInterface $bakerRepository
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\App\Response\Http $response
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        Config\Share $configShare,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        \CND\Baker\Model\Url $bakerUrl,
        ResourceBaker $bakerResource,
        BakerFactory $bakerFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Http\Context $httpContext,
        BakerRepositoryInterface $bakerRepository,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\App\Response\Http $response
    ) {
        $this->_coreUrl = $coreUrl;
        $this->_bakerUrl = $bakerUrl;
        $this->_configShare = $configShare;
        $this->_bakerResource = $bakerResource;
        $this->_bakerFactory = $bakerFactory;
        $this->_urlFactory = $urlFactory;
        $this->_session = $session;
        $this->bakerRepository = $bakerRepository;
        $this->_eventManager = $eventManager;
        $this->_httpContext = $httpContext;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
        $this->groupManagement = $groupManagement;
        $this->response = $response;
        $this->_eventManager->dispatch('baker_session_init', ['baker_session' => $this]);
    }

    /**
     * Retrieve baker sharing configuration model
     *
     * @return Share
     */
    public function getBakerConfigShare()
    {
        return $this->_configShare;
    }

    /**
     * Set baker object and setting baker id in session
     *
     * @param   BakerData $baker
     * @return  $this
     */
    public function setBakerData(BakerData $baker)
    {
        $this->_baker = $baker;
        if ($baker === null) {
            $this->setBakerId(null);
        } else {
            $this->_httpContext->setValue(
                Context::CONTEXT_GROUP,
                $baker->getGroupId(),
                \CND\Baker\Model\Group::NOT_LOGGED_IN_ID
            );
            $this->setBakerId($baker->getId());
        }
        return $this;
    }

    /**
     * Retrieve baker model object
     *
     * @return BakerData
     */
    public function getBakerData()
    {
        if (!$this->_baker instanceof BakerData && $this->getBakerId()) {
            $this->_baker = $this->bakerRepository->getById($this->getBakerId());
        }

        return $this->_baker;
    }

    /**
     * Returns Baker data object with the baker information
     *
     * @return BakerData
     */
    public function getBakerDataObject()
    {
        /* TODO refactor this after all usages of the setBaker is refactored */
        return $this->getBaker()->getDataModel();
    }

    /**
     * Set Baker data object with the baker information
     *
     * @param BakerData $bakerData
     * @return $this
     */
    public function setBakerDataObject(BakerData $bakerData)
    {
        $this->setId($bakerData->getId());
        $this->getBaker()->updateData($bakerData);
        return $this;
    }

    /**
     * Set baker model and the baker id in session
     *
     * @param   Baker $bakerModel
     * @return  $this
     * use setBakerId() instead
     */
    public function setBaker(Baker $bakerModel)
    {
        $this->_bakerModel = $bakerModel;
        $this->_httpContext->setValue(
            Context::CONTEXT_GROUP,
            $bakerModel->getGroupId(),
            \CND\Baker\Model\Group::NOT_LOGGED_IN_ID
        );
        $this->setBakerId($bakerModel->getId());
        if (!$bakerModel->isConfirmationRequired() && $bakerModel->getConfirmation()) {
            $bakerModel->setConfirmation(null)->save();
        }

        /**
         * The next line is a workaround.
         * It is used to distinguish users that are logged in from user data set via methods similar to setBakerId()
         */
        $this->unsIsBakerEmulated();

        return $this;
    }

    /**
     * Retrieve baker model object
     *
     * @return Baker
     * use getBakerId() instead
     */
    public function getBaker()
    {
        if ($this->_bakerModel === null) {
            $this->_bakerModel = $this->_bakerFactory->create()->load($this->getBakerId());
        }

        return $this->_bakerModel;
    }

    /**
     * Set baker id
     *
     * @param int|null $id
     * @return $this
     */
    public function setBakerId($id)
    {
        $this->storage->setData('baker_id', $id);
        return $this;
    }

    /**
     * Retrieve baker id from current session
     *
     * @api
     * @return int|null
     */
    public function getBakerId()
    {
        if ($this->storage->getData('baker_id')) {
            return $this->storage->getData('baker_id');
        }
        return null;
    }

    /**
     * Retrieve baker id from current session
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getBakerId();
    }

    /**
     * Set baker id
     *
     * @param int|null $bakerId
     * @return $this
     */
    public function setId($bakerId)
    {
        return $this->setBakerId($bakerId);
    }

    /**
     * Set baker group id
     *
     * @param int|null $id
     * @return $this
     */
    public function setBakerGroupId($id)
    {
        $this->storage->setData('baker_group_id', $id);
        return $this;
    }

    /**
     * Get baker group id
     * If baker is not logged in system, 'not logged in' group id will be returned
     *
     * @return int
     */
    public function getBakerGroupId()
    {
        if ($this->storage->getData('baker_group_id')) {
            return $this->storage->getData('baker_group_id');
        }
        if ($this->getBakerData()) {
            $bakerGroupId = $this->getBakerData()->getGroupId();
            $this->setBakerGroupId($bakerGroupId);
            return $bakerGroupId;
        }
        return Group::NOT_LOGGED_IN_ID;
    }

    /**
     * Checking baker login status
     *
     * @api
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool)$this->getBakerId()
            && $this->checkBakerId($this->getId())
            && !$this->getIsBakerEmulated();
    }

    /**
     * Check exists baker (light check)
     *
     * @param int $bakerId
     * @return bool
     */
    public function checkBakerId($bakerId)
    {
        if ($this->_isBakerIdChecked === $bakerId) {
            return true;
        }

        try {
            $this->bakerRepository->getById($bakerId);
            $this->_isBakerIdChecked = $bakerId;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Baker $baker
     * @return $this
     */
    public function setBakerAsLoggedIn($baker)
    {
        $this->setBaker($baker);
        $this->_eventManager->dispatch('baker_login', ['baker' => $baker]);
        $this->_eventManager->dispatch('baker_data_object_login', ['baker' => $this->getBakerDataObject()]);
        $this->regenerateId();
        return $this;
    }

    /**
     * @param BakerData $baker
     * @return $this
     */
    public function setBakerDataAsLoggedIn($baker)
    {
        $this->_httpContext->setValue(Context::CONTEXT_AUTH, true, false);
        $this->setBakerData($baker);

        $bakerModel = $this->_bakerFactory->create()->updateData($baker);

        $this->setBaker($bakerModel);

        $this->_eventManager->dispatch('baker_login', ['baker' => $bakerModel]);
        $this->_eventManager->dispatch('baker_data_object_login', ['baker' => $baker]);
        return $this;
    }

    /**
     * Authorization baker by identifier
     *
     * @api
     * @param   int $bakerId
     * @return  bool
     */
    public function loginById($bakerId)
    {
        try {
            $baker = $this->bakerRepository->getById($bakerId);
            $this->setBakerDataAsLoggedIn($baker);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Logout baker
     *
     * @api
     * @return $this
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->_eventManager->dispatch('baker_logout', ['baker' => $this->getBaker()]);
            $this->_logout();
        }
        $this->_httpContext->unsValue(Context::CONTEXT_AUTH);
        return $this;
    }

    /**
     * Authenticate controller action by login baker
     *
     * @param   bool|null $loginUrl
     * @return  bool
     */
    public function authenticate($loginUrl = null)
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        $this->setBeforeAuthUrl($this->_createUrl()->getUrl('*/*/*', ['_current' => true]));
        if (isset($loginUrl)) {
            $this->response->setRedirect($loginUrl);
        } else {
            $arguments = $this->_bakerUrl->getLoginUrlParams();
            if ($this->_session->getCookieShouldBeReceived() && $this->_createUrl()->getUseSession()) {
                $arguments += [
                    '_query' => [
                        $this->sidResolver->getSessionIdQueryParam($this->_session) => $this->_session->getSessionId(),
                    ]
                ];
            }
            $this->response->setRedirect(
                $this->_createUrl()->getUrl(\CND\Baker\Model\Url::ROUTE_ACCOUNT_LOGIN, $arguments)
            );
        }

        return false;
    }

    /**
     * Set auth url
     *
     * @param string $key
     * @param string $url
     * @return $this
     */
    protected function _setAuthUrl($key, $url)
    {
        $url = $this->_coreUrl->removeRequestParam($url, $this->sidResolver->getSessionIdQueryParam($this));
        // Add correct session ID to URL if needed
        $url = $this->_createUrl()->getRebuiltUrl($url);
        return $this->storage->setData($key, $url);
    }

    /**
     * Logout without dispatching event
     *
     * @return $this
     */
    protected function _logout()
    {
        $this->_baker = null;
        $this->_bakerModel = null;
        $this->setBakerId(null);
        $this->setBakerGroupId($this->groupManagement->getNotLoggedInGroup()->getId());
        $this->destroy(['clear_storage' => false]);
        return $this;
    }

    /**
     * Set Before auth url
     *
     * @param string $url
     * @return $this
     */
    public function setBeforeAuthUrl($url)
    {
        return $this->_setAuthUrl('before_auth_url', $url);
    }

    /**
     * Set After auth url
     *
     * @param string $url
     * @return $this
     */
    public function setAfterAuthUrl($url)
    {
        return $this->_setAuthUrl('after_auth_url', $url);
    }

    /**
     * Reset core session hosts after reseting session ID
     *
     * @return $this
     */
    public function regenerateId()
    {
        parent::regenerateId();
        $this->_cleanHosts();
        return $this;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }
}
