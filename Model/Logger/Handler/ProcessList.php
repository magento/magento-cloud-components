<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Process list logger.
 */
class ProcessList extends Base
{
    const LEVEL = 1000;

    /**
     * @var string
     */
    protected $fileName = '/var/log/process_list.log';

    /**
     * @var int
     */
    protected $loggerType = self::LEVEL;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DriverInterface $filesystem
     * @param DeploymentConfig $deploymentConfig
     * @param string $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        DeploymentConfig $deploymentConfig,
        $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);

        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function isHandling(array $record)
    {
        if ($this->deploymentConfig->isAvailable()) {
            return
                $record['level'] === $this->level
//                && $this->isLoggingEnabled()
                ;
        }

        return parent::isHandling($record);
    }

    /**
     * Check that logging functionality is enabled.
     *
     * @return bool
     */
    private function isLoggingEnabled(): bool
    {
        return (bool)$this->deploymentConfig->get('dev/debug/process_list_logging');
    }
}
