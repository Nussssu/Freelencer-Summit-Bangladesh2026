<?php

namespace App\Enums;

/** Labels and {value,label} options for string-backed enums; LABELS overrides the default headline case. */
trait HasOptions
{
    public function label(): string
    {
        return defined('static::LABELS') && isset(static::LABELS[$this->value])
            ? static::LABELS[$this->value]
            : ucfirst(str_replace('-', ' ', $this->value));
    }

    /** @return list<array{value: string, label: string}> */
    public static function options(): array
    {
        return array_map(fn (self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
