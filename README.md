# Eloquent Versioning

[![Build status](https://scrutinizer-ci.com/g/ProAI/eloquent-versioning/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ProAI/eloquent-versioning/) [![Quality score](https://scrutinizer-ci.com/g/ProAI/eloquent-versioning/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ProAI/eloquent-versioning/) [![Latest Stable Version](https://poser.pugx.org/proai/eloquent-versioning/v/stable)](https://packagist.org/packages/proai/eloquent-versioning) [![Total Downloads](https://poser.pugx.org/proai/eloquent-versioning/downloads)](https://packagist.org/packages/proai/eloquent-versioning) [![Latest Unstable Version](https://poser.pugx.org/proai/eloquent-versioning/v/unstable)](https://packagist.org/packages/proai/eloquent-versioning) [![License](https://poser.pugx.org/proai/eloquent-versioning/license)](https://packagist.org/packages/proai/eloquent-versioning)

This is an extension for the Eloquent ORM to support versioning. You can specify attributes as versioned. If an attribute is specified as versioned the value will be saved in a separate version table on each update. It is possible to use timestamps and soft deletes with this feature.

## Installation

Eloquent Versioning is distributed as a composer package. So you first have to add the package to your `composer.json` file:

```
"proai/eloquent-versioning": "~1.0"
```

Then you have to run `composer update` to install the package.

## Example

We assume that we want a simple user model. While the username should be fixed, the email and city should be versionable. Also timestamps and soft deletes should be versioned. The migrations would look like the following:

```php
...

Schema::create('users', function(Blueprint $table) {
    $table->increments('id');
    $table->integer('latest_version');
    $table->string('username');
    $table->timestamp('created_at');
});

Schema::create('users_version', function(Blueprint $table) {
    $table->integer('ref_id')->unsigned();
    $table->integer('version')->unsigned();
    $table->string('email');
    $table->string('city');
    $table->timestamp('updated_at');
    $table->timestamp('deleted_at');
    
    $table->primary(['ref_id', 'version']);
});

...
```

The referring Eloquent model should include the code below:

```php
<?php

namespace Acme\Models;

use Illuminate\Database\Eloquent\Model;
use ProAI\Versioning\Versionable;
use ProAI\Versioning\SoftDeletes;

class User extends Model
{
    use Versionable, SoftDeletes;
    
    public $timestamps = true;
    
    public $versioned = ['email', 'city', 'updated_at', 'deleted_at'];
    
    ...
}
```

## Usage

### Database Tables

You need to add the following columns to your main model table:

* `latest_version` (integer).

Furthermore you need a version table. The name of the version table is identical with the name of the main model table (e. g. for a model table `users` the name would be `users_version`). This table must contain the following columns:

* `ref_` followed by the name of the model's primary key (if the primary key is `id`, the column name will be `ref_id`)
* `version` (integer)

### Eloquent Models

You have to define a `$versioned` array in your model that contains all versioned columns.

### Database Queries

#### Query the database

By default the query builder will fetch the latest version (e. g. `User::find(1);` will return the latest version of user #1). If you want a specific version or all versions, you can use the following:

* `version(VERSION_NO)` returns a specific version<br>Example: `User::version(2)->find(1)` will return version #2 of user #1

* `allVersions()` returns all versions of the queried items<br>Example: `User::allVersions()->get()` will return all versions of all users

* `moment(Carbon)` returns a specific version, closest but lower than the input date<br>Example: `User::moment(Carbon::now()->subWeek())->find(1)` will return the version at that point in time.

#### Create, update and delete records

All these operations can be performed normally. The package will automatically generate a version 1 on create, the next version on update and will remove all versions on delete.

### Timestamps

You can use timestamps in two ways. For both you have to set `$timestamps = true;`.

* Normal timestamps<br>The main table must include a `created_at` and a `updated_at` column. The `updated_at` column will be overriden on every update. So this is the normal use of Eloquent timestamps.

* Versioned timestamps<br>If you add `updated_at` to your `$versioned` array, you need a `created_at` column in the main table and a `updated_at` column in the version table (see example). On update the `updated_at` value of the new version will be set to the current time. The `updated_at` values of previous versions will not be updated. This way you can track the dates of all updates.

### Soft Deletes

If you use the `Versionable` trait with soft deletes, you have to use the `ProAI\Versioning\SoftDeletes` trait **from this package** instead of the Eloquent soft deletes trait.

* Normal soft deletes<br>Just use a `deleted_at` column in the main table. Then on delete or on restore the `deleted_at` value will be updated.

* Versioned soft deletes<br>If you create a `deleted_at` column in the version table and add `deleted_at` to the `$versioned` array, then on delete or on restore the `deleted_at` value of the new version will get updated (see example). The `deleted_at` values of previous versions will not be updated. This way you can track all soft deletes and restores.

## Custom Query Builder

If you want to use a custom versioning query builder, you will have to build your own versioning trait, but that's pretty easy:

```php
<?php

namespace Acme\Versioning;

trait Versionable
{
    use \ProAI\Versioning\BaseVersionable;
    
    public function newEloquentBuilder($query)
    {
        return new MyVersioningBuilder($query);
    }
}
```

Obviously you have to replace `MyVersioningBuilder` by the classname of your custom builder. In addition you have to make sure that your custom builder implements the functionality of the versioning query builder. There are some strategies to do this:

* Extend the versioning query builder `ProAI\Versioning\Builder`
* Use the versioning builder trait `ProAI\Versioning\BuilderTrait`
* Copy and paste the code from the versioning query builder to your custom builder

## Support

Bugs and feature requests are tracked on [GitHub](https://github.com/proai/eloquent-versioning/issues).

## License

This package is released under the [MIT License](LICENSE).
