<?php

namespace ProAI\Versioning\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use ProAI\Versioning\Tests\Models\Post;
use ProAI\Versioning\Tests\Models\User;

class SoftDeletesTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider modelProvider
     * @param string $model
     * @throws \Exception
     */
    public function itWillSaveDeletedAt(string $model)
    {
        $model = factory($model)->create([]);
        $model->delete();

        $this->assertArraySubset([
            'id'            => $model->id,
            'deleted_at'    => $model->deleted_at
        ], $model::withTrashed()->first()->toArray());
    }

    /**
     * @test
     */
    public function itWillGetTheCorrectDeletedAtColumnOnTheMainTable()
    {
        /** @var Post $model */
        $model = factory(Post::class)->create([]);

        $this->assertEquals('posts.deleted_at', $model->getQualifiedDeletedAtColumn());
    }

    /**
     * @test
     */
    public function itWillGetTheCorrectDeletedAtColumnOnTheVersionTable()
    {
        /** @var User $model */
        $model = factory(User::class)->create([]);

        $this->assertEquals('users_version.deleted_at', $model->getQualifiedDeletedAtColumn());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function itWillSaveDeletedAtInTheMainTable()
    {
        /** @var Post $model */
        $model = factory(Post::class)->create([]);
        $model->delete();

        $this->assertDatabaseHas('posts', [
            'id'            => $model->id,
            'deleted_at'    => $model->deleted_at
        ]);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function itWillSaveDeletedAtInTheVersionTable()
    {
        /** @var User $model */
        $model = factory(User::class)->create([]);
        $model->delete();

        $this->assertDatabaseHas('users_version', [
            'ref_id'        => $model->id,
            'version'       => 1,
            'deleted_at'    => null
        ]);

        $this->assertDatabaseHas('users_version', [
            'ref_id'        => $model->id,
            'version'       => 2,
            'deleted_at'    => $model->deleted_at->format('Y-m-d H:i:s')
        ]);
    }

    public function modelProvider()
    {
        return [
            [
                User::class
            ],
            [
                Post::class
            ]
        ];
    }
}
