<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class AnchoredDotNotation
{
        public $genres = [
                [
                        'name'      => 'Punk',
                        'subgenres' => [
                                [
                                        'name'      => 'Hardcore',
                                        'subgenres' => [
                                                [
                                                        'name'      => 'First wave of black metal',
                                                        'subgenres' => [
                                                                ['name' => 'Norwegian black metal'],
                                                                [
                                                                        'name'      => 'Death metal',
                                                                        'subgenres' => [
                                                                                [
                                                                                        'name'      => 'Swedish death metal',
                                                                                        'subgenres' => [
                                                                                                ['name' => 'New wave of American metal'],
                                                                                        ],
                                                                                ],
                                                                        ],
                                                                ],
                                                        ],
                                                ],
                                                [
                                                        'name'      => 'Thrash metal',
                                                        'subgenres' => [
                                                                ['name' => 'Grindcore'],
                                                                [
                                                                        'name'      => 'Metalcore',
                                                                        'subgenres' => [
                                                                                ['name' => 'Nu metal'],
                                                                        ],
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ];
}
