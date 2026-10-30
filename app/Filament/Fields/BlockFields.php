<?php

namespace App\Filament\Fields;

use App\Support\PageBlocks;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Support\Str;

/**
 * Typed builder blocks generated from the coded prop shape: text → input, long text → textarea, bool → toggle,
 * number → numeric, /media image → Media Library picker, object → fieldset, list → repeater. Generating the form from
 * the value the Vue page already receives keeps every saved block in exactly that shape.
 */
class BlockFields
{
    private const IMAGE = '/\.(jpe?g|png|webp|gif|svg|avif)$/i';

    /** @param array<string, mixed> $blocks block key => coded value (PageBlocks::editable) */
    public static function blocks(array $blocks): array
    {
        return array_map(fn ($key, $value) => Block::make($key)->label(Str::headline($key))->maxItems(1)
            ->schema(static::schema(PageBlocks::wrap($value))), array_keys($blocks), $blocks);
    }

    /** @return list<Component> */
    /** @param list<string> $nullable list-item keys that are null in some coded item (blank saves null, not '') */
    public static function schema(array $shape, bool $inList = false, array $nullable = []): array
    {
        return collect($shape)
            ->map(fn ($value, $key) => static::field((string) $key, $value, $inList, in_array($key, $nullable, true)))
            ->filter()->values()->all();
    }

    private static function field(string $key, mixed $value, bool $inList, bool $nullable = false): ?Component
    {
        // Values the CMS must not shape (code-only keys, empty lists of unknown items) ride along unchanged inside list
        // items, and are left to the code everywhere else (PageBlocks::merge ignores them).
        if (in_array($key, PageBlocks::CODE_ONLY, true) || $value === []) {
            return $inList ? Hidden::make($key)->default($value) : null;
        }
        $label = Str::headline($key);
        $default = $inList ? null : $value; // a new list item starts blank, a re-added block starts with the coded copy

        return match (true) {
            is_bool($value) => Toggle::make($key)->label($label)->default($inList ? false : $value),
            is_int($value) || is_float($value) => TextInput::make($key)->label($label)->numeric()->default($default)
                ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? null : $state + 0), // Vue reads numbers, not strings
            is_array($value) && ! array_is_list($value) => Fieldset::make($label)->statePath($key)->columns(2)->schema(static::schema($value, $inList)),
            is_array($value) && is_array($value[0]) => Repeater::make($key)->label($label)->default($default ?? [])
                ->schema(static::schema(static::union($value), true, static::nullableKeys($value)))->columns(2)
                ->reorderable()->collapsible()->collapsed()->cloneable()
                ->mutateDehydratedStateUsing(fn (?array $state) => static::trimToKind(array_values($state ?? []), $value)) // replaces Filament's own UUID-key → list step
                ->itemLabel(fn (array $state) => Str::limit(strip_tags((string) collect(['title', 'name', 'label', 'q', 'role', 'type', 'id'])->map(fn ($k) => $state[$k] ?? null)->first(fn ($v) => is_string($v) && $v !== '')), 60)),
            is_array($value) => Repeater::make($key)->label($label)->default($default ?? [])->reorderable()
                ->simple(static::scalar('value', $value[0], $label)),
            default => static::scalar($key, $nullable ? null : $value, $label, $value)->default($default),
        };
    }

    /** $value decides string vs nullable; $sample (defaults to $value) picks the input type. */
    private static function scalar(string $key, mixed $value, string $label, mixed $sample = null): Component
    {
        $sample ??= $value;
        $field = match (true) {
            is_string($sample) && str_starts_with($sample, '/media/') && preg_match(self::IMAGE, $sample) => MediaPicker::make($key),
            is_string($sample) && mb_strlen($sample) > 90 => Textarea::make($key)->rows(3)->autosize(),
            default => TextInput::make($key),
        };

        // Filament saves a blank input as null; text the code types as a string stays a string ('').
        return $field->label($label)->dehydrateStateUsing(is_string($value) ? fn ($state) => $state ?? '' : null);
    }

    private static function nullableKeys(array $items): array
    {
        $keys = [];
        foreach ($items as $item) {
            foreach ((array) $item as $k => $v) {
                if ($v === null) {
                    $keys[$k] = true;
                }
            }
        }

        return array_keys($keys);
    }

    /**
     * Mixed-kind lists (story tiles: video, quote, stat) share one item form, so each saved item is cut back to the
     * keys its kind has in the coded list. Lists with one shape pass through untouched.
     */
    private static function trimToKind(array $items, array $coded): array
    {
        $kinds = [];
        foreach ($coded as $item) {
            $kind = $item['kind'] ?? $item['type'] ?? null;
            if (is_string($kind)) {
                $kinds[$kind] = array_keys($item);
            }
        }
        if (count(array_unique(array_map(fn ($keys) => implode(',', $keys), $kinds))) < 2) {
            return $items;
        }

        return array_map(fn ($item) => isset($kinds[$item['kind'] ?? $item['type'] ?? null])
            ? array_intersect_key($item, array_flip($kinds[$item['kind'] ?? $item['type']])) : $item, $items);
    }

    /** List items can differ by kind (e.g. story tiles): build one item form from every key any item has. */
    private static function union(array $items): array
    {
        $shape = [];
        foreach ($items as $item) {
            foreach ((array) $item as $k => $v) {
                $shape[$k] = ($shape[$k] ?? null) ?? $v;
            }
        }

        return $shape;
    }
}
