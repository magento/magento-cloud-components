<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model\Logger\Handler;

class Debug extends \Magento\Framework\Logger\Handler\Debug
{
    /**
     * @inheritdoc
     */
    public function isHandling(array $record)
    {
        return parent::isHandling($record);
    }
}
