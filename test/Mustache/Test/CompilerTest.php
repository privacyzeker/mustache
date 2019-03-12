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
class Mustache_Test_CompilerTest extends PHPUnit_Framework_TestCase
{
        /**
         * @dataProvider getCompileValues
         */
        public function testCompile($source, array $tree, $name, $customEscaper, $entityFlags, $charset, $expected)
        {
                $compiler = new Mustache\Compiler();

                $compiled = $compiler->compile($source, $tree, $name, $customEscaper, $charset, false, $entityFlags);
                foreach ($expected as $contains) {
                        $this->assertContains($contains, $compiled);
                }
        }


        public function getCompileValues()
        {
                return [
                        [
                                '', [], 'Banana', false, ENT_COMPAT, 'ISO-8859-1', [
                                "\nclass Banana extends \Mustache\Template",
                                'return $buffer;',
                        ],
                        ],

                        [
                                '', [$this->createTextToken('TEXT')], 'Monkey', false, ENT_COMPAT, 'UTF-8', [
                                "\nclass Monkey extends \Mustache\Template",
                                '$buffer .= $indent . \'TEXT\';',
                                'return $buffer;',
                        ],
                        ],

                        [
                                '',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => 'name',
                                        ],
                                ],
                                'Monkey',
                                true,
                                ENT_COMPAT,
                                'ISO-8859-1',
                                [
                                        "\nclass Monkey extends \Mustache\Template",
                                        '$value = $this->resolveValue($context->find(\'name\'), $context);',
                                        '$buffer .= $indent . call_user_func($this->mustache->getEscape(), $value);',
                                        'return $buffer;',
                                ],
                        ],

                        [
                                '',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => 'name',
                                        ],
                                ],
                                'Monkey',
                                false,
                                ENT_COMPAT,
                                'ISO-8859-1',
                                [
                                        "\nclass Monkey extends \Mustache\Template",
                                        '$value = $this->resolveValue($context->find(\'name\'), $context);',
                                        '$buffer .= $indent . htmlspecialchars($value, ' . ENT_COMPAT . ', \'ISO-8859-1\');',
                                        'return $buffer;',
                                ],
                        ],

                        [
                                '',
                                [
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => 'name',
                                        ],
                                ],
                                'Monkey',
                                false,
                                ENT_QUOTES,
                                'ISO-8859-1',
                                [
                                        "\nclass Monkey extends \Mustache\Template",
                                        '$value = $this->resolveValue($context->find(\'name\'), $context);',
                                        '$buffer .= $indent . htmlspecialchars($value, ' . ENT_QUOTES . ', \'ISO-8859-1\');',
                                        'return $buffer;',
                                ],
                        ],

                        [
                                '',
                                [
                                        $this->createTextToken("foo\n"),
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => 'name',
                                        ],
                                        [
                                                \Mustache\Tokenizer::TYPE => \Mustache\Tokenizer::T_ESCAPED,
                                                \Mustache\Tokenizer::NAME => '.',
                                        ],
                                        $this->createTextToken("'bar'"),
                                ],
                                'Monkey',
                                false,
                                ENT_COMPAT,
                                'UTF-8',
                                [
                                        "\nclass Monkey extends \Mustache\Template",
                                        "\$buffer .= \$indent . 'foo\n';",
                                        '$value = $this->resolveValue($context->find(\'name\'), $context);',
                                        '$buffer .= htmlspecialchars($value, ' . ENT_COMPAT . ', \'UTF-8\');',
                                        '$value = $this->resolveValue($context->last(), $context);',
                                        '$buffer .= \'\\\'bar\\\'\';',
                                        'return $buffer;',
                                ],
                        ],
                ];
        }


        /**
         * @expectedException \Mustache\Exception\SyntaxException
         */
        public function testCompilerThrowsSyntaxException()
        {
                $compiler = new \Mustache\Compiler();
                $compiler->compile('', [[\Mustache\Tokenizer::TYPE => 'invalid']], 'SomeClass');
        }


        /**
         * @param string $value
         */
        private function createTextToken($value)
        {
                return [
                        \Mustache\Tokenizer::TYPE  => \Mustache\Tokenizer::T_TEXT,
                        \Mustache\Tokenizer::VALUE => $value,
                ];
        }
}
