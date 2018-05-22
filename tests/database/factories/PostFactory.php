<?php

use Faker\Generator as Faker;
use ProAI\Versioning\Tests\Models\Post;

/*
|--------------------------------------------------------------------------
| Post Factories
|--------------------------------------------------------------------------
|
*/
$factory->define(Post::class, function (Faker $faker) {
    return [
        'title'         => $faker->title,
        'content'          => $faker->text,
    ];
});