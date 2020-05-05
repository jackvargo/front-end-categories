<?php
/*
Plugin Name: Front-end Categories
Plugin URI: http://wordpress.org/plugins/front-end-categories/
Description: A WordPress plugin to add creation of categories and sub-categories to the front-end.
Author: Jack Vargo, FlipGoal
Base Plugin Author: Jack McConnell, Voltronik
Author URI: http://www.flipgoal.com/wp-plugins/front-end-categories/
Version: 0.3
*/

/******************************
* Global Variables
******************************/

global $taxonomy_to_edit;
$taxonomy_to_edit = get_option('fec_option_target_taxonomy', 'category');
//TODO: Make this a configurable plugin setting

if (isset($_POST['submit_cat'])) {
	require_once('../../../wp-load.php');
	
	$cat_ID = get_cat_ID( $_POST['newcat'] );

	if ($cat_ID == 0) {  
		$cat_name = $_POST['newcat'];  
		$new_cat_ID = wp_insert_term(
			$cat_name,
			$taxonomy_to_edit
		);
		//TODO: Allow adding/editing of the description as well

		// Success
		echo '<span class="fec-success">Category Added</span>';
	}

	// Failure
	else {
		echo '<span class="fec-error">That category already exists</span>';
	}
	
	exit;
}

// Create new sub-category
if (isset($_POST['submit_subcat'])) {
	require_once('../../../wp-load.php');

	if (!empty($_REQUEST['newsubcat'])) {
		$cat_ID = get_cat_ID( $_POST['newsubcat'] ); 
			
		if($cat_ID == 0) {  
			$subcat_name = $_POST['newsubcat'];  
			$parentCatID = $_POST['cat-parent'];
			$arg = array('description' => $subcat_name, 'parent' => $parentCatID);
			$new_subcat_ID = wp_insert_term($subcat_name, $taxonomy_to_edit, $arg);

			// Success
			echo '<span class="fec-success">Sub-category added successfully</span>';
		}

		// Failure
		else {
			echo '<span class="fec-error">That sub-category already exists!</span>';
		}
	}
	exit;
}

// Rename a category
if (isset($_POST['submit_renamecat'])) {
	require_once('../../../wp-load.php');

	if (!empty($_REQUEST['newcatname'])) {
		$check_cat_ID = get_cat_ID( $_POST['newcatname'] ); 
			
		if($check_cat_ID == 0) {  
			$new_cat_name = $_POST['newcatname'];  
			$catID = $_POST['cat-rename'];
			$arg = array('description' => $new_cat_name, 'name' => $new_cat_name);
			$new_cat_ID = wp_update_term($catID, $taxonomy_to_edit, $arg);
			
			//echo '<span class="fec-error">$catID: '.$catID.' - $new_cat_name: '.$new_cat_name.', Category rename response: '.var_dump($new_cat_ID).'</span>';
			
			if ($catID == $new_cat_ID['term_id']) {
				// Success
				echo '<span class="fec-success">Category renamed successfully</span>';
			} else {
				// Something didn't match
				echo '<span class="fec-error">Category was not renamed successfully.  $catID: '.$catID.' $new_cat_ID: '.var_dump($new_cat_ID).'  '.$new_cat_ID['term_id'].'<br/>Please contact support team or site administrator.</span>';
			}
		}

		// Failure
		else {
			echo '<span class="fec-error">That sub-category already exists!</span>';
		}
	}
	exit;
}

// Refresh the category list
if (isset($_POST['refresh'])) {
	require_once('../../../wp-load.php');
	
	echo JSON_encode(array(
		'cat-parent' => wp_dropdown_categories(
			array(
				'hide_empty' => 0, 
				'name' => 'cat-parent', 
				'orderby ' => 'id', 
				'order' => 'DESC', 
				'hierarchical' => true, 
				'show_option_none' => '-',
				'id' => 'cat-drop',
				'echo' => false,
				'taxonomy' => $taxonomy_to_edit,
			)
		),
		'cat-rename' => wp_dropdown_categories(
			array(
				'hide_empty' => 0, 
				'name' => 'cat-rename', 
				'orderby ' => 'id', 
				'order' => 'DESC', 
				'hierarchical' => true, 
				'show_option_none' => '-',
				'id' => 'cat-rename-drop',
				'echo' => false,
				'taxonomy' => $taxonomy_to_edit,
			)
		)
    ));
	exit();			
}

$fec_prefix = 'fec_';
$fec_plugin_name = 'Front-end Categories';

/******************************
* Functions
******************************/

