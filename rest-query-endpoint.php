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


add_action( 'rest_api_init', __NAMESPACE__ . '\register_query_route');

function register_query_route() {

	// Register endpoint
	register_rest_route('wp/v2', '/query',
		array(
			'methods' => 'GET',
			'callback' => function ($data) {

				// Check if JSON parameter is set
				if($data->get_param( 'json' )){
					$json = $data->get_param( 'json' );
				}
				else{
					echo __('ERROR: You need a JSON parameter, e. g. ') . get_site_url() . '/wp-json/wp/v2/query?json={"query":"WP_Query","args":{"orderby":"name","order":"ASC"}}';
					return;
				}

				// Devode JSON to PHP object
				$params = json_decode($json);

				// Check if JSON is valid JSON
				if (!(json_last_error() === JSON_ERROR_NONE)) {
					echo  __('ERROR: Malformed JSON.', 'wp');
					return;
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
					echo __('ERROR: Unknown Query.', 'wp');
					return;
				}
				return $objects;
			}
		)
	);
}