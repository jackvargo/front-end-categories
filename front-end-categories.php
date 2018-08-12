<?php
/*
Plugin Name: Front-end Categories
Plugin URI: http://wordpress.org/plugins/front-end-categories/
Description: A WordPress plugin to add creation of categories and sub-categories to the front-end.
Author: Jack McConnell, Voltronik
Author URI: http://www.voltronik.co.uk/
Version: 0.2.2
*/

/******************************
* Global Variables
******************************/

if (isset($_POST['submit_cat'])) {
	require_once('../../../wp-load.php');
	
	$cat_ID = get_cat_ID( $_POST['newcat'] );

	if ($cat_ID == 0) {  
		$cat_name = $_POST['newcat'];  
		$new_cat_ID = wp_insert_term(
			$cat_name,
			'category'
		);
		
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
			$new_subcat_ID = wp_insert_term($subcat_name, 'category', $arg);

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

// Create new sub-category
if (isset($_POST['refresh'])) {
	require_once('../../../wp-load.php');
	
	echo wp_dropdown_categories(
    	array(
    		'hide_empty' => 0, 
    		'name' => 'cat-parent', 
    		'orderby ' => 'id', 
    		'order' => 'DESC', 
    		'hierarchical' => true, 
    		'show_option_none' => '-',
			'id' => 'cat-drop',
			'echo' => false
    	)
    );
	exit();			
}

$fec_prefix = 'fec_';
$fec_plugin_name = 'Front-end Categories';


/******************************
* Functions
******************************/

// Create category on front-end
function fec_cat_create() {

	// Output HTML
	ob_start(); ?>
		<form id="new-cat" action="" method="post">
			<label>Category name: </label>
			<input type="text" name="newcat" value="">
			<input type="submit" name="submit-cat" value="Submit">
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
								success: function(r) {
									$('#cat-drop').replaceWith(r);
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

	// Output HTML
	ob_start(); ?>
		<form id="new-subcat" action="" method="post">
			<label>Sub-category name:</label>
			<input type="text" name="newsubcat" value=""/>

			<br />

			<label>Add sub-category to which parent category?</label>

		    <?php 
			    wp_dropdown_categories(
			    	array(
			    		'hide_empty' => 0, 
			    		'name' => 'cat-parent', 
			    		'orderby ' => 'id', 
			    		'order' => 'DESC', 
			    		'hierarchical' => true, 
			    		'show_option_none' => '-',
						'id' => 'cat-drop'
			    	)
			    );
			?>

			<input type="submit" name="submit_subcat" value="Submit">
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
								success: function(r) {
									$('#cat-drop').replaceWith(r);
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