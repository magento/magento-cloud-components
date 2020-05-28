<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model\Indexation;

use Magento\Framework\Indexer\ActionInterface;
use Psr\Log\LoggerInterface;

class Logger
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
     * @param ActionInterface $subject
     */
    public function afterExecuteFull(ActionInterface $subject)
    {
        $this->logger->debug(
            'full_indexation: ' . get_class($subject),
            [
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            ]
        );
    }
}
