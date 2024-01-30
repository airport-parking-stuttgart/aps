<?php

$orders = 5;
$ammount = "";
$customers = Database::getInstance()->getCustomerCoupon();

$count_bookings = array("10", "20", "30");
$sum_sales = array("100", "200", "300");

//echo "<pre>"; print_r($customers); echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Gutscheine</h3>
    </div>
    <div class="page-body">
		<form class="form-filter">
			<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Kunden filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="bookings" id="customer_bookings" class="form-item form-control">
								<option value="">Buchungen</option>
								<?php foreach ($count_bookings as $bookings) : ?>
								<option value="<?php echo $bookings ?>"
								<?php if($bookings == $_GET["bookings"]) echo "selected"; ?>><?php echo $bookings ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="sales" id="customer_sales" class="form-item form-control">
								<option value="">Umsatz</option>
								<?php foreach ($sum_sales as $sales) : ?>
								<option value="<?php echo $sales ?>"
								<?php if($sales == $_GET["sales"]) echo "selected"; ?>><?php echo $sales ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=gutscheine' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>				
				</div>
			</div>
		</form>
		<div class="row">
			<div class="col-sm-12 col-md-3 col-lg-12">
				<table class="datatable">
					<thead>
						<tr>
							<th>Name</th>
							<th>Stadt</th>
							<th>Buchungen</th>
							<th>Umsatz</th>
							<th>Gutschein</th>
							<th>Wert</th>
							<th>Aktion</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($customers as $customer) : ?>
						<?php if(isset($_GET['bookings']) && $customer->total_orders < $_GET['bookings'] || isset($_GET['sales']) && $customer->total_amount < $_GET['sales'])
							continue;
						?>
						<tr>
							<td><?php echo $customer->first_name . " " . $customer->last_name ?></td>
							<td><?php echo $customer->city ?></td>
							<td><?php echo $customer->total_orders ?></td>
							<td><?php echo number_format($customer->total_amount,2,".",".") ?></td>
							<td>
								<select name="coupontype-<?php echo $customer->customer_id ?>" id="coupontype-<?php echo $customer->customer_id ?>">
								<option value="p">prozentual</option>
								<option value="f">fix in €</option>
								</select>
							</td>
							<td><input type="number" id="couponval-<?php echo $customer->customer_id ?>" size="3" max="100" value="1"/></td>
							<td><a id="<?php echo $customer->customer_id ?>" class="btn btn-primary" onclick="sendCoupon(this)">Gutschein versenden</a></td>
						</tr>
						<?php endforeach ;?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
function sendCoupon(e){
	var customer_id = e.id;
	var coupontype = document.getElementById("coupontype-"+e.id);
	var couponval = document.getElementById("couponval-"+e.id);
	jQuery.ajax({
		url: "../wp-content/plugins/itweb-booking/classes/Helper.php",
		data: {
			customer_id : customer_id,
			coupontype : coupontype.value,
			couponval : couponval.value,
			task : "send_coupon"
		},
		type: 'POST',
		success: function(data){				
			//window.location.reload();
			if(data)
				alert(data);
		}
	});
}
</script>