<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Test\Unit\Model\UrlFinder;

use Magento\CloudComponents\Model\UrlFinder\Entity;
use Magento\CloudComponents\Model\UrlFixer;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @inheritDoc
 */
class EntityTest extends TestCase
{
    /**
     * @var UrlFactory|MockObject
     */
    private $urlFactoryMock;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $urlFinderMock;

    /**
     * @var UrlFixer|MockObject
     */
    private $urlFixerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->urlFactoryMock = $this->createMock(UrlFactory::class);
        $this->urlFinderMock = $this->createMock(UrlFinderInterface::class);
        $this->urlFixerMock = $this->createMock(UrlFixer::class);
    }

    public function testGetEmptyStores()
    {
        $this->urlFactoryMock->expects($this->never())
            ->method('create');

        $entity = $this->createEntity('category', []);

        $this->assertEquals([], $entity->get());
    }

    public function testGet()
    {
        $storeMock1 = $this->createMock(Store::class);
        $storeMock1->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('store1');
        $storeMock2 = $this->createMock(Store::class);
        $storeMock2->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('store2');

        $urlMock1 = $this->createMock(UrlInterface::class);
        $urlMock1
            ->expects($this->any())
            ->method('setScope')
            ->with('store1')
            ->willReturnSelf();
        $urlMock1
            ->expects($this->any())
            ->method('getUrl')
            ->with('/path1')
            ->willReturn('http://site1.com/path1');

        $urlMock2 = $this->createMock(UrlInterface::class);
        $urlMock2
            ->expects($this->any())
            ->method('getUrl')
            ->with('/path2')
            ->willReturn('http://site2.com/path2');
        $urlMock2
            ->expects($this->any())
            ->method('setScope')
            ->with('store2')
            ->willReturnSelf();

        $urlRewriteMock1 = $this->createMock(UrlRewrite::class);
        $urlRewriteMock1
            ->expects($this->once())
            ->method('getRequestPath')
            ->willReturn('/path1');
        $urlRewriteMock2 = $this->createMock(UrlRewrite::class);
        $urlRewriteMock2
            ->expects($this->once())
            ->method('getRequestPath')
            ->willReturn('/path2');

        $this->urlFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(function () use ($urlMock1, $urlMock2) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    return $urlMock1;
                }

                return $urlMock2;
            });

        $this->urlFinderMock
            ->expects($this->exactly(2))
            ->method('findAllByData')
            ->willReturnCallback(function ($data) use ($urlRewriteMock1, $urlRewriteMock2) {
                $expected1 = ['store_id' => 'store1', 'entity_type' => 'category'];
                $expected2 = ['store_id' => 'store2', 'entity_type' => 'category'];

                if (array_intersect_assoc($expected1, $data) == $expected1) {
                    return [$urlRewriteMock1];
                }

                if (array_intersect_assoc($expected2, $data) == $expected2) {
                    return [$urlRewriteMock2];
                }

                return [];
            });

        $this->urlFixerMock
            ->expects($this->exactly(2))
            ->method('run')
            ->willReturnCallback(function ($store, $url) use ($storeMock1, $storeMock2) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1 && $store === $storeMock1 && $url === '/path1') {
                    return 'http://site1.com/fixed/path1';
                }

                if ($callCount === 2 && $store === $storeMock2 && $url === '/path2') {
                    return 'http://site2.com/fixed/path2';
                }

                return '';
            });

        $entity = $this->createEntity('category', [$storeMock1, $storeMock2]);

        $this->assertEquals(
            [
                'http://site1.com/fixed/path1',
                'http://site2.com/fixed/path2',
            ],
            $entity->get()
        );
    }

    /**
     * @param string $entityType
     * @param array $stores
     * @return Entity
     */
    private function createEntity(string $entityType, array $stores): Entity
    {
        return new Entity(
            $this->urlFactoryMock,
            $this->urlFinderMock,
            $this->urlFixerMock,
            $entityType,
            $stores
        );
    }
}
