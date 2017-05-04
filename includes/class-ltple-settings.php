<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Addon_Settings {

	/**
	 * The single instance of LTPLE_Addon_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		$this->plugin 		 	= new stdClass();
		$this->plugin->slug  	= 'live-template-editor-addon';
		
		add_action('ltple_plugin_settings', array($this, 'plugin_info' ) );
		
		add_action('ltple_plugin_settings', array($this, 'settings_fields' ) );
		
		add_action( 'ltple_admin_menu' , array( $this, 'add_menu_items' ) );	
	}
	
	public function plugin_info(){
		
		$this->parent->settings->addons['addon-plugin'] = array(
			
			'title' 		=> 'Addon Plugin',
			'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-addon',
			'addon_name' 	=> 'live-template-editor-addon',
			'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-addon/archive/master.zip',
			'description'	=> 'This is a first test of addon plugin for live template editor.',
			'author' 		=> 'Rafasashi',
			'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
		);		
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	public function settings_fields () {
		
		$settings = [];

		$settings['test'] = array(
			'title'					=> __( 'Test', $this->plugin->slug ),
			'description'			=> '',
			'fields'				=> array(
				
				array(
					'id' 			=> 'addon_url',
					'label'			=> __( 'Addon Url' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'http://', $this->plugin->slug )
				),				
			)
		);
		
		if( !empty($settings) ){
		
			foreach( $settings as $slug => $data ){
				
				if( isset($this->parent->settings->settings[$slug]['fields']) && !empty($data['fields']) ){
					
					$fields = $this->parent->settings->settings[$slug]['fields'];
					
					$this->parent->settings->settings[$slug]['fields'] = array_merge($fields,$data['fields']);
				}
				else{
					
					$this->parent->settings->settings[$slug] = $data;
				}
			}
		}
	}
	
	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {
		
		//add menu in wordpress dashboard

		add_submenu_page(
			'live-template-editor-client',
			__( 'Addon test', $this->plugin->slug ),
			__( 'Addon test', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=post'
		);
	}
}
