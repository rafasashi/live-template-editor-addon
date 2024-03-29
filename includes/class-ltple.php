<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Addon {

	/**
	 * The single instance of LTPLE_Addon.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	 
	var $slug;
	var $tab;
	 
	public function __construct ( $file='', $parent, $version = '1.0.0' ) {

		$this->parent = $parent;
	
		$this->_version = $version;
		$this->_token	= md5($file);
		
		$this->message = '';
		
		$this->slug = 'addon';
		
		// Load plugin environment variables
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->vendor  		= WP_CONTENT_DIR . '/vendor';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= home_url( trailingslashit( str_replace( ABSPATH, '', $this->dir ))  . 'assets/' );
		
		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$this->script_suffix = '';

		register_activation_hook( $this->file, array( $this, 'install' ) );
		
		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		
		$this->settings = new LTPLE_Addon_Settings( $this->parent );
		
		$this->admin = new LTPLE_Addon_Admin_API( $this );

		if ( !is_admin() ) {

			// Load API for generic admin functions
			
			add_action( 'wp_head', array( $this, 'header') );
			add_action( 'wp_footer', array( $this, 'footer') );
		}
		
		// Handle localisation
		
		$this->load_plugin_textdomain();
		
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		
		//init addon 
		
		add_action( 'wp_loaded', array( $this, 'init' ));	

		// Custom template path
		
		add_filter( 'ltple_layer_template', array( $this, 'filter_template_path'),1,2);
		
		// add user attributes
		
		add_filter( 'ltple_user_loaded', array( $this, 'add_user_attribute'));			

		// hangle user logs
		
		add_filter( 'ltple_first_log_ever', array( $this, 'handle_first_log_ever'));			
		
		add_filter( 'ltple_first_log_today', array( $this, 'handle_first_log_today'));
				
		// add query vars
		
		add_filter('query_vars', array( $this , 'add_query_vars' ), 1);	
	
		// add panel url
		
		add_filter( 'ltple_urls', array( $this, 'get_panel_url'));
		
		// add url parameters
		
		add_filter( 'template_redirect', array( $this, 'get_url_parameters'));		
		
		// add privacy settings
				
		add_filter('ltple_privacy_settings',array($this,'set_privacy_fields'));		
		
		// add panel shortocode
		
		add_shortcode('ltple-client-addon', array( $this , 'get_panel_shortcode' ) );
			
		// add link to theme menu
		
		add_filter( 'ltple_view_my_profile', array( $this, 'add_theme_menu_link'));	
				
		// add button to navbar
				
		add_filter( 'ltple_left_navbar', array( $this, 'add_left_navbar_button'));	
		add_filter( 'ltple_right_navbar', array( $this, 'add_right_navbar_button'));	
						
		// add profile tabs		

		add_filter( 'ltple_profile_tabs', array( $this, 'add_profile_tabs'),10,1);
		
		// add layer fields
		
		add_filter( 'ltple_default_layer_fields', array( $this, 'add_default_layer_fields'),10);
				
		add_filter( 'ltple_layer_options', array( $this, 'add_layer_options'),10,1);
		add_filter( 'ltple_layer_plan_fields', array( $this, 'add_layer_plan_fields'),10,2);
		add_action( 'ltple_save_layer_fields', array( $this, 'save_layer_fields' ),10,1);			
					
		// add layer colums
		
		add_filter( 'ltple_layer_type_columns', array( $this, 'add_layer_columns'));
		add_filter( 'ltple_layer_range_columns', array( $this, 'add_layer_columns'));
		add_filter( 'ltple_layer_option_columns', array( $this, 'add_layer_columns'));
							
		add_filter( 'ltple_layer_column_content', array( $this, 'add_layer_column_content'),10,2);
		
		// handle plan
		
		add_filter( 'ltple_api_layer_plan_option', array( $this, 'add_api_layer_plan_option'),10,1);	
		add_filter( 'ltple_api_layer_plan_option_total', array( $this, 'add_api_layer_plan_option_total'),10,2);
		
		add_filter( 'ltple_plan_shortcode_attributes', array( $this, 'add_plan_shortcode_attributes'),10,2);
		add_filter( 'ltple_plan_subscribed', array( $this, 'handle_subscription_plan'),10);
		
		add_filter( 'ltple_user_plan_option_total', array( $this, 'add_user_plan_option_total'),10,2);
		add_filter( 'ltple_user_plan_info', array( $this, 'add_user_plan_info'),10,1);
		
		add_filter( 'ltple_dashboard_sidebar', array( $this, 'get_sidebar_content' ),2,3);	
		
		$this->add_star_triggers();
		
		// addon post types
		
		/*
		
		$this->parent->register_post_type( 'addon-post-type', __( 'Addon Type', 'live-template-editor-addon' ), __( 'Addon Type', 'live-template-editor-addon' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'addon-post-type',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title','author'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		add_filter('ltple_addon-post-type_layer_area',function(){ 
			
			return 'frontend';
		});
		
		*/
		
		/*
		
		add_action('add_meta_boxes', function(){

			global $post;
			
			if( $post->post_type == 'cb-default-layer' ){
				
				$this->admin->add_meta_box (
					
					'layer-addon-field',
					__( 'Template Addon Field', 'live-template-editor-addon' ), 
					array($post->post_type),
					'side'
				);					
			}
		});

		*/		
	
	} // End __construct ()
	
	public function filter_template_path( $path, $layer ){
		
		
		return $path;
	}
	
	public function init(){	 
	
	}
	
	public function set_privacy_fields(){
		 
		/*
		$this->parent->profile->privacySettings['addon-policy'] = array(

			'id' 			=> $this->parent->_base . 'policy_' . 'addon-policy',
			'label'			=> 'Addon policy',
			'description'	=> 'Addon provacy policy',
			'type'			=> 'switch',
			'default'		=> 'on',
		);
		*/
	}
	
	public function header(){
		
		//echo '<link rel="stylesheet" href="https://raw.githubusercontent.com/dbtek/bootstrap-vertical-tabs/master/bootstrap.vertical-tabs.css">';	
	}
	
	public function footer(){
		
		
	}
	
	public function add_user_attribute(){
		
		// add user attribute
			
		//$this->parent->user->userAttribute = new LTPLE_Addon_User( $this->parent );	
	}
	
	public function handle_first_log_ever(){
		

	}
	
	public function handle_first_log_today(){
		

	}
	
	public function get_panel_shortcode(){
		
		if( !empty($_REQUEST['output']) && $_REQUEST['output'] == 'widget' ){
			
			if($this->parent->user->loggedin){
			
				include($this->views . '/widget.php');
			}
		}
		else{
		
			include($this->parent->views . '/navbar.php');
			
			if($this->parent->user->loggedin){
			
				include($this->views . '/panel.php');
			}
			else{
				
				echo $this->parent->login->get_form();
			}
		}				
	}
	
	public function add_query_vars( $query_vars ){
		
		if(!in_array('tab',$query_vars)){
		
			$query_vars[] = 'tab';
		}
		
		return $query_vars;	
	}
	
	public function get_panel_url(){
		
		/*
		
		$slug = get_option( $this->parent->_base . 'addonSlug' );
		
		if( empty( $slug ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Addon Panel Title',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-addon]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$slug = update_option( $this->parent->_base . 'addonSlug', get_post($post_id)->post_name );
		}
		
		$this->parent->urls->addon = $this->parent->urls->home . '/' . $slug . '/';	
		
		*/
		
		// add rewrite rules
		
		/*
		
		add_rewrite_rule(
		
			$this->slug . '/([^/]+)/?$',
			'index.php?pagename=' . $this->slug . '&tab=$matches[1]',
			'top'
		);
		
		add_rewrite_rule(
		
			$this->slug . '/([^/]+)/([0-9]+)/?$',
			'index.php?pagename=' . $this->slug . '&tab=$matches[1]&aid=$matches[2]',
			'top'
		);
		
		*/
	}

	public function get_url_parameters(){

		// get tab name
		
		/*
		if( !$this->tab = get_query_var('tab') ){
			
			$this->tab = 'addon-tab';
		}
		*/
	}
	
	public function get_sidebar_content($sidebar,$currentTab,$output){
		
		/*
		
		$storage_count = $this->parent->layer->count_layers_by('storage');
		
		$content = '';
		
		if( !empty($storage_count['tab1']) ){

			$content .= '<li'.( $currentTab == 'tab1' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->gallery . '?list=tab1">Tab1</a></li>';
		}
		
		if( !empty($storage_count['tab2']) ){
		
			$content .= '<li'.( $currentTab == 'tab2' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->gallery . '?list=tab2">Tab2</a></li>';
		}
			
		if( !empty($content) ){
		
			$sidebar .= '<li class="gallery_type_title">Addon action</li>';
		
			$sidebar .= $content;
		}
		
		*/
		
		return $sidebar;
	}
	
	public function add_theme_menu_link(){

		// add theme menu link
		
		/*
		echo'<li style="position:relative;">';
			
			echo '<a href="'. $this->parent->urls->addon . '"><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Addon Panel</a>';

		echo'</li>';
		*/
	}
	
	public function add_left_navbar_button(){
	
	}
	
	public function add_right_navbar_button(){
		
		
		
	}
	
	public function add_profile_tabs($tabs){
		
		/*
		
		$tabs[$this->slug]['position'] 	= 3;
		$tabs[$this->slug]['name'] 		= 'Addon Tab';
		
		if( $this->parent->profile->tab == $this->slug ){
			
			add_action( 'wp_enqueue_scripts',function(){

				wp_register_style( $this->parent->_token . $this->slug, false, array());
				wp_enqueue_style( $this->parent->_token . $this->slug );
			
				wp_add_inline_style( $this->parent->_token . $this->slug, '

					#' . $this->slug . ' {
						
						margin-top:15px;
					}
					
				');

			},10 );	

			$tabs[$this->slug]['content'] = 'Addon content';	
		}
			
		*/
		
		return $tabs;
	}
	
	public function add_default_layer_fields(){
		
		/*
		$this->parent->layer->defaultFields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-addon-field"),
				'id'			=> "layerAddonField",
				'label'			=> "",
				'type'			=> 'number',
				'default'		=> '0',
				'placeholder'	=> '0',
				'description'	=> ''
		);
		*/		
	}	
	
	public function add_layer_options($term_id){
		
	}
	
	public function add_layer_plan_fields( $taxonomy, $term_id ){

	}
	
	public function get_layer_addon_fields( $taxonomy_name, $args = [] ){
		
		/*
		
		//get periods
		
		$periods = $this->parent->plan->get_price_periods();
		
		//get price_amount
		
		$amount = 0;
		
		if(isset($args['addon_amount'])){
			
			$amount = $args['addon_amount'];
		}

		//get period
		
		$period = '';
		
		if(isset($args['addon_period'])&&is_string($args['addon_period'])){
			
			$period = $args['addon_period'];
		}
		
		//get fields
		
		$fields='';

		$fields.='<div class="input-group">';

			$fields.='<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">$</span>';
			
			$fields.='<input type="number" step="0.1" min="-1000" max="1000" placeholder="0" name="'.$taxonomy_name.'-addon-amount" id="'.$taxonomy_name.'-addon-amount" style="width: 60px;" value="'.$amount.'"/>';
			
			$fields.='<span> / </span>';
			
			$fields.='<select name="'.$taxonomy_name.'-addon-period" id="'.$taxonomy_name.'-addon-period">';
				
				foreach($periods as $k => $v){
					
					$selected = '';
					
					if($k == $period){
						
						$selected='selected';
					}
					elseif($period=='' && $k=='month'){
						
						$selected='selected';
					}
					
					$fields.='<option value="'.$k.'" '.$selected.'> '.$v.' </option>';
				}
				
			$fields.='</select>';					
			
		$fields.='</div>';
		
		$fields.='<p class="description">The '.str_replace(array('-','_'),' ',$taxonomy_name).' addon used in table pricing & plans </p>';
		
		return $fields;
		*/
	}
	
	public function save_layer_fields($term){
		
		/*
		if( isset($_POST[$term->taxonomy .'-addon-amount']) && is_numeric($_POST[$term->taxonomy .'-addon-amount']) ){

			update_option('addon_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-addon-amount'])),1));			
		}
		*/		
	}
	
	public function add_layer_columns(){
		
		//$this->parent->layer->columns['addon-column'] = 'Addon columns';
	}
	
	public function add_layer_column_content($column_name, $term){
		
		/*
		if( $column_name === 'addon') {

			$this->parent->layer->column .= 'addon column content';
		}
		*/
	}
	
	public function add_api_layer_plan_option ($terms){
		
		/*
		$this->parent->admin->html .= '<td style="width:150px;">';
		
			foreach($terms as $term){
				
				$this->parent->admin->html .= '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
					
					if($term->options['addon_amount']==1){
						
						$this->parent->admin->html .= '+'.$term->options['addon_amount'].' dom';
					}
					elseif($term->options['addon_amount']>0){
						
						$this->parent->admin->html .= '+'.$term->options['addon_amount'].' doms';
					}	
					else{
						
						$this->parent->admin->html .= $term->options['addon_amount'].' doms';
					}					
			
				$this->parent->admin->html .= '</span>';
			}
		
		$this->parent->admin->html .= '</td>';
		*/
	}
	
	public function sum_addon_amount( &$total_addon_amount=0, $options){
		
		/*
		$total_addon_amount = $total_addon_amount + $options['addon_amount'];
		
		return $total_addon_amount;
		*/
	}
	
	public function add_api_layer_plan_option_total($taxonomies,$plan_options){

		/*
	
		$total_addon_amount = 0;
	
		foreach ( $taxonomies as $taxonomy => $terms ) {
	
			foreach($terms as $term){

				if ( in_array( $term->slug, $plan_options ) ) {
					
					$total_addon_amount 	= $this->sum_addon_amount( $total_addon_amount, $term->options);
				}
			}
		}
		
		$this->parent->admin->html .= '<td style="width:150px;">';
		
			if($total_addon_amount==1){
				
				$this->parent->admin->html .= '+'.$total_addon_amount.' addon';
			}
			elseif($total_addon_amount>0){
				
				$this->parent->admin->html .= '+'.$total_addon_amount.' addons';
			}									
			else{
				
				$this->parent->admin->html .= $total_addon_amount.' addons';
			}		
		
		$this->parent->admin->html .= '</td>';
		*/
	}
	
	public function add_plan_shortcode_attributes($taxonomies,$plan_options){
		
		//$this->parent->plan->shortcode .= 'addon attributes';		
	}
		
	public function handle_subscription_plan(){
				
		
	}
	
	public function add_star_triggers(){
		
		/*
		$this->parent->stars->triggers['addon interaction']['ltple_addon_action'] = array(
				
			'description' => 'when you do an addon action'
		);
		*/		

		return true;
	}
	
	public function add_user_plan_option_total( $user_id, $options ){
		
		//$this->parent->plan->user_plans[$user_id]['info']['total_addon_amount'] 	= $this->sum_addon_amount( $this->parent->plan->user_plans[$user_id]['info']['total_addon_amount'], $options);
	}
	
	public function add_user_plan_info( $user_id ){
		

	}
	
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new LTPLE_Client_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new LTPLE_Client_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		//wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		//wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		//wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		//wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		
		load_plugin_textdomain( $this->settings->plugin->slug, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		
	    $domain = $this->settings->plugin->slug;

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main LTPLE_Addon Instance
	 *
	 * Ensures only one instance of LTPLE_Addon is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Addon()
	 * @return Main LTPLE_Addon instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
