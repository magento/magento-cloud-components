<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Validator\Exception;
use Psr\Log\LoggerInterface;

/**
 * Log all indexers invalidations to a file
 */
class IndexerStateSaveAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log all indexers invalidations to a file
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $indexerState = $observer->getData('indexer_state');
        if ($indexerState->getData('status') !== $indexerState->getOrigData('status')
            && $indexerState->getData('status') === StateInterface::STATUS_INVALID
        ) {
            $this->logger->debug(
                'indexer_invalidation: ' . $indexerState->getData('indexer_id'),
                [
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
                ]
            );
        }
    }
}
