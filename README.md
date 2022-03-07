Overview
========

This bundle offers functionality to leverage email sending via a CRON job.

Installation
------------

First, make sure the OHMediaSecurityBundle is installed.

Enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    OHMedia\FileBundle\FileBundle() => ['all' => true],
];
```

Make and run the migration:

```bash
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

Create the daily CRON job:

```bash
0 0 * * * /path/to/php /path/to/symfony/bin/console ohmedia:file:cleanup
```

This removes any files that have `temporary === true`.

Create your upload directory:

```bash
# start in the project root directory
$ mkdir public/files
```

and configure the path:

```yaml
jstn_thms_file:
    upload_dir: /files
```

Note that `upload_dir` is expected to be relative to the `public` directory.

Update your `.gitignore` to ignore everything in your files directory:

```
# ...
public/files/
```

Make sure the directory gets created when you clone your repository:

```bash
$ touch public/files/.empty
$ git add -f public/files/.empty
```

On your remote server, you may need to adjust the permissions of this folder:

```bash
$ sudo chown -R www-data:www-data public/files
```

Entities
--------

There is a `OHMedia\FileBundle\Entity\File`
and `OHMedia\FileBundle\Entity\Image` entity,
with corresponding form types.

Use the maker command to add these to your entity,
then utilize the form types in your entity's form.

Templating
----------

Outputting a file or image path in a template:

```twig
{# OHMedia\FileBundle\Entity\File file #}
<a href="{{ ohmedia_file(file) }}">My File</a>

{# OHMedia\FileBundle\Entity\Image image #}
<img src="{{ ohmedia_image(image) }}" title="My Image" />
```

You can also generate resized images on the fly by passing in width/height:

```twig
{% set width = 100 %}
{% set height = 100 %}

{# OHMedia\FileBundle\Entity\Image image #}
<img src="{{ ohmedia_image(image, width, height) }}" title="My Thumbnail" />
```

If one of width or height is `null` (default value),
the other will be automatically calculated based on the original ratio.

If both are `null`, you will get the original image.
