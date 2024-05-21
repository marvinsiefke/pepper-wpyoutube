<?php
/**
 * Plugin Name: pepper Youtube
 * Description: GDPR-compliant YouTube Embedding Tool. Use it like this: [peppertube id="SXHMnicI6Pg"]
 * Version: 1.0
 * Author: pepper
 * Author URI: https://pepper.green
 * Text Domain: pepperyoutube
 * Domain Path: /languages
 */

defined('ABSPATH') or die('Access denied');

// Configuration
$constants = [
	'PEPPERYOUTUBE_CACHE_DIR'	=> WP_CONTENT_DIR . '/cache/pepperyoutube/',
	'PEPPERYOUTUBE_LOAD_CSS'	=> true
];

foreach ($constants as $key => $value) {
	if (!defined($key)) {
		define($key, $value);
	}
}

// Load plugin textdomain for translations
function pepperyoutube_load_textdomain() {
	load_plugin_textdomain('pepperyoutube', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'pepperyoutube_load_textdomain');

// Register js & css
function pepperyoutube_enqueue_scripts() {
	wp_register_script('pepperyoutube-js', plugins_url('pepperyoutube.js', __FILE__), array(), null, true);
	wp_register_style('pepperyoutube-css', plugins_url('pepperyoutube.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'pepperyoutube_enqueue_scripts');

// Download thumbnail using cURL
function pepperyoutube_download_thumbnail($url, $save_path) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$data = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($http_code == 200 && $data !== false) {
		file_put_contents($save_path, $data);
		return true;
	}

	return false;
}

// Shortcode
function pepperyoutube_shortcode($atts) {
	$attr = shortcode_atts(array('id' => ''), $atts);

	if (empty($attr['id'])) {
		return '<p>' . __('Error: No video ID given.', 'pepperyoutube') . '</p>';
	}

	$video_id = esc_attr($attr['id']);
	$cached_file_path = PEPPERYOUTUBE_CACHE_DIR . $video_id . '.jpg';

	// Check if the cover is already cached, otherwise download it
	if (!file_exists($cached_file_path)) {
		if (!is_dir(PEPPERYOUTUBE_CACHE_DIR)) {
			mkdir(PEPPERYOUTUBE_CACHE_DIR, 0755, true);
		}

		$image_url = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
		if (pepperyoutube_download_thumbnail($image_url, $cached_file_path) === false) {
			return '<p>' . __('Error: Unable to download video cover.', 'pepperyoutube') . '</p>';
		}
	}

	$cover_url = content_url('cache/pepperyoutube/' . $video_id . '.jpg');

	wp_enqueue_script('pepperyoutube-js');

	if(PEPPERYOUTUBE_LOAD_CSS === true) {
		wp_enqueue_style('pepperyoutube-css');
	}

	$html = '<div class="pepperyoutube" data-id="' . $video_id . '">
				<div class="pepperyoutube__cover" style="background-image: url(' . $cover_url . ');"></div>
				<div class="pepperyoutube__consent">
					<div class="pepperyoutube__consentInner">
						<p class="pepperyoutube__consentBody"><strong>' . __('Consent to use YouTube videos', 'pepperyoutube') . '</strong> ' . __('In enhanced privacy mode, YouTube does not set cookies. However, the IP address may be transmitted to YouTube once the video is loaded. Please confirm if the video may be loaded:', 'pepperyoutube') . '</p>
						<button class="pepperyoutube__consentButton">' . __('Load video via YouTube', 'pepperyoutube') . '</button>
					</div>
				</div>
			</div>';

	return $html;
}
add_shortcode('peppertube', 'pepperyoutube_shortcode');
