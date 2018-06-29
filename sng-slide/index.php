<?php
/**
 * Plugin Name: SNG Slider
 * Description: Plugin de slideshow
 * Version: 1.0.0
 * Author: Everson Queiroz
 * License: GPL2
 * Text Domain: sng_slide
 */
 
 
function sng_side_load_plugin_textdomain() {
    load_plugin_textdomain( 'sng_slide', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'sng_side_load_plugin_textdomain' );
 
add_action( 'init', 'slide_registrar', 0 );

function slide_registrar() {
	$labels = array(
		'name'               => __( 'Slides', 'sng_slide' ),
		'singular_name'      => __( 'Slide', 'sng_slide' ),
		'menu_name'          => __( 'Slides', 'sng_slide'),
		'name_admin_bar'     => __( 'Slide', 'sng_slide'),
		'add_new'            => __( 'Add new', 'sng_slide' ),
		'add_new_item'       => __( 'Add new slide', 'sng_slide' ),
		'new_item'           => __( 'New slide', 'sng_slide' ),
		'edit_item'          => __( 'Edit slide', 'sng_slide' ),
		'view_item'          => __( 'View slide', 'sng_slide' ),
		'all_items'          => __( 'All slides', 'sng_slide' ),
		'search_items'       => __( 'Search slides', 'sng_slide' ),
		'parent_item_colon'  => __( 'Parent slide:', 'sng_slide' ),
		'not_found'          => __( 'No slide found.', 'sng_slide' ),
		'not_found_in_trash' => __( 'No slide found in trash.', 'sng_slide' )
	);

	$args = array(
		'labels'             => $labels,
    'description'        => __( 'Description.', 'sng_slide' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'slide' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail' )
	);
	register_post_type( 'slide', $args );
}

function add_menu_icons_styles() { ?>

<style>
#adminmenu #menu-posts-slide div.wp-menu-image:before {
  content: '\f128';
}

#content {
	color:#000 !important;
}

#contvid {
	display:none;
}
</style>

<?php
}
add_action( 'admin_head', 'add_menu_icons_styles' );
add_action( 'add_meta_boxes', 'slides_metaboxes' );

function slides_metaboxes() {
	add_meta_box('link', 'Link', 'slides_link', 'slide', 'side', 'default');
}

function slides_link() {
	global $post;
	
	echo '<input type="hidden" name="linkmeta_noncename" id="linkmeta_noncename" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	$link = get_post_meta($post->ID, 'link', true);
	?>
	
	<input type="url" name="link" class="widefat" value="<?php echo $link; ?>" />
<?php
}

/* Salvar dados da galeria */
function save_slides_meta($post_id, $post) {
	
	if ($post->post_type =="slide"){
		
		if ( isset($_POST['linkmeta_noncename']) && !wp_verify_nonce( $_POST['linkmeta_noncename'], plugin_basename(__FILE__) )) {
		    return $post->ID;
		}
	
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
	
		
		$slides['link'] = $_POST['link'];
		
		foreach ($slides as $key => $value) {
			if( $post->post_type == 'revision' ) return;
			$value = implode(',', (array)$value);
			if(get_post_meta($post->ID, $key, FALSE)) {
				update_post_meta($post->ID, $key, $value);
			} else {
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key);
		}
	}

}

add_action('save_post', 'save_slides_meta', 1, 2);


function move_posteditor( $hook ) {
    if ( $hook == 'post.php' OR $hook == 'post-new.php' ) {
        wp_enqueue_script( 'jquery' );
        add_action('admin_print_footer_scripts', 'move_posteditor_scripts');
    }
}

add_action( 'wp_enqueue_scripts', 'my_enqueued_assets' );

function my_enqueued_assets() {
	wp_enqueue_script( 'caroufredsel', plugin_dir_url( __FILE__ ) . 'js/jquery.carouFredSel-6.2.1-packed.js', array( 'jquery' ), '20150330', true );   
  wp_enqueue_script( 'plugin', plugin_dir_url( __FILE__ ) . 'js/plugin.js', array( 'jquery' ), '20150330', true );
    
	wp_enqueue_style( 'sliderstyle', plugin_dir_url( __FILE__ ) . '/css/sliderstyle.css' );
}


function wp_get_attachment( $attachment_id ) {

    $attachment = get_post( $attachment_id );
    return array(
        'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
        'caption' => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'href' => get_permalink( $attachment->ID ),
        'src' => $attachment->guid,
        'title' => $attachment->post_title
    );
}



add_action('wp_footer','slide_hook');

function slide_hook() {
	echo $output;
}


// SHORTOCODE SLIDER
function slider_func( $atts ) {

    $output = '<div class="sliderWraper">
                    <div class="sliderContent">
                      <div class="slidprev"><span>Prev</span></div>
                      <div class="slidnext"><span>Next</span></div>
                      <div class="slider">';

                            $args    = array(
                             'numberposts' => 4,  
                             'order'       => 'ASC',
                             'post_type'   => 'slide',
                             'post_status' => 'publish'
                            );

                            $loop = new WP_Query( $args );
                            if ( $loop->have_posts() ) {
                                while ( $loop->have_posts() ) : $loop->the_post(); 
                                	$post_id = get_the_ID();
									$link = get_post_meta($post_id, 'link' );

                                    $output .= '<div class="slide">'; ?>
                                        <?php if (!empty($link)) {
                                        	$output .= '<a href="'.$link["0"].'" target="_blank">';
                                        }
                                        	$output .= get_the_post_thumbnail();
                                        ?>
                                        <?php if (!empty($link)) {
                                        	$output .= '</a>';
                                        }
                                        $output .= '<div class="slidtextWrapper"><div class="slid_text">
                                          <h3 class="slid_title"><span>'.get_the_title().'</span></h3>
                                          <p>'.get_the_content().'</p>
                                          </div>
                                        </div>
                                    </div>';
                                endwhile;
                            } else {
                                $output .= 'Nada encontrado';
                            }
                            wp_reset_postdata();

            $output .=  '</div><!-- .slider --><div class="clear"></div>
                        <div class="paginationSlider">';
                            if ( $loop->have_posts() ) {
                               	$i=1;
                                while ( $loop->have_posts() ) : $loop->the_post();
                                $output .= '<div class="control"><span>'.$i.'</span></div>';
                                $i++;
                            endwhile;
                            }
            $output .=  '</div></div>
  </div><div class="clear"></div>';

    return $output;
}
add_shortcode( 'slider', 'slider_func' );