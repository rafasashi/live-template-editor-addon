<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Addon_Domain {
	
	public	$parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		add_action( 'add_meta_boxes', function(){

			$this->parent->admin->add_meta_box ('domainClientUrl',__( 'Domain Client', 'live-template-editor-addon' ), array("domain"),'advanced');
		});	
		
		// Add envent custom fields
		
		add_filter("domain_custom_fields", array( $this, 'get_fields') );		
		
		$this->init_domain();
	}
	
	public function get_fields(){
		
		$fields	  = [];
		$fields[] = array(
		
			"metabox" =>
			
				array('name'	=> "domainClientUrl"),
				'id'			=> "domainClientUrl",
				'label'			=> "",
				'type'			=> 'text',
				'description'	=> ''
		);
		
		return $fields;
	}	
	
	public function init_domain(){
		
		
	}	
}