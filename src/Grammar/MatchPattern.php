<?php

namespace Phiki\Grammar;

use Phiki\Tokenizer;
use Phiki\MatchedPattern;

class MatchPattern extends Pattern
{
    /**
     * @param string $match
     * @param string|null $name
     * @param Capture[] $captures
     */
    public function __construct(
        public string $match,
        public ?string $name,
        public array $captures = [],
    ) {}

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        $regex = $this->match;

        if (preg_match('/' . str_replace('/', '\/', $regex) . '/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
            return false;
        }

        if ($cannotExceed !== null && $matches[0][1] > $cannotExceed) {
            return false;
        }

        return new MatchedPattern($this, $matches);
    }

    public function getCaptures(): array
    {
        return $this->captures;
    }

    public function hasCaptures(): bool
    {
        return count($this->captures) > 0;
    }

    public function scope(): ?string
    {
        return $this->name;
    }
}