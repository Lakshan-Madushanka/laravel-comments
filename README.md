<div align="center">
    
**[Documentation](https://lakm.gitbook.io/commenter)** |
**[Admin Panel](https://github.com/Lakshan-Madushanka/laravel-comments-admin-panel)** |
**[Overview](#overview)** |
**[Articles](#articles)** |
**[Key Features](#key-features)** |
**[Why Commenter](#why-commenter)** |
**[Quick Start](#quick-start)** |
**[Demo](#demo)** |
**[Changelog](#changelog)** |
**[Testing](#testing)** |
**[Roadmap](#roadmap)** |
**[Security](#security)** |
**[License](#license)**
    
<img src="https://github.com/Lakshan-Madushanka/laravel-comments/assets/47297673/811e40d6-3988-4cb9-861d-cc9e98005d65" alt="Commenter logo">

#  <img src="https://github.com/Lakshan-Madushanka/laravel-comments/assets/47297673/73ed97a6-9bdd-4b4e-8a87-fd5027d67149" width="359" height="50">

***Everything you need for your commenting system***

A Laravel package that brings powerful commenting functionality to your apps 😍

[![Laravel](https://img.shields.io/badge/laravel-%5E10.0%20%7C%20%5E11.0-red)](https://laravel.com)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/Lakshan-Madushanka/laravel-comments/run-tests.yml)](https://github.com/Lakshan-Madushanka/laravel-comments/actions?query=workflow%3ATests+branch%3Amain)
[![Packagist Version](https://img.shields.io/packagist/v/lakm/laravel-comments)](https://packagist.org/packages/lakm/laravel-comments)
[![Downloads](https://img.shields.io/packagist/dt/lakm/laravel-comments)](https://packagist.org/packages/lakm/laravel-comments)
[![GitHub License](https://img.shields.io/github/license/Lakshan-Madushanka/laravel-comments)](https://github.com/Lakshan-Madushanka/laravel-comments/blob/main/LICENSE.md)

</div>

## Overview

Commenter is a feature-rich, modern package with an admin panel designed to address all your commenting needs. With this
package, you won't need any additional tools for the comment functionality in your Laravel projects.

See the [documentation](https://lakm.gitbook.io/commenter/basics/installation) for detailed installation and usage instructions.

```php
<x-comments :model="$post" />
```

### Articles
- [Laravel News](https://laravel-news.com/laravel-commentable-package)

<p align="center"><img src="https://github.com/user-attachments/assets/32d964f5-d899-4082-809d-bf400e62501d" alt="screenshot"></p>

## Key Features

- ❤️ Simple, modern, and user-friendly interfaces.
- 📱 Mobile responsiveness.
- 📝 WYSIWYG editor.
- 📔 Syntax highlighting.
- 🔒 Robust security features.
- 🔑 Effective spam prevention.
- 🤩 Reaction options.
- 📞 Support for threaded replies.
- 👤 User mention functionality.
- 👥 Display list of users who reacted (auth mode only).
- 🔢 Pagination.
- 👮‍♂️Support for both authentication mode and guest mode (mutually exclusive).
- 🔍 Advanced filtering and sorting options.
- 🥰 Responsive design using a combination of Livewire and Alpine.js.
- 🚀 Optimized performance.
And much more.

## Why Commenter

The commenting feature is a common requirement for most websites. Allowing users to comment enables interaction and enhances the user experience. While Laravel offers a wealth of packages to meet various project needs, there are limited options when it comes to commenting features. 

### Here are some drawbacks of existing commenting packages:

- Outdated: Uses outdated technologies and is not actively maintained.
- Lack of Features: Missing many essential features.
- No Admin Panel: Requires additional time to implement an admin panel independently.
- Bad Design: Interfaces are not user-friendly.
- Not Mobile Responsive: Not optimized for mobile devices.
- Performance Issues: May cause performance slowdowns.
- Lack of Configurability: Limited options for customization and configuration.

Due to these issues, most companies tend to opt for commercial packages or plugins. However, spending extra money on commercial packages reduces the company's overall profit. This package is developed to address all these shortcomings.

> As a full-stack developer, I have personally encountered these issues. That's why I developed this package—not only for my own projects but also to benefit other developers.

## Quick Start

### Installation
```bash
composer require lakm/laravel-comments -W
php artisan commenter:install
```

### Usage
Implement `CommentableContract` and import `Commentable` trait in commentable model.

```php
use LakM\Comments\Concerns\Commentable;
use LakM\Comments\Contracts\CommentableContract;

class Post extends Model implements CommentableContract
{
    use Commentable;
}

```

Implement `CommenterContract` and import `Commenter` trait in commenter model.

```php
use LakM\Comments\Concerns\Commenter;
use LakM\Comments\Contracts\CommenterContract;

class User extends Model implements CommenterContract
{
    use Commenter;
}
```

Include styles in your layout.

```html
<html>
    <head>
        @commentsStyles
    </head>
</html>
```

Include scripts in your layout.

```html
<html>
    <body>
        @commentsScripts
    </body>
</html>
```

Include the comments component with the related model.

```html
    <x-comments::index :model="$post" />
```

> [!Warning]
> You can omit the index part but make sure to include the double colon. Otherwise Laravel will search for the component in project instead of package.

```html
    <x-comments:: :model="$post" />
```


## Demo

### Project
https://github.com/Lakshan-Madushanka/laravel-comments-demo

### Mini video
https://youtu.be/6CxgmvESsdc

### Full Video
https://youtu.be/dvFIOhSpmv8

> [!Note]
> This mini demo video provides a basic overview of Commenter. The full scope and features of Commenter are much more extensive. A comprehensive video will be published with the stable release. 

## Changelog
Please see [CHANGELOG](https://github.com/Lakshan-Madushanka/laravel-comments/blob/main/CHANGELOG.md) for more information what has changed recently.

## Testing
```bash
./vendor/bin/pest
```

## Roadmap

|Feature                                                   | Status            |
|----------------------------------------------------------|-------------------|                                                           
|Comment/Reply Report                                      | TBI               |             
|Guest mode email verification                             | TBI               | 
|Dark Mode Support

## Security
Please see [here](https://github.com/Lakshan-Madushanka/laravel-comments/blob/main/SECURITY.md) for our security policy.

## License
The MIT License (MIT). Please see [License File](https://github.com/Lakshan-Madushanka/laravel-comments/blob/main/LICENSE.md) for more information.
