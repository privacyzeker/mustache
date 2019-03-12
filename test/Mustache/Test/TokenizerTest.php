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
class Mustache_Test_TokenizerTest extends PHPUnit_Framework_TestCase
{
        /**
         * @dataProvider getTokens
         */
        public function testScan($text, $delimiters, $expected)
        {
                $tokenizer = new \Mustache\Tokenizer();
                $this->assertSame($expected, $tokenizer->scan($text, $delimiters));
        }


        /**
         * @expectedException \Mustache\Exception\SyntaxException
         */
        public function testUnevenBracesThrowExceptions()
        {
                $tokenizer = new \Mustache\Tokenizer();

                $text = '{{{ name }}';
                $tokenizer->scan($text, null);
        }


        /**
         * @expectedException \Mustache\Exception\SyntaxException
         */
        public function testUnevenBracesWithCustomDelimiterThrowExceptions()
        {
                $tokenizer = new \Mustache\Tokenizer();

                $text = '<%{ name %>';
                $tokenizer->scan($text, '<% %>');
        }


        public function getTokens()
        {
                return [
                        [
                                'text',
                                null,
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'text',
                                        ],
                                ],
                        ],

                        [
                                'text',
                                '<<< >>>',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'text',
                                        ],
                                ],
                        ],

                        [
                                '{{ name }}',
                                null,
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'name',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 10,
                                        ],
                                ],
                        ],

                        [
                                '{{ name }}',
                                '<<< >>>',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => '{{ name }}',
                                        ],
                                ],
                        ],

                        [
                                '<<< name >>>',
                                '<<< >>>',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'name',
                                                \Mustache\Tokenizer::OTAG  => '<<<',
                                                \Mustache\Tokenizer::CTAG  => '>>>',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 12,
                                        ],
                                ],
                        ],

                        [
                                "{{{ a }}}\n{{# b }}  \n{{= | | =}}| c ||/ b |\n|{ d }|",
                                null,
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_UNESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'a',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 8,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => "\n",
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'b',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 1,
                                                \Mustache\Tokenizer::INDEX => 18,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 1,
                                                \Mustache\Tokenizer::VALUE => "  \n",
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_DELIM_CHANGE,
                                                \Mustache\Tokenizer::LINE => 2,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'c',
                                                \Mustache\Tokenizer::OTAG  => '|',
                                                \Mustache\Tokenizer::CTAG  => '|',
                                                \Mustache\Tokenizer::LINE  => 2,
                                                \Mustache\Tokenizer::INDEX => 37,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'b',
                                                \Mustache\Tokenizer::OTAG  => '|',
                                                \Mustache\Tokenizer::CTAG  => '|',
                                                \Mustache\Tokenizer::LINE  => 2,
                                                \Mustache\Tokenizer::INDEX => 37,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 2,
                                                \Mustache\Tokenizer::VALUE => "\n",
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_UNESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'd',
                                                \Mustache\Tokenizer::OTAG  => '|',
                                                \Mustache\Tokenizer::CTAG  => '|',
                                                \Mustache\Tokenizer::LINE  => 3,
                                                \Mustache\Tokenizer::INDEX => 51,
                                        ],

                                ],
                        ],

                        // See https://github.com/bobthecow/mustache.php/issues/183
                        [
                                '{{# a }}0{{/ a }}',
                                null,
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'a',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 8,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => '0',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'a',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 9,
                                        ],
                                ],
                        ],

                        // custom delimiters don't swallow the next character, even if it is a }, }}}, or the same delimiter
                        [
                                '<% a %>} <% b %>%> <% c %>}}}',
                                '<% %>',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'a',
                                                \Mustache\Tokenizer::OTAG  => '<%',
                                                \Mustache\Tokenizer::CTAG  => '%>',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 7,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => '} ',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'b',
                                                \Mustache\Tokenizer::OTAG  => '<%',
                                                \Mustache\Tokenizer::CTAG  => '%>',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 16,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => '%> ',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'c',
                                                \Mustache\Tokenizer::OTAG  => '<%',
                                                \Mustache\Tokenizer::CTAG  => '%>',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 26,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => '}}}',
                                        ],
                                ],
                        ],

                        // unescaped custom delimiters are properly parsed
                        [
                                '<%{ a }%>',
                                '<% %>',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_UNESCAPED,
                                                \Mustache\Tokenizer::NAME  => 'a',
                                                \Mustache\Tokenizer::OTAG  => '<%',
                                                \Mustache\Tokenizer::CTAG  => '%>',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 9,
                                        ],
                                ],
                        ],

                        // Ensure that $arg token is not picked up during tokenization
                        [
                                '{{$arg}}default{{/arg}}',
                                null,
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_BLOCK_VAR,
                                                \Mustache\Tokenizer::NAME  => 'arg',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 8,
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::VALUE => 'default',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_END_SECTION,
                                                \Mustache\Tokenizer::NAME  => 'arg',
                                                \Mustache\Tokenizer::OTAG  => '{{',
                                                \Mustache\Tokenizer::CTAG  => '}}',
                                                \Mustache\Tokenizer::LINE  => 0,
                                                \Mustache\Tokenizer::INDEX => 15,
                                        ],
                                ],
                        ],
                ];
        }
}
