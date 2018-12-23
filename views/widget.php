<?php
	
	if(!empty($_REQUEST['action'])){
		
		if( $_REQUEST['action'] == 'addAddonAction' ){
			
			echo'<form action="' . $this->parent->urls->current . '" method="post">';

				echo'<input type="hidden" name="output" value="widget" />';
				
				echo'<input type="hidden" name="action" value="addAddonAction" />';
				
				// addon widget inputs
				
			echo'</form>';
		}
	}