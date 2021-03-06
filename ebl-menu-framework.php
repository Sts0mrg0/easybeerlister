<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$ebl_menu_templates = [];
//Class to call for menu query
class ebl_menu{
  public function __construct($column_default = 10){
    $this->filter = [
      'sort'                 => get_post_meta(get_the_ID(),'ebl_export_sort_order',true),
      'sortby'               => get_post_meta(get_the_ID(),'ebl_export_sortby',true),
      'on-tap'               => get_post_meta(get_the_ID(),'ebl_export_ontap',true),
      'pairings'             => get_post_meta(get_the_ID(),'ebl_export_pairings',true),
      'tags'                 => ebl_parse_taxonomy_checkbox('tags'),
      'style'                => ebl_parse_taxonomy_checkbox('style'),
      'availability'         => ebl_parse_taxonomy_checkbox('availability'),
      'show_description'     => get_post_meta(get_the_ID(),'ebl_export_show_description',true),
      'show_price'           => get_post_meta(get_the_ID(),'ebl_export_show_price',true),
      'show_image'           => get_post_meta(get_the_ID(),'ebl_export_show_img',true),
      'show_ibu'             => get_post_meta(get_the_ID(),'ebl_export_show_ibu',true),
      'show_abv'             => get_post_meta(get_the_ID(),'ebl_export_show_abv',true),
      'show_og'              => get_post_meta(get_the_ID(),'ebl_export_show_og',true),
      'show_style'           => get_post_meta(get_the_ID(),'ebl_export_show_style',true),
      'show_brewer_name'     => get_post_meta(get_the_ID(),'ebl_export_show_brewer_name',true),
      'show_brewer_city'     => get_post_meta(get_the_ID(),'ebl_export_show_brewer_city',true),
      'show_brewer_state'    => get_post_meta(get_the_ID(),'ebl_export_show_brewer_state',true),
      'beers_to_exclude'     => get_post_meta(get_the_ID(),'ebl_beers_to_filter',true),
      'beers_to_include'     => get_post_meta(get_the_ID(),'ebl_beers_to_include',true),
      'is_menu_public'       => get_post_meta(get_the_ID(),'ebl_menu_public',true) == 'ebl_public' ? true : false,
    ];
		$this->columnDefault = $column_default;
		$this->beerColumnOverride = get_post_meta(get_the_ID(),'ebl_beers_per_column',true);
		if($this->beerColumnOverride != null || $this->beerColumnOverride > 0){
			$this->beersPerColumn = $this->beerColumnOverride;
		}
		else{
			$this->beersPerColumn = $this->columnDefault;
		}
    $this->heading = get_post_meta(get_the_ID(),'ebl_export_menu_heading',true);
    $this->subheading = get_post_meta(get_the_ID(),'ebl_export_menu_subheading',true);
    $this->beforeMenu = get_post_meta(get_the_ID(),'ebl_export_menu_before_menu',true);
    $this->afterMenu = get_post_meta(get_the_ID(),'ebl_export_menu_after_menu',true);
    if(get_the_post_thumbnail_url() != null){
      $this->thumbnail = get_the_post_thumbnail_url();
    }
    elseif(get_option('ebl_default_menu_image') != null || get_option('ebl_default_menu_image') != ''){
      $this->thumbnail = get_option('ebl_default_menu_image');
    }
    else{
      $this->thumbnail = false;
    }
	}
	//Batch Imports Brewery Info
   public function brewery_info(){
    ebl_beer_info_exists('ebl_brewer_name')  &&  $this->filter['show_brewer_name'] ==  true ? ebl_beer_info('ebl_brewer_name') : '';
    if(ebl_beer_info_exists('ebl_brewer_city')  &&  ebl_beer_info_exists('ebl_brewer_name') && $this->filter['show_brewer_name'] ==  true && $this->filter['show_brewer_city'] == true){
      echo ' - ';
    }
    if(ebl_beer_info_exists('ebl_brewer_city') && ebl_beer_info_exists('ebl_brewer_state')  &&  ebl_beer_info_exists('ebl_brewer_name') && $this->filter['show_brewer_name'] ==  true && $this->filter['show_brewer_state'] == true && $this->filter['show_brewer_city'] == false){
      echo ' - ';
    }

    ebl_beer_info_exists('ebl_brewer_city')  &&  $this->filter['show_brewer_city'] ==  true ? ebl_beer_info('ebl_brewer_city') : '';
    if(ebl_beer_info_exists('ebl_brewer_state') &&  ebl_beer_info_exists('ebl_brewer_city') && $this->filter['show_brewer_city'] ==  true && $this->filter['show_brewer_state'] == true){
      echo ', ';
    }
    ebl_beer_info_exists('ebl_brewer_state') &&  $this->filter['show_brewer_state'] == true ? ebl_beer_info('ebl_brewer_state') : '';
   }
  
  //Inserts the featured image with logic
   public function thumbnail(){
     if($this->thumbnail != false){?>
       <img src="<?php echo $this->thumbnail; ?>">
     <?php }
   }
   
   private function parse_beer_list($list){
     $list_items = explode(PHP_EOL, $list);
     $items = [];
     foreach($list_items as $list_item){
       if(is_numeric($list_item)){
         array_push($exclude,$list_item);
       }
       else{
         $obj = get_page_by_title($list_item,'OBJECT','beers');
         array_push($items, $obj->ID);
       }
     }
     return $items;
   }

