<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model\Cache;

/**
 * Invalidate logger cache.
 */
class InvalidateLogger extends \Magento\Framework\Cache\InvalidateLogger
{
    /**
     * @param mixed $invalidateInfo
     */
    public function execute($invalidateInfo)
    {
        $invalidateInfo['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        parent::execute($invalidateInfo);
    }
}
