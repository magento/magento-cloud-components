<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Console\Command;

use Magento\CloudComponents\Model\UrlFixer;
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
     * @var UrlFixer
     */
    private $urlFixer;

    /**
     * @param StoreManagerInterface $storeManager
     * @param UrlFixer $urlFixer
     */
    public function __construct(StoreManagerInterface $storeManager, UrlFixer $urlFixer)
    {
        parent::__construct();
        $this->storeManager = $storeManager;
        $this->urlFixer = $urlFixer;
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
        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, $store->isUrlSecure());
        $output->writeln($this->urlFixer->run($store, $baseUrl));

        return Cli::RETURN_SUCCESS;
    }
}
