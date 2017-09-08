# HBM Twig Extensions Bundle

## Status

### Dependencies

[![Dependency Status](https://gemnasium.com/badges/github.com/burdanews/helper-bundle.svg)](https://gemnasium.com/github.com/burdanews/helper-bundle)

## Team

### Developers
Christian Puchinger - puchinger@playboy.de

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require burdanews/twig-extensions-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new HBM\HelperBundle\HBMHelperBundle(),
        );

        // ...
    }

    // ...
}
```
