<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CloudComponents\Model;

/**
 * Class to get compressed debug back trace
 */
class DebugTrace
{
    /**
     * List of useless classes
     *
     * @var string[]
     */
    private $notAllowedClasses = [
        'Symfony\Component\Console\Application',
        'Magento\Framework\Console\Cli',
        'Symfony\Component\Console\Command\Command',
        'Magento\Staging\Model\Event\Manager\Proxy',
        'Magento\Staging\Model\Event\Manager',
        'Magento\Framework\Event\Invoker\InvokerDefault',
        'Magento\CloudComponents\Model\Observer\CacheFlushAll',
        'Magento\CloudComponents\Model\Cache\InvalidateLogger',
        'Magento\CloudComponents\Model\Cache\InvalidateLogger',
        'Magento\CloudComponents\Model\Indexation\Logger',
        'Magento\Framework\Cache\Frontend\Decorator\Logger',
        'Magento\Framework\Cache\Frontend\Decorator\Bare',
        'Magento\Framework\App\Cache\Type\AccessProxy',
        'Magento\Framework\Cache\Frontend\Decorator\TagScope',
        'Magento\Framework\ObjectManager\ObjectManager',
        'Magento\Framework\ObjectManager\Config\Compiled',
        'Magento\Framework\ObjectManager\Config\Config',
        'Magento\Framework\ObjectManager\Factory\Dynamic\Developer',
        'Magento\Framework\ObjectManager\Factory\Dynamic\Production'
    ];

    /**
     * List of useless functions
     *
     * @var string[]
     */
    private $notAllowedFunctions = [
        '___callPlugins',
        '___callParent',
        'Magento\Framework\Interception\{closure}'
    ];

    /**
     * Returns debug back trace
     *
     * @return array
     */
    public function getTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($trace as $index => $line) {
            if (!isset($line['function'], $line['class'], $line['file'])) {
                continue;
            }
            if (in_array($line['function'], $this->notAllowedFunctions)
                || in_array($line['class'], $this->notAllowedClasses)
                || strpos($line['file'], 'Interceptor.php') !== false
            ) {
                unset($trace[$index]);
            }
            unset($trace[$index]['type']);
        }

        if (function_exists('gzcompress')) {
            return bin2hex(
                gzcompress(
                    print_r(
                        $trace,
                        true
                    )
                )
            );
        }
        return $trace;
    }
}
