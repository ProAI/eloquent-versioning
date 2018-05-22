<?php

namespace ProAI\Versioning\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use ProAI\Versioning\Versionable;
use ProAI\Versioning\SoftDeletes;

class Post extends Authenticatable
{
    use Versionable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content'
    ];

    public $timestamps = true;

    public $versioned = ['content', 'updated_at'];
}
