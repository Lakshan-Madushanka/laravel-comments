# Upgrade Guide

> [!important]
> This guide outlines the steps to upgrade the package to the next major version, which includes breaking changes.
> Please follow the instructions carefully to avoid any critical issues during the process.

## v1 to v2 [⚠️Breaking Changes⚠️]

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

## v2 to v3 [⚠️Breaking Changes⚠️]

> [!important]
> Upgrading to v3 may feel a bit cumbersome due to the major changes introduced in this release.
> However, we’ve dedicated significant time and effort to ensure these updates improve the package for the long term.
> We apologize for any inconvenience 🙏, but we truly believe these changes will greatly enhance the package’s functionality and maintainability.

v3 introduces significant changes to the package that will break the existing version if upgraded. 
Please carefully follow the guide below before upgrading.

### Database
Following tables have changed their structures.
- `comments` table
   - `reply_id` column has been removed ❌.
   - `reply` morph column has been added ✅.
  ```php
        - $table->unsignedBigInteger('reply_id')->nullable()->index();
        + $table->nullableMorphs('reply');
  ```
***This adds an additional `reply_type` column, so you’ll need to add that column and update the morph type of existing records in your comments table.***

> [!important]
> The class namespace has changed ([refer]). Therefore, you must update the morph types in your existing table records—unless you’re using morph maps.

### Namespace
The package namespace has been changed from `LakM\Commets` to `LakM\Commenter`. 

***You have to change your existing code to use the new namespace. specially in Commentable Model and
Commenter Model[refer](https://lakm.gitbook.io/commenter/basics/usage)***

### Asset Directives

***The asset directives have been changed. New directives are as follows:***
```php
@commenterStyles
@commenterScripts
```
Refer [docs](https://lakm.gitbook.io/commenter/basics/usage#include-styles-in-your-layout) for more details.

### Views

The blade directive namespace has been changed from `comments` to `commenter`. 

***So you have to use new directive namespace***

ex:
```php
<x-comments::index :model="$post" /> ❌
<x-commenter::index :model="$post" /> ✅
```
Refer [docs](https://lakm.gitbook.io/commenter/basics/usage#then-simply-include-component-in-your-blade-file) for more details.

### Structure

Classes 've been grouped into relevant directories. As instance comment related events are now in the `Comment` directory.

***You may have to change namespace to match the new structure.***

### All the other namespaces 've changed

- config file is now `commenter.php` under commenter namespace in publishable assets.
- All the other publishable assets including Assets, Views are published to `vendor/lakm/commenter` directory.
