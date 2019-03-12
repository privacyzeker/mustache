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
class Mustache_Test_Logger_StreamLoggerTest extends PHPUnit_Framework_TestCase
{
        /**
         * @dataProvider acceptsStreamData
         */
        public function testAcceptsStream($name, $stream)
        {
                $logger = new \Mustache\Logger\StreamLogger($stream);
                $logger->log(\Mustache\Logger::CRITICAL, 'message');

                $this->assertEquals("CRITICAL: message\n", file_get_contents($name));
        }


        public function acceptsStreamData()
        {
                $one = tempnam(sys_get_temp_dir(), 'mustache-test');
                $two = tempnam(sys_get_temp_dir(), 'mustache-test');

                return [
                        [$one, $one],
                        [$two, fopen($two, 'a')],
                ];
        }


        /**
         * @expectedException \Mustache\Exception\LogicException
         */
        public function testPrematurelyClosedStreamThrowsException()
        {
                $stream = tmpfile();
                $logger = new \Mustache\Logger\StreamLogger($stream);
                fclose($stream);

                $logger->log(\Mustache\Logger::CRITICAL, 'message');
        }


        /**
         * @dataProvider getLevels
         */
        public function testLoggingThresholds($logLevel, $level, $shouldLog)
        {
                $stream = tmpfile();
                $logger = new \Mustache\Logger\StreamLogger($stream, $logLevel);
                $logger->log($level, 'logged');

                rewind($stream);
                $result = fread($stream, 1024);

                if ($shouldLog) {
                        $this->assertContains('logged', $result);
                } else {
                        $this->assertEmpty($result);
                }
        }


        public function getLevels()
        {
                // $logLevel, $level, $shouldLog
                return [
                        // identities
                        [\Mustache\Logger::EMERGENCY, \Mustache\Logger::EMERGENCY, true],
                        [\Mustache\Logger::ALERT, \Mustache\Logger::ALERT, true],
                        [\Mustache\Logger::CRITICAL, \Mustache\Logger::CRITICAL, true],
                        [\Mustache\Logger::ERROR, \Mustache\Logger::ERROR, true],
                        [\Mustache\Logger::WARNING, \Mustache\Logger::WARNING, true],
                        [\Mustache\Logger::NOTICE, \Mustache\Logger::NOTICE, true],
                        [\Mustache\Logger::INFO, \Mustache\Logger::INFO, true],
                        [\Mustache\Logger::DEBUG, \Mustache\Logger::DEBUG, true],

                        // one above
                        [\Mustache\Logger::ALERT, \Mustache\Logger::EMERGENCY, true],
                        [\Mustache\Logger::CRITICAL, \Mustache\Logger::ALERT, true],
                        [\Mustache\Logger::ERROR, \Mustache\Logger::CRITICAL, true],
                        [\Mustache\Logger::WARNING, \Mustache\Logger::ERROR, true],
                        [\Mustache\Logger::NOTICE, \Mustache\Logger::WARNING, true],
                        [\Mustache\Logger::INFO, \Mustache\Logger::NOTICE, true],
                        [\Mustache\Logger::DEBUG, \Mustache\Logger::INFO, true],

                        // one below
                        [\Mustache\Logger::EMERGENCY, \Mustache\Logger::ALERT, false],
                        [\Mustache\Logger::ALERT, \Mustache\Logger::CRITICAL, false],
                        [\Mustache\Logger::CRITICAL, \Mustache\Logger::ERROR, false],
                        [\Mustache\Logger::ERROR, \Mustache\Logger::WARNING, false],
                        [\Mustache\Logger::WARNING, \Mustache\Logger::NOTICE, false],
                        [\Mustache\Logger::NOTICE, \Mustache\Logger::INFO, false],
                        [\Mustache\Logger::INFO, \Mustache\Logger::DEBUG, false],
                ];
        }


        /**
         * @dataProvider getLogMessages
         */
        public function testLogging($level, $message, $context, $expected)
        {
                $stream = tmpfile();
                $logger = new \Mustache\Logger\StreamLogger($stream, \Mustache\Logger::DEBUG);
                $logger->log($level, $message, $context);

                rewind($stream);
                $result = fread($stream, 1024);

                $this->assertEquals($expected, $result);
        }


        public function getLogMessages()
        {
                // $level, $message, $context, $expected
                return [
                        [\Mustache\Logger::DEBUG, 'debug message', [], "DEBUG: debug message\n"],
                        [\Mustache\Logger::INFO, 'info message', [], "INFO: info message\n"],
                        [\Mustache\Logger::NOTICE, 'notice message', [], "NOTICE: notice message\n"],
                        [\Mustache\Logger::WARNING, 'warning message', [], "WARNING: warning message\n"],
                        [\Mustache\Logger::ERROR, 'error message', [], "ERROR: error message\n"],
                        [\Mustache\Logger::CRITICAL, 'critical message', [], "CRITICAL: critical message\n"],
                        [\Mustache\Logger::ALERT, 'alert message', [], "ALERT: alert message\n"],
                        [\Mustache\Logger::EMERGENCY, 'emergency message', [], "EMERGENCY: emergency message\n"],

                        // with context
                        [
                                \Mustache\Logger::ERROR,
                                'error message',
                                ['name' => 'foo', 'number' => 42],
                                "ERROR: error message\n",
                        ],

                        // with interpolation
                        [
                                \Mustache\Logger::ERROR,
                                'error {name}-{number}',
                                ['name' => 'foo', 'number' => 42],
                                "ERROR: error foo-42\n",
                        ],

                        // with iterpolation false positive
                        [
                                \Mustache\Logger::ERROR,
                                'error {nothing}',
                                ['name' => 'foo', 'number' => 42],
                                "ERROR: error {nothing}\n",
                        ],

                        // with interpolation injection
                        [
                                \Mustache\Logger::ERROR,
                                '{foo}',
                                ['foo' => '{bar}', 'bar' => 'FAIL'],
                                "ERROR: {bar}\n",
                        ],
                ];
        }


        public function testChangeLoggingLevels()
        {
                $stream = tmpfile();
                $logger = new \Mustache\Logger\StreamLogger($stream);

                $logger->setLevel(\Mustache\Logger::ERROR);
                $this->assertEquals(\Mustache\Logger::ERROR, $logger->getLevel());

                $logger->log(\Mustache\Logger::WARNING, 'ignore this');

                $logger->setLevel(\Mustache\Logger::INFO);
                $this->assertEquals(\Mustache\Logger::INFO, $logger->getLevel());

                $logger->log(\Mustache\Logger::WARNING, 'log this');

                $logger->setLevel(\Mustache\Logger::CRITICAL);
                $this->assertEquals(\Mustache\Logger::CRITICAL, $logger->getLevel());

                $logger->log(\Mustache\Logger::ERROR, 'ignore this');

                rewind($stream);
                $result = fread($stream, 1024);

                $this->assertEquals("WARNING: log this\n", $result);
        }


        /**
         * @expectedException \Mustache\Exception\InvalidArgumentException
         */
        public function testThrowsInvalidArgumentExceptionWhenSettingUnknownLevels()
        {
                $logger = new \Mustache\Logger\StreamLogger(tmpfile());
                $logger->setLevel('bacon');
        }


        /**
         * @expectedException \Mustache\Exception\InvalidArgumentException
         */
        public function testThrowsInvalidArgumentExceptionWhenLoggingUnknownLevels()
        {
                $logger = new \Mustache\Logger\StreamLogger(tmpfile());
                $logger->log('bacon', 'CODE BACON ERROR!');
        }
}
