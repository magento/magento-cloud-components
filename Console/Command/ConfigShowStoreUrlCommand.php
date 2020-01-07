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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for getting store ulr by store id.
 */
class ConfigShowStoreUrlCommand extends Command
{
    /**
     * Name of input argument.
     */
    const INPUT_ARGUMENT_STORE_ID = 'store-id';

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
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlFixer $urlFixer
    ) {
        parent::__construct();
        $this->storeManager = $storeManager;
        $this->urlFixer = $urlFixer;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('config:show:store-url')
            ->setDescription(
                'Shows store base url for given id. Shows base url for all stores if id wasn\'t passed'
            );

        $this->addArgument(
            self::INPUT_ARGUMENT_STORE_ID,
            InputArgument::OPTIONAL,
            'Store ID'
        );

        parent::configure();
    }

    /**
     * Returns store url or all store urls if store id wasn't provided
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var Store $store */
            $storeId = $input->getArgument(self::INPUT_ARGUMENT_STORE_ID);
            if ($storeId !== null) {
                $store = $this->storeManager->getStore($storeId);
                $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, $store->isUrlSecure());

                $output->writeln($this->urlFixer->run($store, $baseUrl));
            } else {
                $urls = [];
                foreach ($this->storeManager->getStores(true) as $store) {
                    $urls[$store->getId()] = $this->urlFixer->run(
                        $store,
                        $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, $store->isUrlSecure())
                    );
                }

                $output->writeln(json_encode($urls, JSON_FORCE_OBJECT));
            }

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        }
    }
}
