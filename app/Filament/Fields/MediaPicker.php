<?php

namespace App\Filament\Fields;

use App\Models\Media;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;

/** Pick an image from the Media Library; the field stores its /media/... URL, the same form coded content uses. */
class MediaPicker
{
    public static function make(string $name): Select
    {
        $label = fn (Media $m) => '<span style="display:flex;align-items:center;gap:.6rem"><img src="'.e($m->url).'" alt="" style="width:44px;height:30px;object-fit:cover;border-radius:4px">'.e($m->name).'</span>';

        return Select::make($name)
            ->searchable()
            ->allowHtml()
            ->options(fn () => Media::images()->latest()->limit(60)->get()->mapWithKeys(fn (Media $m) => [$m->url => $label($m)])->all())
            ->getSearchResultsUsing(fn (string $search) => Media::images()->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('path', 'like', "%{$search}%"))->limit(40)->get()->mapWithKeys(fn (Media $m) => [$m->url => $label($m)])->all())
            ->getOptionLabelUsing(fn (?string $value) => ($m = Media::query()->where('path', Str::after((string) $value, '/media/'))->first()) ? $label($m) : e((string) $value))
            ->helperText('Upload new images in Content › Media Library.');
    }
}
