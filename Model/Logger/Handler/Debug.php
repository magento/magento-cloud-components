<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\Logger\Handler;

/**
 * Debug handler which doesn't require debug mode enabled
 */
class Debug extends \Magento\Framework\Logger\Handler\Debug
{
    /**
     * @param array $record
     * @return mixed
     */
    public function isHandling(array $record): bool
    {
        return parent::isHandling($record);
    }
}
