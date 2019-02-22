<?php
/*
	Plugin Name: Princeton - Assignment
	Plugin URI:
	Description: Creates a new content type called Assignment. Submissions can either through and embedded submission form or via an 'Add Post' button.
	Version: 1.0
	Author: Ben Johnston
*/



function puassignment_add_scripts() {
    wp_register_style('pu-assignment-css', plugins_url('css/style.css',__FILE__ ));
    wp_enqueue_style('pu-assignment-css');
    wp_register_script('pu-assignment-js', plugins_url('js/script.js', __FILE__), array('jquery'),'1.1', true);
    wp_enqueue_script('pu-assignment-js');
}

add_action( 'wp_enqueue_scripts', 'puassignment_add_scripts' );  




/*************** INSERT 'ADD POST' BUTTON SHORTCODE *****************/

function puassignment_addpost_button( $atts ) {
	if(isset($atts['cat'])) { $categorystr = "?category=".$atts['cat']; } else { $categorystr = ""; }

	$url = site_url()."/wp-admin/post-new.php".$categorystr;
	$html = '<div class="add-post-button-wrap"><a class="add-post-button" href="'.$url.'">Add Post</a></div>';
	return $html;

}
add_shortcode( 'assignment_button', 'puassignment_addpost_button' );




/*************** INSERT AN IN-PAGE EDITOR *****************/

function puassignment_add_post_submit( $atts ) {
  if(is_user_logged_in()) {

	global $wp;
	$current_url = home_url(add_query_arg(array(), $wp->request));

	if(isset($atts['cat'])) { $categorystr = $atts['cat']; } else { $categorystr = ""; }
	$html = '<div class="add-post-submit-wrap">';
	$html .= ' <form name="" method="POST" action="" enctype="multipart/form-data">';
	$html .= '   <p><label for="pu-assignment-submit-title">Title</label><input type="text" name="pu-assignment-submit-title" id="pu-assignment-submit-title" /></p>';
	$html .= '   <input type="hidden" name="pu-assignment-userid" value="'.get_current_user_id().'" />';
	$html .= '   <input type="hidden" name="pu-assignment-redirect" value="'.$current_url.'" />';
	$html .= '   <input type="hidden" name="pu-assignment-submit-categories" id="pu-assignment-submit-title" value="'.$categorystr.'" />';
	ob_start();
	wp_editor( '', 'pu-assignment-submit-editor', array('textarea_name'=>'pu-assignment-submit-editor','editor_class'=>'pu-assignment-submit-editor','media_buttons'=>false,'teeny'=>'true') );
	$html .=  ob_get_clean();
	$html .= '   <p><label for="pu-assignment-submit-image">Image (optional)</label><input type="file" name="pu-assignment-submit-image" id="pu-assignment-submit-image" accept="image/*" /></p>';
	$html .= '   <p style="margin-top:20px;text-align:right;"><input type="submit" value="Submit" /></p>';
	$html .= ' </form>';
	$html .= '</div>';
	return $html;

  } // end is user logged in
}
add_shortcode( 'assignment_submit', 'puassignment_add_post_submit' );





/*************** ADD POST LIST SHORTCODE *****************/

function puassignment_postlist( $atts ) {


	if(isset($atts['display'])) { $display = $atts['display']; } else { $display = 'posts'; }
	if(isset($atts['cat'])) { $cat_slug_string = $atts['cat']; }
	static $w4dev_custom_loop;
	if( !isset($w4dev_custom_loop) )
		$w4dev_custom_loop = 1;
	else
		$w4dev_custom_loop ++;

	$atts = shortcode_atts( array(
		'paging'		=> 'pg'. $w4dev_custom_loop,
		'post_type' 		=> 'post',
		'posts_per_page' 	=> get_option('posts_per_page'),
		'post_status' 		=> 'publish'
	), $atts );

	if(isset($cat_slug_string)) { 	$atts['category_name'] = $cat_slug_string;  }


	$paging = $atts['paging'];
	unset( $atts['paging'] );

	if( isset($_GET[$paging]) )
		$atts['paged'] = $_GET[$paging];
	else
		$atts['paged'] = 1;

	$html  = '';


	$custom_query = new WP_Query( $atts );


	$pagination_base = add_query_arg( $paging, '%#%' );

	if( $custom_query->have_posts() && $display == 'list' ):
	  $html = puassignment_listview($custom_query);
	endif;

	if( $custom_query->have_posts() && $display == 'grid' ):
	  $html = puassignment_gridview($custom_query);
	endif;

	if( $custom_query->have_posts() && $display == 'posts' ):
	  $html = puassignment_postsview($custom_query);
	endif;

	$html .= paginate_links( array(
		'type' 		=> '',
		'base' 		=> $pagination_base,
		'format' 	=> '?'. $paging .'=%#%',
		'current' 	=> max( 1, $custom_query->get('paged') ),
		'total' 	=> $custom_query->max_num_pages
	));

	return $html;

}
add_shortcode( 'assignment_list', 'puassignment_postlist' );


