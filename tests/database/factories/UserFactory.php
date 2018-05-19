<?php

use Faker\Generator as Faker;
use ProAI\Versioning\Tests\Models\User;

/*
|--------------------------------------------------------------------------
| User Factories
|--------------------------------------------------------------------------
|
*/
$factory->define(User::class, function (Faker $faker) {
    return [
        'email'         => $faker->unique()->safeEmail,
        'username'      => $faker->userName,
        'city'          => $faker->city
    ];
});