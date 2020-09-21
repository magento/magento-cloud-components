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
use Cm_Cache_Backend_Redis as Backend;

/**
 * Performs force key eviction with a "scan" command.
 */
class Evictor
{
    const DEFAULT_EVICTION_LIMIT = 10000;
    const DEFAULT_SLEEP_TIMEOUT = 20000;
    const CONFIG_PATH_ENABLED = 'cache_evict/enabled';
    const CONFIG_PATH_LIMIT = 'cache_evict/limit';

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
        $options = $this->deploymentConfig->getConfigData(FrontendPool::KEY_CACHE)[FrontendPool::KEY_FRONTEND_CACHE]
            ?? [];
        $evictedKeys = 0;

        foreach ($options as $name => $cacheConfig) {
            $this->logger->info(sprintf(
                'Scanning keys for "%s" database',
                $name
            ));

            if (!$this->isCacheConfigValid($cacheConfig)) {
                $this->logger->debug(sprintf(
                    'Cache config for database "%s" config is not valid',
                    $name
                ));

                continue;
            }

            $dbKeys = $this->run(
                (string)$this->getCacheConfigValue($name, $cacheConfig, 'server'),
                (int)$this->getCacheConfigValue($name, $cacheConfig, 'port'),
                (int)$this->getCacheConfigValue($name, $cacheConfig, 'database')
            );
            $evictedKeys += $dbKeys;

            $this->logger->info(sprintf('Keys scanned: %s', $dbKeys));
        }

        return $evictedKeys;
    }

    /**
     * Get Cache Config Value
     *
     * @param array $cacheConfig
     * @param string $configKey
     * @return string
     */
    private function getCacheConfigValue($cacheConfig, $configKey): string
    {
        if (isset($cacheConfig['backend_options'][$configKey])) {
            return $cacheConfig['backend_options'][$configKey];
        }
        if (isset($cacheConfig['backend_options']['remote_backend_options'][$configKey])) {
            return $cacheConfig['backend_options']['remote_backend_options'][$configKey];
        }
        return '';
    }

    /**
     * Validate Cache Configuration
     *
     * @param $cacheConfig
     * @return bool
     */
    private function isCacheConfigValid($cacheConfig): bool
    {
        if ($this->getCacheConfigValue($cacheConfig, 'server')
            && $this->getCacheConfigValue($cacheConfig, 'port')
            && $this->getCacheConfigValue($cacheConfig, 'database')
        ) {
            return true;
        }
        return false;
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
            $keys = $client->scan(
                $iterator,
                Backend::PREFIX_KEY . '*',
                (int)$this->deploymentConfig->get(self::CONFIG_PATH_LIMIT, self::DEFAULT_EVICTION_LIMIT)
            );

            if ($keys === false) {
                $this->logger->debug('Reached end');
            } else {
                $keysCount = count($keys);
                $evictedKeys += $keysCount;
            }

            /* Give Redis some time to handle other requests */
            usleep(self::DEFAULT_SLEEP_TIMEOUT);
        } while ($iterator > 0);

        return $evictedKeys;
    }
}
