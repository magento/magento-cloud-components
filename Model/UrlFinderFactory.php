<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

/**
 * Creates UrlFinder objects depends on entity type.
 */
class UrlFinderFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private static $classMap = [
        Rewrite::ENTITY_TYPE_CMS_PAGE => UrlFinder\Entity::class,
        Rewrite::ENTITY_TYPE_CATEGORY => UrlFinder\Entity::class,
        Rewrite::ENTITY_TYPE_PRODUCT => UrlFinder\Product::class,
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates UrlFinder objects depends on entity type.
     *
     * @param string $entityType
     * @param array $data
     * @return mixed
     */
    public function create(string $entityType, array $data): UrlFinderInterface
    {
        if (!isset(self::$classMap[$entityType])) {
            throw new \UnexpectedValueException('Wrong entity type.');
        }

        if ($entityType === Rewrite::ENTITY_TYPE_PRODUCT) {
            return $this->objectManager->create(self::$classMap[$entityType], [
                'stores' => $data['stores'],
                'productSku' => $data['productSku'],
                'productLimit' => $data['productLimit']
            ]);
        }

        return $this->objectManager->create(self::$classMap[$entityType], [
            'entityType' => $entityType,
            'stores' => $data['stores']
        ]);
    }
}