/*********** CREATE HTML FOR GRID VIEW  *****************/

function puassignment_gridview($query) {
  ob_start();
	?>
	<div class='site-main' style='clear:both;'>
	<?php


	    // Start looping over the query results.
	    while ( $query->have_posts() ) {
	 
		$query->the_post();
	 	$thumb = get_the_post_thumbnail_url();
		?>
	 
		<div class='tile' id="post-<?php the_ID(); ?>" <?php post_class( 'left' ); ?> style="background-image:url('<?php echo $thumb;?>')">
		  <header class="entry-header">
		    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
		    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
		       <h3 class="entry-title"><?php the_title(); ?></h3>
		    </a>
		        <?php 
			//post_thumbnail( 'thumbnail' );
			?>
		    </a>
		  </header>
		  <div class="entry-content">

		  </div>
		  <div class="meta">
		    Date: <?php echo get_the_date(); ?>
		    <?php echo get_the_author_link(); ?>
		  </div>
		</div>
	 
		<?php
	 
	    }
	    ?>
	</div>
	<div class='clear:both;'></div>
    <?php
  $html = ob_get_contents();
  ob_end_clean();
  return $html;
}


/*********** CREATE HTML FOR DEFAULT POSTS VIEW  *****************/

function puassignment_postsview($query) {
  ob_start();
	?>
	<div class='site-main' style='clear:both;'>
	<?php


	    // Start looping over the query results.
	    while ( $query->have_posts() ) {
	 
		$query->the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'left' ); ?>>
		  <header class="entry-header">
		    <a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true">
			<?php the_post_thumbnail( 'post-thumbnail', array( 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
		    </a>
		    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
		    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
		        <h3 class="entry-title"><?php the_title(); ?></h3>
		    </a>
		        <?php 
			//post_thumbnail( 'thumbnail' );
			?>
		    </a>
		  </header>
		  <div class="entry-content">
		    <?php the_excerpt(); ?>
		  </div>
		  <div class="meta">
		    <?php echo get_the_date(); ?><br />
		    <?php echo get_the_author_link(); ?><br />
		  </div>
		</article>
	 
		<?php
	 
	    }
	    ?>
	</div>
	<div class='clear:both;'></div>
    <?php
  $html = ob_get_contents();
  ob_end_clean();
  return $html;
}


/*********** CREATE HTML FOR SIMPLE LIST VIEW  *****************/

function puassignment_listview($custom_query) {

		$html = '<ul>';
			while( $custom_query->have_posts()) : $custom_query->the_post();
			$html .= sprintf( 
				'<li><a href="%1$s">%2$s</a></li>',
				get_permalink(),
				get_the_title()
			);
			endwhile;
		$html .= '</ul>';
    return $html;
}





/*********** GET OBJECT TERMS  *****************/

add_filter('wp_get_object_terms', function($terms, $object_ids, $taxonomies, $args)
{


    if (!$terms && basename($_SERVER['PHP_SELF']) == 'post-new.php') {

	$taxonomies = str_replace("'","",$taxonomies);

        // Category - note: only 1 category is supported currently
        if ($taxonomies == 'category' && isset($_REQUEST['category'])) {

            $id = get_cat_id($_REQUEST['category']);
            if ($id) {
                return array($id);
            }
        }
        if ($taxonomies == 'category' && isset($_REQUEST['catid'])) {
            $id = $_REQUEST['catid'];
            if ($id) {
                return array($id);
            }
        }

        // Tags
        if ($taxonomies == "'post_tag'" && isset($_REQUEST['tags'])) {
            $tags = $_REQUEST['tags'];
            $tags = is_array($tags) ? $tags : explode( ',', trim($tags, " \n\t\r\0\x0B,") );
            $term_ids = array();
            foreach ($tags as $term) {
                if ( !$term_info = term_exists($term, 'post_tag') ) {
                    // Skip if a non-existent term ID is passed.
                    if ( is_int($term) )
                        continue;
                    $term_info = wp_insert_term($term, 'post_tag');
                }
                $term_ids[] = $term_info['term_id'];
            }
            return $term_ids;
        }
    }
    return $terms;
}, 10, 4);




/************* SET CATGEORIES ON NEW POST PAGE ****************/



function puassignment_set_category () {
	global $post;
  	//Check for a category parameter in our URL, and sanitize it as a string
	$category_slug = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING, array("options" => array("default" => 0)));
	if(strstr($category_slug, ',')) { $cat_arr = explode(',',$category_slug);} else { $cat_arr = array($category_slug); }

  	//If we've got a category by that name, set the post terms for it
       $cat_ids = array();
       foreach($cat_arr as $slug) {
	if ( $category = get_category_by_slug($slug) ) {
		$cat_ids[] = $category->term_id;
		//
	}
       }
       wp_set_post_terms( $post->ID, $cat_ids, 'category' );
}

