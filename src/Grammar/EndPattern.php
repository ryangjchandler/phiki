<?php

namespace Phiki\Grammar;

use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Tokenizer;
use Phiki\MatchedPattern;

class EndPattern extends Pattern implements PatternCollectionInterface, ContainsCapturesInterface
{
    public function __construct(
        public MatchedPattern $begin,
        public string $end,
        public ?string $name,
        public ?string $contentName,
        public array $endCaptures = [],
        public array $captures = [],
        public array $patterns = [],
    ) {}

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    public function hasPatterns(): bool
    {
        return count($this->patterns) > 0;
    }

    public function getCaptures(): array
    {
        $captures = count($this->endCaptures) > 0 ? $this->endCaptures : $this->captures;

        return $captures;
    }

    public function hasCaptures(): bool
    {
        return count($this->endCaptures) > 0 || count($this->captures) > 0;
    }

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        $regex = preg_replace_callback('/\\\\(\d+)/', function ($matches) {
            return $this->begin->matches[$matches[1]][0] ?? $matches[0];
        }, $this->end);

        if (preg_match('/' . str_replace('/', '\/', $regex) . '/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
            return false;
        }

        if ($cannotExceed !== null && $matches[0][1] > $cannotExceed) {
            return false;
        }

        return new MatchedPattern($this, $matches);
    }

    public function scope(): ?string
    {
        return $this->name;
    }
}