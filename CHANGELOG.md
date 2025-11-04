# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [[1b90cb7](https://github.com/ohmediaorg/file-bundle/commit/1b90cb78274c97897c54e922b223c78b7247c056)] - 2025-10-17

### Added

- attributes parameter to `image_tag` Twig function

### Changed

### Fixed

## [[84b7bc9](https://github.com/ohmediaorg/file-bundle/commit/84b7bc98356f9c28a0a349ab38f89ad7f817e78c)] - 2025-10-17

### Added

- width and height parameters to `image_tag` Twig function
- support for webp and bmp image uploads
- optional parameters to FileBrowser listing function to specify images, files,
or both

### Changed

- renamed `default_image_width` bundle config parameter to `max_image_dimension`
- shortcode images are now constrained in both dimensions

### Fixed

## [[84c7e53](https://github.com/ohmediaorg/file-bundle/commit/84c7e53f224c336900baec26b8fc0424a63fcbbf)] - 2025-09-11

### Added

- developer users can download a representation of the Files listing

### Changed

### Fixed

## [[785022a](https://github.com/ohmediaorg/file-bundle/commit/785022a7bd1cf3858a6023e58adb3620254f7e0d)] - 2025-06-13

### Added

### Changed

### Fixed

- fixes related to addition of FileEntityType form events

## [[719506f](https://github.com/ohmediaorg/file-bundle/commit/719506f828555a76c604799c716e481f342beac7)] - 2025-06-03

### Added

### Changed

- FileEntityType updated to use form events so "data" option does not have to
be set manually

### Fixed

## [[4ff94cd](https://github.com/ohmediaorg/file-bundle/commit/4ff94cdb30fd2e57794e86b1069451b01e4fbb4a)] - 2025-05-06

### Added

- bulk actions to move or delete files in the file listing
- background JS checks for listing delete icons to improve page load times

### Changed

- if a file has `getimagesize()` data it is automatically flagged as an image

### Fixed

- wrong function called in resize parent collection helper functions

## [[6901666](https://github.com/ohmediaorg/file-bundle/commit/690166643f9dbad10f745e776dff417679931e41)] - 2025-02-12

### Added

### Changed

- `<img>` rendered through the `image_tag` Twig function or ImageManager get the
`loading="lazy"` attribute by default

### Fixed
