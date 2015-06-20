# Eloquent Versioning

[![Latest Stable Version](https://poser.pugx.org/markusjwetzel/eloquent-versioning/v/stable)](https://packagist.org/packages/markusjwetzel/eloquent-versioning) [![Total Downloads](https://poser.pugx.org/markusjwetzel/eloquent-versioning/downloads)](https://packagist.org/packages/markusjwetzel/eloquent-versioning) [![Latest Unstable Version](https://poser.pugx.org/markusjwetzel/eloquent-versioning/v/unstable)](https://packagist.org/packages/markusjwetzel/eloquent-versioning) [![License](https://poser.pugx.org/markusjwetzel/eloquent-versioning/license)](https://packagist.org/packages/markusjwetzel/eloquent-versioning)

**Important: For now the package does not work! The first version of the Laravel Data Mapper is actually under development. You can star this repository to show your interest in this package.**

An extension for the Eloquent ORM to support versioning.

## Installation

Eloquent Versioning is distributed as a composer package. So you first have to add the package to your `composer.json` file:

```
"markusjwetzel/eloquent-versioning": "~1.0@dev"
```

Then you have to run `composer update` to install the package. Once this is completed, you have to add the service provider to the providers array in `config/app.php`:

```
'Wetzel\Versioning\VersioningServiceProvider'
```

## Usage

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
        return new MyBuilder($query);
    }
}
```

Obviously you have to replace `MyBuilder` by the classname of your custom builder. In addition you have to make sure that your custom builder implements the functionality of the versioning query builder. There are some strategies to do this:

* Extend the versioning query builder `ProAi\Versioning\Builder`
* Use the versioning builder trait `ProAi\Versioning\BuilderTrait`
* Copy and paste the code from the versioning query builder to your custom builder

## Support

Bugs and feature requests are tracked on [GitHub](https://github.com/markusjwetzel/eloquent-versioning/issues).

## License

This package is released under the [MIT License](LICENSE).
