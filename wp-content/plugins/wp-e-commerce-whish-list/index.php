<?php defined('ABSPATH') or die("No script kiddies please!");
/*

Plugin Name: WP eCommerce Wishlist

Plugin URI: http://www.websitedesignwebsitedevelopment.com/wp-e-commerce-wish-list

Description: This is Wishlist plugin for WP eCommerce Site. It has a widget which you can use with ultimate convenience.

Version: 1.1.1

Author: Fahad Mahmood 

Author URI: http://www.androidbubbles.com

License: GPL3

*/ 
 /*  Copyright YEAR  Fahad Mahmood  (email : fahad@androidbubbles.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	global $wpews_premium_link, $wpews_pro, $wpews_dir, $wpews_data;
	
	$wpews_dir = plugin_dir_path( __FILE__ );
	$wpews_pro = file_exists($wpews_dir.'pro/wpews_extended.php');
	$wpews_premium_link = 'http://shop.androidbubbles.com/product/wp-e-commerce-whish-list-pro';
	$wpews_data = get_plugin_data(__FILE__);
	

	
	if($wpews_pro)
	wpews_backup_pro();

	function wpews_backup_pro($src='pro', $dst='') { 

		$plugin_dir = plugin_dir_path( __FILE__ );
		$uploads = wp_upload_dir();
		$dst = ($dst!=''?$dst:$uploads['basedir']);
		$src = ($src=='pro'?$plugin_dir.$src:$src);
		
		$pro_check = basename($plugin_dir);

		$pro_check = $dst.'/'.$pro_check.'.dat';

		if(file_exists($pro_check)){
			if(!is_dir($plugin_dir.'pro')){
				mkdir($plugin_dir.'pro');
			}
			$files = file_get_contents($pro_check);
			$files = explode('\n', $files);
			if(!empty($files)){
				foreach($files as $file){
					
					if($file!=''){
						
						$file_src = $uploads['basedir'].'/'.$file;
						//echo $file_src.' > '.$plugin_dir.'pro/'.$file.'<br />';
						//copy($file_src, $plugin_dir.'pro/'.$file);

						$trg = $plugin_dir.'pro/'.$file;
						if(!file_exists($trg))
						copy($file_src, $trg);
						
					}
				}//exit;
			}
		}
		
		if(is_dir($src)){
			if(!file_exists($pro_check)){
				$f = fopen($pro_check, 'w');
				fwrite($f, '');
				fclose($f);
			}	
			$dir = opendir($src); 
			@mkdir($dst); 
			while(false !== ( $file = readdir($dir)) ) { 
				if (( $file != '.' ) && ( $file != '..' )) { 
					if ( is_dir($src . '/' . $file) ) { 
						wpews_backup_pro($src . '/' . $file, $dst . '/' . $file); 
					} 
					else { 
						$dst_file = $dst . '/' . $file;
						
						if(!file_exists($dst_file)){
							
							copy($src . '/' . $file,$dst_file); 
							$f = fopen($pro_check, 'a+');
							fwrite($f, $file.'\n');
							fclose($f);
						}
					} 
				} 
			} 
			closedir($dir); 
			
		}	
	}		
	
	
	
		
	if(is_admin()){

		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'wpews_plugin_links' );	
		
		
	}else{
		
	
		
		
	}

	
	
	if($wpews_pro){					
		$wpews_dir.'pro/wpews_extended.php';//exit;
		include($wpews_dir.'pro/wpews_extended.php');
	}
	
	include('inc/functions.php');
	
	if(is_admin() && get_option('wpsc_compatibility')=='')
	update_option('wpsc_compatibility', wp_plugin_info('wp-e-commerce'));
			
	add_action('wpsc_product_form_fields_end', 'initialize_wpecwl');
	add_action('wp_footer', 'ajaxUrl');
	add_action( 'wp_enqueue_scripts', 'wpecwl_scripts' );	
	add_action( 'wp_ajax_add_wish_list', 'add_wish_list_callback' );
	add_action( 'wp_ajax_remove_wish_list', 'remove_wish_list_callback' );
	add_action( 'wp_ajax_load_wish_list', 'load_wish_list_callback' );
	add_action( 'widgets_init', create_function('', 'return register_widget("wpecwlWidget");') );