<?php

/**
 * Plugin Name: Rest Query Endpoint (Experiment)
 * Plugin URI: https://github.com/mariohamann/rest-query-endpoint
 * Version: 1.0.0
 * Description: Experiment to create an Query-endpoint for Gutenberg
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Author: mariohamann
 * Author URI: https://github.com/mariohamann
 */

namespace MarioHamann\RestQueryEndpoint;

/* Verbiete den direkten Zugriff auf die Plugin-Datei */
if ( ! defined( 'ABSPATH' ) ) exit;
/* Nach dieser Zeile den Code einfügen*/

QueryEndpoint::get_instance();

class QueryEndpoint {
	public static function get_instance() {
	static $instance = null;
		if (is_null($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array($this, 'registerQueryRoute'));
	}

	public function registerQueryRoute() {

		// Register endpoint
		register_rest_route('wp/v2', '/query',
			array(
				'methods' => 'GET',
				'callback' => function ($data) {
					// Get JSON
					$json = $data->get_param( 'json' ) ?? '';
					$params = json_decode($json);

					// Check if JSON is valid JSON
					if (!(json_last_error() === JSON_ERROR_NONE)) {
						return  __('ERROR: Malformed JSON.', 'wp');
					}

					// Set defaults
					$query = $params->query ?? 'WP_Query';
					$args = $params->args ?? array("post_type" => "post");
					
					// Create the Query
					if($query === 'WP_Query'){
						$query = new \WP_Query( $args );
						$objects = $query->posts;
					}
					elseif($query === 'WP_Term_Query'){
						$query = new \WP_Term_Query( $args );
						$objects = $query->terms;
					}
					else{
						return __('ERROR: Unknown Query.', 'wp');
					}
					return $objects;
				}
			)
		);
	}
}