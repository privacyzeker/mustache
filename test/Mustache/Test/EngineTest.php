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
class Mustache_Test_EngineTest extends Mustache_Test_FunctionalTestCase
{
        public function testConstructor()
        {
                $logger = new \Mustache\Logger\StreamLogger(tmpfile());
                $loader = new \Mustache\Loader\StringLoader();
                $partialsLoader = new \Mustache\Loader\ArrayLoader();
                $mustache = new \Mustache\Engine([
                        'template_class_prefix' => '__whot__',
                        'cache'                 => self::$tempDir,
                        'cache_file_mode'       => 777,
                        'logger'                => $logger,
                        'loader'                => $loader,
                        'partials_loader'       => $partialsLoader,
                        'partials'              => [
                                'foo' => '{{ foo }}',
                        ],
                        'helpers'               => [
                                'foo' => [$this, 'getFoo'],
                                'bar' => 'BAR',
                        ],
                        'escape'                => 'strtoupper',
                        'entity_flags'          => ENT_QUOTES,
                        'charset'               => 'ISO-8859-1',
                        'pragmas'               => [\Mustache\Engine::PRAGMA_FILTERS],
                ]);

                $this->assertSame($logger, $mustache->getLogger());
                $this->assertSame($loader, $mustache->getLoader());
                $this->assertSame($partialsLoader, $mustache->getPartialsLoader());
                $this->assertEquals('{{ foo }}', $partialsLoader->load('foo'));
                $this->assertContains('__whot__', $mustache->getTemplateClassName('{{ foo }}'));
                $this->assertEquals('strtoupper', $mustache->getEscape());
                $this->assertEquals(ENT_QUOTES, $mustache->getEntityFlags());
                $this->assertEquals('ISO-8859-1', $mustache->getCharset());
                $this->assertTrue($mustache->hasHelper('foo'));
                $this->assertTrue($mustache->hasHelper('bar'));
                $this->assertFalse($mustache->hasHelper('baz'));
                $this->assertInstanceOf(\Mustache\Cache\AbstractCache::class, $mustache->getCache());
                $this->assertEquals([\Mustache\Engine::PRAGMA_FILTERS], $mustache->getPragmas());
        }


        public static function getFoo()
        {
                return 'foo';
        }


        public function testRender()
        {
                $source = '{{ foo }}';
                $data = ['bar' => 'baz'];
                $output = 'TEH OUTPUT';

                $template = $this->getMockBuilder(\Mustache\Template::class)
                        ->disableOriginalConstructor()
                        ->getMock();

                $mustache = new MustacheStub();
                $mustache->template = $template;

                $template->expects($this->once())
                        ->method('render')
                        ->with($data)
                        ->will($this->returnValue($output));

                $this->assertEquals($output, $mustache->render($source, $data));
                $this->assertEquals($source, $mustache->source);
        }


        public function testSettingServices()
        {
                $logger = new \Mustache\Logger\StreamLogger(tmpfile());
                $loader = new \Mustache\Loader\StringLoader();
                $tokenizer = new \Mustache\Tokenizer();
                $parser = new \Mustache\Parser();
                $compiler = new \Mustache\Compiler();
                $mustache = new \Mustache\Engine();
                $cache = new \Mustache\Cache\FilesystemCache(self::$tempDir);

                $this->assertNotSame($logger, $mustache->getLogger());
                $mustache->setLogger($logger);
                $this->assertSame($logger, $mustache->getLogger());

                $this->assertNotSame($loader, $mustache->getLoader());
                $mustache->setLoader($loader);
                $this->assertSame($loader, $mustache->getLoader());

                $this->assertNotSame($loader, $mustache->getPartialsLoader());
                $mustache->setPartialsLoader($loader);
                $this->assertSame($loader, $mustache->getPartialsLoader());

                $this->assertNotSame($tokenizer, $mustache->getTokenizer());
                $mustache->setTokenizer($tokenizer);
                $this->assertSame($tokenizer, $mustache->getTokenizer());

                $this->assertNotSame($parser, $mustache->getParser());
                $mustache->setParser($parser);
                $this->assertSame($parser, $mustache->getParser());

                $this->assertNotSame($compiler, $mustache->getCompiler());
                $mustache->setCompiler($compiler);
                $this->assertSame($compiler, $mustache->getCompiler());

                $this->assertNotSame($cache, $mustache->getCache());
                $mustache->setCache($cache);
                $this->assertSame($cache, $mustache->getCache());
        }


