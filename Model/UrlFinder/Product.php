<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\UrlFinder;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CloudComponents\Model\UrlFinderInterface as CloudUrlFinderInterface;
use Magento\CloudComponents\Model\UrlFixer;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Returns product urls by given skus and store ids
 */
class Product implements CloudUrlFinderInterface
{
    /**
     * Product limit per store
     */
    public const PRODUCT_LIMIT = 100;

    /**
     * @var UrlFixer
     */
    private $urlFixer;

    /**
     * @var StoreInterface[]
     */
    private $stores;

    /**
     * @var array
     */
    private $productSku;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var integer
     */
    private $productLimit;

    /**
     * @param UrlFixer $urlFixer
     * @param CollectionFactory $productCollectionFactory
     * @param StoreInterface[] $stores
     * @param array $productSku
     * @param int $productLimit
     */
    public function __construct(
        UrlFixer $urlFixer,
        CollectionFactory $productCollectionFactory,
        array $stores,
        array $productSku = [],
        int $productLimit = self::PRODUCT_LIMIT
    ) {
        $this->urlFixer = $urlFixer;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stores = $stores;
        $this->productSku = $productSku;
        $this->productLimit = $productLimit;
    }

    /**
     * Returns product urls by given skus and store ids
     *
     * @return array
     */
    public function get(): array
    {
        $urls = [];

        foreach ($this->stores as $store) {
            $products = $this->getProducts($store->getId());
            foreach ($products as $product) {
                $urls[] = $this->urlFixer->run($store, $product->getProductUrl());
            }
        }

        return $urls;
    }

    /**
     * Returns product collection by given product SKUs
     * In case when product SKUs wasn't provided returns self::PRODUCT_LIMIT products
     *
     * @param $storeId
     * @return Collection
     */
    private function getProducts($storeId): Collection
    {
        /** @var Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addStoreFilter($storeId);

        if (count($this->productSku)) {
            $collection->addAttributeToFilter('sku', $this->productSku);
        } else {
            $collection->setPageSize($this->productLimit);
        }

        return $collection;
    }
}
