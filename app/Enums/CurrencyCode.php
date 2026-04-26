<?php

namespace App\Enums;

enum CurrencyCode: string
{
    case USD = 'USD';
    case CDF = 'CDF';

    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        $supported = config('currency.supported', ['USD', 'CDF']);

        return array_values(array_map(
            static fn (string $code): string => strtoupper($code),
            array_filter($supported, static fn ($code): bool => is_string($code) && $code !== '')
        ));
    }

    public static function default(): string
    {
        $default = strtoupper((string) config('currency.default', self::USD->value));

        return in_array($default, self::supported(), true) ? $default : self::USD->value;
    }
}
