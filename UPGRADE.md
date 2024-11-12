# Upgrade Guide

> [!important]
> This guide outlines the steps to upgrade the package to the next major version, which includes breaking changes.
> Please follow the instructions carefully to avoid any critical issues during the process.

## v1 to v2

This version marks a significant leap from v1, introducing many useful features. However, it also includes notable
breaking changes.

The primary change lies in the migrations. This version creates a new migration for the `guests` table and removes the
guest-related columns (`guest_name`, `guest_email`, `ip_address`) from the `comments` table and `user_id` removed from
`reactions` and added owner morph column (`owner_id`, `owner_type`) instead. While manually migrating data can
be cumbersome, there's no need to go through that hassle. An upgrade command has been introduced in the latest v1
release. Simply follow the steps below to complete the upgrade.

### Step 1 : Upgrade v1

Update the package to its latest v1 release or at least version 1.5.*.
```bash
composer update lakm/laravel-comments:^1.5
```

### Step 3 : Prepare v1

```bash 
php artisan commenter:upgrade
```

You will receive a success message, then proceed to sep 4.

### Step 4 : Upgrade to v2

Change the commenter version constraint in `composer.json`

```json
{
    "require": {
        "lakm/laravel-comments": "^2.0"
    }
}
```
Run following command

```bash
composer update lakm/laravel-comments:^2.0
```

### Step 4: Publish assets

> [!optional]
> Remove the previous assets by deleting `build` folder in `public/vendor/lakm/laravel-comments`
> path to free up space

```bash 
php artisan vendor:publish --tag=comments-assets --force
```

Enjoy the v2 🤩.

## >= 2.0.5

This version fix the css style conflicting issue by adding a class prefix. So you must republish styles using
below command.

```bash 
php artisan vendor:publish --tag=comments-assets --force
```
