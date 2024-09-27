<?php

namespace Phiki;

use Phiki\Contracts\ThemeRepositoryInterface;
use Phiki\Exceptions\UnrecognisedThemeException;

class ThemeRepository implements ThemeRepositoryInterface
{
    protected array $themes = [
        'github-dark' => __DIR__.'/../themes/github-dark.json',
    ];

    public function get(string $name): array
    {
        if (! $this->has($name)) {
            throw UnrecognisedThemeException::make($name);
        }

        $theme = $this->themes[$name];

        if (is_array($theme)) {
            return $theme;
        }

        return $this->themes[$name] = json_decode(file_get_contents($theme), true);
    }

    public function has(string $name): bool
    {
        return isset($this->themes[$name]);
    }

    public function register(string $name, string|array $pathOrTheme): void
    {
        $this->themes[$name] = $pathOrTheme;
    }
}
