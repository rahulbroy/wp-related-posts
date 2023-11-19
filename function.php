<?php
//Get the related posts for each post and save it in meta field linked to the post
function se_related_posts() {
	do_action( 'simple_related_posts');
}
add_action( 'simple_related_posts', 'refresh_related_posts', 10 );

function refresh_related_posts( ) {
    
	$all_posts = get_posts( 
			array( 
				'numberposts' => -1 
			) 
		);
	
	foreach ($all_posts as $post):
		
		setup_postdata( $post );
		
		$a = [];
		$i = 0;
		$args = array(
		    'category__in'   => wp_get_post_categories( $post->ID ),
			'posts_per_page' => 6,
			'post_status' => 'publish',
			'post__not_in'   => array( $post->ID )
		  );
		  
		 $my_query = new WP_Query($args);
		
		 if ($my_query->have_posts() ){ //Check if there are any related posts for the post
			
			while( $my_query->have_posts() ) {
				$my_query->the_post();
				
				$a[$i][0] = get_the_permalink();
				$a[$i][1] = wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()), 'thumbnail' );
				$a[$i][2] = get_the_title();
				$a[$i][3] = get_the_date();	
				$i++;
				
			}
			
			if ( get_option('run_only_once_01') ):
				delete_post_meta($post->ID, 'post_related_posts');				
			endif;
			add_post_meta($post->ID, 'post_related_posts' , $a );
		 }
		 wp_reset_query();
		 
	   
	endforeach;

}


//Refresh the related posts if new post is published
function wpdocs_run_on_publish_only( $new_status, $old_status, $post ) {
    if ( ( 'publish' === $new_status && 'publish' !== $old_status ) ) {
        se_related_posts();
    }
}
add_action( 'transition_post_status', 'wpdocs_run_on_publish_only', 10, 3 );

//Run this code once to cache related posts
function rr_run_code_one_time() {
    if ( !get_option('run_only_once_01') ):
 
		se_related_posts();
 
        add_option('run_only_once_01', 1); 
    endif;
}
add_action( 'init', 'rr_run_code_one_time' );
?>
