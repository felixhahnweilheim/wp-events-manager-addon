# Felix' Events Manager Addon

This is a simple WordPress plugin adding a few features to the [Events Manager](https://wordpress.org/plugins/events-manager/) plugin.

Each of this features needs to be switched on seperately via the admin settings page.

**Tested with**
- Events Manger 6.4.6.4
- WordPress 6.4.2
- PHP 8.2

## 1. Image Fallback
Use the category image as fallback if an individual event has no image.

## 2. Bookings default
Enable bookings by default for every new event if the user can manage bookings.

## 3. Allow GET search
Allow to search via some URL parameters, namely category by tag_id, scope, country by 2 letter uppercase code.

## 4. Add Schema.org tags to single events pages
for SEO optimization

## 5. Add events and locations to the search

## 6. Placeholder for duplication
`#_DUPLICATELINK`

## 7. Allow Emojis in event description

## 8. Translatable Event description
Add an extra description field for every additional language. The description will be output depending on the current language (`get_locale()`).
If the current language's description is empty, the base language one will be taken instead.
If the base language's description is empty, the first other language with a non-empty description will be taken.

You have to fill both admin fields (base languages and translation languages) to use the feature.

