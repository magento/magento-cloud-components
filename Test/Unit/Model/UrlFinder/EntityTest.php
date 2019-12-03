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
    protected function setUp()
    {
        $this->urlFactoryMock = $this->createMock(UrlFactory::class);
        $this->urlFinderMock = $this->getMockForAbstractClass(UrlFinderInterface::class);
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

        $urlMock1 = $this->getMockForAbstractClass(UrlInterface::class);
        $urlMock1->expects($this->once())
            ->method('getUrl')
            ->with('/path1')
            ->willReturn('http://site1.com/path1');
        $urlMock1->expects($this->once())
            ->method('setScope')
            ->with('store1')
            ->willReturnSelf();
        $urlMock2 = $this->getMockForAbstractClass(UrlInterface::class);
        $urlMock2->expects($this->once())
            ->method('getUrl')
            ->with('/path2')
            ->willReturn('http://site2.com/path2');
        $urlMock2->expects($this->once())
            ->method('setScope')
            ->with('store2')
            ->willReturnSelf();

        $urlRewriteMock1 = $this->createMock(UrlRewrite::class);
        $urlRewriteMock1->expects($this->once())
            ->method('getRequestPath')
            ->willReturn('/path1');
        $urlRewriteMock2 = $this->createMock(UrlRewrite::class);
        $urlRewriteMock2->expects($this->once())
            ->method('getRequestPath')
            ->willReturn('/path2');

        $this->urlFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($urlMock1, $urlMock2);
        $this->urlFinderMock->expects($this->exactly(2))
            ->method('findAllByData')
            ->withConsecutive(
                [['store_id' => 'store1', 'entity_type' => 'category']],
                [['store_id' => 'store2', 'entity_type' => 'category']]
            )
            ->willReturnOnConsecutiveCalls(
                [$urlRewriteMock1],
                [$urlRewriteMock2]
            );

        $this->urlFixerMock->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive(
                [$storeMock1, 'http://site1.com/path1'],
                [$storeMock2, 'http://site2.com/path2']
            )
            ->willReturnOnConsecutiveCalls('http://site1.com/fixed/path1', 'http://site2.com/fixed/path2');

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
