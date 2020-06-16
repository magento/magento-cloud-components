<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Test\Unit\Model;

use Magento\CloudComponents\Model\UrlFinder\Entity;
use Magento\CloudComponents\Model\UrlFinder\Product;
use Magento\CloudComponents\Model\UrlFinderFactory;
use Magento\CloudComponents\Model\UrlFinderInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @inheritDoc
 */
class UrlFinderFactoryTest extends TestCase
{
    /**
     * @var UrlFinderFactory
     */
    private $urlFinderFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->urlFinderFactory = new UrlFinderFactory($this->objectManagerMock);
    }

    public function testCreateCategoryOrCmsPageEntity(): void
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Entity::class, [
                'entityType' => 'category',
                'stores' => ['store1'],
            ])
            ->willReturn($this->getMockForAbstractClass(UrlFinderInterface::class));

        $this->urlFinderFactory->create('category', [
            'stores' => ['store1'],
        ]);
    }

    public function testCreateProductEntity(): void
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Product::class, [
                'stores' => ['store1'],
                'productSku' => ['sku1', 'sku2'],
                'productLimit' => 100
            ])
            ->willReturn($this->getMockForAbstractClass(UrlFinderInterface::class));

        $this->urlFinderFactory->create('product', [
            'stores' => ['store1'],
            'productSku' => ['sku1', 'sku2'],
            'productLimit' => 100
        ]);
    }

    public function testCreateWrongType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Wrong entity type.');

        $this->urlFinderFactory->create('wrong_type', []);
    }
}
