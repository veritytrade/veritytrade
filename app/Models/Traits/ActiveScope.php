<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ActiveScope
{
    /**
     * Boot the active scope trait for a model.
     * Applies to ALL queries EXCEPT admin panel.
     */
    protected static function bootActiveScope(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            // Skip scope in admin panel (routes starting with /admin)
            if (request()->is('admin/*') || request()->is('admin')) {
                return;
            }
            
            // Skip scope for soft-deleted queries
            if (method_exists($builder->getModel(), 'withTrashed')) {
                return;
            }
            
            // Only show active records to public users
            $builder->where('is_active', true);
        });
    }
}