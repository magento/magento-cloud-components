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

/**
 * Log cache flush action to a file
 */
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
     * Log cache flush action to a file
     *
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->logger->execute(['tags' => ['all']]);
    }
}
