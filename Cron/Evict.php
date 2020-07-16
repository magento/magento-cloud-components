<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Cron;

use Magento\CloudComponents\Model\Cache\Evictor;
use Magento\Framework\App\DeploymentConfig;
use Psr\Log\LoggerInterface;

/**
 * The cron cprocess to evict keys.
 */
class Evict
{
    /**
     * @var Evictor
     */
    private $evictor;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Evictor $evictor
     * @param DeploymentConfig $deploymentConfig
     * @param LoggerInterface $logger
     */
    public function __construct(Evictor $evictor, DeploymentConfig $deploymentConfig, LoggerInterface $logger)
    {
        $this->evictor = $evictor;
        $this->deploymentConfig = $deploymentConfig;
        $this->logger = $logger;
    }

    /**
     * Perform keys eviction.
     */
    public function execute()
    {
        if (!$this->deploymentConfig->get(Evictor::CONFIG_PATH_ENABLED)) {
            $this->logger->info('Keys eviction is disabled');

            return;
        }

        $this->evictor->evict();
    }
}
