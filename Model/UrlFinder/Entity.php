<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\UrlFinder;

use Magento\CloudComponents\Model\UrlFinderInterface as CloudUrlFinderInterface;
use Magento\CloudComponents\Model\UrlFixer;
use Magento\Store\Api\Data\StoreInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\UrlFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * Returns list of url by store id and entity type
 */
class Entity implements CloudUrlFinderInterface
{
    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var UrlFixer
     */
    private $urlFixer;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var StoreInterface[]
     */
    private $stores;

    /**
     * @param UrlFactory $urlFactory
     * @param UrlFinderInterface $urlFinder
     * @param UrlFixer $urlFixer
     * @param string $entityType
     * @param StoreInterface[] $stores
     */
    public function __construct(
        UrlFactory $urlFactory,
        UrlFinderInterface $urlFinder,
        UrlFixer $urlFixer,
        string $entityType,
        array $stores
    ) {
        $this->urlFactory = $urlFactory;
        $this->urlFinder = $urlFinder;
        $this->urlFixer = $urlFixer;
        $this->entityType = $entityType;
        $this->stores = $stores;
    }

    /**
     * Returns list of url by store ids and entity type
     *
     * @return array
     */
    public function get(): array
    {
        $urls = [];

        foreach ($this->stores as $store) {
            $url = $this->urlFactory->create()->setScope($store->getId());

            $entities = $this->urlFinder->findAllByData([
                UrlRewrite::STORE_ID => $store->getId(),
                UrlRewrite::ENTITY_TYPE => $this->entityType
            ]);

            foreach ($entities as $urlRewrite) {
                $urls[] = $this->urlFixer->run($store, $url->getUrl($urlRewrite->getRequestPath()));
            }
        }

        return $urls;
    }
}
