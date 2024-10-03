<?php

namespace Phiki;

use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Exceptions\UnrecognisedGrammarException;
use Phiki\Grammar\Grammar;

class GrammarRepository implements GrammarRepositoryInterface
{
    protected array $grammars = [
        'blade' => __DIR__.'/../languages/blade.json',
        'php' => __DIR__.'/../languages/php.json',
        'html' => __DIR__.'/../languages/html.json',
        'shellscript' => __DIR__.'/../languages/shellscript.json',
    ];

    protected array $scopesToGrammar = [
        'text.html.basic' => 'html',
        'text.html.php.blade' => 'blade',
        'source.php' => 'php',
        'source.shell' => 'shellscript',
    ];

    protected array $aliases = [
        'bash' => 'shellscript',
    ];

    public function get(string $name): Grammar
    {
        if (! $this->has($name)) {
            throw UnrecognisedGrammarException::make($name);
        }

        $name = $this->aliases[$name] ?? $name;
        $grammar = $this->grammars[$name];

        if ($grammar instanceof Grammar) {
            return $grammar;
        }

        $parser = new GrammarParser;

        return $this->grammars[$name] = $parser->parse(json_decode(file_get_contents($grammar), true));
    }

    public function getFromScope(string $scope): Grammar
    {
        if (! isset($this->scopesToGrammar[$scope])) {
            throw UnrecognisedGrammarException::make($scope);
        }

        return $this->get($this->scopesToGrammar[$scope]);
    }

    public function has(string $name): bool
    {
        return isset($this->grammars[$name]) || isset($this->aliases[$name]);
    }

    public function alias(string $alias, string $target): void
    {
        $this->aliases[$alias] = $target;
    }

    public function register(string $name, string|Grammar $pathOrGrammar): void
    {
        $this->grammars[$name] = $pathOrGrammar;
    }

    public function getAllGrammarNames(): array
    {
        return array_keys($this->grammars);
    }
}
