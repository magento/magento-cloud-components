<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model\Cache;

use Magento\Framework\App\Request\Http as HttpRequest;
use Psr\Log\LoggerInterface as Logger;
use Magento\CloudComponents\Model\DebugTrace;

/**
 * Log cache invalidation to a file
 */
class InvalidateLogger extends \Magento\Framework\Cache\InvalidateLogger
{
    /**
     * @var string[]
     */
    private $tagsToLog = [
        'cat_p',
        'cat_c',
        'PRODUCT_PRICE',
        'cms_b',
        'cms_p',
        'config_scopes',
        'eav',
        'eav_attribute',
        'fpc',
        'review_block',
        'SEARCH_QUERY',
        'search_query',
        'store_group',
        'store',
        'store_relations',
        'website',
        'CORE_DESIGN',
        'core_design',
        'WEBSERVICE',
        'webservice',
        'banner',
        'catalog_event',
        'config',
        'block_html',
        'COLLECTION_DATA',
        'collection_data',
        'collections',
        'layout_general_cache_tag',
        'layout',
        'compiled_config',
        'acl_cache',
        'reflection',
        'db_ddl',
        'LOCKED_RECORD_INFO_SYSTEM_CONFIG',
        'all'
    ];

    /**
     * @var DebugTrace
     */
    private $debugTrace;

    /**
     * @param HttpRequest $request
     * @param Logger $logger
     * @param DebugTrace $debugTrace
     */
    public function __construct(
        HttpRequest $request,
        Logger $logger,
        DebugTrace $debugTrace
    ) {
        parent::__construct($request, $logger);
        $this->debugTrace = $debugTrace;
    }

    /**
     * Log cache invalidation to a file
     *
     * @param mixed $invalidateInfo
     */
    public function execute($invalidateInfo)
    {
        $needTrace = false;
        if (is_array($invalidateInfo) && isset($invalidateInfo['tags'])) {
            foreach ($invalidateInfo['tags'] as $tag) {
                if (in_array(strtolower($tag), $this->tagsToLog)) {
                    $needTrace = true;
                }
            }

            if ($needTrace) {
                $invalidateInfo['trace'] = $this->debugTrace->getTrace();
            }
        }
        parent::execute($invalidateInfo);
    }
}
