<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Console\Command;

use Magento\CloudComponents\Model\ConstantList;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Changes DB health logging status
 */
class ConfigSetLogDbHealthCommand extends Command
{
    const NAME = 'config:set:log-db-health';

    const ARG_VALUE = 'value';

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @param Writer $writer
     */
    public function __construct(Writer $writer)
    {
        $this->writer = $writer;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Log DB health to file')
            ->addArgument(
                self::ARG_VALUE,
                InputArgument::REQUIRED,
                'Value (1/0)'
            );
    }

    /**
     * {@inheritDoc}
     *
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $value = (int)$input->getArgument(self::ARG_VALUE);

        $this->writer->saveConfig(
            [ConfigFilePool::APP_ENV => [ConstantList::CONFIG_PATH_LOG_DB_HEALTH => $value]],
            true
        );

        $output->writeln('<info>Configuration saved.</info>');
    }
}
