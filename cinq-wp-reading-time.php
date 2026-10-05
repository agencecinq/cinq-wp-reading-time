<?php
/**
 * Plugin Name: CINQ Reading Time
 * Plugin URI: https://github.com/agencecinq/cinq-wp-reading-time
 * Description: Stores an estimated reading time (minutes) on posts and exposes a raw integer. No markup, no settings screen.
 * Version: 1.0.2
 * Author: CINQ
 * Author URI: https://agencecinq.com/
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Text Domain: cinq-wp-reading-time
 *
 * @package CinqReadingTime
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post meta key for the stored reading time (minutes).
 */
const CINQ_READING_TIME_META_KEY = '_cinq_reading_time';

/**
 * Default words-per-minute used for the estimate.
 */
const CINQ_READING_TIME_WPM = 200;

/**
 * Estimated reading time in minutes for a post.
 *
 * Prefers the stored meta; falls back to a live estimate from post_content
 * when the meta is missing (e.g. posts not re-saved yet).
 *
 * @param int $post_id Post ID. Defaults to the current post in the loop.
 * @return int Minutes (0 when empty or unavailable).
 */
function cinq_reading_time( int $post_id = 0 ): int {
	if ( $post_id <= 0 ) {
		$post_id = (int) get_the_ID();
	}

	if ( $post_id <= 0 ) {
		return 0;
	}

	$stored = get_post_meta( $post_id, CINQ_READING_TIME_META_KEY, true );

	if ( is_numeric( $stored ) && (int) $stored > 0 ) {
		return (int) $stored;
	}

	return cinq_reading_time_estimate( (string) get_post_field( 'post_content', $post_id ) );
}

/**
 * Estimate reading time in minutes from HTML or plain text.
 *
 * @param string $content Post content.
 * @return int At least 1 when the content is not empty, 0 otherwise.
 */
function cinq_reading_time_estimate( string $content ): int {
	$text = trim( wp_strip_all_tags( $content ) );

	if ( '' === $text ) {
		return 0;
	}

	$words = preg_match_all( '/[\p{L}\p{N}\'-]+/u', $text );

	if ( ! $words ) {
		return 1;
	}

	$wpm = (int) apply_filters( 'cinq_reading_time_wpm', CINQ_READING_TIME_WPM );
	$wpm = $wpm > 0 ? $wpm : CINQ_READING_TIME_WPM;

	return max( 1, (int) ceil( $words / $wpm ) );
}

/**
 * Persist reading time when a post is saved.
 *
 * @param int      $post_id Post ID.
 * @param \WP_Post $post    Post object.
 * @return void
 */
function cinq_reading_time_save_post( int $post_id, $post ): void {
	if ( ! $post instanceof \WP_Post ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$minutes = cinq_reading_time_estimate( (string) $post->post_content );

	if ( $minutes > 0 ) {
		update_post_meta( $post_id, CINQ_READING_TIME_META_KEY, $minutes );
		return;
	}

	delete_post_meta( $post_id, CINQ_READING_TIME_META_KEY );
}

add_action( 'save_post_post', 'cinq_reading_time_save_post', 10, 2 );
