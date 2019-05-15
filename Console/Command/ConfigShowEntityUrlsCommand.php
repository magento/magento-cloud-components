<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CloudComponents\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Returns list of category or cms-page urls for given stores
 */
class ConfigShowEntityUrlsCommand extends Command
{
    const INPUT_OPTION_STORE_ID = 'store-id';
    const INPUT_OPTION_ENTITY_TYPE = 'entity-type';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var array
     */
    private $possibleEntities = [Rewrite::ENTITY_TYPE_CMS_PAGE, Rewrite::ENTITY_TYPE_CATEGORY];

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlFinderInterface $urlFinder,
        UrlInterface $url
    ) {
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
        $this->url = $url;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->addOption(
            self::INPUT_OPTION_STORE_ID,
            null,
            InputOption::VALUE_OPTIONAL,
            'Store ID'
        );
        $this->addOption(
            self::INPUT_OPTION_ENTITY_TYPE,
            null,
            InputOption::VALUE_REQUIRED,
            'Entity type: ' . implode(',', $this->possibleEntities)
        );

        $this->setName('config:show:urls')
            ->setDescription(
                'Returns urls for entity type and given store id or for all stores if store id isn\'t provided.'
            );
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $entityType = $input->getOption(self::INPUT_OPTION_ENTITY_TYPE);
            if (!in_array($entityType, $this->possibleEntities)) {
                $output->write(sprintf(
                    'Wrong entity type "%s", possible values: %s',
                    $entityType,
                    implode(',', $this->possibleEntities)
                ));
                return Cli::RETURN_FAILURE;
            }

            $storeId = $input->getOption(self::INPUT_OPTION_STORE_ID);

            if ($storeId === null) {
                $stores = $this->storeManager->getStores();
            } else {
                $stores = [$this->storeManager->getStore($storeId)];
            }

            $urls = $this->getPageUrls($stores, $entityType);

            $output->write(json_encode(array_unique($urls)));
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * @param StoreInterface[] $stores
     * @param string $entityType
     * @return array
     */
    private function getPageUrls(array $stores, string $entityType): array
    {
        $urls = [];

        foreach ($stores as $store) {
            $dataFilter = [];
            $dataFilter[UrlRewrite::STORE_ID] = $store->getId();
            $dataFilter[UrlRewrite::ENTITY_TYPE] = $entityType;
            $entities = $this->urlFinder->findAllByData($dataFilter);
            $this->url->setScope($store->getId());
            foreach ($entities as $urlRewrite) {
                $urls[] = $this->url->getUrl($urlRewrite->getRequestPath());
            }
        }

        return $urls;
    }
}
