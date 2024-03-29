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
 * @group lambdas
 * @group functional
 */
class Mustache_Test_Functional_HigherOrderSectionsTest extends Mustache_Test_FunctionalTestCase
{
        private $mustache;


        public function setUp()
        {
                $this->mustache = new \Mustache\Engine();
        }


        /**
         * @dataProvider sectionCallbackData
         */
        public function testSectionCallback($data, $tpl, $expect)
        {
                $this->assertEquals($expect, $this->mustache->render($tpl, $data));
        }


        public function sectionCallbackData()
        {
                $foo = new Mustache_Test_Functional_Foo();
                $foo->doublewrap = [$foo, 'wrapWithBoth'];

                $bar = new Mustache_Test_Functional_Foo();
                $bar->trimmer = [get_class($bar), 'staticTrim'];

                return [
                        [$foo, '{{#doublewrap}}{{name}}{{/doublewrap}}', sprintf('<strong><em>%s</em></strong>', $foo->name)],
                        [$bar, '{{#trimmer}}   {{name}}   {{/trimmer}}', $bar->name],
                ];
        }


        public function testViewArraySectionCallback()
        {
                $tpl = $this->mustache->loadTemplate('{{#trim}}    {{name}}    {{/trim}}');

                $foo = new Mustache_Test_Functional_Foo();

                $data = [
                        'name' => 'Bob',
                        'trim' => [get_class($foo), 'staticTrim'],
                ];

                $this->assertEquals($data['name'], $tpl->render($data));
        }


        public function testMonsters()
        {
                $tpl = $this->mustache->loadTemplate('{{#title}}{{title}} {{/title}}{{name}}');

                $frank = new Mustache_Test_Functional_Monster();
                $frank->title = 'Dr.';
                $frank->name = 'Frankenstein';
                $this->assertEquals('Dr. Frankenstein', $tpl->render($frank));

                $dracula = new Mustache_Test_Functional_Monster();
                $dracula->title = 'Count';
                $dracula->name = 'Dracula';
                $this->assertEquals('Count Dracula', $tpl->render($dracula));
        }


        public function testPassthroughOptimization()
        {
                $mustache = $this->createMock('\Mustache\Engine');
                $mustache->expects($this->never())
                        ->method('loadLambda');

                $tpl = $mustache->loadTemplate('{{#wrap}}NAME{{/wrap}}');

                $foo = new Mustache_Test_Functional_Foo();
                $foo->wrap = [$foo, 'wrapWithEm'];

                $this->assertEquals('<em>NAME</em>', $tpl->render($foo));
        }


        public function testWithoutPassthroughOptimization()
        {
                $mustache = $this->createMock('\Mustache\Engine');
                $mustache->expects($this->once())
                        ->method('loadLambda')
                        ->will($this->returnValue($mustache->loadTemplate('<em>{{ name }}</em>')));

                $tpl = $mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

                $foo = new Mustache_Test_Functional_Foo();
                $foo->wrap = [$foo, 'wrapWithEm'];

                $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
        }


        /**
         * @dataProvider cacheLambdaTemplatesData
         */
        public function testCacheLambdaTemplatesOptionWorks($dirName, $tplPrefix, $enable, $expect)
        {
                $cacheDir = $this->setUpCacheDir($dirName);
                $mustache = new \Mustache\Engine([
                        'template_class_prefix'  => $tplPrefix,
                        'cache'                  => $cacheDir,
                        'cache_lambda_templates' => $enable,
                ]);

                $tpl = $mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');
                $foo = new Mustache_Test_Functional_Foo();
                $foo->wrap = [$foo, 'wrapWithEm'];
                $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
                $this->assertCount($expect, glob($cacheDir . '/*.php'));
        }


        protected function setUpCacheDir($name)
        {
                $cacheDir = self::$tempDir . '/' . $name;
                if (file_exists($cacheDir)) {
                        self::rmdir($cacheDir);
                }
                mkdir($cacheDir, 0777, true);

                return $cacheDir;
        }


        public function cacheLambdaTemplatesData()
        {
                return [
                        ['test_enabling_lambda_cache', '_TestEnablingLambdaCache_', true, 2],
                        ['test_disabling_lambda_cache', '_TestDisablingLambdaCache_', false, 1],
                ];
        }
}

class Mustache_Test_Functional_Foo
{
        public $name = 'Justin';
        public $lorem = 'Lorem ipsum dolor sit amet,';


        public static function staticTrim($text)
        {
                return trim($text);
        }


        public function wrapWithBoth($text)
        {
                return self::wrapWithStrong(self::wrapWithEm($text));
        }


        /**
         * @param string $text
         *
         * @return string
         */
        public function wrapWithStrong($text)
        {
                return sprintf('<strong>%s</strong>', $text);
        }


        public function wrapWithEm($text)
        {
                return sprintf('<em>%s</em>', $text);
        }
}

class Mustache_Test_Functional_Monster
{
        public $title;
        public $name;
}
