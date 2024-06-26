<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShopBundle\Service\Log;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PrestaShop\PrestaShop\Adapter\LegacyLogger;
use Symfony\Component\DependencyInjection\Container;

/**
 * @phpstan-import-type LevelName from Logger
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type Record from Logger
 *
 * @phpstan-type FormattedRecord array{message: string, context: mixed[], level: Level, level_name: LevelName, channel: string, datetime: \DateTimeImmutable, extra: mixed[], formatted: mixed}
 */
class LogHandler extends AbstractProcessingHandler
{
    protected $container;

    public function __construct(Container $container, $level = Logger::DEBUG, $bubble = true)
    {
        $this->container = $container;
        parent::__construct($level, $bubble);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @phpstan-param FormattedRecord $record
     */
    protected function write(array $record): void
    {
        /** @var LegacyLogger $logger */
        $logger = $this->container->get('prestashop.adapter.legacy.logger');
        $logger->log($record['level'], $record['message'], $record['context']);
    }
}
