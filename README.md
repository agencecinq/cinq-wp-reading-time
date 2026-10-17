# CINQ Reading Time

WordPress plugin that stores an estimated reading time (minutes) on posts and exposes a raw integer. No markup, no settings screen.

**Repository:** [`agencecinq/cinq-wp-reading-time`](https://github.com/agencecinq/cinq-wp-reading-time)

## Requirements

- WordPress 6.0+
- PHP 8.1+

## Lint (WordPress Coding Standards)

```bash
composer install
composer lint          # phpcs
composer lint:fix     # phpcbf (auto-fix)
```

Use `./vendor/bin/phpcs`, not the global `phpcs` binary — the global install does not register the WordPress standards.

## Install

Copy the plugin file into `mu-plugins`. WordPress loads it on every request. It is listed under **Plugins → Must-Use**, not with the regular plugins.

```bash
cp cinq-wp-reading-time.php /path/to/wp-content/mu-plugins/
```

Replace that file when the plugin changes.

To install it as a regular plugin, copy the folder to `wp-content/plugins/cinq-wp-reading-time` and activate **CINQ Reading Time** in the WordPress admin. It then appears in the plugin list and stays off until activated.

## API

```php
// Minutes for the current post in the Loop, or a given ID.
$minutes = cinq_wp_reading_time();
$minutes = cinq_wp_reading_time( 42 ); // int, 0 when empty
```

Stored under the private meta key `_cinq_reading_time`. Recalculated on every save for the configured post types. When the meta is missing, the value is estimated from `post_content`, whatever the post type.

### Optional filters

```php
// Post types that store a reading time on save (default: post).
add_filter( 'cinq_wp_reading_time_post_types', fn () => array( 'post', 'guide' ) );

// Words per minute (default: 200).
add_filter( 'cinq_wp_reading_time_wpm', fn () => 180 );
```

## Theme usage

In the template, inside the Loop. Formatting stays in the theme. This plugin only returns the number of minutes.

```php
$minutes = function_exists( 'cinq_wp_reading_time' )
	? cinq_wp_reading_time()
	: 0;

if ( $minutes > 0 ) {
	printf(
		esc_html__( '%d min read', 'cinq-wp-reading-time' ),
		$minutes
	);
}
```

## Scope

- Post types: `post` by default, more through `cinq_wp_reading_time_post_types`
- No options, no admin UI, no shortcode, no front-end assets

## Migration from 1.x

2.0.0 names the hooks, functions and constants after the plugin slug, like every CINQ plugin: `cinq-wp-reading-time` gives the `cinq_wp_reading_time_` prefix. The old names are not kept.

| | 1.x | 2.0.0 |
| --- | --- | --- |
| Filter | `cinq_reading_time_post_types` | `cinq_wp_reading_time_post_types` |
| Filter | `cinq_reading_time_wpm` | `cinq_wp_reading_time_wpm` |
| Function | `cinq_reading_time()` | `cinq_wp_reading_time()` |
| Function | `cinq_reading_time_estimate()` | `cinq_wp_reading_time_estimate()` |
| Function | `cinq_reading_time_post_types()` | `cinq_wp_reading_time_post_types()` |
| Function | `cinq_reading_time_save_post()` | `cinq_wp_reading_time_save_post()` |
| Constant | `CINQ_READING_TIME_WPM` | `CINQ_WP_READING_TIME_WPM` |
| Constant | `CINQ_READING_TIME_POST_TYPES` | `CINQ_WP_READING_TIME_POST_TYPES` |
| Constant | `CINQ_READING_TIME_META_KEY` | `CINQ_WP_READING_TIME_META_KEY` |

Rename them in the theme: filters in `add_filter()` and `remove_filter()` calls, functions where they are called, including `function_exists()` checks. The `_cinq_reading_time` meta key does not change: stored reading times are kept.

## License

MIT © [CINQ](https://agencecinq.com)