        /**
         * @group functional
         */
        public function testCache()
        {
                $mustache = new \Mustache\Engine([
                        'template_class_prefix' => '__whot__',
                        'cache'                 => self::$tempDir,
                ]);

                $source = '{{ foo }}';
                $template = $mustache->loadTemplate($source);
                $className = $mustache->getTemplateClassName($source);

                $this->assertInstanceOf($className, $template);
        }


        public function testLambdaCache()
        {
                $mustache = new MustacheStub([
                        'cache'                  => self::$tempDir,
                        'cache_lambda_templates' => true,
                ]);

                $this->assertNotInstanceOf(\Mustache\Cache\NoopCache::class, $mustache->getProtectedLambdaCache());
                $this->assertSame($mustache->getCache(), $mustache->getProtectedLambdaCache());
        }


        public function testWithoutLambdaCache()
        {
                $mustache = new MustacheStub([
                        'cache' => self::$tempDir,
                ]);

                $this->assertInstanceOf(\Mustache\Cache\NoopCache::class, $mustache->getProtectedLambdaCache());
                $this->assertNotSame($mustache->getCache(), $mustache->getProtectedLambdaCache());
        }


        /**
         * @expectedException InvalidArgumentException
         */
        public function testEmptyTemplatePrefixThrowsException()
        {
                new \Mustache\Engine([
                        'template_class_prefix' => '',
                ]);
        }


        /**
         * @expectedException \Mustache\Exception\InvalidArgumentException
         * @dataProvider getBadEscapers
         */
        public function testNonCallableEscapeThrowsException($escape)
        {
                new \Mustache\Engine(['escape' => $escape]);
        }


        public function getBadEscapers()
        {
                return [
                        ['nothing'],
                        ['foo', 'bar'],
                ];
        }


        /**
         * @expectedException \Mustache\Exception\RuntimeException
         */
        public function testImmutablePartialsLoadersThrowException()
        {
                $mustache = new \Mustache\Engine([
                        'partials_loader' => new \Mustache\Loader\StringLoader(),
                ]);

                $mustache->setPartials(['foo' => '{{ foo }}']);
        }


        public function testMissingPartialsTreatedAsEmptyString()
        {
                $mustache = new \Mustache\Engine([
                        'partials_loader' => new \Mustache\Loader\ArrayLoader([
                                'foo' => 'FOO',
                                'baz' => 'BAZ',
                        ]),
                ]);

                $this->assertEquals('FOOBAZ', $mustache->render('{{>foo}}{{>bar}}{{>baz}}', []));
        }


        public function testHelpers()
        {
                $foo = [$this, 'getFoo'];
                $bar = 'BAR';
                $mustache = new \Mustache\Engine([
                        'helpers' => [
                                'foo' => $foo,
                                'bar' => $bar,
                        ],
                ]);

                $helpers = $mustache->getHelpers();
                $this->assertTrue($mustache->hasHelper('foo'));
                $this->assertTrue($mustache->hasHelper('bar'));
                $this->assertTrue($helpers->has('foo'));
                $this->assertTrue($helpers->has('bar'));
                $this->assertSame($foo, $mustache->getHelper('foo'));
                $this->assertSame($bar, $mustache->getHelper('bar'));

                $mustache->removeHelper('bar');
                $this->assertFalse($mustache->hasHelper('bar'));
                $mustache->addHelper('bar', $bar);
                $this->assertSame($bar, $mustache->getHelper('bar'));

                $baz = [$this, 'wrapWithUnderscores'];
                $this->assertFalse($mustache->hasHelper('baz'));
                $this->assertFalse($helpers->has('baz'));

                $mustache->addHelper('baz', $baz);
                $this->assertTrue($mustache->hasHelper('baz'));
                $this->assertTrue($helpers->has('baz'));

                // ... and a functional test
                $tpl = $mustache->loadTemplate('{{foo}} - {{bar}} - {{#baz}}qux{{/baz}}');
                $this->assertEquals('foo - BAR - __qux__', $tpl->render());
                $this->assertEquals('foo - BAR - __qux__', $tpl->render(['qux' => "won't mess things up"]));
        }


        public static function wrapWithUnderscores($text)
        {
                return '__' . $text . '__';
        }


        /**
         * @expectedException \Mustache\Exception\InvalidArgumentException
         */
        public function testSetHelpersThrowsExceptions()
        {
                $mustache = new \Mustache\Engine();
                $mustache->setHelpers('monkeymonkeymonkey');
        }


