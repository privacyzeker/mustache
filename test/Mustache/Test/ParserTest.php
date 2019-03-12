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
class Mustache_Test_ParserTest extends PHPUnit_Framework_TestCase
{
        /**
         * @dataProvider getTokenSets
         */
        public function testParse($tokens, $expected)
        {
                $parser = new \Mustache\Parser();
                $this->assertEquals($expected, $parser->parse($tokens));
        }


        /**
         * @return array
         * @author <ted@privacyzeker.nl>
         */
        public function getTokenSets()
        {
                return [
                        [
                                [],
                                [],
                        ],

                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'text',
                                        ],
                                ],
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'text',
                                        ],
                                ],
                        ],

                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::LINE => 0,
                                                \Mustache\Tokenizer::NAME => 'name',
                                        ],
                                ],
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::LINE => 0,
                                                \Mustache\Tokenizer::NAME => 'name',
                                        ],
                                ],
                        ],

                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'foo',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_INVERTED,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::LINE => 0,
                                                \Mustache\Tokenizer::NAME => 'name',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 456,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'bar',
                                        ],
                                ],

                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'foo',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_INVERTED,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                                \Mustache\Tokenizer::END   => 456,
                                                \Mustache\Tokenizer::NODES => [
                                                        [
                                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                                \Mustache\Tokenizer::LINE => 0,
                                                                \Mustache\Tokenizer::NAME => 'name',
                                                        ],
                                                ],
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'bar',
                                        ],
                                ],
                        ],

                        // This *would* be an invalid inheritance parse tree, but that pragma
                        // isn't enabled so it'll thunk it back into an "escaped" token:
                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_BLOCK_VAR,
                                                \Mustache\Tokenizer::NAME => 'foo',
                                                \Mustache\Tokenizer::OTAG => '{{',
                                                \Mustache\Tokenizer::CTAG => '}}',
                                                \Mustache\Tokenizer::LINE => 0,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'bar',
                                        ],
                                ],
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => '$foo',
                                                \Mustache\Tokenizer::OTAG => '{{',
                                                \Mustache\Tokenizer::CTAG => '}}',
                                                \Mustache\Tokenizer::LINE => 0,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'bar',
                                        ],
                                ],
                        ],

                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => '  ',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_DELIM_CHANGE,
                                                \Mustache\Tokenizer::LINE => 0,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => "  \n",
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => 'foo',
                                                \Mustache\Tokenizer::OTAG => '[[',
                                                \Mustache\Tokenizer::CTAG => ']]',
                                                \Mustache\Tokenizer::LINE => 1,
                                        ],
                                ],
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => 'foo',
                                                \Mustache\Tokenizer::OTAG => '[[',
                                                \Mustache\Tokenizer::CTAG => ']]',
                                                \Mustache\Tokenizer::LINE => 1,
                                        ],
                                ],
                        ],

                ];
        }


        /**
         * @param $tokens
         * @param $expected
         *
         * @dataProvider getInheritanceTokenSets
         */
        public function testParseWithInheritance($tokens, $expected)
        {
                $parser = new \Mustache\Parser();
                $parser->setPragmas([\Mustache\Engine::PRAGMA_BLOCKS]);
                $this->assertEquals($expected, $parser->parse($tokens));
        }


        /**
         * @return array
         * @author <ted@privacyzeker.nl>
         */
        public function getInheritanceTokenSets()
        {
                return [
                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_PARENT,
                                                \Mustache\Tokenizer::NAME  => 'foo',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 8,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_BLOCK_VAR,
                                                \Mustache\Tokenizer::NAME  => 'bar',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 16,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'baz',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'bar',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 19,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'foo',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 27,
                                        ],
                                ],
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_PARENT,
                                                \Mustache\Tokenizer::NAME  => 'foo',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 8,
                                                \Mustache\Tokenizer::END   => 27,
                                                \Mustache\Tokenizer::NODES => [
                                                        [
                                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_BLOCK_ARG,
                                                                \Mustache\Tokenizer::NAME  => 'bar',
                                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                                \Mustache\Tokenizer::LINE  => 0,
                                                                \Mustache\Tokenizer::INDEX => 16,
                                                                \Mustache\Tokenizer::END   => 19,
                                                                \Mustache\Tokenizer::NODES => [
                                                                        [
                                                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                                                \Mustache\Tokenizer::LINE  => 0,
                                                                                \Mustache\Tokenizer::VALUE => 'baz',
                                                                        ],
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],

                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_BLOCK_VAR,
                                                \Mustache\Tokenizer::NAME => 'foo',
                                                \Mustache\Tokenizer::OTAG => '{{',
                                                \Mustache\Tokenizer::CTAG => '}}',
                                                \Mustache\Tokenizer::LINE => 0,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'bar',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'foo',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 11,
                                        ],
                                ],
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_BLOCK_VAR,
                                                \Mustache\Tokenizer::NAME  => 'foo',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::END   => 11,
                                                \Mustache\Tokenizer::NODES => [
                                                        [
                                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                                \Mustache\Tokenizer::LINE  => 0,
                                                                \Mustache\Tokenizer::VALUE => 'bar',
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ];
        }


        /**
         * @dataProvider getBadParseTrees
         * @expectedException \Mustache\Exception\SyntaxException
         */
        public function testParserThrowsExceptions($tokens)
        {
                $parser = new \Mustache\Parser();
                $parser->parse($tokens);
        }


        /**
         * @return array
         * @author <ted@privacyzeker.nl>
         */
        public function getBadParseTrees()
        {
                return [
                        // no close
                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                        ],
                                ],
                        ],

                        // no close inverted
                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_INVERTED,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                        ],
                                ],
                        ],

                        // no opening inverted
                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                        ],
                                ],
                        ],

                        // weird nesting
                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'child',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'parent',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'child',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 123,
                                        ],
                                ],
                        ],

                        // This *would* be a valid inheritance parse tree, but that pragma
                        // isn't enabled here so it's going to fail :)
                        [
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_BLOCK_VAR,
                                                \Mustache\Tokenizer::NAME => 'foo',
                                                \Mustache\Tokenizer::OTAG => '{{',
                                                \Mustache\Tokenizer::CTAG => '}}',
                                                \Mustache\Tokenizer::LINE => 0,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'bar',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'foo',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 11,
                                        ],
                                ],
                        ],
                ];
        }
}
