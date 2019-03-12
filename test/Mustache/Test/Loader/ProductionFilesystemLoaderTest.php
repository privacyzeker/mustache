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
class Mustache_Test_Loader_ProductionFilesystemLoaderTest extends PHPUnit_Framework_TestCase
{
        public function testConstructor()
        {
                $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
                $loader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir, ['extension' => '.ms']);
                $this->assertInstanceOf('Mustache_Source', $loader->load('alpha'));
                $this->assertEquals('alpha contents', $loader->load('alpha')->getSource());
                $this->assertInstanceOf('Mustache_Source', $loader->load('beta.ms'));
                $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
        }


        public function testTrailingSlashes()
        {
                $baseDir = dirname(__FILE__) . '/../../../fixtures/templates/';
                $loader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir);
                $this->assertEquals('one contents', $loader->load('one')->getSource());
        }


        public function testConstructorWithProtocol()
        {
                $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

                $loader = new \Mustache\Loader\ProductionFilesystemLoader('file://' . $baseDir, ['extension' => '.ms']);
                $this->assertEquals('alpha contents', $loader->load('alpha')->getSource());
                $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
        }


        public function testLoadTemplates()
        {
                $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
                $loader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir);
                $this->assertEquals('one contents', $loader->load('one')->getSource());
                $this->assertEquals('two contents', $loader->load('two.mustache')->getSource());
        }


        public function testEmptyExtensionString()
        {
                $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

                $loader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir, ['extension' => '']);
                $this->assertEquals('one contents', $loader->load('one.mustache')->getSource());
                $this->assertEquals('alpha contents', $loader->load('alpha.ms')->getSource());

                $loader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir, ['extension' => null]);
                $this->assertEquals('two contents', $loader->load('two.mustache')->getSource());
                $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
        }


        /**
         * @expectedException \Mustache\Exception\RuntimeException
         */
        public function testMissingBaseDirThrowsException()
        {
                new \Mustache\Loader\ProductionFilesystemLoader(dirname(__FILE__) . '/not_a_directory');
        }


        /**
         * @expectedException \Mustache\Exception\UnknownTemplateException
         */
        public function testMissingTemplateThrowsException()
        {
                $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
                $loader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir);

                $loader->load('fake');
        }


        public function testLoadWithDifferentStatProps()
        {
                $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
                $noStatLoader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir, ['stat_props' => null]);
                $mtimeLoader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir, ['stat_props' => ['mtime']]);
                $sizeLoader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir, ['stat_props' => ['size']]);
                $bothLoader = new \Mustache\Loader\ProductionFilesystemLoader($baseDir, ['stat_props' => ['mtime', 'size']]);

                $noStatKey = $noStatLoader->load('one.mustache')->getKey();
                $mtimeKey = $mtimeLoader->load('one.mustache')->getKey();
                $sizeKey = $sizeLoader->load('one.mustache')->getKey();
                $bothKey = $bothLoader->load('one.mustache')->getKey();

                $this->assertNotEquals($noStatKey, $mtimeKey);
                $this->assertNotEquals($noStatKey, $sizeKey);
                $this->assertNotEquals($noStatKey, $bothKey);
                $this->assertNotEquals($mtimeKey, $sizeKey);
                $this->assertNotEquals($mtimeKey, $bothKey);
                $this->assertNotEquals($sizeKey, $bothKey);
        }
}
