<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\Cron;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class LogProcessList
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(ResourceConnection $resource, LoggerInterface $logger)
    {
        $this->connection = $resource->getConnection();
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->connection->query('SHOW PROCESSLIST;')->fetchAll();

        $this->logger->debug('Log processlist', [
            'data' => $result
        ]);
    }
}
