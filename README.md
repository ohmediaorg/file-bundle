# Overview

This bundle offers functionality for managing files.

## Installation

Update `composer.json` by adding this to the `repositories` array:

```json
{
    "type": "vcs",
    "url": "https://github.com/ohmediaorg/file-bundle"
}
```

Then run `composer require ohmediaorg/file-bundle:dev-main`.

Import the routes in `config/routes.yaml`:

```yaml
oh_media_file:
    resource: '@OHMediaFileBundle/config/routes.yaml'
```

Run `php bin/console make:migration` then run the subsequent migration.

Create the non-public upload directory in the root of your project:

```bash
$ mkdir oh_media_files
```

_**Note:** this directory is not public on purpose so file access can go through
permission checks._

Update `.gitignore` to ignore everything in the non-public upload directory:

```
# ...
/oh_media_files
```

Make sure the directory gets created when you clone your repository:

```bash
$ touch oh_media_files/.gitkeep
$ git add -f oh_media_files/.gitkeep
```

On your remote server, you may need to adjust the permissions of this folder:

```bash
$ sudo chown -R www-data:www-data oh_media_files
```

Make sure this Twig tag is on every backend page:

```twig
{{ file_script() }}
```

## Configuration

Optionally, create `config/packages/oh_media_file.yml` with the following contents:

```yaml
oh_media_file:
    file_browser:
        enabled: true # default
        limit_mb: 1024 # default, between [100-5120]
```

## Entities

There is a `OHMedia\FileBundle\Entity\File` entity with a corresponding form type.

Use the maker command to add these to your entity, then utilize the form types
in your entity's form.

## Templating

Outputting a file or image path in a template:

```twig
{# OHMedia\FileBundle\Entity\File file #}
<a href="{{ file_path(file) }}">My File</a>

{# OHMedia\FileBundle\Entity\File file #}
<img src="{{ image_path(file) }}" title="My Image" />
```

You can also generate resized images on the fly by passing in width/height:

```twig
{% set width = 100 %}
{% set height = 100 %}

{# OHMedia\FileBundle\Entity\File file #}
<img src="{{ image_path(file, width, height) }}" title="My Thumbnail" />
```

If one of width or height is `null` (default value),
the other will be automatically calculated based on the original ratio.

If both are `null`, you will get the original image.

There's also a function to generate an entire `<img />` tag:

```twig
{{ image_tag(file, {width: 600, height: 400}) }}
```

The second parameter is for HTML attributes. The `width` and/or `height`
attributes will be used to generated a resized image like the above. The `alt`
and `src` attribute will be overridden and don't need to be provided.
