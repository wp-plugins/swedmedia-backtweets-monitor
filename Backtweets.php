<?
/*
Plugin Name: Swedmedia Backtweets Monitor
Plugin URI: http://utveckling.swedmedia.se/swedmedia-backtweets-monitor/
Description: A dashboard widget that tells you what people say about your blog on Twitter. 
Author: Per-Mattias "P-M" Nordkvist
Version: 1.1
Author URI: http://utveckling.swedmedia.se/
*/

$plugins = array();
$active = array();

function backtweets() {
	global $plugins, $active;
	
	$plugins = get_plugins();
		
	foreach($plugins as $file => $data) {
		if(is_plugin_active($file)) {
			$active[$file] = get_plugin_data(WP_PLUGIN_DIR."/$file");
		}
	}
	
	wp_add_dashboard_widget('swedmedia_backtweets', 'Backtweets', 'backtweets_dashboard_widget');	
}

function my_plugin_menu() {
  add_options_page('Backtweets', 'Swedmedia Backtweets', 8, 'swedmedia_backtweets', 'swedmedia_backtweets_options');
}

function swedmedia_backtweets_options() {
	 // variables for the field and option names 
	    $hidden_field_name = 'mt_submit_hidden';
	    $opt_name = 'swedmedia_backtweets_ignoreuser';
	    $data_field_name = 'swedmedia_backtweets_ignoreuser';
	
	    // Read in existing option value from database
	    $opt_val = get_option( $opt_name );
	
	    // See if the user has posted us some information
	    // If they did, this hidden field will be set to 'Y'
	    if( $_POST[ $hidden_field_name ] == 'Y' ) {
	        // Read their posted value
	        $opt_val = $_POST[ $data_field_name ];
	
	        // Save the posted value in the database
	        update_option( $opt_name, $opt_val );
	
	        // Put an options updated message on the screen
		
			?>
			<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div>
			<?php
	    }
	
	    // Now display the options editing screen
	
	    echo '<div class="wrap">';
	
	    // header
	
	    echo "<h2>" . __( 'Swedmedia Backtweets Monitor', 'mt_trans_domain' ) . "</h2>";
	
	    // options form
	    
	    ?>
	
	<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		
		<p><?php _e("User to ignore in feed:", 'swedmedia_backtweets_ignoreuser' ); ?> 
		<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20">
		</p>
		<hr />
		
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options', 'swedmedia_backtweets_ignoreuser' ) ?>" />
		</p>
	
	</form>
	</div>
	
	<?php

}


function backtweets_dashboard_widget() {
	global $plugins, $active;
	
	$ignoreuser = get_option("swedmedia_backtweets_ignoreuser");
	$anyMatch = false;

	$url = "http://backtweets.com/search.rss?q=".str_replace("http://", "", get_bloginfo("url"));

	include_once(ABSPATH.WPINC.'/rss.php');
	$rss = fetch_rss($url);
	
	if ( $url ) {
		$rss = fetch_rss( $url );
		
		if(isset($rss)) {
			if(isset($rss->items) && count($rss->items) > 0) {
				echo "<ul>";
				foreach ($rss->items as $item) {
					$href = ""; 
					$title = ""; 
					$description = ""; 
					$dagte = ""; 

					if(isset($item['pubdate']))
						$date = $item['pubdate'];		
		
					if(isset($item['link']))
						$href = $item['link'];
		
					if(isset($item['title']))
						$title = $item['title'];
		
					if(isset($item['description']))
						$description = $item['description'];	
					
					if(!(strlen($ignoreuser) > 0 && strstr($href, "twitter.com/".$ignoreuser) == true)) {
						echo "<li><a href=$href>".utf8_encode(date("j F Y, H:i", strtotime($date)))."</a><br />".utf8_encode($description)."</li>";
						$anyMatch = true; 
					}
				}
				echo "</ul>";
				
				if(!$anyMatch) {
					echo("There were no tweets found about your blog on Twitter using your settings.<br /><br /><a href='".get_bloginfo("url")."/wp-admin/options-general.php?page=swedmedia_backtweets'>Change settings</a>");
				}
			} else {
				echo("There were no tweets about your blog.");
			}
		} else {
			echo("Sorry, dude. There was an error when reading from Backtweets.com: ".$url);		
		}
	} else {
		echo("Sorry, dude. There was an error when reading from Backtweets.com: ".$url);
	}	
}

add_action('admin_menu', 'my_plugin_menu');
add_action('wp_dashboard_setup', 'backtweets');
?>