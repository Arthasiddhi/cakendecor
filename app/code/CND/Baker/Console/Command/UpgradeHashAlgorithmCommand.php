<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CND\Baker\Console\Command;

use CND\Baker\Model\Baker;
use Magento\Framework\Encryption\Encryptor;
use CND\Baker\Model\ResourceModel\Baker\Collection;
use CND\Baker\Model\ResourceModel\Baker\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeHashAlgorithmCommand extends Command
{
    /**
     * @var CollectionFactory
     */
    private $bakerCollectionFactory;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param CollectionFactory $bakerCollectionFactory
     * @param Encryptor $encryptor
     */
    public function __construct(
        CollectionFactory $bakerCollectionFactory,
        Encryptor $encryptor
    ) {
        parent::__construct();
        $this->bakerCollectionFactory = $bakerCollectionFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('baker:hash:upgrade')
            ->setDescription('Upgrade baker\'s hash according to the latest algorithm');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collection = $this->bakerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $bakerCollection = $this->collection->getItems();
        /** @var $baker Baker */
        foreach ($bakerCollection as $baker) {
            $baker->load($baker->getId());
            if (!$this->encryptor->validateHashVersion($baker->getPasswordHash())) {
                list($hash, $salt, $version) = explode(Encryptor::DELIMITER, $baker->getPasswordHash(), 3);
                $version .= Encryptor::DELIMITER . Encryptor::HASH_VERSION_LATEST;
                $baker->setPasswordHash($this->encryptor->getHash($hash, $salt, $version));
                $baker->save();
                $output->write(".");
            }
        }
        $output->writeln(".");
        $output->writeln("<info>Finished</info>");
    }
}
