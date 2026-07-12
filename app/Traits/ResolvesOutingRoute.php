<?php

namespace App\Traits;

trait ResolvesOutingRoute
{
    protected function resolveRouteData(array $validated): array
    {
        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            if ($validated['full_direction'] === 'soverato-pizzo') {
                $validated['start_location'] = 'Soverato';
                $validated['end_location']   = 'Pizzo';
            } else {
                $validated['start_location'] = 'Pizzo';
                $validated['end_location']   = 'Soverato';
            }
        } else {
            $validated['is_full_trail'] = (
                ($validated['start_location'] === 'Soverato' && $validated['end_location'] === 'Pizzo') ||
                ($validated['start_location'] === 'Pizzo'    && $validated['end_location'] === 'Soverato')
            );
        }

        unset($validated['route_type'], $validated['full_direction']);

        return $validated;
    }
}
