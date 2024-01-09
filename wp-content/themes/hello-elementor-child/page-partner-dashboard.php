<?php
$products = Database::getInstance()->getProducts();

$user = wp_get_current_user();
$roles = ( array ) $user->roles;

if($roles[0] == 'administrator' && isset($_GET['pID']))
	$user_id = $_GET['pID'];
else
	$user_id = get_current_user_id();

$product_id = HotelTransfers::getHotelProdukt($user_id);
if($product_id){
	$hotelProduct = wc_get_product($product_id->product_id);
	$variations = $hotelProduct->get_children();
}

//echo "<pre>"; print_r($roles); echo "</pre>";

get_header();

?>

<div class="container">
	<?php if($roles[0] == 'administrator' && empty($_GET['pID']) && empty($_GET['rucktransfer']) && empty($_GET['hintransfer'])): ?>
		<h2>Partner wählen</h2>			
		<form action="/partner-dashboard" method="GET">
			<div class="row">
				<div class="col-sm-12 col-md-3 col-lg-2">
					<select class="form-control" name="pID">
						<option value="24">AMH</option>
						<option value="25">HMA</option>
						<option value="26">IAPS</option>
					</select>
				</div>
				<div class="col-sm-12 col-md-2 col-lg-1">
					<button type="submit" class="btn btn-primary">Weiter</button>
				</div>
				<div class="col-sm-12 col-md-2 col-lg-1">
					<a href="<?php echo wp_logout_url(get_permalink()) ?>" type="button" class="btn btn-primary">Abmelden</a>
				</div>
			</div>
		</form>
		
		
    <?php elseif($variations == null): ?>
		<h2>Produkte nicht vorhanden</h2>
	<?php else: ?>		
		<h2>Transferbuchung</h2>
	
		<?php
		if(isok($_GET, 'rucktransfer') && isok($_GET, 'hintransfer')){
			require_once get_stylesheet_directory() . '/partner-dashboard-templates/all.php';
		}else if (isok($_GET, 'hintransfer')) {
			require_once get_stylesheet_directory() . '/partner-dashboard-templates/hintransfer.php';
		}else if(isok($_GET, 'rucktransfer')) {
			require_once get_stylesheet_directory() . '/partner-dashboard-templates/rucktransfer.php';
		}else{
			require_once get_stylesheet_directory() . '/partner-dashboard-templates/index.php';
		}
		?>
	<?php endif; ?>
</div>
    <br><br><br>
<?php get_footer() ?>