# Dullahan Symfony Bundle

Headless CMS bundle created on top of Symfony 7.0.

## Installation

You can start new Symfony 7.0 + Dullahan with [Dullahan Project](https://github.com/Mortimer333/dullahan-project).

Independent installation
```bash
composer require mortimer333/dullahan
```

## Adding new subbundle:
- remove .lock from master repo (which requests this bundle via composer)
- `composer clear-cache && composer dump && composer install`
