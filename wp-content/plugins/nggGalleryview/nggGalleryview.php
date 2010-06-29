<?php
/*
Plugin Name: NextGEN Galleryview
Plugin URI: http://alexrabe.de/
Description: Add the script files and template for the jQuery Plugin Galleryview integration from Jack Anderson (http://www.spaceforaname.com/galleryview/). Use the shortcode [nggallery id=x template="galleryview"] to show the new layout.
Author: Alex Rabe
Author URI: http://alexrabe.de/
Version: 1.0.1
*/

if (!class_exists('nggGalleryview')) {
	class nggGalleryview {

		var $plugin_url = false;

		function nggGalleryview() {

			$this->plugin_url = WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/';

			// load scripts into the head
			add_action('wp_print_scripts', array(&$this, 'load_scripts') );
			add_action('wp_print_styles', array(&$this, 'load_styles') );
			add_filter('ngg_render_template', array(&$this, 'add_template'), 10, 2);
		}

		function add_template( $path, $template_name = false) {
			if ($template_name == 'gallery-galleryview')
				$path = WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) . '/view/gallery-galleryview.php';

			return $path;
		}

		function load_styles() {
			wp_enqueue_style('galleryview', $this->plugin_url . 'galleryview.css', false, '1.0.1', 'screen');
		}

		function load_scripts() {
			//wp_enqueue_script('easing', $this->plugin_url . 'jquery.easing.1.2.js', array('jquery'), '1.2');
			//wp_enqueue_script('galleryview', $this->plugin_url . 'jquery.galleryview-1.1-pack.js', array('jquery'), '1.1');
 			//wp_enqueue_script('timers', $this->plugin_url . 'jquery.timers-1.1.2.js', array('jquery'), '1.1.2');
		}

	}

	// Start this plugin once all other plugins are fully loaded
	add_action( 'plugins_loaded', create_function( '', 'global $nggGalleryview; $nggGalleryview = new nggGalleryview();' ) );

}