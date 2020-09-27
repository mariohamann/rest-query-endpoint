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

			
	 /**
	 * init routes
     */

 	private function __construct() {
 		add_action( 'rest_api_init', array($this, 'registerGetQueryRoute'));
 		add_action( 'rest_api_init', array($this, 'registerPostQueryRoute'));
 	}

	
	 /**
     * Callback for Query-REST requests
     * @param array parameters of REST request as array
     * @return array of objects (e. g. posts or terms)
     */

	private function do_query($params) {

		// Set defaults
		$query = $params['query'] ?? 'WP_Query';
		$args = $params['args'] ?? array("post_type" => "post");
		
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


	 /**
     * Register POST Endpoint
     * @return array return of do_query-callback
     */

 	public function registerPostQueryRoute() {

		// Register endpoint
		register_rest_route('wp/v2', '/query',
			array(
				'methods' => 'POST',
				'callback' => function (\WP_REST_Request $data){
					$params = $data->get_params();
					return $this->do_query($params);
				}
		));
	}
	
	 /**
     * DEPRECATED: Register GET Endpoint
     * @return array return of do_query-callback
     */

 	public function registerGetQueryRoute() {
		// Register endpoint
		register_rest_route('wp/v2', '/query',
			array(
				'methods' => 'GET',
				'callback' => function($data){
					// Check if JSON parameter is set
					if($data->get_param( 'json' )){
						$json = $data->get_param( 'json' );
						$params = json_decode($json, TRUE);

						// Check if JSON is valid JSON
						if (!(json_last_error() === JSON_ERROR_NONE)) {
							return  __('ERROR: Buggy JSON.', 'wp');
						}
						return $this->do_query($params);
					}
					else{
						return __('ERROR: You need a JSON parameter,', 'wp');
					}
				}
		));
	}

		
	 /**
     * Create instance of Class
     * @return class self
     */

 	public static function get_instance() {
 		static $instance = null;
 		if (is_null($instance)) {
 			$instance = new self();
 		}
 		return $instance;
 	}
}