# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ACF to REST API is a WordPress plugin (v3.3.5) that exposes Advanced Custom Fields (ACF) endpoints in the WordPress REST API. It supports both ACF Pro and the free version.

## Development Environment

This is a WordPress plugin - no build commands, tests, or linting are defined. Development requires:
- A WordPress installation with ACF plugin active
- PHP 5.3.2+
- WordPress REST API (built into WP 4.7+)

## Architecture

### Version System

The plugin supports two API versions controlled by `ACF_TO_REST_API_REQUEST_VERSION` constant or admin setting:

- **V3 (default)**: Modern implementation in `/v3/` - registers routes per post type/taxonomy dynamically
- **V2 (legacy)**: Older implementation in `/legacy/v2/` - uses different controller structure

Version selection happens in `class-acf-to-rest-api.php:58-67` via `handle_request_version()`.

### Controller Hierarchy (V3)

All controllers extend `ACF_To_REST_API_Controller` which extends `WP_REST_Controller`:

```
ACF_To_REST_API_Controller (base)
├── ACF_To_REST_API_Posts_Controller     (posts, pages, CPTs)
├── ACF_To_REST_API_Attachments_Controller (media)
├── ACF_To_REST_API_Terms_Controller     (taxonomies)
├── ACF_To_REST_API_Comments_Controller
├── ACF_To_REST_API_Options_Controller   (ACF options pages)
└── ACF_To_REST_API_Users_Controller
```

### Key Classes

- `ACF_To_REST_API_ACF_API` (`v3/lib/class-acf-to-rest-api-acf-api.php`): Core class handling ACF field retrieval and ID formatting. Critical method `format_id()` converts IDs to ACF's expected format (e.g., `user_123`, `comment_456`, `taxonomy_789`).

- `ACF_To_REST_API_ACF_Field_Settings` (`v3/lib/class-acf-to-rest-api-acf-field-settings.php`): Adds per-field "Show in REST" and "Edit in REST" settings to ACF admin when enabled via filters.

### REST API Namespace

All endpoints use `acf/v3` namespace (or `acf/v2` for legacy).

### Hook Integration

The plugin hooks into `rest_pre_dispatch` (ACF 5.12+) or `rest_api_init` (older versions) to register routes. This is checked in `class-acf-to-rest-api.php:70-71`.

## Important Filters

```php
// Control field visibility per request
acf/rest_api/item_permissions/get
acf/rest_api/item_permissions/update

// Modify the request key (default: 'fields')
acf/rest_api/key

// Transform field data
acf/rest_api/{type}/get_fields
acf/rest_api/{type}/prepare_item

// Enable per-field REST settings in admin
acf/rest_api/field_settings/show_in_rest
acf/rest_api/field_settings/edit_in_rest

// Customize entity ID resolution
acf/rest_api/id
```

## File Organization

```
/
├── class-acf-to-rest-api.php      # Main plugin entry, version handling
├── v3/                             # Current API implementation
│   ├── class-acf-to-rest-api-v3.php
│   └── lib/
│       ├── class-acf-to-rest-api-acf-api.php      # ACF field operations
│       ├── class-acf-to-rest-api-acf-field-settings.php
│       └── endpoints/              # REST controllers
├── legacy/v2/                      # Legacy API (deprecated)
└── shared/includes/admin/          # Settings UI
```

## Text Domain

The plugin uses `acf-to-rest-api` for all translatable strings.

## Version 3.3.5 Changes

### Security Fix: CVE-2025-62979

**Vulnerability**: Sensitive Data Exposure (CVSS 5.3)

The plugin previously allowed unauthenticated access to all ACF field data through the REST API. Permission callbacks returned `true` by default, exposing potentially sensitive information including API keys, user data, and configuration stored in ACF fields.

#### Security Changes

1. **Authentication Required**: All GET endpoints now require users to be logged in by default
2. **Options Endpoints**: Require `manage_options` capability (admin-level access)
3. **User List Endpoints**: Require `list_users` capability
4. **Individual User Data**: Users can view their own ACF data, or need `list_users` for others
5. **register_rest_field Fix**: The ACF data exposed via WordPress core REST endpoints now respects permissions

#### Backward Compatibility

Sites that rely on public ACF API access can restore the previous behavior:

```php
// Add to wp-config.php
define( 'ACF_TO_REST_API_ALLOW_PUBLIC_ACCESS', true );
```

Or use existing filter hooks for granular control:

```php
// Allow public access to specific entity types
add_filter( 'acf/rest_api/item_permissions/get', function( $permission, $request, $type ) {
    if ( $type === 'post' ) {
        return true; // Allow public access to post ACF data
    }
    return $permission;
}, 10, 3 );
```

### Author Change

Plugin authorship changed from `airesvsg` to `Prolific Digital`:
- Author: Prolific Digital
- Author URI: https://prolificdigital.com

### Donation Feature Removal

The donation notice functionality was completely removed from the plugin.

### Complete File Change Summary

#### Files Modified

| File | Changes |
|------|---------|
| `class-acf-to-rest-api.php` | Version bump to 3.3.5, author change to Prolific Digital, removed donation class require |
| `v3/lib/endpoints/class-acf-to-rest-api-controller.php` | Added `allow_public_access()`, `check_read_permission()`, fixed permission callbacks |
| `v3/lib/endpoints/class-acf-to-rest-api-options-controller.php` | Override `check_read_permission()` to require `manage_options` |
| `v3/lib/endpoints/class-acf-to-rest-api-users-controller.php` | User-specific permission checks for get/update operations |
| `legacy/v2/lib/endpoints/class-acf-to-rest-api-controller.php` | Added `allow_public_access()`, `check_read_permission()`, fixed permission callback |
| `legacy/v2/lib/endpoints/class-acf-to-rest-api-option-controller.php` | Override permissions to require `manage_options` |
| `shared/includes/admin/classes/class-acf-to-rest-api-settings.php` | Removed donation link from plugin row meta |
| `shared/includes/admin/views/html-settings-field.php` | Removed donation button from settings page |
| `readme.txt` | Updated stable tag to 3.3.5, changed contributor, removed donate link, added changelog |

#### Files Deleted

| File | Reason |
|------|--------|
| `shared/includes/admin/classes/class-acf-to-rest-api-donation.php` | Donation feature removed |
| `shared/includes/admin/views/html-notice-donation.php` | Donation feature removed |
| `shared/assets/css/acf-to-rest-api-donation.css` | Donation feature removed |
| `shared/assets/js/acf-to-rest-api-donation.js` | Donation feature removed |

### Permission Matrix

| Endpoint Type | GET Permission | UPDATE Permission |
|---------------|----------------|-------------------|
| Posts/Pages/CPTs | `is_user_logged_in()` | `edit_posts` |
| Options | `manage_options` | `manage_options` |
| Users (own data) | `is_user_logged_in()` | `edit_user` |
| Users (others) | `list_users` | `edit_user` |
| Terms | `is_user_logged_in()` | `edit_posts` |
| Comments | `is_user_logged_in()` | `edit_posts` |
| Attachments | `is_user_logged_in()` | `edit_posts` |
