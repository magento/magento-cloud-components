<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\Cron;

use Magento\Framework\App\ResourceConnection;

class LogProcessList
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    public function execute()
    {
        $result = $this->connection->query('SHOW PROCESSLIST;')->fetchAll();

        die(var_dump($result));
    }
}
