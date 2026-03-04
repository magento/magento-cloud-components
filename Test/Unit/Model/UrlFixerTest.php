<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Test\Unit\Model;

use Magento\CloudComponents\Model\UrlFixer;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @inheritDoc
 */
class UrlFixerTest extends TestCase
{
    /**
     * @var UrlFixer
     */
    private $urlFixer;

    /**
     * @var MockObject|Store
     */
    private $storeMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig', 'getData'])
            ->getMock();
        $this->urlFixer = new UrlFixer();
    }

    /**
     * Tests URL fixing behavior based on rewrite configuration flags.
     *
     * @param bool $rewritesDisabled
     * @param bool $useConfigRewrites
     * @param string $url
     * @param string $expectedUrl
     * @dataProvider runDataProvider
     */
    #[DataProvider('runDataProvider')]
    public function testRunWithConfigRewrites(
        string $url,
        string $expectedUrl,
        bool $rewritesDisabled = false,
        bool $useConfigRewrites = true
    ) {
        $this->storeMock->expects($this->once())
            ->method('getData')
            ->willReturn($rewritesDisabled);

        if (!$rewritesDisabled) {
            $this->storeMock->expects($this->once())
                ->method('getConfig')
                ->with(Store::XML_PATH_USE_REWRITES)
                ->willReturn($useConfigRewrites);
        } else {
            $this->storeMock->expects($this->never())
                ->method('getConfig');
        }

        $this->assertEquals($expectedUrl, $this->urlFixer->run($this->storeMock, $url));
    }

    /**
     * Provides test cases for URL rewrite handling.
     *
     * @return array
     */
    public static function runDataProvider(): array
    {
        return [
            'rewrites enabled, url without "magento" part' => [
                'http://example.com/',
                'http://example.com',
            ],
            'rewrites disabled, url without "magento" part' => [
                'http://example.com/',
                'http://example.com',
                true,
                true,
            ],
            'rewrites enabled, url with "magento" part' => [
                'http://example.com/magento/',
                'http://example.com/magento',
                false,
                true,
            ],
            'rewrites disabled in store, url with "magento" part' => [
                'http://example.com/magento/',
                'http://example.com/',
                true,
                false,
            ],
            'rewrites disabled in config, url with "magento" part' => [
                'http://example.com/magento/',
                'http://example.com/',
                false,
                false,
            ],
            'rewrites disabled, url with multiple "magento" part' => [
                'http://example.com/magento/magento/magento/test.html',
                'http://example.com/magento/magento/test.html',
                true,
                false,
            ],
            'rewrites disabled, url with "magento2" part' => [
                'http://example.com/magento2/',
                'http://example.com/magento2',
                true,
                false,
            ],
            'rewrites disabled, with "magento" host' => [
                'http://magento.com/magento2/',
                'http://magento.com/magento2',
                true,
                false,
            ],
        ];
    }
}