   public function get_beers(){
     if($this->has_filters() == true){
       $filtered_beers = new WP_Query($this->args());
       $added_beers    = new WP_Query($this->include_args());
       $result         = new WP_Query();
       $result->posts = array_merge($filtered_beers->posts,$added_beers->posts);
       $result->post_count = $filtered_beers->post_count + $added_beers->post_count;
       $result->found_posts = $filtered_beers->found_posts + $added_beers->found_posts;
     }
     else{
       $result = new WP_Query($this->include_args());
     }
     return $result;
   }
  
   //Checks if beer filters exist
   private function has_filters(){
     $filters_to_check = [
        'on-tap',
        'tags',
        'style',
        'availability',
        'beers_to_exclude'
      ];
     $i = 0;
     $result = false;
     foreach($filters_to_check as $filter_to_check){
       if(!empty($this->filter[$filter_to_check]) && $i == 0){
         $i++;
       }
       if($i != 0){
         $result = true;
         break;
       }
     }
      return $result;
   }
   
	//Filters beers from query. This is only half of the finished query, which is merged in get_beers()
	public function args(){
       $args = [
        'post_type'      => 'beers',
        'order'          => $this->filter['sort'],
        'orderby'        => 'meta_value_num',
        'meta_key'       => $this->filter['sortby'],
        "tax_query"      => [],
        'posts_per_page' => -1,
        'post__not_in'   => ''
        ];

       if($this->filter['sortby'] == 'name'){
          $args['orderby'] = 'name';
          unset($args['meta_key']);
       }
        //--- ON TAP ---//
      if($this->filter['on-tap'] != null){
        array_push($args['tax_query'], [
            "taxonomy"  => "availability",
            "field" => "slug",
            "terms" => "on-tap"
          ]);
      };
        //--- Pairings ---//
      if($this->filter['pairings'] != null){
        $this->filter['pairings'] = str_replace(' ','-',$this->filter['pairings']);
        $this->filter['pairings'] = strtolower($this->filter['pairings']);
        $this->filter['pairings'] = str_getcsv($this->filter['pairings']);
        array_push($args['tax_query'],[
          'taxonomy' => 'pairing',
          'field' => 'slug',
          'terms' => $this->filter['pairings']
        ]);
      };
        //--- Tags ---//
      if($this->filter['tags'] != null){
        array_push($args['tax_query'],[
          'taxonomy' => 'tags',
          'field' => 'slug',
          'terms' => $this->filter['tags']
        ]);
      };
        //--- Availability ---//
      if($this->filter['availability'] != null){
        array_push($args['tax_query'],[
          'taxonomy' => 'availability',
          'field' => 'slug',
          'terms' => $this->filter['availability']
        ]);
      };
        //--- type ---//
      if($this->filter['style'] != null){
        array_push($args['tax_query'],[
          'taxonomy' => 'style',
          'field' => 'slug',
          'terms' => $this->filter['style']
        ]);
      };
        //--- Beers to exclude ---//
     if($this->filter['beers_to_exclude'] != null){
       $args['post__not_in'] = $this->parse_beer_list($this->filter['beers_to_exclude']);;
     }
     return $args;
	}
  
   //Adds specified beers to query. This is only half of the finished query, which is merged in get_beers()
   public function include_args(){
      $args = [
        'post_type'  => 'beers',
        'post_count' => -1,
        'order'      => $this->filter['sort'],
        'orderby'    => 'meta_value_num',
        'meta_key'   => $this->filter['sortby'],
      ];
      //--- Beers to include ---//
      if($this->filter['beers_to_include'] != null){
      $args['post__in'] = $this->parse_beer_list($this->filter['beers_to_include']);;
    }
    return $args;
  }
};

//Constructor for new menu template
class ebl_menu_template{
	public function register($args){
		$this->directory = $args['directory'];
		$this->file_name = $args['file_name'];
		$this->name = $args['template_name'];
		$this->register_template();
		$this->slug = strtolower(str_replace(" ","-",sanitize_text_field($this->name)));
	}
	private function register_template(){
		global $ebl_menu_templates;
		$ebl_menu_templates[] = $this;
	}
}

function ebl_get_menu_template($slug){
	global $ebl_menu_templates;
	foreach($ebl_menu_templates as $template){
		if($template->slug == $slug){
			$result = $template;
			return $result;
		}
		else{
			$result = false;
		}
	}
	return $result;
}

//Constructs the default menu
$ebl_default_print_menu = new ebl_menu_template;
$ebl_default_print_menu->register([
	'directory' => plugin_dir_path(__FILE__),
	'file_name' => 'ebl-menu-template.php',
	'template_name' => 'Default Print Template'
]);

//Constructs the default TV menu template
$ebl_default_tv_menu = new ebl_menu_template;
$ebl_default_tv_menu->register([
	'directory' => plugin_dir_path(__FILE__),
	'file_name' => 'ebl-tv-menu-template.php',
	'template_name' => 'Default TV Template'
]);

//Setup for a menu
function ebl_menu_head($menu_object){
		if(!is_user_logged_in() && $menu_object->filter['is_menu_public'] != true){
        do_action('ebl_menu_not_logged_in');
		  	$error_message = '<h1>Please log in to view this content</h1>';
        echo $error_message;
		  die;
		}
      else{?>
        <!DOCTYPE HTML>
        <head><?php
          do_action('ebl_menu_head_scripts');?>
          <?php }; ?>
          <style>
            <?php echo get_post_meta(get_the_ID(),'ebl_export_menu_css',true); ?>
          </style>
        </head>
<?php   do_action('ebl_menu_head');
}

//Checks if menu template exists
function ebl_locate_menu_template($template){
	if(file_exists(get_stylesheet_directory().'/'.'menu-'.$template.'.php')){
		return get_stylesheet_directory().'/'.'menu-'.$template.'.php';
	}
	else{
		return false;
	}
}
