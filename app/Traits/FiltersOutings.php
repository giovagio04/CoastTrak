<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersOutings
{
    protected function applyOutingFilters(Builder $query, Request $request): void
    {
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('difficulty') && $request->difficulty !== 'all') {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $routeType = $request->input('route_type_filter', 'all');

        if ($routeType === 'full') {
            $query->where('is_full_trail', true);
            if ($request->filled('full_direction')) {
                if ($request->full_direction === 'soverato-pizzo') {
                    $query->where('start_location', 'Soverato')->where('end_location', 'Pizzo');
                } elseif ($request->full_direction === 'pizzo-soverato') {
                    $query->where('start_location', 'Pizzo')->where('end_location', 'Soverato');
                }
            }
        } elseif ($routeType === 'custom') {
            $query->where('is_full_trail', false);
            if ($request->filled('start_location')) {
                $query->where('start_location', $request->start_location);
            }
            if ($request->filled('end_location')) {
                $query->where('end_location', $request->end_location);
            }
        } else {
            if ($request->filled('start_location')) {
                $query->where('start_location', 'LIKE', '%' . $request->start_location . '%');
            }
            if ($request->filled('end_location')) {
                $query->where('end_location', 'LIKE', '%' . $request->end_location . '%');
            }
        }
    }
}
