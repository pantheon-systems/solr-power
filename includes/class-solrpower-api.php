<?php

class SolrPower_Api {

	/**
	 * Singleton instance
	 * @var SolrPress_Api|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPress_Api
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {

	}

	function call(){
		
	}
}
