<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\DeploymentConfig;
use Psr\Log\LoggerInterface;
use Credis_Client as Client;

/**
 * Performs force key eviction with a "scan" command.
 */
class Evictor
{
    const EVICTION_LIMIT = 10000;
    const CONFIG_PATH = 'cache_evict/enabled';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param LoggerInterface $logger
     */
    public function __construct(DeploymentConfig $deploymentConfig, LoggerInterface $logger)
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->logger = $logger;
    }

    /**
     * Evicts all keys using iterator.
     *
     * @return int
     */
    public function evict(): int
    {
        $cacheOptions = $this->deploymentConfig->getConfigData(FrontendPool::KEY_CACHE)[FrontendPool::KEY_FRONTEND_CACHE] ?? [];
        $evictedKeys = 0;

        foreach ($cacheOptions as $name => $cacheConfig) {
            $this->logger->info(sprintf(
                'Evicting keys for "%s" database',
                $name
            ));

            $dbKeys = $this->run(
                $cacheConfig['backend_options']['server'],
                $cacheConfig['backend_options']['port'],
                $cacheConfig['backend_options']['database']
            );
            $evictedKeys += $dbKeys;

            $this->logger->info(sprintf('Keys evicted: %s', $dbKeys));
        }

        return $evictedKeys;
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $db
     * @return int
     */
    private function run(string $host, int $port, int $db): int
    {
        $client = new Client($host, $port, null, '', $db);
        $evictedKeys = 0;

        do {
            $keys = $client->scan($iterator, '*', self::EVICTION_LIMIT);

            if ($keys === false) {
                $this->logger->debug('Reached end');
            } else {
                $keysCount = count($keys);
                $evictedKeys += $keysCount;
            }
        } while ($iterator > 0);

        return $evictedKeys;
    }
}
