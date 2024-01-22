# Felix' Events Manager Addon

This is a simple WordPress plugin adding a few features to the [Events Manager](https://wordpress.org/plugins/events-manager/) plugin.

Each of this features can be switched off via `wp-config.php`. The respective PHP constants are mentioned below.

**Tested with**
- Events Manger 6.4.6.4
- WordPress 6.4.2
- PHP 8.2

## 1. Image Fallback
Use the category image as fallback if an individual event has no image.

`boolean FX_EM_IMAGE_FALLBACK`

## 2. Bookings default
Enable bookings by default for every new event if the user can manage bookings.

`boolean FX_EM_BOOKINGS_DEFAULT`

## 3. Allow GET search
Allow to search via some URL parameters, namely category by tag_id, scope, country by 2 letter uppercase code.

`boolean FX_EM_ALLOW_GET_SEARCH`
