<?php

namespace ProAI\Versioning\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use ProAI\Versioning\Tests\Models\User;

class BuilderTest extends TestCase
{
    /**
     * @test
     */
    public function itWillRetrieveVersionedAttributes()
    {
        /** @var User $model */
        $model = factory(User::class)->create([]);

        $this->assertArraySubset([
            'username'          => $model->username,
            'email'             => $model->email,
            'city'              => $model->city,
            'latest_version'    => $model->latest_version,
            'updated_at'        => $model->updated_at,
            'created_at'        => $model->created_at
        ], User::first()->toArray());
    }

    /**
     * @test
     */
    public function itWillRetrieveTheLatestVersionedAttributes()
    {
        /** @var User $model */
        $model = factory(User::class)->create([]);

        $model->update([
            'city'  => 'Citadel'
        ]);

        $this->assertArraySubset([
            'latest_version' => 2,
        ], User::first()->toArray());
    }

    /**
     * @test
     */
    public function itWillRetrieveTheCorrectVersionsAttributes()
    {
        /** @var User $model */
        $model = factory(User::class)->create([]);
        $city = $model->city;

        $model->update([
            'city'  => 'Citadel'
        ]);

        $model->update([
            'city'  => 'Ricklantis'
        ]);

        $this->assertArraySubset([
            'city' => $city,
            'version' => 1
        ], User::version(1)->find($model->id)->toArray());

        $this->assertArraySubset([
            'city' => 'Citadel',
            'version' => 2
        ], User::version(2)->find($model->id)->toArray());

        $this->assertArraySubset([
            'city' => 'Ricklantis',
            'version' => 3
        ], User::version(3)->find($model->id)->toArray());
    }

    /**
     * @test
     */
    public function itWillRetrieveAllVersions()
    {
        /** @var User $model */
        $model = factory(User::class)->create([]);
        $city = $model->city;

        $model->update([
            'city'  => 'Citadel'
        ]);

        $model->update([
            'city'  => 'Ricklantis'
        ]);

        $this->assertArraySubset([
            [
                'city' => $city
            ],
            [
                'city' => 'Citadel'
            ],
            [
                'city' => 'Ricklantis'
            ]
        ], User::allVersions()->get()->toArray());
    }

    /**
     * @test
     */
    public function itWillRetrieveTheCorrectMomentsAttributes()
    {
        /** @var User $model */
        $model = factory(User::class)->create([
            'updated_at' => Carbon::now()->subDays(2)
        ]);
        $date = $model->created_at;

        DB::table('users_version')->insert([
            'ref_id'        => 1,
            'version'       => 2,
            'email'         => $model->email,
            'city'          => 'Citadel',
            'updated_at'    => $date->copy()->addDays(1)
        ]);

        DB::table('users_version')->insert([
            'ref_id'        => 1,
            'version'       => 3,
            'email'         => $model->email,
            'city'          => 'Ricklantis',
            'updated_at'    => $date->copy()->addDays(2)
        ]);

        $this->assertArraySubset([
            'version' => 1
        ], User::moment($date)->find($model->id)->toArray());

        $this->assertArraySubset([
            'version' => 2
        ], User::moment($date->copy()->addDays(1))->find($model->id)->toArray());

        $this->assertArraySubset([
            'version' => 3
        ], User::moment($date->copy()->addDays(2))->find($model->id)->toArray());
    }
}
