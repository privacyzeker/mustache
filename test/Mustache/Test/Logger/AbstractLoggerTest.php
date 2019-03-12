<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @group unit
 */
class Mustache_Test_Logger_AbstractLoggerTest extends PHPUnit_Framework_TestCase
{
        public function testEverything()
        {
                $logger = new Mustache_Test_Logger_TestLogger();

                $logger->emergency('emergency message');
                $logger->alert('alert message');
                $logger->critical('critical message');
                $logger->error('error message');
                $logger->warning('warning message');
                $logger->notice('notice message');
                $logger->info('info message');
                $logger->debug('debug message');

                $expected = [
                        [\Mustache\Logger::EMERGENCY, 'emergency message', []],
                        [\Mustache\Logger::ALERT, 'alert message', []],
                        [\Mustache\Logger::CRITICAL, 'critical message', []],
                        [\Mustache\Logger::ERROR, 'error message', []],
                        [\Mustache\Logger::WARNING, 'warning message', []],
                        [\Mustache\Logger::NOTICE, 'notice message', []],
                        [\Mustache\Logger::INFO, 'info message', []],
                        [\Mustache\Logger::DEBUG, 'debug message', []],
                ];

                $this->assertEquals($expected, $logger->log);
        }
}

class Mustache_Test_Logger_TestLogger extends \Mustache\Logger\AbstractLogger
{
        public $log = [];


        /**
         * Logs with an arbitrary level.
         *
         * @param mixed  $level
         * @param string $message
         * @param array  $context
         */
        public function log($level, $message, array $context = [])
        {
                $this->log[] = [$level, $message, $context];
        }
}
