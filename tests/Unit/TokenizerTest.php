<?php

use Phiki\Token;

describe('match', function () {
    it('can tokenize simple match patterns', function () {
        $tokens = tokenize('if else while end', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'keyword.control.test',
                    'match' => '\\b(if|else|while|end)\\b',
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'keyword.control.test'], 'if', 0, 2),
                new Token(['source.test'], ' ', 2, 3),
                new Token(['source.test', 'keyword.control.test'], 'else', 3, 7),
                new Token(['source.test'], ' ', 7, 8),
                new Token(['source.test', 'keyword.control.test'], 'while', 8, 13),
                new Token(['source.test'], ' ', 13, 14),
                new Token(['source.test', 'keyword.control.test'], 'end', 14, 17),
                new Token(['source.test'], "\n", 17, 17),
            ],
        ]);
    });

    it('can tokenize a simple match with simple named captures', function () {
        $tokens = tokenize('function foo() {}', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.function.test',
                    'match' => '(function)\\s*([a-zA-Z_\\x{7f}-\\x{10ffff}][a-zA-Z0-9_\\x{7f}-\\x{10ffff}]*)',
                    'captures' => [
                        '1' => [
                            'name' => 'storage.type.function.test',
                        ],
                        '2' => [
                            'name' => 'entity.name.function.test',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.function.test', 'storage.type.function.test'], 'function', 0, 8),
                new Token(['source.test', 'meta.function.test'], ' ', 8, 9),
                new Token(['source.test', 'meta.function.test', 'entity.name.function.test'], 'foo', 9, 12),
                new Token(['source.test'], "() {}\n", 12, 17),
            ],
        ]);
    });

    it('can tokenize a match with captures and subpatterns, where the subpatterns are not found', function () {
        $tokens = tokenize('namespace Foo;', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.namespace.test',
                    'match' => '(?i)(?:^|(?<=<\\?php))\\s*(namespace)\\s+([a-z0-9_\\x{7f}-\\x{10ffff}\\\\]+)(?=\\s*;)',
                    'captures' => [
                        '1' => [
                            'name' => 'keyword.other.namespace.test',
                        ],
                        '2' => [
                            'name' => 'entity.name.type.namespace.test',
                            'patterns' => [
                                [
                                    'match' => '\\\\',
                                    'name' => 'punctuation.separator.inheritance.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // dd($tokens);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.namespace.test', 'keyword.other.namespace.test'], 'namespace', 0, 9),
                new Token(['source.test', 'meta.namespace.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Foo', 10, 13),
                new Token(['source.test', 'meta.namespace.test'], ";\n", 13, 14),
            ],
        ]);
    });

    it('can tokenize a match with captures and subpatterns, where the subpatterns are found', function () {
        $tokens = tokenize('namespace Foo\\Bar\\Baz;', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'name' => 'meta.namespace.test',
                    'match' => '(?i)(?:^|(?<=<\\?php))\\s*(namespace)\\s+([a-z0-9_\\x{7f}-\\x{10ffff}\\\\]+)(?=\\s*;)',
                    'captures' => [
                        '1' => [
                            'name' => 'keyword.other.namespace.test',
                        ],
                        '2' => [
                            'name' => 'entity.name.type.namespace.test',
                            'patterns' => [
                                [
                                    'match' => '\\\\',
                                    'name' => 'punctuation.separator.inheritance.test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'meta.namespace.test', 'keyword.other.namespace.test'], 'namespace', 0, 9),
                new Token(['source.test', 'meta.namespace.test'], ' ', 9, 10),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Foo', 10, 13),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test', 'punctuation.separator.inheritance.test'], '\\', 13, 14),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Bar', 14, 17),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test', 'punctuation.separator.inheritance.test'], '\\', 17, 18),
                new Token(['source.test', 'meta.namespace.test', 'entity.name.type.namespace.test'], 'Baz', 18, 21),
                new Token(['source.test', 'meta.namespace.test'], ";\n", 21, 22),
            ],
        ]);
    });
});

describe('subpattern includes', function () {
    it('can tokenize an include with only subpatterns', function () {
        $tokens = tokenize('$hello', [
            'scopeName' => 'source.test',
            'patterns' => [
                [
                    'include' => '#variable-name',
                ],
            ],
            'repository' => [
                'variable-name' => [
                    'patterns' => [
                        [
                            'captures' => [
                                '1' => [
                                    'name' => 'variable.other.php',
                                ],
                                '10' => [
                                    'name' => 'string.unquoted.index.php',
                                ],
                                '11' => [
                                    'name' => 'punctuation.section.array.end.php',
                                ],
                                '2' => [
                                    'name' => 'punctuation.definition.variable.php',
                                ],
                                '4' => [
                                    'name' => 'keyword.operator.class.php',
                                ],
                                '5' => [
                                    'name' => 'variable.other.property.php',
                                ],
                                '6' => [
                                    'name' => 'punctuation.section.array.begin.php',
                                ],
                                '7' => [
                                    'name' => 'constant.numeric.index.php',
                                ],
                                '8' => [
                                    'name' => 'variable.other.index.php',
                                ],
                                '9' => [
                                    'name' => 'punctuation.definition.variable.php',
                                ],
                            ],
                            'match' => '(?i)((\\$)(?<name>[a-z_\\x{7f}-\\x{10ffff}][a-z0-9_\\x{7f}-\\x{10ffff}]*))\\s*(?:(\\??->)\\s*(\\g<name>)|(\\[)(?:(\\d+)|((\$)\\g<name>)|([a-z_\\x{7f}-\\x{10ffff}][a-z0-9_\\x{7f}-\\x{10ffff}]*))(\\]))?',
                        ],
                        [
                            'captures' => [
                                '1' => [
                                    'name' => 'variable.other.php',
                                ],
                                '2' => [
                                    'name' => 'punctuation.definition.variable.php',
                                ],
                                '4' => [
                                    'name' => 'punctuation.definition.variable.php',
                                ],
                            ],
                            'match' => '(?i)((\\${)(?<name>[a-z_\\x{7f}-\\x{10ffff}][a-z0-9_\\x{7f}-\\x{10ffff}]*)(}))',
                        ],
                    ],
                ],
            ],
        ]);

        expect($tokens)->toEqualCanonicalizing([
            [
                new Token(['source.test', 'variable.other.php'], '$hello', 0, 6),
                new Token(['source.test'], "\n", 6, 6),
            ],
        ]);
    });
});