        /**
         * @expectedException \Mustache\Exception\InvalidArgumentException
         */
        public function testSetLoggerThrowsExceptions()
        {
                $mustache = new \Mustache\Engine();
                $mustache->setLogger(new StdClass());
        }


        public function testLoadPartialCascading()
        {
                $loader = new \Mustache\Loader\ArrayLoader([
                        'foo' => 'FOO',
                ]);

                $mustache = new \Mustache\Engine(['loader' => $loader]);

                $tpl = $mustache->loadTemplate('foo');

                $this->assertSame($tpl, $mustache->loadPartial('foo'));

                $mustache->setPartials([
                        'foo' => 'f00',
                ]);

                // setting partials overrides the default template loading fallback.
                $this->assertNotSame($tpl, $mustache->loadPartial('foo'));

                // but it didn't overwrite the original template loader templates.
                $this->assertSame($tpl, $mustache->loadTemplate('foo'));
        }


        public function testPartialLoadFailLogging()
        {
                $name = tempnam(sys_get_temp_dir(), 'mustache-test');
                $mustache = new \Mustache\Engine([
                        'logger'   => new \Mustache\Logger\StreamLogger($name, \Mustache\Logger::WARNING),
                        'partials' => [
                                'foo' => 'FOO',
                                'bar' => 'BAR',
                        ],
                ]);

                $result = $mustache->render('{{> foo }}{{> bar }}{{> baz }}', []);
                $this->assertEquals('FOOBAR', $result);

                $this->assertContains('WARNING: Partial not found: "baz"', file_get_contents($name));
        }


        public function testCacheWarningLogging()
        {
                list($name, $mustache) = $this->getLoggedMustache(\Mustache\Logger::WARNING);
                $mustache->render('{{ foo }}', ['foo' => 'FOO']);
                $this->assertContains('WARNING: Template cache disabled, evaluating', file_get_contents($name));
        }


        public function testLoggingIsNotTooAnnoying()
        {
                list($name, $mustache) = $this->getLoggedMustache();
                $mustache->render('{{ foo }}{{> bar }}', ['foo' => 'FOO']);
                $this->assertEmpty(file_get_contents($name));
        }


        public function testVerboseLoggingIsVerbose()
        {
                list($name, $mustache) = $this->getLoggedMustache(\Mustache\Logger::DEBUG);
                $mustache->render('{{ foo }}{{> bar }}', ['foo' => 'FOO']);
                $log = file_get_contents($name);
                $this->assertContains('DEBUG: Instantiating template: ', $log);
                $this->assertContains('WARNING: Partial not found: "bar"', $log);
        }


        /**
         * @expectedException \Mustache\Exception\InvalidArgumentException
         */
        public function testUnknownPragmaThrowsException()
        {
                new \Mustache\Engine([
                        'pragmas' => ['UNKNOWN'],
                ]);
        }


        public function testCompileFromMustacheSourceInstance()
        {
                $baseDir = realpath(dirname(__FILE__) . '/../../fixtures/templates');
                $mustache = new \Mustache\Engine([
                        'loader' => new \Mustache\Loader\ProductionFilesystemLoader($baseDir),
                ]);
                $this->assertEquals('one contents', $mustache->render('one'));
        }


        private function getLoggedMustache($level = \Mustache\Logger::ERROR)
        {
                $name = tempnam(sys_get_temp_dir(), 'mustache-test');
                $mustache = new \Mustache\Engine([
                        'logger' => new \Mustache\Logger\StreamLogger($name, $level),
                ]);

                return [$name, $mustache];
        }


        public function testCustomDelimiters()
        {
                $mustache = new \Mustache\Engine([
                        'delimiters' => '[[ ]]',
                        'partials'   => [
                                'one' => '[[> two ]]',
                                'two' => '[[ a ]]',
                        ],
                ]);

                $tpl = $mustache->loadTemplate('[[# a ]][[ b ]][[/a ]]');
                $this->assertEquals('c', $tpl->render(['a' => true, 'b' => 'c']));

                $tpl = $mustache->loadTemplate('[[> one ]]');
                $this->assertEquals('b', $tpl->render(['a' => 'b']));
        }
}

class MustacheStub extends \Mustache\Engine
{
        public $source;
        public $template;


        public function loadTemplate($source)
        {
                $this->source = $source;

                return $this->template;
        }


        public function getProtectedLambdaCache()
        {
                return $this->getLambdaCache();
        }
}
