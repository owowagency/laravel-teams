<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Team Model
    |--------------------------------------------------------------------------
    |
    | This is the team model used by the Teams package. You can extend the
    | model's functionality by using a custom model. Tip: extend the package
    | base model.
    |
    */

    'model' => OwowAgency\Teams\Models\Team::class,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the user model used by the Teams package.
    |
    */

    'user_model' => config('auth.providers.users.model', App\User::class),

];
