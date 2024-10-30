<?php

	/*

		Plugin Name: LCIMedia Broadcast
		Version: 1.0
		
		Author: LCIMedia
		Author URI: http://www.LCIMedia.co.uk
		
		Description: Adds the ability to Broadcast from WordPress blog to LCIMedia.
		
		Network: True
		
		License: GPLv3
		
		Copyright (C) 2014 LCIMedia

	    This program is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License, or
	    (at your option) any later version.
	
	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with this program.  If not, see <http://www.gnu.org/licenses/>.
		
	*/

	//if(!class_exists('LCIBroadcast')) :

		// DEFINE PLUGIN ID
		define('LCIPLUGINOPTIONS_ID', 'LCI-plugin-options');
		// DEFINE PLUGIN NICK
		define('LCIPLUGINOPTIONS_NICK', 'LCIMedia Plugin Options');
			
		class LCIBroadcast {
		
			// Register actions and filters on class instanation
			function __construct() {
				// Add metabox action
				//add_action( 'add_meta_boxes', array( &$this, 'register_metabox' ) );
				
				// Add save post action
				add_action( 'publish_post',  array( &$this, 'LCIBroadcast_post' ) );
				
				
				if ( is_admin() )
				{
					add_action('admin_init', array(&$this, 'register'));
					add_action('admin_menu', array(&$this, 'menu'));
				}
				add_filter('the_content', array( &$this, 'content_with_quote'));
			}
			
			// LCIBroadcast the post
			function LCIBroadcast_post( $post_id ) {

				$url = get_option('feedUrl');

				error_log("LCIBroadcast_post URL: " . $url);
				
				// Check that I am only LCIBroadcasting once
				if ( $url != '' /*&& did_action( 'save_post' ) == 1 */) {
					
					error_log("LCIBroadcast_post STEP 1");
                     
					// Retrieve the post
					$post = get_post( $post_id, 'ARRAY_A' );
					
					error_log("LCIBroadcast_post STEP 1.1  " . $post['post_status']  . "   *******  " . $post['post_type'] );

					// If user is publishing a post
					//if ( $post['post_status'] == 'publish' && $post['post_type'] == 'post' ) {
					if( ( $post['post_status'] == 'publish' ) && ( $post['original_post_status'] != 'publish' ) ) {	
						error_log("LCIBroadcast_post STEP 2");

						// And user did want to multicase
						// if ( ! empty( $_POST['blogs'] ) ) {
							
							// List of data to keep in LCIBroadcasted posts
							$post_data = array( 
											'post_author',
											'post_date',
											'post_date_gmt',
											'post_content',
											'post_title',
											'post_excerpt',
											'post_status',
											'comment_status',
											'ping_status',
											'post_password',
											'post_name',
											'post_modified',
											'post_modified_gmt',
											'post_type'
										);
							
							// Create a new post array
							foreach ( $post_data as $key )
								$new_post[$key] = $post[$key];
							
							$new_post['post_linkurl'] = get_permalink($post_id);							
							
							if (has_post_thumbnail( $post_id )){
								$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
								$new_post['post_image'] = $image[0];
							}
							
							error_log("LCIBroadcast_post Before Post");
							
							$response = wp_remote_post( $url, array(
								'method' => 'POST',
								'timeout' => 45,
								'redirection' => 5,
								'httpversion' => '1.0',
								'blocking' => true,
								'headers' => array(),
								'body' => $new_post,
								'cookies' => array()
								)
							);

							if ( is_wp_error( $response ) ) {
							   $error_message = $response->get_error_message();
							   error_log("LCIBroadcast_post After Post error: " . $error_message);
							   
							} else {
							   error_log("LCIBroadcast_post After Post : " . $response );
							}
							
							// // Retrieve the post format
							// $format = get_post_format( $post_id );
							
							// // Retrieve the post tags
							// $tags = wp_get_post_tags( $post_id );
							
							// // Create a list of tags
							// if ( ! empty( $tags ) )
								// foreach ( $tags as $tag )
									// $new_tags[] = $tag->name;
									
							// // Retrieve the post categories
							// $categories = wp_get_post_categories( $post_id );
							
							// // Create a list of categories
							// if ( ! empty( $categories ) )
								// foreach ( $categories as $category ) {
									// $cat = get_category( $category, 'ARRAY_A' );
									// $new_categories[ $cat['slug'] ] = $cat['name'];	
								// }
							
							
							// // Go through each blog
							// foreach ( $_POST['blogs'] as $blog_id => $value ) {
							
								// // Check this blog isn't the current blog
								// if ( ! $blog_id != get_current_blog_id() ) {
									
									// // Ensure it was checked
									// if ( $value == 'on' ) {
									
										// // Switch WordPress to that blog
										// if ( switch_to_blog( $blog_id, true ) ) {
										
											// // Check the current user can publish_posts
											// if ( current_user_can( 'publish_posts' ) ) {
												
												// // Insert the post
												// $post_id = wp_insert_post( $new_post );
												
												// // Set post format
												// set_post_format( $post_id, $format );
												
												// // Set post tags											
												// if ( ! empty( $new_tags ) )
													// wp_set_post_tags( $post_id, $new_tags );
												
												// // Create categories
												// foreach ( $new_categories as $ $slug => $name )
													// if ( is_category( $slug ) ) {
														// $cat = get_object_vars( get_category_by_slug( $slug ) );
														// $new_cats[] = $cat->cat_ID;
													// } else {
														// $new_cats[] = intval( wp_create_category( $name ) );
													// }
												
												// // Set Categories
												// if ( ! empty( $new_cats ))
													// wp_set_post_categories( $post_id, $new_cats );
												
											// }
											
											// // Restore back to the current blog, or die
											// if ( ! restore_current_blog() )
												// wp_die( "Unable to switch back to current blog." );
										// }
									// }
								// }
							// }
						// }
					}
				}
			}
			
			// Get list of blogs that specified user can peform specified capability on
			function get_blogs_of_user( $user_id, $capability ) {
				
				// Get all blogs of user
				$blogs = get_blogs_of_user( $user_id );
				
				// Make sure at least one blog was returned
				if ( ! empty( $blogs ) ) {
					
					// Go through each blog
					foreach ( $blogs as $blog_id => $data ) {
					
						// Switch WordPress to that blog
						if ( switch_to_blog( $blog_id, true ) ) {
						
							// Check user can perform capability
							if ( user_can( $user_id, $capability ) )
								$validated_blogs[$blog_id] = $data;
								
							// Restore back to the current blog, or die
							if ( ! restore_current_blog() )
								wp_die( "Unable to switch back to current blog." );
						}
					}
				}
				
				// Return the validated blogs list
				return $validated_blogs;
			}
			
			// Register metabox
			function register_metabox() {
				if ( $_REQUEST['action'] != 'edit' )
					add_meta_box( 'LCIBroadcast', 'LCIBroadcast', array( &$this, 'output_metabox' ), 'post', 'side', 'default', null );
			}
			
			// Output metabox HTML
			function output_metabox() {
				$blogs = $this->get_blogs_of_user( get_current_user_id(), 'publish_posts' );
				?>
					<small>Post to:</small>
					<?php if ( ! empty( $blogs ) ): ?>
						<ul>
							<?php foreach ( $blogs as $blog ): ?>
								<li>
									<input type="checkbox" <?php checked( $blog->userblog_id, get_current_blog_id() ) ?> <?php disabled( $blog->userblog_id, get_current_blog_id() ) ?> name="blogs[<?php echo $blog->userblog_id ?>]" id="blog_<?php echo $blog->userblog_id ?>" />
									<label for="blog_<?php echo $blog->userblog_id ?>"><?php echo $blog->blogname ?></label>
								</li>						
							<?php endforeach ?>
						</ul>
					<?php else: ?>
						<p>There are no other blogs.</p>
					<?php endif ?>
				<?php
			}
			
			
			/** function/method
			* Usage: return absolute file path
			* Arg(1): string
			* Return: string
			*/
			public function file_path($file)
			{
				return ABSPATH.'wp-content/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).$file;
			}
			/** function/method
			* Usage: hooking the plugin options/settings
			* Arg(0): null
			* Return: void
			*/
			public function register()
			{
				register_setting(LCIPLUGINOPTIONS_ID.'_options', 'feedUrl');
			}
			/** function/method
			* Usage: hooking (registering) the plugin menu
			* Arg(0): null
			* Return: void
			*/
			public function menu()
			{
				// Create menu tab
				add_options_page(LCIPLUGINOPTIONS_NICK.' Plugin Options', LCIPLUGINOPTIONS_NICK, 'manage_options', LCIPLUGINOPTIONS_ID.'_options', array( &$this, 'options_page'));
			}
			/** function/method
			* Usage: show options/settings form page
			* Arg(0): null
			* Return: void
			*/
			public function options_page()
			{ 
				if (!current_user_can('manage_options')) 
				{
					wp_die( __('You do not have sufficient permissions to access this page.') );
				}
				
				$plugin_id = LCIPLUGINOPTIONS_ID;
				// display options page
				include(self::file_path('options.php'));
			}
			/** function/method
			* Usage: filtering the content
			* Arg(1): string
			* Return: string
			*/
			public function content_with_quote($content)
			{
				$quote = '<p><blockquote>' . get_option('feedUrl') . '</blockquote></p>';
				return $content . $quote;
			}
		}
		
		// Instanate LCIBroadcast class	
		$LCIBroadcast = new LCIBroadcast();
	//endif;	
?>