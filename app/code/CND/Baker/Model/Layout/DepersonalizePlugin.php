<?php
/**
 * Depersonalize baker session data
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Model\Layout;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \CND\Baker\Model\Session
     */
    protected $bakerSession;

    /**
     * @var \CND\Baker\Model\BakerFactory
     */
    protected $bakerFactory;

    /**
     * @var \CND\Baker\Model\Visitor
     */
    protected $visitor;

    /**
     * @var int
     */
    protected $bakerGroupId;

    /**
     * @var string
     */
    protected $formKey;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \CND\Baker\Model\Session $bakerSession
     * @param \CND\Baker\Model\BakerFactory $bakerFactory
     * @param \CND\Baker\Model\Visitor $visitor
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \CND\Baker\Model\Session $bakerSession,
        \CND\Baker\Model\BakerFactory $bakerFactory,
        \CND\Baker\Model\Visitor $visitor
    ) {
        $this->session = $session;
        $this->bakerSession = $bakerSession;
        $this->bakerFactory = $bakerFactory;
        $this->visitor = $visitor;
        $this->depersonalizeChecker = $depersonalizeChecker;
    }

    /**
     * Before generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @return array
     */
    public function beforeGenerateXml(\Magento\Framework\View\LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->bakerGroupId = $this->bakerSession->getBakerGroupId();
            $this->formKey = $this->session->getData(\Magento\Framework\Data\Form\FormKey::FORM_KEY);
        }
        return [];
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->visitor->setSkipRequestLogging(true);
            $this->visitor->unsetData();
            $this->session->clearStorage();
            $this->bakerSession->clearStorage();
            $this->session->setData(\Magento\Framework\Data\Form\FormKey::FORM_KEY, $this->formKey);
            $this->bakerSession->setBakerGroupId($this->bakerGroupId);
            $this->bakerSession->setBaker($this->bakerFactory->create()->setGroupId($this->bakerGroupId));
        }
        return $result;
    }
}
