<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Model;

use Magento\Store\Model\Store;

/**
 * Removes /magento/ part from url when rewrites for store are disabled.
 */
class UrlFixer
{
    /**
     * Removes SCRIPT_FILENAME (/magento/) from url if rewrites are disabled.
     *
     * @param Store $store
     * @param string $url
     * @return string
     */
    public function run(Store $store, $url): string
    {
        if (($store->getForceDisableRewrites() || !$store->getConfig(Store::XML_PATH_USE_REWRITES))
            && strpos($url, '/magento/') !== false
        ) {
            return preg_replace('|/magento/|', '/', $url, 1);
        }

        return rtrim($url, '/');
    }
}
