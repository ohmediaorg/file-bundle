# Overview

This bundle offers functionality for managing files.

## Installation

First, make sure the OHMediaCleanupBundle and OHMediaSecurityBundle are installed.

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

Import the routes in `config/routes.yaml`:

```yaml
oh_media_file:
    resource: '@OHMediaFileBundle/Controller/'
    type: annotation
```

On your remote server, you may need to adjust the permissions of this folder:

```bash
$ sudo chown -R www-data:www-data oh_media_files
```

## Entities

There is a `OHMedia\FileBundle\Entity\File`
and `OHMedia\FileBundle\Entity\Image` entity,
with corresponding form types.

Use the maker command to add these to your entity,
then utilize the form types in your entity's form.

## Templating

Outputting a file or image path in a template:

```twig
{# OHMedia\FileBundle\Entity\File file #}
<a href="{{ file_path(file) }}">My File</a>

{# OHMedia\FileBundle\Entity\Image image #}
<img src="{{ image_path(image) }}" title="My Image" />
```

You can also generate resized images on the fly by passing in width/height:

```twig
{% set width = 100 %}
{% set height = 100 %}

{# OHMedia\FileBundle\Entity\Image image #}
<img src="{{ image_path(image, width, height) }}" title="My Thumbnail" />
```

If one of width or height is `null` (default value),
the other will be automatically calculated based on the original ratio.

If both are `null`, you will get the original image.

There's also a function to generate an entire `<img />` tag:

```twig
{{ image_tag(image, {width: 600, height: 400}) }}
```

The second parameter is for HTML attributes. The `width` and/or `height`
attributes will be used to generated a resized image like the above. The `alt`
and `src` attribute will be overridden and don't need to be provided.

## Uploading

Files can be uploaded using Javascript via the `oh_media_file_upload` route. The
data should be in a parameter called `files`.

```twig
<input id="file-upload" type="file" multiple />

<script>
let uploadRoute = '{{ path('oh_media_file_upload') }}';
let fileInput = document.getElementById('#file-upload');

fileInput.addEventListener('change', () => {
  let formData = new FormData();
  formData.append('files', fileInput.files);
  
  let xhr = new XMLHttpRequest();
  xhr.responseType = 'json';
  
  xhr.onload = () => {
    let files = xhr.response.files;
    
    files.forEach((file) => {
      // file.id, file.name, file.path
    });
  };
  
  xhr.open('POST', uploadRoute, true);
  xhr.send(formData);
});
</script>
```

_**Note:** Files uploaded this way will be marked as temporary. It's your
responsibility to mark them as not temporary as needed. The idea being, if a
process is exited early, the files created during this process won't just sit
there forever._

## Temporary Files

Temporary files will be deleted if they are over 1 day old.
