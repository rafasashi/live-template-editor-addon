<?php 

	if(!empty($this->parent->message)){ 
	
		//output message
	
		echo $this->parent->message;
	}

	// get current tab
	
	$tabs = ['tab1','tab2'];
	
	$currentTab = ( !empty($this->tab) && in_array($this->tab,$tabs) ? $this->tab : 'addon-tab' );
	
	// ------------- output panel --------------------
	
	echo'<div id="panel">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Addon Sidebar</li>';
				
				echo'<li'.( $currentTab == 'tab1' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->addon . '">Tab1 <span class="label label-success pull-right"> pro </span></a></li>';

				echo'<li'.( $currentTab == 'tab2' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->addon . '?tab=urls">Tab2 <span class="label label-success pull-right"> pro </span></a></li>';
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';

				if( $currentTab == 'tab1' ){
					
					echo 'Addon Tab1';
					
				}
				elseif( $currentTab == 'tab2' ){
					
					echo 'Addon Tab2';
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
	
	?>
	
	<script>

		;(function($){		
			
			$(document).ready(function(){

			
				
			});
			
		})(jQuery);

	</script>