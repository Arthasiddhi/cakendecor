<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Account;

use CND\Baker\Model\Session;
use CND\Baker\Model\Url as BakerUrl;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Url\HostChecker;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\Forward as ResultForward;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redirect
{
    /** URL to redirect user on successful login or registration */
    const LOGIN_REDIRECT_URL = 'login_redirect';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var BakerUrl
     */
    protected $cakerUrl;

    /**
     * @deprecated 100.1.8
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var HostChecker
     */
    private $hostChecker;

    /**
     * @param RequestInterface $request
     * @param Session $cakerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     * @param DecoderInterface $urlDecoder
     * @param BakerUrl $cakerUrl
     * @param ResultFactory $resultFactory
     * @param HostChecker|null $hostChecker
     */
    public function __construct(
        RequestInterface $request,
        Session $cakerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        DecoderInterface $urlDecoder,
        BakerUrl $cakerUrl,
        ResultFactory $resultFactory,
        HostChecker $hostChecker = null
    ) {
        $this->request = $request;
        $this->session = $cakerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->urlDecoder = $urlDecoder;
        $this->cakerUrl = $cakerUrl;
        $this->resultFactory = $resultFactory;
        $this->hostChecker = $hostChecker ?: ObjectManager::getInstance()->get(HostChecker::class);
    }

    /**
     * Retrieve redirect
     *
     * @return ResultRedirect|ResultForward
     */
    public function getRedirect()
    {
        $this->updateLastBakerId();
        $this->prepareRedirectUrl();

        /** @var ResultRedirect|ResultForward $result */
        if ($this->session->getBeforeRequestParams()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result->setParams($this->session->getBeforeRequestParams())
                ->setModule($this->session->getBeforeModuleName())
                ->setController($this->session->getBeforeControllerName())
                ->forward($this->session->getBeforeAction());
        } else {
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setUrl($this->session->getBeforeAuthUrl(true));
        }
        return $result;
    }

    /**
     * Update last caker id, if required
     *
     * @return void
     */
    protected function updateLastBakerId()
    {
        $lastBakerId = $this->session->getLastBakerId();
        if (isset($lastBakerId)
            && $this->session->isLoggedIn()
            && $lastBakerId != $this->session->getId()
        ) {
            $this->session->unsBeforeAuthUrl()
                ->setLastBakerId($this->session->getId());
        }
    }

    /**
     * Prepare redirect URL
     *
     * @return void
     */
    protected function prepareRedirectUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        $url = $this->session->getBeforeAuthUrl();
        if (!$url) {
            $url = $baseUrl;
        }

        switch ($url) {
            case $baseUrl:
                if ($this->session->isLoggedIn()) {
                    $this->processLoggedBaker();
                } else {
                    $this->applyRedirect($this->cakerUrl->getLoginUrl());
                }
                break;

            case $this->cakerUrl->getLogoutUrl():
                $this->applyRedirect($this->cakerUrl->getDashboardUrl());
                break;

            default:
                if (!$this->session->getAfterAuthUrl()) {
                    $this->session->setAfterAuthUrl($this->session->getBeforeAuthUrl());
                }
                if ($this->session->isLoggedIn()) {
                    $this->applyRedirect($this->session->getAfterAuthUrl(true));
                }
                break;
        }
    }

    /**
     * Prepare redirect URL for logged in caker
     *
     * Redirect caker to the last page visited after logging in.
     *
     * @return void
     */
    protected function processLoggedBaker()
    {
        // Set default redirect URL for logged in caker
        $this->applyRedirect($this->cakerUrl->getAccountUrl());

        if (!$this->scopeConfig->isSetFlag(
            BakerUrl::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            $referer = $this->request->getParam(BakerUrl::REFERER_QUERY_PARAM_NAME);
            if ($referer) {
                $referer = $this->urlDecoder->decode($referer);
                preg_match('/logoutSuccess/', $referer, $matches, PREG_OFFSET_CAPTURE);
                if (!empty($matches)) {
                    $referer = str_replace('logoutSuccess', '', $referer);
                }
                if ($this->hostChecker->isOwnOrigin($referer)) {
                    $this->applyRedirect($referer);
                }
            }
        } elseif ($this->session->getAfterAuthUrl()) {
            $this->applyRedirect($this->session->getAfterAuthUrl(true));
        }
    }

    /**
     * Prepare redirect URL
     *
     * @param string $url
     * @return void
     */
    private function applyRedirect($url)
    {
        $this->session->setBeforeAuthUrl($url);
    }

    /**
     * Get Cookie manager. For release backward compatibility.
     *
     * @deprecated 100.0.10
     * @return CookieManagerInterface
     */
    protected function getCookieManager()
    {
        if (!is_object($this->cookieManager)) {
            $this->cookieManager = ObjectManager::getInstance()->get(CookieManagerInterface::class);
        }
        return $this->cookieManager;
    }

    /**
     * Set cookie manager. For unit tests.
     *
     * @deprecated 100.0.10
     * @param object $value
     * @return void
     */
    public function setCookieManager($value)
    {
        $this->cookieManager = $value;
    }

    /**
     * Get redirect route from cookie for case of successful login/registration
     *
     * @return null|string
     */
    public function getRedirectCookie()
    {
        return $this->getCookieManager()->getCookie(self::LOGIN_REDIRECT_URL, null);
    }

    /**
     * Save redirect route to cookie for case of successful login/registration
     *
     * @param string $route
     * @return void
     */
    public function setRedirectCookie($route)
    {
        $this->getCookieManager()->setPublicCookie(self::LOGIN_REDIRECT_URL, $route);
    }

    /**
     * Clear cookie with requested route
     *
     * @return void
     */
    public function clearRedirectCookie()
    {
        $this->getCookieManager()->deleteCookie(self::LOGIN_REDIRECT_URL);
    }
}
