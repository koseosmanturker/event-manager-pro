# Event Manager Pro

A custom WordPress plugin that provides a complete Event management
system including:

-   Custom Post Type (Events)
-   Custom Taxonomy (Event Types)
-   Custom Meta Fields (Event Date, Location)
-   Admin UI enhancements
-   Front-end templates
-   Shortcode with filtering
-   RSVP system
-   Email notifications
-   REST API integration
-   Unit tests
-   Internationalization support

------------------------------------------------------------------------

# Architecture Overview

The plugin follows WordPress best practices:

-   **Custom Post Type:** `emp_event`
-   **Taxonomy:** `emp_event_type`
-   **Meta keys:**
    -   `_emp_event_date`
    -   `_emp_event_location`
    -   `_emp_rsvps`
-   **REST namespace:** `emp/v1`
-   Template override via `template_include`
-   Secure form handling via:
    -   Nonce verification
    -   Capability checks
    -   Input sanitization
    -   Output escaping

------------------------------------------------------------------------

# Features

## 1. Custom Post Type & Taxonomy

-   `emp_event` (public, REST-enabled)
-   `emp_event_type` (hierarchical)
-   Registered with `show_in_rest = true`

------------------------------------------------------------------------

## 2. Admin Enhancements

-   Custom Meta Box:
    -   Event Date (validated YYYY-MM-DD)
    -   Location
-   Admin list columns for Date & Location

------------------------------------------------------------------------

## 3. Front-End

-   Custom templates:
    -   `single-emp_event.php`
    -   `archive-emp_event.php`
-   Filterable listing form
-   Shortcode: `[events]`

------------------------------------------------------------------------

## 4. Filtering System

Shortcode supports:

    [events]
    [events type="conference"]
    [events from="2026-01-01" to="2026-12-31"]
    [events search="istanbul"]

URL parameters (GET):

-   `emp_type`
-   `emp_from`
-   `emp_to`
-   `emp_s`

Example:

    /events/?emp_type=conference&emp_from=2026-01-01&emp_to=2026-12-31&emp_s=ai

Optimized with:

-   `no_found_rows`
-   meta_query date comparison
-   minimal query footprint

------------------------------------------------------------------------

## 5. RSVP System

-   Front-end form on single event page
-   Duplicate prevention (by email)
-   Stored in `_emp_rsvps` post meta
-   Redirect-after-submit pattern (prevents resubmission)

### Email Notifications

-   On RSVP → confirmation email to user
-   On publish → admin notified
-   On update → RSVP users notified
-   Simple rate limiting (5-minute throttle)

------------------------------------------------------------------------

## 6. REST API

### Default Event Endpoint

    GET /wp-json/wp/v2/emp_event

Meta fields are exposed via `register_post_meta()`.

### RSVP Endpoint

    POST /wp-json/emp/v1/events/<id>/rsvp

Body:

``` json
{
  "name": "John",
  "email": "john@example.com"
}
```

Responses:

``` json
{"status":"success","event_id":8}
```

``` json
{"status":"already_rsvped"}
```

Includes:

-   Input validation
-   Email validation
-   Basic rate limiting
-   Duplicate detection

------------------------------------------------------------------------

# Security Practices

-   Nonce verification for all POST forms
-   `sanitize_text_field`
-   `sanitize_email`
-   `is_email` validation
-   Output escaping (`esc_html`, `esc_attr`, `esc_url`)
-   Capability checks (`current_user_can`)
-   Safe redirects (`wp_safe_redirect`)

------------------------------------------------------------------------

# Performance Considerations

-   `no_found_rows` in custom queries
-   Meta query limited to required fields
-   Basic transient-based rate limiting
-   Avoids unnecessary database writes

------------------------------------------------------------------------

# Internationalization

-   Text domain: `event-manager-pro`
-   All strings wrapped with `__()` or `esc_html__()`

Generate POT file:

    wp i18n make-pot wp-content/plugins/event-manager-pro wp-content/plugins/event-manager-pro/languages/event-manager-pro.pot

------------------------------------------------------------------------

# Installation

1.  Copy folder to:

```{=html}
<!-- -->
```
    wp-content/plugins/event-manager-pro

2.  Activate from WordPress Admin → Plugins
3.  Flush permalinks:
    -   Settings → Permalinks → Save

------------------------------------------------------------------------

# Running Tests

The plugin includes PHPUnit tests.

### Requirements

-   PHP
-   PHPUnit
-   WordPress Test Suite

### Setup Example

Clone WordPress test suite:

    git clone https://github.com/WordPress/wordpress-develop.git

Set environment variable:

    set WP_TESTS_DIR=C:\path\to\wordpress-develop\tests\phpunit

Run tests:

    phpunit

### Covered Scenarios

-   Custom Post Type registration
-   Taxonomy registration
-   RSVP duplicate prevention

------------------------------------------------------------------------

# Future Improvements

-   Custom database table for RSVP scalability
-   Caching layer for archive queries
-   REST authentication options
-   Gutenberg block for events
-   Pagination support for shortcode

------------------------------------------------------------------------

# License

GPL-2.0+
