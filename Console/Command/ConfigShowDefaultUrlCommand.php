<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CloudComponents\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for getting url for default store of default website.
 */
class ConfigShowDefaultUrlCommand extends Command
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        parent::__construct();
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('config:show:default-url')
            ->setDescription('Shows base url for default store of default website');

        parent::configure();
    }

    /**
     * Returns base url for default store of default website
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Store $store */
        $store = $this->storeManager->getDefaultStoreView();
        $output->write($store->getBaseUrl(UrlInterface::URL_TYPE_LINK, $store->isUrlSecure()));

        return Cli::RETURN_SUCCESS;
    }
}
