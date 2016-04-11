<?php function tasbb_register_addons_page(){
	add_submenu_page(
		'edit.php?post_type=beers',
		__( 'BrewBuddy Extensions' ),
		__( 'Extensions' ),
		'manage_options',
		'brewbuddy-addons',
		'tasbb_addons_page'
	);

	add_submenu_page(
		'edit.php?post_type=menus',
		__( 'Menu Themes' ),
		__( 'Themes' ),
		'manage_options',
		'brewbuddy-themes',
		'tasbb_themes_page'
	);

}
add_action('admin_menu','tasbb_register_addons_page');

function tasbb_addons_page(){
	$json = file_get_contents('http://brewbuddy.io/edd-api/products/');
	$json = json_decode($json);
	if(get_option('tasbb_referral_id') != null){
		$referral = '/?ref='.get_option('tasbb_referral_id');
	}
	foreach($json->products as $product){
		$product = $product->info; ?>
	<div class="product">
		<img src="<?php echo $product->thumbnail; ?>">
		<h2><?php echo $product->title; ?></h2>
		<p><?php echo $product->excerpt; ?></p>
		<div class="cta-buttons">
		<a class="button primary" href="#">Documentation</a>
		<a class="button primary" href="http://www.brewbuddy.io/downloads/<?php echo $product->slug.$referral ?>">Learn More</a>
		</div>
	</div>
<?php }
}

function tasbb_themes_page(){
	echo "Hi :)";
}
