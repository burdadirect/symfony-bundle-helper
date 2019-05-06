# HBM Helper Bundle

## Status

### Dependencies

[![Dependency Status](https://gemnasium.com/badges/github.com/burdanews/helper-bundle.svg)](https://gemnasium.com/github.com/burdanews/helper-bundle)

## Team

### Developers
Christian Puchinger - christian.puchinger@playboy.de

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require burdanews/symfony-bundle-helper
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

With Symfony 4 the bundle is enabled automatically for all environments (see `config/bundles.php`). 

### Step 3: Configuration

```yml
hbm_helper:
  bitly:
    client_id:
    client_secret:
    user_login:
    user_password:

  blitline:
    appid:
    postback:
      url:
      route:

  webshrinker:
    access_key:
    secret_key:

  screenshotapi:
    apikey:

  cleverreach:
    apikey:
    listid:
    formid:
    source:
    doi:
      info:
      data:
    fields:
      - {key: , value: }

  hmac:
    secret:

  s3:
    - {key: , secret: , bucket: , region: 'eu-central-1', local: './'}

  sanitizing:
    sep: '/'
    language: 'de'
```
