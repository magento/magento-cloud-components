<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model\Indexation;

use Magento\CloudComponents\Model\DebugTrace;
use Magento\Framework\Indexer\ActionInterface;
use Psr\Log\LoggerInterface;

/**
 * Log full re-indexation to a file
 */
class Logger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DebugTrace
     */
    private $debugTrace;

    /**
     * @param LoggerInterface $logger
     * @param DebugTrace $debugTrace
     */
    public function __construct(
        LoggerInterface $logger,
        DebugTrace $debugTrace
    ) {
        $this->logger = $logger;
        $this->debugTrace = $debugTrace;
    }

    /**
     * Log full re-indexation to a file
     *
     * @param ActionInterface $subject
     */
    public function afterExecuteFull(ActionInterface $subject)
    {
        $this->logger->debug(
            'full_indexation: ' . get_class($subject),
            [
                'trace' => $this->debugTrace->getTrace()
            ]
        );
    }
}
