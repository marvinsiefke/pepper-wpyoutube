<?php
/**
 * Plugin Name: pepper Youtube
 * Description: GDPR-compliant YouTube Embedding Tool. Use it like this: [pepperyoutube id="SXHMnicI6Pg"]
 * Version: 1.0
 * Author: pepper
 * Author URI: https://pepper.green
 */

defined('ABSPATH') or die('No script kiddies please!');

// Central Cache Directory
define('PEPPERYOUTUBE_CACHE_DIR', WP_CONTENT_DIR . '/cache/pepperyoutube/');

// Assets
function pepperyoutube_enqueue_scripts() {
	// Registriert das JavaScript-File
	wp_register_script('pepperyoutube-js', plugins_url('pepperyoutube.js', __FILE__), array(), null, true);

	// Registriert das CSS-File
	wp_register_style('pepperyoutube-css', plugins_url('pepperyoutube.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'pepperyoutube_enqueue_scripts');

// Shortcode
function pepperyoutube_shortcode($atts) {
	$a = shortcode_atts(array(
		'id' => ''
	), $atts);

	if (empty($a['id'])) {
		return '<p>Fehler: Keine Video-ID angegeben.</p>';
	}

	$video_id = esc_attr($a['id']);
	$cached_file_path = PEPPERYOUTUBE_CACHE_DIR . $video_id . '.jpg';

	// Prüfen, ob das Cover bereits gecacht ist, sonst herunterladen
	if (!file_exists($cached_file_path)) {
		if (!is_dir(PEPPERYOUTUBE_CACHE_DIR)) {
			mkdir(PEPPERYOUTUBE_CACHE_DIR, 0755, true);
		}
		$image_url = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
		$image_data = file_get_contents($image_url);
		if ($image_data !== false) {
			file_put_contents($cached_file_path, $image_data);
		}
	}

	$cover_url = content_url('cache/pepperyoutube/' . $video_id . '.jpg');

	wp_enqueue_script('pepperyoutube-js');
	wp_enqueue_style('pepperyoutube-css');

	$html = '<div class="pepperyoutube" data-id="' . $video_id . '">
				<div class="pepperyoutube__cover" style="background-image: url(' . $cover_url . ');"></div>
				<div class="pepperyoutube__consent">
					<div class="pepperyoutube__consentInner">
						<p class="pepperyoutube__consentBody"><strong>Zustimmung zur Nutzung von YouTube-Videos:</strong> Im erweiterten Datenschutzmodus setzt YouTube keine Cookies. Allerdings könnte die IP-Adresse an YouTube übertragen werden, sobald das Video geladen wird. Bitte bestätigen, ob das Video geladen werden darf:</p>
						<button class="pepperyoutube__consentButton">Video über YouTube laden</button>
					</div>
				</div>
			</div>';

	return $html;
}

add_shortcode('pepperyoutube', 'pepperyoutube_shortcode');