// Create category on front-end
function fec_cat_create() {
	global $taxonomy_to_edit;

	// Output HTML
	ob_start(); ?>
		<h2>Add New Category</h2>
		<form id="new-cat" action="" method="post">
			<table class="form-table">
				<tbody>
					<tr><th scope="row" valign="top">
						<label>Category name: </label></th>
					<td>
						<input type="text" name="newcat" value="">
						<br/><small><?php _e('Categories can be used to classify the content being published.', 'paid-memberships-pro' );?></small></td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit-cat" class="button-primary" value="Save New Category" /></p>
			<span style="display: none;" id="new-cat-message"></span>
		</form>

		<br />
		
		<script>
			jQuery("document").ready(function($) { 
				$("#new-cat").bind("submit", function(evt) { 
					evt.preventDefault();
					
					if ($('input[name="newcat"]').val() === '') {
						$('#new-cat-message').html('<span class="fec-error">New category name required</span>').stop(true).fadeIn('250').delay('3000').fadeOut('250');
						$('input[name="newcat"]').focus();
						return false;
					}
					
					var post_data = $('#new-cat').serialize();
					$.ajax({
						type: "POST",  
						async: false,
						url: "<?php echo plugins_url(); ?>/front-end-categories/front-end-categories.php", 
						data: post_data+"&submit_cat=Submit",
						success: function(d) {
							$('#new-cat-message').html(d).fadeIn('250').delay('3000').fadeOut('250');
							
							$.ajax({
								type: "POST",  
								async: false,
								url: "<?php echo plugins_url(); ?>/front-end-categories/front-end-categories.php", 
								data: "refresh=refresh",
								success: function(refreshResponse) {
									$('#cat-drop').replaceWith(JSON.parse(refreshResponse)['cat-parent']);
									$('#cat-rename-drop').replaceWith(JSON.parse(refreshResponse)['cat-rename']);
								}
							});
						}
					});
				});
			});
		</script>
		
	<?php
	return ob_get_clean();
}

// Shortcode for creating category
add_shortcode('front-end-cat', 'fec_cat_create');


// Create sub-category on front-end
function fec_subcat_create() {
	global $taxonomy_to_edit;

	// Output HTML
	ob_start(); ?>
		<h2>Add New Sub-Category</h2>
		<form id="new-subcat" action="" method="post">
			<table class="form-table">
				<tbody>
					<tr><th scope="row" valign="top">
						<label>Sub-category name:</label></th>
					<td>
						<input type="text" name="newsubcat" value=""/>
						<br/><small><?php _e('Sub-Categories can be grouped under other categories to distinguish different types within a category.', 'paid-memberships-pro' );?></small></td>
					</tr>

					<tr><th scope="row" valign="top">
						<label>Add sub-category to which parent category?</label></th>

					<td>
						<?php 
							wp_dropdown_categories(
								array(
									'hide_empty' => 0, 
									'name' => 'cat-parent', 
									'orderby ' => 'id', 
									'order' => 'DESC', 
									'hierarchical' => true, 
									'show_option_none' => '-',
									'id' => 'cat-drop',
                  'taxonomy' => $taxonomy_to_edit,
								)
							);
						?>
					</td></tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit_subcat" class="button-primary" value="Save New Sub-Category"></p>
			<span style="display: none;" id="new-subcat-message"></span>
		</form>
		
		<script>
			jQuery("document").ready(function($) { 
				$("#new-subcat").bind("submit", function(evt) { 
					evt.preventDefault();
					
					if ($('input[name="newsubcat"]').val() === '' && $('#cat-drop').find(':selected').text() === '-') {
						$('#new-subcat-message').html('<span class="fec-error">Please select a category name and parent category</span>').stop(true).fadeIn('250').delay('3000').fadeOut('250');
						$('input[name="newsubcat"]').focus();
						return false;
					}

					else if ($('input[name="newsubcat"]').val() !== '' && $('#cat-drop').find(':selected').text() === '-') {
						$('#new-subcat-message').html('<span class="fec-error">Parent category required</span>').stop(true).fadeIn('250').delay('3000').fadeOut('250');
						$('#cat-drop').focus();
						return false;
					}

					else if ($('input[name="newsubcat"]').val() === '' && $('#cat-drop').find(':selected').text() !== '-') {
						$('#new-subcat-message').html('<span class="fec-error">New category name required</span>').stop(true).fadeIn('250').delay('3000').fadeOut('250');
						$('#cat-drop').focus();
						return false;
					}
					
					var post_data = $('#new-subcat').serialize();
					$.ajax({
						type: "POST",  
						async: false,
						url: "<?php echo plugins_url(); ?>/front-end-categories/front-end-categories.php", 
						data: post_data+"&submit_subcat=Submit",
						success: function(d) {
							$('#new-subcat-message').html(d).fadeIn('250').delay('3000').fadeOut('250');

							$.ajax({
								type: "POST",  
								async: false,
								url: "<?php echo plugins_url(); ?>/front-end-categories/front-end-categories.php", 
								data: "refresh=refresh",
								success: function(refreshResponse) {
									$('#cat-drop').replaceWith(JSON.parse(refreshResponse)['cat-parent']);
									$('#cat-rename-drop').replaceWith(JSON.parse(refreshResponse)['cat-rename']);
								}
							});
						}
					});
				});
			});
		</script>
		
	<?php
	return ob_get_clean();
}

// Shortcode for creating sub-category
add_shortcode('front-end-subcat', 'fec_subcat_create');

