<?php

namespace Arrocy\Template\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'enabled', 'name', 'avatar', 'text'])]
class Template extends Model
{
    protected $table = 'ez_templates';

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $pattern = '%'.$search.'%';

        return $query->where(fn (Builder $query): Builder => $query
            ->where('name', 'like', $pattern)
            ->orWhere('text', 'like', $pattern));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
