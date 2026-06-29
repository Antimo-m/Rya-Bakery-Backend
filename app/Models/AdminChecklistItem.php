<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['title', 'checklist_date', 'is_done', 'position'])]
class AdminChecklistItem extends Model
{
    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    protected function checklistDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Carbon::parse($value) : null,
            set: fn (mixed $value) => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }
}
