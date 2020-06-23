<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Cron;

use Magento\CloudComponents\Model\Cache\Evictor;

class Eviction
{
    /**
     * @var Evictor
     */
    private $evictor;

    /**
     * @param Evictor $evictor
     */
    public function __construct(Evictor $evictor)
    {
        $this->evictor = $evictor;
    }

    /**
     * Perform keys eviction.
     */
    public function execute()
    {
        $this->evictor->evict();
    }
}
