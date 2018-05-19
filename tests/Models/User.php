<?php

namespace ProAI\Versioning\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use ProAI\Versioning\Versionable;
use ProAI\Versioning\SoftDeletes;

class User extends Authenticatable
{
    use Versionable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'username', 'city', 'latest_version',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public $timestamps = true;

    public $versioned = ['email', 'city', 'updated_at', 'deleted_at'];
}
