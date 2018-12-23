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
	public function __construct ( $file='', $parent, $version = '1.0.0' ) {

		$this->parent = $parent;
	
		$this->_version = $version;
		$this->_token	= md5($file);
		
		$this->message = '';
		
		// Load plugin environment variables
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->vendor  		= WP_CONTENT_DIR . '/vendor';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		
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
		
		add_filter( 'template_include', array( $this, 'template_path'), 1 );
		
		// add user attributes
		
		add_filter( 'ltple_user_loaded', array( $this, 'add_user_attribute'));			
		
		// add panel shortocode
		
		add_shortcode('ltple-client-addon', array( $this , 'get_panel_shortcode' ) );
	
		// add panel url
		
		add_filter( 'ltple_urls', array( $this, 'get_panel_url'));
		
		// add subscription features
		
		add_filter( 'ltple_plan_subscribed', array( $this, 'handle_subscription_plan'));
		
		// add link to theme menu
		
		add_filter( 'ltple_view_my_profile', array( $this, 'add_theme_menu_link'));	
				
		// add layer fields
		
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
		
		*/
	
	} // End __construct ()
	
	public function template_path( $template_path ){
		
		
		return $template_path;
	}
	
	public function init(){	 
	
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
	
	public function get_panel_shortcode(){
		
		if($this->parent->user->loggedin){
			
			if( !empty($_REQUEST['output']) && $_REQUEST['output'] == 'widget' ){
				
				include($this->views . '/widget.php');
			}
			else{
			
				include($this->parent->views . '/navbar.php');
			
				include($this->views . '/panel.php');
			}
		}
		else{
			
			echo'<div style="font-size:20px;padding:20px;margin:0;" class="alert alert-warning">';
				
				echo'You need to log in first...';
				
				echo'<div class="pull-right">';

					echo'<a style="margin:0 2px;" class="btn-lg btn-success" href="'. wp_login_url( $this->parent->request->proto . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'">Login</a>';
					
					echo'<a style="margin:0 2px;" class="btn-lg btn-info" href="'. wp_login_url( $this->parent->urls->editor ) .'&action=register">Register</a>';
				
				echo'</div>';
				
			echo'</div>';
		}				
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
	}
	
	public function add_theme_menu_link(){

		// add theme menu link
		
		/*
		echo'<li style="position:relative;">';
			
			echo '<a href="'. $this->parent->urls->addon . '"><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Addon Panel</a>';

		echo'</li>';
		*/
	}
	
	public function add_layer_options($term_slug){
		
		/*
		
		if(!$addon_amount = get_option('addon_amount_' . $term_slug)){
			
			$addon_amount = 0;
		}

		$this->parent->layer->options = array(
			
			'addon_amount' 	=> $addon_amount,
		);
		*/
	}
	
	public function add_layer_plan_fields( $taxonomy, $term_slug = '' ){
		
		/*
		
		$data = [];

		if( !empty($term_slug) ){
		
			$data['addon_amount'] = get_option('addon_amount_' . $term_slug); 
			$data['addon_period'] = get_option('addon_period_' . $term_slug); 
		}

		echo'<div class="form-field" style="margin-bottom:15px;">';
			
			echo'<label for="'.$taxonomy.'-addon-amount">Addon plan attribute</label>';

			echo $this->get_layer_addon_fields($taxonomy,$data);
			
		echo'</div>';
		
		*/
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