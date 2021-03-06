<?php
/*--- On-Tap Widget ---*/
if ( ! defined( 'ABSPATH' ) ) exit;
class ebl_on_tap extends WP_Widget {
  function __construct(){
    parent::__construct(
      'ebl_on_tap', // Base ID
      __('On Tap','ebl_on_tap_domain'), // Widget name will appear in UI
      ['description' => __('Display a list of what beers are on-tap','ebl_on_tap_domain' )] // Widget description
    );
  }
  /*--- WIDGET FRONT END ---*/
  public function widget($args, $instance){
    $title = apply_filters('widget_title', $instance['title']);
    // before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if (!empty($title))
    echo $args['before_title'] . $title . $args['after_title'];
    
    // This is where you run the code and display the output
    $ebl_on_tap = new WP_Query(["post_type" => "beers","tax_query" => [["taxonomy"  => "availability","field" => "slug","terms" => "on-tap",],],]);
    do_action('ebl_before_on_tap_widget');?>
    <?php echo apply_filters('ebl_on_tap_widget_before','<ul class="ebl-on-tap-widget">'); ?> 
    <?php if($ebl_on_tap->have_posts()) : while($ebl_on_tap->have_posts()) : $ebl_on_tap->the_post();
      echo apply_filters('ebl_on_tap_widget_loop','<li><a href="'.get_post_permalink().'">'.get_the_title().'</a></li>');
    endwhile; endif;?>
    <?php echo apply_filters('ebl_on_tap_widget_after','</ul>'); ?>
    <?php do_action('ebl_after_on_tap_widget');
    echo $args['after_widget'];
    wp_reset_query();
  }
  // Widget Backend 
  public function form($instance){
    if(isset($instance['title'])){
      $title = $instance[ 'title' ];
    }
    else{
      $title = __( "On Tap", 'ebl_on_tap_domain' );
    }
    // Widget admin form
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php 
  }
  // Updating widget replacing old instances with new
  public function update($new_instance,$old_instance){
    $instance = [];
    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    return $instance;
  }
}
function ebl_on_tap_function() {
	register_widget( 'ebl_on_tap' );
}
/*--- END ON TAP WIDGET ---*/
/*--- Random Beer ---*/
class ebl_random_beer extends WP_Widget{
  function __construct(){
    parent::__construct(
      'ebl_random_beer', // Base ID of your widget
      __('Random Beer', 'ebl_random_beer_domain'), // Widget name will appear in UI
      ['description' => __('Features a random beer on page load', 'ebl_random_beer_domain')] // Widget description
    );
  }
  // Widget front-end
  public function widget($args, $instance){
    $title = apply_filters( 'widget_title', $instance['title'] );
    // before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if (!empty($title))
    echo $args['before_title'] . $title . $args['after_title'];
    // This is where you run the code and display the output
    $ebl_random_beer = new WP_Query(["posts_per_page" => 1, "post_type" => "beers", 'orderby' => 'rand']);
    if($ebl_random_beer->have_posts()) : while($ebl_random_beer->have_posts()) : $ebl_random_beer->the_post();
    do_action('ebl_before_random_beer_shortcode');
    echo apply_filters('ebl_random_beer_shortcode_content','<div class="ebl-random-beer">
      <h3><a href="'.get_post_permalink().'">'.get_the_title().'</a></h3>
      <p>'.get_the_excerpt().'</p>
      '.wp_get_attachment_image( get_post_thumbnail_id(),'small' ).'
    </div>');
    do_action('ebl_after_random_beer_shortcode'); ?>
    <?php
       endwhile; endif;
    echo $args['after_widget'];
    wp_reset_query();
  }	
  // Widget Backend 
  public function form($instance){
    if(isset($instance['title'])){
      $title = $instance[ 'title' ];
    }
    else {
      $title = __( "Featured Beer", 'ebl_random_beer_domain' );
    }
    // Widget admin form
    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php 
  }
  // Updating widget replacing old instances with new
  public function update($new_instance,$old_instance){
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    return $instance;
  }
}
function ebl_random_beer_function() {
  register_widget( 'ebl_random_beer' );
}
/*--- END RANDOM BEER WIDGET ---*/
add_action( 'widgets_init', 'ebl_random_beer_function' );
add_action( 'widgets_init', 'ebl_on_tap_function' );
?>