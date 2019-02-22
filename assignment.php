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
	$html = '<a class="add-post-button" href="'.$url.'">Add Post</a>';
	return $html;

}
add_shortcode( 'assignment_button', 'puassignment_addpost_button' );





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

function puassignment_gridview($custom_query) {

	$html = "<div class='site-main' style='clear:both;'>";
	while( $custom_query->have_posts()) : $custom_query->the_post();

		$html .= '<div id="post-337" class="tile post-337 post type-post status-publish">';
		$html .= '<header class="entry-header">';
		$html .= '<h3 class="entry-title"><a href="'.get_permalink().'" rel="bookmark">'.get_the_title().'</a></h3>';
		$html .= '</header>';
		$html .= '<div class="entry-content">';
		//$html .= get_the_excerpt();
		$html .= '</div>';
		$html .= '<footer class="entry-footer">';
		$html .= '<span class="byline">';
		$html .= '<span class="screen-reader-text">Author </span> <a class="url fn n" href="http://localhost/wordpress/author/admin/">admin</a></span></span><span class="posted-on"><span class="screen-reader-text">Posted on </span><a href="http://localhost/wordpress/default-title/" rel="bookmark"><time class="entry-date published updated" datetime="2019-02-20T21:40:14+00:00">February 20, 2019</time></a></span><span class="cat-links"><span class="screen-reader-text">Categories </span><a href="http://localhost/wordpress/category/article/" rel="category tag">Article</a>, <a href="http://localhost/wordpress/category/learning-strategy/exams/" rel="category tag">Exams</a></span><span class="comments-link"><a href="http://localhost/wordpress/default-title/#respond">Leave a comment<span class="screen-reader-text"> on Default title</span></a></span>		<span class="edit-link"><a class="post-edit-link" href="http://localhost/wordpress/wp-admin/post.php?post=337&#038;action=edit">Edit<span class="screen-reader-text"> "Default title"</span></a></span>';
		$html .= '</footer>';
		$html .= '</div>';
				
	endwhile;
		$html .= '<div style="clear:both"></div>';
		$html .= '</div>';
    return $html;

}


/*********** CREATE HTML FOR DEFAULT POSTS VIEW  *****************/

function puassignment_postsview($custom_query) {

	$html = "<div class='site-main'>";
	while( $custom_query->have_posts()) : $custom_query->the_post();

		$html .= '<article id="post-337" class="post-337 post type-post status-publish">';
		$html .= '<header class="entry-header">';
		$html .= '<h2 class="entry-title"><a href="'.get_permalink().'" rel="bookmark">'.get_the_title().'</a></h2>';
		$html .= '</header>';
		$html .= '<div class="entry-content">';
		$html .= get_the_excerpt();
		$html .= '</div>';
		$html .= '<footer class="entry-footer">';
		$html .= '<span class="byline">';
		$html .= '<span class="screen-reader-text">Author </span> <a class="url fn n" href="http://localhost/wordpress/author/admin/">admin</a></span></span><span class="posted-on"><span class="screen-reader-text">Posted on </span><a href="http://localhost/wordpress/default-title/" rel="bookmark"><time class="entry-date published updated" datetime="2019-02-20T21:40:14+00:00">February 20, 2019</time></a></span><span class="cat-links"><span class="screen-reader-text">Categories </span><a href="http://localhost/wordpress/category/article/" rel="category tag">Article</a>, <a href="http://localhost/wordpress/category/learning-strategy/exams/" rel="category tag">Exams</a></span><span class="comments-link"><a href="http://localhost/wordpress/default-title/#respond">Leave a comment<span class="screen-reader-text"> on Default title</span></a></span>		<span class="edit-link"><a class="post-edit-link" href="http://localhost/wordpress/wp-admin/post.php?post=337&#038;action=edit">Edit<span class="screen-reader-text"> "Default title"</span></a></span>';
		$html .= '</footer>';
		$html .= '</article>';
				
	endwhile;

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