//hook it into our post-new.php specific action hook
add_action( 'admin_head-post-new.php', 'puassignment_set_category', 10, 1 );



/************* PROCESS SUBMITTED POSTS ****************/

function puassignment_process_post() {

     if( isset( $_POST['pu-assignment-submit-title'] ) && is_user_logged_in() ) {


	// set the post title to be the user's submitted title
	if(isset($_POST['pu-assignment-submit-title']) && $_POST['pu-assignment-submit-title'] != "") {
	 $post_title = sanitize_text_field($_POST['pu-assignment-submit-title']);
	}

	// set the post content to be the user's submitted response text
	if(isset($_POST['pu-assignment-submit-editor']) && $_POST['pu-assignment-submit-editor'] != "") {
	  $post_content = $_POST['pu-assignment-submit-editor'];
	}
	else { $post_content = ""; }

	$post_user_id = $_POST['pu-assignment-userid'];


	if(isset($_POST['pu-assignment-submit-categories']) && $_POST['pu-assignment-submit-categories'] != "") {
	   $cats = explode(',',$_POST['pu-assignment-submit-categories']);
	   $catid_array = array();
	   foreach($cats as $slug) {
		$slug = trim($slug);
		if($cat = get_category_by_slug( $slug )) {
		  $catid_array[] = $cat->term_id;
		}
	   }
	}



	  $args = array(
	    'post_title' => $post_title,
	    'post_author' => $post_user_id,
	    'post_content' => $post_content,
	    'post_type' => 'post',
	    'post_status' => 'publish',
	    'comment_status' => 'open',
	    'post_category'  => $catid_array,
	    'ping_status' => 'closed'
	  );


	$post_id = wp_insert_post($args);

	// CHECK WHICH FILES WERE INCLUDED IN THE FORM
	if(isset($_FILES) && $_FILES['pu-assignment-submit-image']['name'] != ""  ) { 

		$img_attachment = puassignment_handle_upload('pu-assignment-submit-image');
		$featured_image_set = set_post_thumbnail($post_id, $img_attachment->id );
		$media_id = $img_attachment->id;
		update_post_meta( $post_id, 'Media ID', $media_id );
		update_post_meta( $post_id, 'Media Path', $img_attachment->path );

	}





	// finally - redirect to the post itself
	$redriect = $_POST['pu-assignment-redirect'];
	if ( wp_redirect( $redriect ) ) {
	  exit;
	}




     }
}
add_action( 'init', 'puassignment_process_post' );




/******************************************
 * Handle file upload
 ******************************************/


function puassignment_handle_upload($field_name) {
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');

    $file_handler = $field_name;

    $attach_id = media_handle_upload( $file_handler, 0 );
    $mimetype = get_post_mime_type( $attach_id );
    $media_url = wp_get_attachment_url($attach_id, 'full');
    $media_path = get_attached_file( $attach_id );
    $returnObj = new StdClass();
    $returnObj->id = $attach_id;
    $returnObj->mimetype = $mimetype;
    $returnObj->url = $media_url;
    $returnObj->path = $media_path;
    return $returnObj;
}



