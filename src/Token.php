<?php

namespace Phiki;

readonly class Token
{
    public function __construct(
        public array $scopes,
        public string $text,
        public int $start,
        public int $end,
    ) {}
}
