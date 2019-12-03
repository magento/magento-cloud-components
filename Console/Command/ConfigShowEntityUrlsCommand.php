<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Console\Command;

use Magento\CloudComponents\Model\UrlFinder\Product;
use Magento\CloudComponents\Model\UrlFinderFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Returns list of category or cms-page urls for given stores
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigShowEntityUrlsCommand extends Command
{
    /**
     * Names of input arguments or options.
     */
    const INPUT_OPTION_STORE_ID = 'store-id';
    const INPUT_OPTION_ENTITY_TYPE = 'entity-type';
    const INPUT_OPTION_PRODUCT_SKU = 'product-sku';
    const INPUT_OPTION_PRODUCT_LIMIT = 'product-limit';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $state;

    /**
     * @var UrlFinderFactory
     */
    private $urlFinderFactory;

    /**
     * @var array
     */
    private $possibleEntities = [
        Rewrite::ENTITY_TYPE_CMS_PAGE,
        Rewrite::ENTITY_TYPE_CATEGORY,
        Rewrite::ENTITY_TYPE_PRODUCT
    ];

    /**
     * @param StoreManagerInterface $storeManager
     * @param UrlFinderFactory $urlFinderFactory
     * @param State $state
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlFinderFactory $urlFinderFactory,
        State $state
    ) {
        $this->storeManager = $storeManager;
        $this->urlFinderFactory = $urlFinderFactory;
        $this->state = $state;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('config:show:urls')
            ->setDescription(
                'Returns urls for entity type and given store id or for all stores if store id isn\'t provided.'
            );

        $this->addOption(
            self::INPUT_OPTION_STORE_ID,
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            'Store ID'
        );
        $this->addOption(
            self::INPUT_OPTION_ENTITY_TYPE,
            null,
            InputOption::VALUE_REQUIRED,
            'Entity type: ' . implode(',', $this->possibleEntities)
        );
        $this->addOption(
            self::INPUT_OPTION_PRODUCT_SKU,
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            'Product SKUs'
        );
        $this->addOption(
            self::INPUT_OPTION_PRODUCT_LIMIT,
            null,
            InputOption::VALUE_OPTIONAL,
            'Product limit per store uses in case when product SKUs isn\'t provided',
            Product::PRODUCT_LIMIT
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
            $this->setArea();
            $entityType = $input->getOption(self::INPUT_OPTION_ENTITY_TYPE);
            if (!in_array($entityType, $this->possibleEntities)) {
                $output->write(sprintf(
                    'Wrong entity type "%s", possible values: %s',
                    $entityType,
                    implode(',', $this->possibleEntities)
                ));
                return Cli::RETURN_FAILURE;
            }

            $urlFinder = $this->urlFinderFactory->create($entityType, [
                'stores' => $this->getStores($input),
                'productSku' => $input->getOption(self::INPUT_OPTION_PRODUCT_SKU),
                'productLimit' => $input->getOption(self::INPUT_OPTION_PRODUCT_LIMIT),
            ]);

            $output->writeln(json_encode(array_unique($urlFinder->get())));
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * @param InputInterface $input
     * @return StoreInterface[]
     * @throws NoSuchEntityException
     */
    private function getStores(InputInterface $input): array
    {
        $storeIds = $input->getOption(self::INPUT_OPTION_STORE_ID);

        if (!empty($storeIds)) {
            $stores = [];
            foreach ($storeIds as $storeId) {
                $stores[] = $this->storeManager->getStore($storeId);
            }
        } else {
            $stores = $this->storeManager->getStores();
        }

        return $stores;
    }

    /**
     * Sets area code.
     */
    private function setArea()
    {
        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        } catch (LocalizedException $e) {
        }
    }
}
