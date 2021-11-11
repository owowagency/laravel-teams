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

    'user_model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Default Privacy
    |--------------------------------------------------------------------------
    |
    | When users are added to the team it could be that they need to be invited
    | or they need to request to join. Here we'll define the default privacy
    | setting. This value is used if the team doesn't have the privacy setting
    | set itself.
    |
    */

    'default_privacy' => OwowAgency\Teams\Enums\TeamPrivacy::OPEN,

];
