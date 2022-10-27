<?php

namespace DSI\TechPub;

use DSI\TechPub\User\UserRoles;
?>
<?php
$tech_pub_lib = (TechPubLib::get_instance());
$products = '';
if (!empty($product_query_obj)) {
	$products = $product_query_obj->products;
}
$helper = (Helper::get_instance());
$is_retail = (UserRoles::get_instance())->is_retail_user();
$css_class = 'w3-auto';
if ($is_retail === true) {
	$css_class = 'w3-col s10';
}
?>
<div class="dsi-tech-pub-lib">
	<form name="dsi-search-form" id="dsi-search-form" action="" method="get">
		<section class="dsi-search w3-row w3-padding-48">
			<div class="dsi-keyword-search w3-col s12">
				<div class="<?php echo $css_class; ?>">
					<input type="text" value="<?php echo (isset($_GET['keyword'])) ? $_GET['keyword'] : ''; ?>" class="s8" name="keyword" id="keyword" placeholder="Search..." />
				</div>
				<?php if ($is_retail) : ?>
					<div class="w3-col s1">
						<a class="cart-icon" href="/cart"><i class="fa fa-3x fa-shopping-cart" aria-hidden="true"></i></a>
					</div>
					<div class="w3-col s1">
						<a class="checkout-button link elementor-size-sm" href="<?php echo wc_get_checkout_url(); ?>">Checkout</a>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<section class="dsi-search-filters w3-row w3-padding-48">
			<div class="dsi-label">
				<div class="heading w3-container">
					<h3>Filter by:</h3>
				</div>
			</div>
			<div class="filters-list w3-row w3-container">
				<div class="filter filter-category w3-col m4 w3-padding-16">
					<?php
					$first_element = array('' => '--Select Category--');
					$filter_name = 'category';
					$extra = array('id' => $filter_name, 'class' => 'w3-col m11');
					$options = $first_element + $category_options;
					$selected_item = (isset($_GET[$filter_name])) ? $_GET[$filter_name] : '';
					$selected = [$selected_item];
					ksort($options);
					echo $helper->form_dropdown($filter_name, $options, $selected, $extra);
					?>
				</div>
				<div class="filter filter-make w3-col m4 w3-padding-16">
					<?php
					$first_element = array('' => '--Select Aircraft Make--');
					$filter_name = 'aircraft_make';
					$extra = array('id' => $filter_name, 'class' => 'w3-col m11');
					$options = $first_element + $make_options;
					$selected_item = (isset($_GET[$filter_name])) ? $_GET[$filter_name] : '';
					$selected = [$selected_item];
					ksort($options);
					echo $helper->form_dropdown($filter_name, $options, $selected, $extra);
					?>
				</div>
				<div class="filter filter-model w3-col m4 w3-padding-16">
					<?php
					$first_element = array('' => '--Select Aircraft Model--');
					$filter_name = 'aircraft_model';
					$extra = array('id' => $filter_name, 'class' => 'w3-col m11');
					$options = $first_element + $model_options;
					$selected_item = (isset($_GET[$filter_name])) ? $_GET[$filter_name] : '';
					$selected = [$selected_item];
					ksort($options);
					echo $helper->form_dropdown($filter_name, $options, $selected, $extra);
					?>
				</div>
			</div>
			<div class="dsi-search-button w3-row w3-padding-16 w3-container">
				<button class="w3-display-right" type="submit" name="dsi-search" value="dsi-search">Search</button>
			</div>
		</section>
		<?php wp_nonce_field('search-form-nonce', '_wpnonce', false); ?>
	</form>
	<section class="dsi-content w3-row w3-padding-16 w3-border">
		<div class="prodcuts-list w3-row w3-container w3-padding-large">
			<?php if (!empty($products)) : ?>
				<?php foreach ($products as $product) : ?>
					<div id="<?php echo $product->get_id(); ?>" class="single-product w3-col w3-padding-16">
						<div class="product left-section w3-col m10">
							<div class="product-image w3-col m2">
								<img class="w3-image" src="<?php echo DSI_CUST_PLUGIN_DIR_URL . '/assets/images/tech-pub.png'; ?>" />
							</div>
							<div class="product-info w3-col m8 ">
								<h4><?php echo $product->get_name(); ?></h4>
								<p><?php echo $product->get_description(); ?></p>
								<?php if ($item = $product->get_meta('part_numbers')) : ?>
									<p class="info"><strong>Part Numbers: </strong><?php echo $item; ?></p>
								<?php endif ?>
								<?php if ($item = $product->get_meta('user_guide_number')) : ?>
									<p class="info"><strong>User Guide Number: </strong><?php echo $item; ?></p>
								<?php endif ?>
								<?php if ($item = $product->get_meta('revision_number')) : ?>
									<p class="info"><strong>Revision Number: </strong><?php echo $item; ?></p>
								<?php endif ?>
								<?php if ($item = $product->get_meta('supported_products')) : ?>
									<p class="info"><strong>Supported Products: </strong><?php echo $item; ?></p>
								<?php endif ?>
								<?php if ($item = $product->get_meta('aircraft_make')) : ?>
									<p class="info"><strong>Aircraft Make: </strong><?php echo $item; ?></p>
								<?php endif ?>
								<?php if ($item = $product->get_meta('aircraft_model')) : ?>
									<p class="info"><strong>Aircraft Model: </strong><?php echo $item; ?></p>
								<?php endif ?>
							</div>
						</div>
						<div class="product right-section w3-col m2 w3-padding-16">
							<?php $access = $tech_pub_lib->is_user_has_access($product->get_id()); ?>
							<?php if (!is_user_logged_in()) : ?>
								<div class="instructions">
									<p><a class="text" href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>">Login</a> as distributor or <a class="text" href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>">register</a> for a retail customer account.</p>
								</div>
							<?php elseif (
								false === $access &&
								((UserRoles::get_instance())->is_distributor_plus_user() || (UserRoles::get_instance())->is_distributor_user())
							) : ?>
								<div class="instructions">
									<p>Call us at to renew your subscription: <a class="text" href="tel:18773745521">(877) 374-5521</a></p>
								</div>
							<?php elseif (true === $access) : ?>
								<div class="instructions">

									<?php if ($tech_pub_lib_obj->is_product_specific_to_distributor_plus($product->get_id()) === false) : ?>
										<p>Distributor Plus Users Only. Please <a class="text" href="/<?php echo TechPubLib::CONTACT_PAGE; ?>">contact us.</a></p>
									<?php else : ?>
										<a href="<?php echo $tech_pub_lib->get_media_url($product->get_meta('upload_file'), $product->get_id()); ?>" class="button link elementor-size-sm">Download</a>
									<?php endif; ?>

								</div>
							<?php elseif (false === $access && (UserRoles::get_instance())->is_retail_user()) : ?>
								<div class="instructions">

									<?php if ($tech_pub_lib_obj->is_product_specific_to_distributor_plus($product->get_id()) === false) : ?>
										<p>Distributor Plus Users Only. Please <a class="text" href="/<?php echo TechPubLib::CONTACT_PAGE; ?>">contact us.</a></p>
									<?php else : ?>
										<p class="dsi-price">$<?php echo $product->get_price(); ?></p>
										<a href="<?php echo $tech_pub_lib->get_product_add_url($product->get_id()); ?>" class="button link elementor-size-sm">Add to Cart</a>
									<?php endif; ?>



								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="w3-row">
					<div class="w3-container w3-col s12">
						<p>Sorry, no items match your search criteria.</p>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div class="dsi-pagination w3-row">
			<div class="w3-center">
				<div class="w3-bar w3-xlarge">
					<div class="s4"></div>
					<div class="s4"><?php echo $tech_pub_lib->get_pagination($product_query_obj); ?></div>
					<div class="s4"></div>
				</div>
			</div>
		</div>
	</section>
</div>