// Rename category on front-end
function fec_cat_rename() {
	global $taxonomy_to_edit;

	// Output HTML
	ob_start(); ?>
		<h2>Rename a Category</h2>
		<form id="new-renamecat" action="" method="post">
			<table class="form-table">
				<tbody>
					<tr><th scope="row" valign="top">
						<label>Select category to rename:</label></th>
					<td>
						<?php 
							wp_dropdown_categories(
								array(
									'hide_empty' => 0, 
									'name' => 'cat-rename', 
									'orderby ' => 'id', 
									'order' => 'DESC', 
									'hierarchical' => true, 
									'show_option_none' => '-',
									'id' => 'cat-rename-drop',
									'taxonomy' => $taxonomy_to_edit,
								)
							);
						?></td></tr>
					<tr><th scope="row" valign="top">
						<label>New category name:</label>
					<td>
						<input type="text" name="newcatname" id="newcatname" value=""/>
					</td></tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit_renamecat" class="button-primary" value="Rename and Save Category"></p>
			<span style="display: none;" id="new-renamecat-message"></span>
		</form>
		
		<script>
			jQuery("document").ready(function($) { 
				$("#new-renamecat").bind("submit", function(evt) { 
					evt.preventDefault();
					
					if ($('input[name="newcatname"]').val() === '' && $('#cat-drop').find(':selected').text() === '-') {
						$('#new-renamecat-message').html('<span class="fec-error">Please select a category and enter a new name</span>').stop(true).fadeIn('250').delay('3000').fadeOut('250');
						$('input[name="newrenamecat"]').focus();
						return false;
					}

					else if ($('input[name="newcatname"]').val() !== '' && $('#cat-drop').find(':selected').text() === '-') {
						$('#new-renamecat-message').html('<span class="fec-error">Category to rename must be selected</span>').stop(true).fadeIn('250').delay('3000').fadeOut('250');
						$('#cat-drop').focus();
						return false;
					}

					else if ($('input[name="newcatname"]').val() === '' && $('#cat-drop').find(':selected').text() !== '-') {
						$('#new-renamecat-message').html('<span class="fec-error">New name for the category is required</span>').stop(true).fadeIn('250').delay('3000').fadeOut('250');
						$('#cat-drop').focus();
						return false;
					}
					
					var post_data = $('#new-renamecat').serialize();
					$.ajax({
						type: "POST",  
						async: false,
						url: "<?php echo plugins_url(); ?>/front-end-categories/front-end-categories.php", 
						data: post_data+"&submit_renamecat=Submit",
						success: function(d) {
							$('#new-renamecat-message').html(d).fadeIn('250').delay('3000').fadeOut('250');

							$.ajax({
								type: "POST",  
								async: false,
								url: "<?php echo plugins_url(); ?>/front-end-categories/front-end-categories.php", 
								data: "refresh=refresh",
								success: function(refreshResponse) {
									$('#cat-drop').replaceWith(JSON.parse(refreshResponse)['cat-parent']);
									$('#cat-rename-drop').replaceWith(JSON.parse(refreshResponse)['cat-rename']);
									$('#newcatname').val("");
								}
							});
						}
					});
				});
			});
		</script>
		
	<?php
	return ob_get_clean();
}

// Shortcode for renaming categories
add_shortcode('front-end-renamecat', 'fec_cat_rename');

//WPAdmin settings pages
//TODO: Make this whole thing look better.  Reference the format of the PMPro Approvals plugin or my itg_complex_pricing.php plugin for examples.

function fec_register_settings() {
	add_option( 'fec_option_target_taxonomy', '');  //Wistia account private key.
	register_setting( 'fec_options_group', 'fec_option_target_taxonomy', 'fec_callback' );
	/* add_option( 'fec_option_project_id', '');  //Wistia account project ID.
	register_setting( 'fec_options_group', 'fec_option_project_id', 'fec_callback' );
	if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
		add_option( 'fec_option_pmpro_default_levels', '');  //Default PM Pro levels when uploading a new video.
		register_setting( 'fec_options_group', 'fec_option_pmpro_default_levels', 'fec_option_pmpro_default_levels_callback' );
	}
	*/
}
add_action( 'admin_init', 'fec_register_settings' );

function fec_register_options_page() {
	add_options_page('Front-End Categories Settings', 'Front-End Categories', 'manage_options', 'fec', 'fec_options_page');
}
add_action('admin_menu', 'fec_register_options_page');

function fec_options_page()
{
	?>
  <div>
    <h2>Front-End Categories</h2>
    <form method="post" action="options.php">
			<?php settings_fields( 'fec_options_group' ); ?>
      <h3>Front-End Categories Configuration</h3>
      <p><i>These options define what will be accessible to be modified from the front end short-codes.</i></p>
      <table>
        <tr valign="top">
          <th scope="row"><label for="fec_option_target_taxonomy">Target Taxonomy to Edit</label></th>
          <td><input type="text" id="fec_option_target_taxonomy" name="fec_option_target_taxonomy" value="<?php echo get_option('fec_option_target_taxonomy'); ?>" /></td>
        </tr>
      </table>
			<?php  submit_button(); ?>
    </form>
  </div>
	<?php
}