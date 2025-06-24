<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin User ID
    |--------------------------------------------------------------------------
    |
    | This value determines which user ID has admin privileges in the
    | application. Only the user with this specific ID will be granted
    | admin access through the Gate::define('admin') configuration.
    |
    */

    'admin' => (int) env('ADMIN_ID', 1),

];
