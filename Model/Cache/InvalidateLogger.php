<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model\Cache;

/**
 * Log cache invalidation to a file
 */
class InvalidateLogger extends \Magento\Framework\Cache\InvalidateLogger
{
    /**
     * Log cache invalidation to a file
     *
     * @param mixed $invalidateInfo
     */
    public function execute($invalidateInfo)
    {
        if (is_array($invalidateInfo)) {
            $invalidateInfo['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } elseif (is_string($invalidateInfo)) {
            $invalidateInfo = [
                'main' => $invalidateInfo,
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            ];
        }

        parent::execute($invalidateInfo);
    }
}
