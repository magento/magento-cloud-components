<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Cache\InvalidateLogger;

class CacheFlushAll implements ObserverInterface
{
    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * @param InvalidateLogger $logger
     */
    public function __construct(InvalidateLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Apply model save operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->execute(['tags' => ['all']]);
    }
}
