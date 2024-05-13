<?php

namespace Schwartzmj\StatamicImg;
enum Breakpoint: int
{
    case sm = 640;
    case md = 768;
    case lg = 1024;
    case xl = 1280;
    case xxl = 1536;

    public static function tryGetValueFromKey(string $name): ?Breakpoint
    {
        return match ($name) {
            'sm' => Breakpoint::sm,
            'md' => Breakpoint::md,
            'lg' => Breakpoint::lg,
            'xl' => Breakpoint::xl,
            'xxl' => Breakpoint::xxl,
            default => null,
        };
    }
}
