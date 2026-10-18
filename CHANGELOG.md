# Changelog

All notable changes to this module are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [2.2.1]

### Fixed

- Updating from a version older than 2.2.1 maps a legacy Smarty hook name stored in the search script setting to its `layout.*` theme hook, and resets unknown values to `layout.body.bottom`. Before, such an install silently stopped printing the script.
- The back-office help of the search script field lists the valid theme hook names.

### Changed

- The search script theme hook reads its setting once per request instead of on every theme hook of the page.
