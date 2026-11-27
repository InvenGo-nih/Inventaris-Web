<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('hasPermission')) {
    function hasPermission($permissions): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $requested = array_values(array_unique(array_map('strval', (array) $permissions)));
        sort($requested);

        static $memo = [];
        $key = $user->getAuthIdentifier().'|'.implode(',', $requested);

        if (array_key_exists($key, $memo)) {
            return $memo[$key];
        }

        try {
            return $memo[$key] = (bool) $user->hasAnyPermission($requested);
        } catch (\Throwable $e) {
            return $memo[$key] = false;
        }
    }
}
