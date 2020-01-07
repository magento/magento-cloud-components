<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model;

/**
 * Uses for finding urls by given parameters.
 */
interface UrlFinderInterface
{
    /**
     * @return array
     */
    public function get(): array;
}
