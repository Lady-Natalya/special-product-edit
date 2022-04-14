// Special Product Edit
// Do things like applying discounts across an entire product line
// Add or remove some arbitrary number of sales unit across a product line

// ################
// Add Link to Menu
// ################

add_action('admin_menu', 'special_product_menu_link');

function special_product_menu_link() {
	add_submenu_page(
		'woocommerce',
		'Special Product Edit', /* Title of the page itself. */
		'Special Product Edit', /* How it displays in the menu on the left. */
		'manage_options',
		'special-product-page',
		'special_product_menu_link_callback' );
}

// ############
// Display Page
// ############


function special_product_menu_link_callback() {
  	global $wpdb;

	// Load Settings
  	$custom_prod_type_list = explode(' ', get_option('spe_tool_settings')['product_types']);
  
	$type_column_width = get_option('spe_tool_settings')['product_type_width'];
	if (($type_column_width <= 0) || (!is_numeric($type_column_width))) {
		$type_column_width = 3;
	}

	$default_page = get_option('spe_tool_settings')['default_page'];


	$product_limit = intval(get_option('spe_tool_settings')['products_per_page']);
	if ($product_limit <= 0) {
		$product_limit = 500;
	}

	$starter_page = handle_default_page_selection($default_page, $custom_prod_type_list);

  	?>
	<style>
		h4 {
			margin:0.25rem 0;
		}
		.spe-main-container-div {
			display:inline-block;
			background:#FFF;
			color:#000;
			margin:0.5rem 0;
			padding:0.5rem;
			width:96%;
		}
		.load-status-text {
			font-size:1rem;padding:0.125rem 0.5rem;
		}
		.load-status-text__trash {
			background-color:#FFCCCC;
		}
		.load-status-text__normal {
			background-color:#CCFFCC;
		}
		.desc-text {
			padding:0.125rem 0.5rem;
			background-color:#E0E0E0;
		}
		.l-red, .spe_error {
			background-color:#FFCCCC;
		}
		.l-blue {
			background-color:#CCCCFF;
		}
		.spe-prod-table {
			 font-size:0.9rem;
		}
		.spe-pt__row {
			display:inline-block;
			border-bottom:1px solid black;
		}
		.spe-pt__cell {
			text-align:left;
			padding:0.125rem 0.5rem;
			/* background-color:#FFFFFF; */
			border-bottom:none;
			display:inline-block;
		}
		.spe-pt__cell--id {
			background-color:#F0F0F0;
			min-width:5rem;
		}
		.spe-pt__cell--type {
			min-width:<?=$type_column_width;?>rem;
		}
		.spe-pt__cell--status {
			width:3rem;
		}
		.spe-pt__cell--title {
			width:26rem;
			white-space:nowrap;
			overflow:hidden;
			vertical-align:top;
		}
		.id {
			min-width:7.5rem;
		}
		.sku {
			width:30rem;
		}
	    .attribute {
			width:17rem;
		}
	  	.sku, .attribute {
			white-space:nowrap;
			overflow:hidden;
			vertical-align:top;
		}
		.price{
			min-width:4.25rem;
		}
		.spe-prod-table--info-label .spe-pt__cell.man-stock {
			font-size:0.8rem;
		}
		div.man-stock {
			min-width:6rem;
		}
		.product-cat {
			min-width:11rem;
		}
		.spe-prod-table div.no-border div.man-stock{
			padding:0;
		}
		div.stock{
			min-width:5rem;
		}
		.spe-dropdown--vis {
			width:8rem;
		}
	  	.spe-dropdown--status {
			width:3rem;
		}
		div.attribute {
			min-width:17rem;
		}
		.spe-prod-table div.no-border{
			border-bottom:none;
		}
		div div.center {
			text-align:center;
		}
		.spe-prod-table--info-label {
			background-color:#E0E0E0;
		}
		.spe-prod-table__draft {
			background-color:#BBC0BB;
		}
		.spe-prod-table__private {
			background-color:#D0E0DD;
		}
		.spe-prod-table__trash {
			background-color:#FFCCCC;
		}
		.inactive {
			background-color:#E0E0E0;
	 	}
		.negative {
			color:#FF0000;
		}
	 	.increased {
			background-color:#CCFFCC;
	 	}
		.decreased {
			background-color:#FFCCCC;
	 	}
		.bold {
			font-weight:bold;
		}
		.editing-mode {
			box-shadow:inset 0 0 3px #000;
			background-color:#CCCCFF;
		}
		summary::marker {
			display: none;
		}
		details > summary {
			list-style:none;
		}
		div.edited-product-info-box {
			background-color:#F0F0F0;
			padding:0.125rem 0;
		}
		.spe-prod-info {
			padding: 0.25rem;
		}
		.spe-prod-selection {
			display:inline-block;
		}
		.spe-var-label {
			padding: 0.25rem 0;
		}
		.spe-prod-table div div.man-stock {
			padding:0.125rem 0;
		}
		.dropdiv-content {
			background-color:#CCFFDD;
			overflow:auto;
			z-index:1;
		}
		.spe-prod-table div.dropdiv-content, .spe-dropdown-parent div.dropdiv-content {
			display:none;
			position:absolute;
			margin-left: -.5rem;
			box-shadow: 0.25rem 0.25rem 0.25rem 0 rgba(0, 0, 0, .4);
			border: 1px solid black;
			border-radius: .25rem;
			padding:0.25rem;
		}
		.dropdiv-content.man-stock {
			margin:0.125rem 0;
		}
		.spe-dropdown-parent .dropdiv-content.show {
			display:block;
		}
		.edited {
			background-color:#CCCCFF;
		}
		.dropdiv-content-option, .dropdiv-content-view-only {
			width:100%;
			margin:0;
			display:inline-block;
		}
		.dropdiv-content-option:hover {
			background-color:#C0F0D0;
		}
		.dropdiv-content-view-only, .dropdiv-content-view-only--container {
			background-color:#E8FFF8;
			z-index:0;
		}
		.dropdiv-content--standalone {
			display:none;
			position:absolute;
			box-shadow: 0.25rem 0.25rem 0.25rem 0 rgba(0, 0, 0, .4);
			border: 1px solid black;
			border-radius: .25rem;
			padding:0.25rem;
		}
		.dropdiv-content--standalone.show {
			display:inline-block;
		}
		.menu_order_input::-webkit-outer-spin-button, .menu_order_input::-webkit-inner-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}
		.menu_order_input[type=number] {
			-moz-appearance: text;
		}
	</style>

	<h2 style="margin-bottom:0;">Special Product Edit Tool</h2>
	<div class="spe-main-container-div">
		When complete, this tool will allow for quick editing of products.<br /><br />

		<!-- Product Type Dropdown -->
		<form method="GET">
			<input type="hidden" name="page" value="special-product-page"/>
			<select id="type" name="producttype">
				<option value="variable">variable</option>
				<option value="simple">simple</option>
				<option value="external">external</option>
				<?php
  			if (!empty($custom_prod_type_list) && ($custom_prod_type_list[0] != '')) {
					foreach ($custom_prod_type_list as $custom_product_type) {
						echo '<option value="' . $custom_product_type . '">' . $custom_product_type . '</option>';
					}
				}
  			?>
				<option value="all">all</option>
			</select>
			<input type="submit" value="Submit" />
		</form><br /><br />
	<?php
	// Check if data was submitted POST first, to see if anything needs to be updated in the database.
  	if($_SERVER['REQUEST_METHOD'] == "POST") {
	  	$data = json_decode( html_entity_decode( stripslashes ($_POST['data'])));
	  	if (!empty($data)) {
			echo '<h4>Products Updated:</h4>';
			?>
	  		<div class="edited-product-info-box">
	  		<?php
  			foreach($data as $product_id => $edited_properties) {
				if ($product = wc_get_product($product_id)) {
					$exclude_from_search = -1;
					$exclude_from_catalog = -1;
					foreach($edited_properties as $property_name => $edited_value) {
						switch ($property_name) {
							case 'stock':
								$new_stock = wc_update_product_stock( $product, $edited_value, 'set' );
								if ( is_wp_error( $new_stock ) ) {
									echo '<span class="bold red">ERROR:</span> could not set <span class="bold">' . $product_id . '</span> <span class="bold">' . $property_name . '</span> to <span class="bold">' . $edited_value . '</span><br /><span class="bold">Message:</span> ' . $new_stock->get_error_message() . '<br />';
									break;
								} else {
									$product->set_stock_quantity($edited_value);
									$product->save();
								}
								break;
							case 'sku':
								$product->set_sku($edited_value);
						  		$product->save();
								break;
							case 'menuOrder':
								$product->set_menu_order($edited_value);
								$product->save();
								break;
							case 'manageStock':
								$product->set_manage_stock($edited_value);
						  		$product->save();
								break;
							case 'name':
								$product->set_name($edited_value);
						  		$product->save();
								break;
							case 'stockStatus':
								$product->set_stock_status($edited_value);
								$product->save();
								break;
							case 'status':
								$product->set_status($edited_value);
						  		$product->save();
								break;
							case 'excludeFromSearch':
								$exclude_from_search = $edited_value;
								break;
							case 'excludeFromCatalog':
								$exclude_from_catalog = $edited_value;
								break;
							case 'externalLink':
								$product->set_product_url($edited_value);
						  		$product->save();
								break;
							case 'salePrice':
								$product->set_sale_price($edited_value);
						  		$product->save();
								break;
							case 'regularPrice':
								$product->set_regular_price($edited_value);
						  		$product->save();
								break;
						}
						echo '<span class="bold">' . $product_id . '</span> had <span class="bold">' . $property_name . '</span> set to <span class="bold">' . $edited_value . '</span><br />';
					}
					if (($exclude_from_search != -1) && ($exclude_from_catalog != -1)) {
						// echo 'exc fr sr <br />';
						if ($exclude_from_search == 1) {
							if ($exclude_from_catalog == 1) {
								$product->set_catalog_visibility('hidden');
							} else {
								$product->set_catalog_visibility('catalog');
							}
						} else {
							if ($exclude_from_catalog == 1) {
								$product->set_catalog_visibility('search');
							} else {
								$product->set_catalog_visibility('visible');
							}
						}
					  	// echo 'before save '. $product_id;
						$product->save();
					}
				} else echo '<span class="bold negative">ERROR: PRODUCT #' . $product_id . ' IS INVALID</span><br />';
			}
			echo '</div><br />';
		}
  	unset($value);
  	unset($sub_val);
	}

	if(isset($_GET['producttype']) || isset($starter_page))
	{
		$selected_product_type = ($_GET['producttype'] ? $_GET['producttype'] : $starter_page);

	  // User has selected a product type from the dropdown.
	  if($selected_product_type != "all") {
			$typequerystr = "SELECT term_id FROM `" . $wpdb->prefix . "terms` where name='" . $selected_product_type . "' LIMIT " . $product_limit;

			$product_type_map = $wpdb->get_results($typequerystr);

			$querystr = "SELECT ID,post_title FROM `" . $wpdb->prefix . "posts` where post_type='product' and ID in (SELECT object_id FROM `" . $wpdb->prefix . "term_relationships` where term_taxonomy_id = '" . $product_type_map[0]->term_id . "') LIMIT " . $product_limit . ";";

		} else {
			$querystr = "SELECT ID,post_title FROM `" . $wpdb->prefix . "posts` where post_type='product' LIMIT " . $product_limit;
		};
	  $returned_product_data = $wpdb->get_results($querystr);

	  if (count($returned_product_data) >= 1) {
			?>
			<span class="load-status-text load-status-text__normal">Loaded <?php echo ucfirst($selected_product_type); ?> Product List</span><br /><br >
			<?php
			spe_display_save_button();
			spe_initialize_save_buttons();
			?>
			<div class="spe-pt__cell spe-pt__cell--id">Product ID</div><div class="spe-pt__cell spe-pt__cell--type">Type</div><div class="spe-pt__cell spe-pt__cell--status">Status</div><div class="spe-pt__cell spe-pt__cell--title">post_title</div><?php
			
			if (($selected_product_type != 'external') && ($selected_product_type != 'variable')) {
				echo '<div class="spe-pt__cell center man-stock">Manage Stock?</div><div class="spe-pt__cell center stock">Stock</div>';
			}
			?><br /><?php
			foreach($returned_product_data as $row) {
				$product = wc_get_product($row->ID);
				$prod_status = $product->get_status();
				$prod_vis_status = evaluate_prod_vis_style($prod_status, ' ');
				$type = $product->get_type();
				
				$initial_value_array = array(
					"status" => ($prod_status ?? '')
				);
			  
				echo '<div class="spe-pt__cell spe-pt__cell--id">', spe_product_link($row->ID), '</a></div>';
				echo '<div class="spe-pt__cell spe-pt__cell--type ',$prod_vis_status,'">', $type, '</div>';
				echo '<div class="spe-pt__cell spe-pt__cell--status ',$prod_vis_status,'">';
				spe_display_product_post_status($row->ID, $prod_status, 0, '');
				echo '</div>';
				echo '<div class="spe-pt__cell spe-pt__cell--title ',$prod_vis_status,'">', $row->post_title, '</div>';

				if (($type != 'external') && ($type != 'variable')){
				  	//echo '[TYPE: ' . $type . ']';
					$stock = evaluate_stock($row->ID, $wpdb); // $stock[0] is if stock is managed, $stock[1] is stock quantity, $stock[2] is stock status
				
					$initial_value_array['manageStock'] = ($stock[0] ?? 'no');
					$initial_value_array['stock'] = ($stock[1] ?? 0);
					$initial_value_array['stockStatus'] = ($stock[2] ?? 'outofstock');
				
					$prod_html = '<div id="'. $row->ID . '-managestock-parentdiv" class="spe-pt__cell spe-dropdown-parent center man-stock">'.(($stock[0] == 1) ? 'yes' : 'no');
					$prod_html .= '<div id="'. $row->ID .'-managestock-dropdown" class="dropdiv-content man-stock center">';
					$prod_html .= '<span class="dropdiv-content-option">yes</span><br/><span class="dropdiv-content-option">no</span>';
					$prod_html .= '</div>';
					$prod_html .= '</div>';
					$prod_html .= '<div id="'. $row->ID . '-stock" class="spe-pt__cell stock stock-val '.(($stock[0] == 1) ? 'integer-val' : 'string-val').' center bold'.set_bg($stock).'" contentEditable="true">'.(($stock[0] == 1) ? $stock[1] : $stock[2]).'</div>';
					echo $prod_html;
				}

				echo '<br />';

				if (!empty($initial_value_array)) {
					spe_initial_value_setup_script($type, $initial_value_array, $row->ID);
				}
			}
			generate_product_edit_script();
		} else {
			if($selected_product_type != "all") {
				echo 'Sorry, no products with product_type "' . $selected_product_type . '" were found.';
			} else echo 'Sorry, no products were found.';
		}
	}
	//	##########################
  	//	Individual Product Display
  	//	##########################
  	else if (isset($_GET['product_id'])) {
	  	// User has selected a product.
	  	if (is_numeric($_GET['product_id'])) {
		  	if ($product = wc_get_product($_GET['product_id'])) {
			  	// The product selected is valid.
			  	$type = $product->get_type();
			  	$link = product_link($_GET['product_id']);
				$prod_status = $product->get_status();
				$prependstatus_str = '';
				if ($prod_status == 'trash') {
					$statusstyle = 'load-status-text load-status-text__trash';
					$prependstatus_str = ' Trashed ';
				} else $statusstyle = 'load-status-text load-status-text__normal';
					
			 	$product_name = $product->get_name();
				?>

					<span class="<?= $statusstyle; ?>">Loaded <?php echo $prependstatus_str . ucfirst($type) . ' Product ' . $link; ?></span><br />
					<h3 id="<?php echo $_GET['product_id']; ?>-name" class="name string-val" contentEditable="true" spellcheck="false"><?php echo $product_name; ?></h3>
			  		<?php
					spe_display_save_button();
					spe_initialize_save_buttons();
					?>
	  			<?php

				switch ($type) {
					case 'variable':
						$initial_value_array = array(
							"name" => ($product_name ?? ''),
							"status" => ($prod_status ?? '')
						);

						if (!empty($initial_value_array)) {
							spe_initial_value_setup_script($type, $initial_value_array, $_GET['product_id']);
						}
						
						if ($variations = $product->get_children()) {
						  ?><div class="spe-prod-table"><?php // Overall variation table div

							display_variable_product_table_header();

						  foreach($variations as $variation) {
								$var_prod = wc_get_product($variation);
								$sku = $var_prod->get_sku();
								$reg_price = $var_prod->get_regular_price();
								$sale_price = $var_prod->get_sale_price();

								$stock = evaluate_stock($variation, $wpdb); // $stock[0] is if stock is managed, $stock[1] is stock quantity, $stock[2] is stock status
								$externals = get_linked_externals($variation, $wpdb);

							  ?>
			  				<div class="spe-pt__row <?php echo $externals ? 'no-border': ''; ?>">
								<?php
								$variation_html = '<div id="number-'.$variation.'" class="spe-pt__cell spe-prod-table--info-label id">Variation &#35;'.$variation.'</div>';
								$variation_html .= '<div id="'. $variation . '-sku" class="spe-pt__cell sku string-val" contentEditable="true" spellcheck="false">'.$sku.'</div>';
							 	$variation_html .= '<div id="'. $variation . '-reg-price" class="spe-pt__cell center price float-val" contentEditable="true">'.($reg_price ? $reg_price : 'N/A').'</div>';
								$variation_html .= '<div id="'. $variation . '-sales-price" class="spe-pt__cell center price sales-price float-val" contentEditable="true">'.($sale_price ? $sale_price : '&nbsp;').'</div>';
								$variation_html .= '<div id="'. $variation . '-managestock-parentdiv" class="spe-pt__cell spe-dropdown-parent center man-stock">'.(($stock[0] == 1) ? 'yes' : 'no');
							  	$variation_html .= '<div id="'.$variation.'-managestock-dropdown" class="dropdiv-content man-stock center">';
							  	$variation_html .= '<span class="dropdiv-content-option">yes</span><br/><span class="dropdiv-content-option">no</span>';
							  	$variation_html .= '</div>';
							  	$variation_html .= '</div>';
								$variation_html .= '<div id="'. $variation . '-stock" class="spe-pt__cell stock stock-val '.(($stock[0] == 1) ? 'integer-val' : 'string-val').' center bold'.set_bg($stock).'" contentEditable="true">'.(($stock[0] == 1) ? $stock[1] : $stock[2]).'</div>';
								echo $variation_html;
								?>
							  </div><br />
			  				<?php

							  // If there's a linked external, display it
							  display_external_product_rows($externals, $wpdb);

								$initial_value_array = array(
									"sku" => ($sku ?? ''),
									"manageStock" => ($stock[0] ?? 'no'), // Could also be set to 'yes' as default.  Could add as optional setting for the user.
									"stock" => ($stock[1] ?? 0),
									"stockStatus" => ($stock[2] ?? 'outofstock'),
									"regularPrice" => ($reg_price ?? ''),
									"salePrice" => ($sale_price ?? ''),
								);

								if (!empty($initial_value_array)) {
									spe_initial_value_setup_script($type, $initial_value_array, $variation);
								}
							}
						  	echo '</div>'; // Close overall variation table div
							generate_product_edit_script();
						} else echo 'No variations found.';
						break;
				  	case 'external':
						$prod_id = $product->get_id();
						$menu_order = $product->get_menu_order();
						$prod_status = $product->get_status();

						spe_display_product_image($product);
						$prices = spe_display_product_prices($product, $prod_id);
						spe_display_product_menu_order($prod_id, $menu_order);
						spe_display_product_post_status($prod_id, $prod_status, 1, '');

						$terms = get_product_visibility_terms($wpdb);
		  				$meta = get_product_meta($prod_id, $wpdb, $terms);
					
						$external_meta = get_external_product_meta($prod_id, $wpdb, 1);
						spe_display_external_target_url($prod_id, $external_meta);

						$visvar = evaluate_visibility_vars($meta[2]);

						$initial_value_array = array(
							"excludeFromSearch" => ($visvar[0] ?? 0),
							"excludeFromCatalog" => ($visvar[1] ?? 0),
							"externalLink" => ($external_meta ?? ''),
							"menuOrder" => ($menu_order ?? 0),
							"name" => ($product_name ?? ''),
							"regularPrice" => ($prices[0] ?? ''),
							"salePrice" => ($prices[1] ?? ''),
							"status" => ($prod_status ?? '')
						);

						if (!empty($initial_value_array)) {
							spe_initial_value_setup_script($type, $initial_value_array, $prod_id);
						}

						spe_display_product_visibility($prod_id, $visvar);
						spe_display_product_categories($product, $prod_id);

						generate_product_edit_script();
						break;
				  	default:
						$prod_id = $product->get_id();
						$sku = $product->get_sku();
						$menu_order = $product->get_menu_order();
						$prod_status = $product->get_status();

						spe_display_product_image($product);
						$prices = spe_display_product_prices($product, $prod_id);
						spe_display_product_menu_order($prod_id, $menu_order);
						spe_display_product_post_status($prod_id, $prod_status, 1, '');

						$terms = get_product_visibility_terms($wpdb);
		  				$meta = get_product_meta($prod_id, $wpdb, $terms);
						$visvar = evaluate_visibility_vars($meta[2]);;
						$stock = evaluate_stock($prod_id, $wpdb);
					
						spe_display_product_stock($product, $prod_id, $stock);

						$initial_value_array = array(
							"excludeFromCatalog" => ($visvar[1] ?? 0),
							"excludeFromSearch" => ($visvar[0] ?? 0),
							"manageStock" => ($stock[0] ?? 'no'),
							"menuOrder" => ($menu_order ?? 0),
							"name" => ($product_name ?? ''),
							"regularPrice" => ($prices[0] ?? ''),
							"salePrice" => ($prices[1] ?? ''),
							"sku" => ($sku ?? ''),
							"status" => ($prod_status ?? ''),
							"stock" => ($stock[1] ?? 0),
							"stockStatus" => ($stock[2] ?? 'outofstock'),
						);

						if (!empty($initial_value_array)) {
							spe_initial_value_setup_script($type, $initial_value_array, $prod_id);
						}

						spe_display_product_visibility($prod_id, $visvar);
						spe_display_product_categories($product, $prod_id);

						generate_product_edit_script();
						break;
				}
			} else {
				echo '<span class="load-status-text spe_error">ERROR -- Product #', $_GET['product_id'], ' DOES NOT EXIST</span><br /><br />';
				$post_type = get_post_type($_GET['product_id']);
				if ($post_type) {
					echo '<span class="desc-text">Object #',$_GET['product_id'],' is not a product.  Its type is:',$post_type,'</span>';
				} else echo '<span class="desc-text">Post ID #',$_GET['product_id'],' does not exist in the database.</span>';
			}
		} else echo '<span class="load-status-text spe_error">ERROR -- INVALID PRODUCT ID</span><br /><br /><span class="desc-text">Product ID must be a positive integer.</span>';
	}
  ?>
		<br /><br />
	</div>
	<div class="spe-main-container-div">
    	<form action="options.php" method="post">
        	<?php
        	settings_fields( 'spe_tool_settings' );
        	do_settings_sections( 'spe_tools_script' ); ?>
        	<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    	</form>
	</div>
	<?php
}


// ####################
// Supporting Functions
// ####################


function handle_default_page_selection($default_page, $custom_prod_type_list) {
	if ((!isset($_GET['product_id'])) && (!isset($_GET['producttype'])) && (!empty($default_page))) {
  		if (!empty($custom_prod_type_list) && ($custom_prod_type_list[0] != '')) {
			if (in_array($default_page, $custom_prod_type_list, false)) {
			  	return $default_page;
			} else {
				$default_product_type_list = ['variable', 'simple', 'external'];
				if (in_array($default_page, $default_product_type_list, false)) {
					return $default_page;
				}
			}
		}
	}
}

function spe_display_save_button() {
	?>
	<button type="submit" class="spe-save-button">Save Changes</button><br /><br />
	<?php
}

function spe_initialize_save_buttons() {
	?>
	<script>
		window.initialValues = {productsDisplayed: 0};
		window.modifiedValues = {};

		function post_to_url(path, params, method) {
			method = method || "post";

			var form = document.createElement("form");
			form.setAttribute("method", method);
			form.setAttribute("action", path);

			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", 'data');
			hiddenField.setAttribute("value", JSON.stringify(params));
			form.appendChild(hiddenField);

			console.log('post_to_url', params);

			document.body.appendChild(form);
			form.submit();
		}

	  	saveButtons = document.getElementsByClassName("spe-save-button");
		let itemsEdited = 0;
		let currentURL = window.location.href;
		for (let saveButton of saveButtons) {
			saveButton.addEventListener("click", function() {
			// If a save button is clicked iterate through all editable items and see how many had their values changed
			console.log('SAVE BUTTON CLICKED -- window.modifiedValues:', window.modifiedValues);

			if (Object.keys(window.modifiedValues).length > 0) {
				post_to_url(currentURL, window.modifiedValues, 'post');
			} else console.log('No Edited Products');
			});
		}
	</script>
	<?php
}

function spe_tool_register_settings() {
    register_setting( 'spe_tool_settings', 'spe_tool_settings' /*,  'spe_tool_settings_validate' */);
    add_settings_section( 'spe_tool_config', 'Configuration', 'spe_tool_settings_text', 'spe_tools_script' );
    add_settings_field( 'spe_tool_setting_custom_product_types',
		'Custom Product Types',
		'spe_tool_setting_custom_product_types',
		'spe_tools_script',
		'spe_tool_config',
		array('label_for' => 'spe_tool_setting_custom_product_types', 'description' => '&nbsp;List all custom product types separated by spaces.') );
    add_settings_field( 'spe_tool_setting_custom_product_type_width',
		'Type Column Width',
		'spe_tool_setting_custom_product_type_width',
		'spe_tools_script',
		'spe_tool_config',
		array('label_for' => 'spe_tool_setting_custom_product_type_width', 'description' => '&nbsp;Width in rem (root-element font size) of the "type" column.  Behaves as 3 if non-numeric or negative.') );
    add_settings_field( 'spe_tool_setting_products_per_page',
		'Products Per Page',
		'spe_tool_setting_products_per_page',
		'spe_tools_script',
		'spe_tool_config',
		array('label_for' => 'spe_tool_setting_products_per_page', 'description' => '&nbsp;It is recommended to keep this at 500 or less.  If this is empty, given an invalid value, or less than 1 it will behave as though the limit is 500.') );
    add_settings_field( 'spe_tool_setting_default_page',
		'Default Page',
		'spe_tool_setting_default_page',
		'spe_tools_script',
		'spe_tool_config',
		array('label_for' => 'spe_tool_setting_default_page', 'description' => '&nbsp;Default page shown on start.  Leave blank to start at an empty page.') );
}
add_action( 'admin_init', 'spe_tool_register_settings' );


function spe_tool_settings_text() {
    echo '<span>These settings are optional.</span><br />';
}

function spe_tool_setting_custom_product_types($args) {
    $options = get_option( 'spe_tool_settings' );
  	echo "<input id='spe_tool_setting_custom_product_types' name='spe_tool_settings[product_types]' type='text' value='" . esc_attr( $options['product_types'] ) . "' />";
  	echo '<span class="wndspan">' . esc_html( $args['description'] ) .'</span>';
}
function spe_tool_setting_custom_product_type_width($args) {
    $options = get_option( 'spe_tool_settings' );
  	echo "<input id='spe_tool_setting_custom_product_type_width' name='spe_tool_settings[product_type_width]' type='text' value='" . esc_attr( $options['product_type_width'] ) . "' />";
  	echo '<span class="wndspan">' . esc_html( $args['description'] ) .'</span>';
}
function spe_tool_setting_products_per_page($args) {
    $options = get_option( 'spe_tool_settings' );
  	echo "<input id='spe_tool_setting_products_per_page' name='spe_tool_settings[products_per_page]' type='text' value='" . esc_attr( $options['products_per_page'] ) . "' />";
  	echo '<span class="wndspan">' . esc_html( $args['description'] ) .'</span>';
}
function spe_tool_setting_default_page($args) {
    $options = get_option( 'spe_tool_settings' );
  	echo "<input id='spe_tool_setting_default_page' name='spe_tool_settings[default_page]' type='text' value='" . esc_attr( $options['default_page'] ) . "' />";
  	echo '<span class="wndspan">' . esc_html( $args['description'] ) .'</span>';
}


function product_link($prod_id) {
  return '<a href="'. admin_url( 'post.php?post=' . absint( $prod_id ) . '&action=edit' ) .'" >#' . $prod_id . '</a>';
}
function spe_product_link($prod_id) {
  return '<a href="?page=special-product-page&product_id=' . absint( $prod_id ) . '" >' . $prod_id . '</a>';
}

function evaluate_stock($var, $db) {
  	// This function retrieves all 3 stock values for a product
	$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $var . " and meta_key = '_manage_stock' LIMIT 1";
	$man_stock = $db->get_results($querystr);
  	$ret = array(3);

  	if (($man_stock[0]->meta_value) == 'yes') {
	  	$ret[0] = 1;
	} else {
	  	$ret[0] = 0;
	}

	$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $var . " and meta_key = '_stock' LIMIT 1";
  	$result = $db->get_results($querystr);
	$ret[1] = intval($result[0]->meta_value, 10);

	$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $var . " and meta_key = '_stock_status' LIMIT 1";
	$result = $db->get_results($querystr);
	$ret[2] = $result[0]->meta_value;

  	return $ret;
}
function set_bg($stock) {
  	if ($stock[0] == 1) {
	  	if ($stock[1] <= 0) {
			if (($stock[1] <0)){
			  return ' inactive negative ';
			}
			else return ' inactive ';
		}
	}
  	else if ($stock[2] == 'outofstock') {
	  	return ' inactive ';
	}
  	else if ($stock[2] == 'onbackorder') {
	  	return ' inactive negative ';
	}
  	else return (' ');
}

function generate_product_edit_script() {
	?>
	<script>
		console.log('window.initialValues', window.initialValues);

		function setNumberBGColor(objRef, newVal, origVal) {
			// console.log('setNumberBGColor', newVal, origVal);
			if (newVal < origVal) {
				if(!objRef.classList.contains('decreased')) objRef.classList.add('decreased');
				if(objRef.classList.contains('increased')) objRef.classList.remove('increased');
			} else if (newVal > origVal) {
				if(objRef.classList.contains('decreased')) objRef.classList.remove('decreased');
				if(!objRef.classList.contains('increased')) objRef.classList.add('increased');
			}
			else if (newVal == origVal) {
				if(objRef.classList.contains('decreased')) objRef.classList.remove('decreased');
				if(objRef.classList.contains('increased')) objRef.classList.remove('increased');
			}
		}
		function setStockFontColor(objRef, newStock) {
			negativeClass = objRef.className.includes('negative');
			if ((newStock >= 0) || (newStock == 'instock') || (newStock == 'outofstock')) {
				if (negativeClass) {
					objRef.classList.toggle('negative');
				}
			} else {
				if (!negativeClass) {
					objRef.classList.toggle('negative');
				}
			}
		}
		function setEditedClass(objRef, edited) {
			if (objRef.className.includes('edited') && !edited) {
				objRef.classList.toggle('edited');
			} else if (!objRef.className.includes('edited') && edited) {
				objRef.classList.toggle('edited');
			}
		}
		function checkProdModified(prodId) {
			if (window.modifiedValues[prodId] === undefined) {
				return false;
			} else return true;
		}
		function ensureModifiedProductDefined(prodId) {
			if (window.modifiedValues[prodId] === undefined) {
				window.modifiedValues[prodId] = {};
			}
		}
		function removeModifiedProductValue(prodId, key) {
			if (checkProdModified(prodId)) {
				if (window.modifiedValues[prodId] != undefined) {
					delete window.modifiedValues[prodId][key];
					if (Object.keys(window.modifiedValues[prodId]).length === 0) {
						delete window.modifiedValues[prodId];
					}
				}
			}
		}
		function evaluateModifiedValue(prodId, key, newValue) {
			// Check if product has been modified yet
			if (window.modifiedValues[prodId] === undefined) {
				window.modifiedValues[prodId] = {};
			}

			// Mark the value as modified
			window.modifiedValues[prodId][key] = newValue;
		}

		// This may get removed
		window.addEventListener('focusin',function(e){
			if(e.target) {
				if ((e.target.className.includes('float-val')) || (e.target.className.includes('integer-val')) || (e.target.className.includes('string-val'))) {
					if(!e.target.classList.contains('editing-mode')) e.target.classList.add('editing-mode');
				}
			}
		});

		// Disallow pasting of formatting into product data fields
		window.addEventListener('paste', function(e) {
			if(e.target) {
				if (!e.target.id.includes('spe_tool_setting')){
					e.preventDefault();
					var text = e.clipboardData.getData("text/plain");
					document.execCommand("insertHTML", false, text);
				}
			}
		});

		// This is the main section for detecting user modification to product data fields
		window.addEventListener('focusout',function(e){
			if(e.target) {
				if ((e.target.className.includes('float-val')) || (e.target.className.includes('integer-val')) || (e.target.className.includes('string-val'))) {
					if(e.target.classList.contains('editing-mode')) e.target.classList.remove('editing-mode');

					prodId = e.target.id.substring(0, e.target.id.indexOf("-"));

					if (e.target.className.includes('string-val')) {
						newValue = e.target.textContent;
					} else newValue = Number(e.target.textContent);

					dataKey = '';
					// console.log(e.target.className);
					if (e.target.className.includes('stock-val')) {
						dataKey = 'stock';
					} else if (e.target.className.includes('sku')) {
						dataKey = 'sku';
					} else if (e.target.className.includes('external-link')) {
						dataKey = 'externalLink';
					} else if (e.target.className.includes('menu-order')) {
						dataKey = 'menuOrder';
					} else if (e.target.className.includes('name')) {
						dataKey = 'name';
					} else if (e.target.className.includes('sales-price')) {
						dataKey = 'salePrice';
					} else if (e.target.className.includes('price')) dataKey = 'regularPrice';

					if (e.target.className.includes('integer-val')) {
						if (!(newValue === parseInt(newValue, 10))){
							console.log('Invalid Entry -- Data will not be saved.  Entry must be an integer.  Product #', prodId);
							removeModifiedProductValue(prodId, 'stock');
							return;
						}
					}
					if (e.target.className.includes('float-val')) {
						if (!(newValue === parseFloat(newValue, 10))){
							console.log('Invalid Entry -- Data will not be saved.  Entry must be a float.  Product #', prodId);
							removeModifiedProductValue(prodId, 'stock');
							return;
						}
					}

					switch (dataKey) {
						case 'stock':
							// console.log('dataKey stock ' , prodId , ' ' , window.modifiedValues[prodId]);
							if (window.modifiedValues[prodId] == undefined) {
								// console.log('window.modifiedValues[prodId] = undefined');
								// console.log('window.initialValues[prodId].manageStock = ', window.initialValues[prodId].manageStock);
								manageStock = window.initialValues[prodId].manageStock;
								if (manageStock == 0) {
									dataKey = 'stockStatus';
								}
							} else if (window.modifiedValues[prodId].manageStock == undefined) {
								manageStock = window.initialValues[prodId].manageStock;
								if (manageStock == 0) {
									dataKey = 'stockStatus';
								}
							} else if (window.modifiedValues[prodId].manageStock == 0) {
								dataKey = 'stockStatus';
							}
							
							if (dataKey == 'stockStatus') {
								origStock = window.initialValues[prodId].stockStatus;
								
								if (origStock == newValue) {
									setNumberBGColor(e.target, 1, 1);
								
									// Set background to inactive if stock field unedited and originally was outofstock
									if (newValue == 'outofstock') {
										if(!e.target.classList.contains('inactive')) e.target.classList.add('inactive');
									} else if(e.target.classList.contains('inactive')) e.target.classList.remove('inactive');
								  
									removeModifiedProductValue(prodId, dataKey);
								} else if ((newValue == 'instock') && (origStock == 'outofstock')){
									setNumberBGColor(e.target, 1, 0);
									evaluateModifiedValue(prodId, dataKey, newValue);
								} else {
									setNumberBGColor(e.target, 0, 1);
									evaluateModifiedValue(prodId, dataKey, newValue);
								}
								
							} else {
								origStock = window.initialValues[prodId].stock;

								// Style the stock field
								setNumberBGColor(e.target, newValue, origStock);
							  
								if (origStock == newValue) {
									if (newValue <= 0) {
										if(!e.target.classList.contains('inactive')) e.target.classList.add('inactive');
									} else if(e.target.classList.contains('inactive')) e.target.classList.remove('inactive');
									removeModifiedProductValue(prodId, dataKey);
								} else {
									evaluateModifiedValue(prodId, dataKey, newValue);
								}
							}
						
							// Style the stock font
							setStockFontColor(e.target, newValue);
						
							console.log('dataKey', dataKey, 'origStock', origStock, 'newValue', newValue);
							break;

						case 'salePrice':
							origPrice = window.initialValues[prodId].salePrice;

							if (origPrice == newValue) {
								setNumberBGColor(e.target, newValue, origPrice);
/*								if (newValue <= 0) {
									if(!e.target.classList.contains('inactive')) e.target.classList.add('inactive');
								} else if(e.target.classList.contains('inactive')) e.target.classList.remove('inactive'); */
								removeModifiedProductValue(prodId, dataKey);
							} else {
								// Style the stock field
								setNumberBGColor(e.target, newValue, origPrice);

								evaluateModifiedValue(prodId, dataKey, newValue);
							}
							break;

						case 'regularPrice':
							origPrice = window.initialValues[prodId].regularPrice;

							if (origPrice == newValue) {
								setNumberBGColor(e.target, newValue, origPrice);
/*								if (newValue <= 0) {
									if(!e.target.classList.contains('inactive')) e.target.classList.add('inactive');
								} else if(e.target.classList.contains('inactive')) e.target.classList.remove('inactive'); */
								removeModifiedProductValue(prodId, dataKey);
							} else {
								// Style the stock field
								setNumberBGColor(e.target, newValue, origPrice);

								evaluateModifiedValue(prodId, dataKey, newValue);
							}
							break;
						default:
							origVal = window.initialValues[prodId][dataKey];
							// console.log(dataKey, prodId, origVal, newValue);
							if (origVal == newValue) {
								setEditedClass(e.target, false);
								removeModifiedProductValue(prodId, dataKey);
							} else {
								setEditedClass(e.target, true);
								evaluateModifiedValue(prodId, dataKey, newValue);
							}
							break;
					}
				}
			}
		});
		document.addEventListener('click',function(e){
			if(e.target) {
				if (e.target.className.includes('dropdiv-content-view-only')) {
					e.target.parentNode.classList.remove("show");
					e.target.parentNode.parentNode.innerHTML = e.target.parentNode.parentNode.innerHTML.replace('▲', '▼');
				} else if (e.target.className.includes('spe-dropdown-parent')) {
					e.target.firstElementChild.classList.toggle("show");
					if (e.target.firstElementChild.classList.contains("show")) {
						e.target.innerHTML = e.target.innerHTML.replace('▼', '▲');
					} else e.target.innerHTML = e.target.innerHTML.replace('▲', '▼');
				} else if (e.target.className.includes('dropdiv-content-option')) {
					selectedValue = e.target.innerHTML;
					prodId = e.target.parentNode.id.substring(0, e.target.parentNode.id.indexOf("-"));

					if (e.target.parentNode.className.includes('spe-dropdown--status')) {
						if (window.initialValues[prodId].status == selectedValue) {
							removeModifiedProductValue(prodId, 'status');
							e.target.parentNode.parentNode.childNodes[0].nodeValue = selectedValue;
							setEditedClass(e.target.parentNode.parentNode, false);
						} else {
							// Check if product has been modified yet
							ensureModifiedProductDefined(prodId);
						  
							// Mark post status as modified
							window.modifiedValues[prodId].status = selectedValue;
							e.target.parentNode.parentNode.childNodes[0].nodeValue = selectedValue;
							setEditedClass(e.target.parentNode.parentNode, true);
						}
					} else if (e.target.parentNode.className.includes('stock-status')) {
						if (window.initialValues[prodId].status == selectedValue) {
							e.target.parentNode.parentNode.childNodes[0].nodeValue = selectedValue;
							setEditedClass(e.target.parentNode.parentNode, false);
						} else {
							// Check if product has been modified yet
							ensureModifiedProductDefined(prodId);
						  
							// Mark post status as modified
							window.modifiedValues[prodId].stockStatus = selectedValue;
							e.target.parentNode.parentNode.childNodes[0].nodeValue = selectedValue;
							setEditedClass(e.target.parentNode.parentNode, true);
						}
					} else if (e.target.parentNode.className.includes('man-stock')) {
						selectedValueInt = (selectedValue == 'yes') ? 1: 0;
						if (window.initialValues[prodId].manageStock == selectedValueInt) {
							removeModifiedProductValue(prodId, 'manageStock');
							e.target.parentNode.parentNode.childNodes[0].nodeValue = (window.initialValues[prodId].manageStock ? 'yes' : 'no');
							setEditedClass(e.target.parentNode.parentNode, false);
						} else {
							// Check if product has been modified yet
							ensureModifiedProductDefined(prodId);

							// Mark manageStock as modified
							window.modifiedValues[prodId].manageStock = selectedValueInt;
							e.target.parentNode.parentNode.childNodes[0].nodeValue = selectedValue;
							setEditedClass(e.target.parentNode.parentNode, true);

							//
							if (selectedValueInt == 0) {
								console.log('selectedValueInt = 0');
								if (window.initialValues[prodId].stock <= 0) {
									window.modifiedValues[prodId].stockStatus = 'outofstock';
								}
							}
						}
					} else if (e.target.parentNode.className.includes('spe-dropdown--vis')) {
						switch(selectedValue) {
							case 'Shop and Search':
								excludeFromSearch = 0;
								excludeFromCatalog = 0;
								break;
							case 'Shop Only':
								excludeFromSearch = 1;
								excludeFromCatalog = 0;
								break;
							case 'Search Only':
								excludeFromSearch = 0;
								excludeFromCatalog = 1;
								break;
							case 'Hidden':
								excludeFromSearch = 1;
								excludeFromCatalog = 1;
								break;
						}
						console.log(window.initialValues[prodId].excludeFromSearch, window.initialValues[prodId].excludeFromCatalog, excludeFromSearch, excludeFromCatalog);
						if ((window.initialValues[prodId].excludeFromSearch == excludeFromSearch) && (window.initialValues[prodId].excludeFromCatalog == excludeFromCatalog)) {
							// Unmodified
							removeModifiedProductValue(prodId, 'excludeFromSearch');
							removeModifiedProductValue(prodId, 'excludeFromCatalog');
							setEditedClass(e.target.parentNode.parentNode, false);
						} else {
							// Because of how visibility is calculated by woocommerce we need to send both or neither
							ensureModifiedProductDefined(prodId);
							window.modifiedValues[prodId].excludeFromSearch = excludeFromSearch;
							window.modifiedValues[prodId].excludeFromCatalog = excludeFromCatalog;
							setEditedClass(e.target.parentNode.parentNode, true);
						}
						e.target.parentNode.parentNode.childNodes[0].nodeValue = selectedValue;
					}
					e.target.parentNode.classList.toggle("show");
				}
			}
		});
	</script>
	<?php
}

function get_linked_externals($var, $db) {
  	// A product variation may have externals linked to it.  We can try to find those with a few techniques
	$querystr = "SELECT post_id FROM `" . $db->prefix . "postmeta` WHERE meta_value = " . $var . " and meta_key = 'linked_variation_id'";
	$res = $db->get_results($querystr);
  	if ($res) {
	  	return $res;
	}
  	else {
		$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $var . " and meta_key = 'attribute_pa_color'";
		$color = $db->get_results($querystr)[0]->meta_value;
	  	if ($color) {
			$color_str = '?attribute_pa_color=' . $color;
			$querystr = "SELECT post_id FROM `" . $db->prefix . "postmeta` WHERE meta_value LIKE '%" . $color_str . "%' and meta_key = '_product_url'";
			$res = $db->get_results($querystr);
			if ($res) {
				return $res;
			}
		}
	}
  	return false;
}
function evaluate_visibility_vars($visstr) {
	$result = array(3);
	switch ($visstr) {
		case 0:	// WooCommerce makes this a little confusing by using a negative word (exclude) instead of an affirmative word (include)
			$result[0] = 0; // Exclude from Search? 1 = Exclude
			$result[1] = 0; // Exclude from Catalog? 1 = Exclude
			$result[2] = 'Shop and Search';
			break;
		case 1:
			$result[0] = 1;
			$result[1] = 0;
			$result[2] = 'Shop Only';
			break;
		case 2:
			$result[0] = 0;
			$result[1] = 1;
			$result[2] = 'Search Only';
			break;
		case 3:
			$result[0] = 1;
			$result[1] = 1;
			$result[2] = 'Hidden';
			break;
	}
	return $result;
}
function evaluate_prod_vis_style($external_status, $vis_str) {
	/**
	*	Returns $result which is a string containing either a blank space or a class name to be aplied to a div element
	*/
	$result = ' ';

	switch ($external_status) {
		case 'trash':
			$result = 'spe-prod-table__trash';
			break;
		case 'private':
			$result = 'spe-prod-table__private';
			break;
		case 'draft':
			$result = 'spe-prod-table__draft';
			break;
		default:
			$result = ' ';
			break;
	}
  
	if (($result == ' ') && ($vis_str == 'Hidden')) {
		$result = ' inactive ';
	}
	return $result;
}
function display_variable_product_table_header() {
	 ?><div class="spe-pt__row spe-prod-table--info-label"><div class="spe-pt__cell id">ID</div><div class="spe-pt__cell sku">SKU</div><div class="spe-pt__cell center price">Reg Price</div><div class="spe-pt__cell center price">Sale Price</div><div class="spe-pt__cell center man-stock">Manage Stock?</div><div class="spe-pt__cell center stock">Stock</div></div><br /><?php
	return;
}
function display_external_product_rows($res, $db) {
	if (!$res) {
		return;
	}

	// There may be more than 1 result, so we have to loop through them all
	$i = 0;
 	$terms = get_product_visibility_terms($db);

	foreach($res as $row) {
		$ext_prod_id = $row->post_id;
		if ($ext_prod_id) {
			$meta = get_product_meta($ext_prod_id, $db, $terms, true);
			$external_meta = get_external_product_meta($ext_prod_id, $db, 0);
			$external_product = wc_get_product($ext_prod_id);

			if (!$external_product = wc_get_product( $ext_prod_id )) {
				echo '<span class="spe_error desc-text">DEBUG: External ID #', $ext_prod_id, ' is invalid.  Skipping.</span><br />';
				continue 1;
			}

			$external_status = $external_product->get_status();
			$cat_ids = $external_product->get_category_ids();
			$visstyle = ' ';

			$visvar = evaluate_visibility_vars($meta[2]);
			$visstyle = evaluate_prod_vis_style($external_status, $visvar[2]);

			?>
		  	<div class="spe-pt__row<?php echo ($i == (count($res) - 1)) ? '' : ' no-border'; ?>">
			<?php
		 		$external_html = '<div class="spe-pt__cell spe-prod-table--info-label ' . $external_status . ' id">External ' . spe_product_link($ext_prod_id) . '</div>';

		  		$external_html .= '<div id="'.$ext_prod_id.'-visibility" class="spe-pt__cell spe-dropdown-parent '.$visstyle.' spe-dropdown--vis edit">'.$visvar[2];
		  		$external_html .= '<div id="'.$ext_prod_id.'-visibility-dropdown" class="dropdiv-content spe-dropdown--vis center">';
				$external_html .= '<span class="dropdiv-content-option">Shop and Search</span><br /><span class="dropdiv-content-option">Shop Only</span><br/><span class="dropdiv-content-option">Search Only</span><br/><span class="dropdiv-content-option">Hidden</span>';
				$external_html .= '</div>';
		  		$external_html .= '</div>';
				//echo $external_html;
		
				//$external_html .= spe_display_product_post_status($ext_prod_id, $prod_status, 2, $visstyle);

				$external_html .= '<div id="'.$ext_prod_id.'-post-status" class="spe-pt__cell spe-dropdown-parent '.$visstyle.' spe-dropdown--status">'.$external_status;
		  		$external_html .= '<div id="'.$ext_prod_id.'-post-status-dropdown" class="dropdiv-content spe-dropdown--status center">';
					$external_html .= '<span class="dropdiv-content-option">publish</span><br /><span class="dropdiv-content-option">draft</span><br/><span class="dropdiv-content-option">private</span><br/><span class="dropdiv-content-option">trash</span>';
					$external_html .= '</div>';
		  		$external_html .= '</div>';

				$external_html .= '<div class="spe-pt__cell '.$visstyle.' attribute">'.$external_meta.'</div>';
		 		$external_html .= '<div id="'. $ext_prod_id . '-reg-price" class="spe-pt__cell center '.$visstyle.' price float-val" contentEditable="true">'.($meta[0] ? $meta[0] : 'N/A').'</div>';
				$external_html .= '<div id="'. $ext_prod_id . '-sales-price" class="spe-pt__cell center '.$visstyle.' price sales-price float-val" contentEditable="true">'.($meta[1] ? $meta[1] : '&nbsp;').'</div>';
				$external_html .= '<div id="' . $ext_prod_id . '-cat-drop-button" class="spe-pt__cell spe-dropdown-parent '.$visstyle.' product-cat edit">';
		  	if (!empty($cat_ids)) {
						if (count($cat_ids) > 1) {
							$external_html .= 'Categories &#9660;';
						} else $external_html .= 'Category &#9660;';
						$external_html .= '<div id="' . $ext_prod_id . '-cat-dropdown" class="dropdiv-content dropdiv-content-view-only--container product-cat center">';
						foreach ($cat_ids as $cat_id) {
								$term = get_term_by( 'id', $cat_id, 'product_cat' );
								$external_html .= '<span class="dropdiv-content-view-only">' . $term->name . '</span><br />';
						}
						$external_html .= '</div>';
				} else $external_html .= 'uncategorized';
		  	$external_html .= '</div>';
		  	echo $external_html;
			?>
			</div><br />
			<script>
				// Set up inititial value info for this external product
				window.initialValues.productsDisplayed += 1;
	  			externalId = <?php echo $ext_prod_id; ?>;
	  			excludeFromSearch = <?php echo $visvar[0]; ?>;
				excludeFromCatalog = <?php echo $visvar[1]; ?>;
				regularPrice = <?php echo $meta[0]; ?>;
				salePrice = <?php echo ($meta[1] ? $meta[1] : "''"); ?>;
				status = "<?php echo $external_status; ?>";
				

	  			window.initialValues[externalId] = {
	  				'excludeFromSearch': excludeFromSearch,
	  				'excludeFromCatalog': excludeFromCatalog,
					'regularPrice' : regularPrice,
					'salePrice' : salePrice,
					'status' : status
				};
			</script>
			<?php
		}
	  	$i += 1;
	}
}
function set_bg_visibility($visibility) {
  if ($stock[0] == 1) {
	  	if ($stock[1] <= 0) {
			if (($stock[1] <0)){
			  return ' inactive negative ';
			} else return ' inactive ';
		}
	} else if ($stock[1] == 'outofstock') {
	  	return ' inactive ';
	} else if ($stock[1] == 'onbackorder') {
		return ' inactive negative ';
	} else return (' ');
}

function spe_initial_value_setup_script($type, $initial_value_array, $prod_id) {
	if (empty($initial_value_array)) {
		return;
	}

	?>
	<script>
		window.initialValues.productsDisplayed += 1;
		prodId = <?php echo $prod_id; ?>;
	<?php

	foreach($initial_value_array as $k => $v) {
		if (is_numeric($v)) {
			echo $k , ' = ' , $v , ';';
		} else if ($v == ''){
		  echo $k , " = undefined;";
		} else echo $k , " = '" , $v , "';";
	}
	echo 'window.initialValues[prodId] = {';
	foreach($initial_value_array as $k => $v) {
		if (is_numeric($v)) {
			echo $k , " : " , $v , ",";
		} else echo "'" , $k , "' : '" , $v , "',";
	}
	echo '};';
	echo '</script>';
}

function spe_display_product_post_status($prod_id, $status, $display_mode, $visstyle) {
	
	if ($display_mode == 1) {echo '<div class="spe-var-label">Product Status:';}
	?>	<div id="<?= $prod_id; ?>-post-status" class="<?php if ($display_mode == 2) echo 'spe-pt__cell ' . $visstyle . ' '; ?>spe-dropdown-parent spe-dropdown--status edit"><?= $status; ?>
			<div id="<?= $prod_id; ?>-post-status-dropdown" class="dropdiv-content spe-dropdown--status">
				<span class="dropdiv-content-option">publish</span><br/>
				<span class="dropdiv-content-option">draft</span><br/>
				<span class="dropdiv-content-option">private</span><br/>
				<span class="dropdiv-content-option">trash</span><br/>  
			</div>
	 	</div>
	<?php
	if ($display_title) { echo '</div>';}
}
function spe_display_product_image($product) {
	$image_id  = $product->get_image_id();
	$image_url = wp_get_attachment_image_url( $image_id, 'full' );
	?>
	<a href="<?php echo $image_url; ?>" target="_blank"><img src="<?php echo $image_url; ?>" width="200"/></a><br />
	<?php
}
function spe_display_product_prices($product, $prod_id) {
	$result = array(2);
	$result[0] = $product->get_regular_price();
	$result[1] = $product->get_sale_price();

	echo '<div class="spe-var-label price">Regular Price: <span id="'. $prod_id . '-reg-price" class="spe-prod-info price float-val" contentEditable="true">',($result[0] ? $result[0] : '&nbsp;&nbsp;'),'</span></div>';
	echo '<div class="spe-var-label price">Sale Price: <span id="'. $prod_id . '-sales-price" class="spe-prod-info price sales-price float-val" contentEditable="true">',($result[1] ? $result[1] : '&nbsp;&nbsp;') ,'</span></div>';

	return $result;
}
function spe_display_product_menu_order($prod_id, $menu_order) {
	?>
	<div class="spe-var-label">Menu Order:
		<span id="<?= $prod_id ?>-menu-order" class="spe-prod-info spe-menu-order integer-val bold" contentEditable="true"><?= $menu_order ?></span>
	</div>
	<?php
}
function spe_display_product_stock($product, $prod_id, $stock) {
	?>
	<div class="spe-var-label">Manage Stock:
		<div id="<?= $prod_id; ?>-managestock-parentdiv" class="spe-prod-info spe-dropdown-parent man-stock spe-prod-selection"><?= ($stock[0] ? 'yes' : 'no'); ?>
			<div id="<?= $prod_id; ?>-managestock-dropdown" class="dropdiv-content man-stock">
				<span class="dropdiv-content-option">yes</span><br/><span class="dropdiv-content-option">no</span>
			</div>
		</div>
	</div>
	<div class="spe-var-label">Stock: 
		<?php
		if ($stock[0] == 1) {
			?>
			<span id="<?= $prod_id; ?>-stock" class="spe-prod-info stock stock-val integer-val bold<?= set_bg($stock); ?>" contentEditable="true">
				<?= ($product->get_stock_quantity() ? $product->get_stock_quantity() : '0'); ?>
			</span>
			<?php
		} else {
			?>
			<div id="<?= $prod_id; ?>-stock-status-parentdiv" class="spe-prod-info spe-dropdown-parent stock-status spe-prod-selection bold<?= set_bg($stock); ?>"><?= $product->get_stock_status(); ?>
	  			<div id="<?= $prod_id; ?>-stock-status-dropdown" class="dropdiv-content stock-status">
					<span class="dropdiv-content-option">instock</span><br/><span class="dropdiv-content-option">outofstock</span>
				</div>
	  		</div>
	  		<?php
		}
  		?>
	</div>
	<?php
}
function spe_display_product_visibility($product, $visvar) {
	?>
		<div class="spe-var-label">Visibility:
			<div id="<?= $product; ?>-visibility" class="spe-dropdown-parent vis edit"><?= $visvar[2]; ?>
				<div id="<?= $product; ?>-visibility-dropdown" class="dropdiv-content spe-dropdown--vis">
					<span class="dropdiv-content-option">Shop and Search</span><br/>
					<span class="dropdiv-content-option">Shop Only</span><br/>
					<span class="dropdiv-content-option">Search Only</span><br/>
					<span class="dropdiv-content-option">Hidden</span>
				</div>
	 		</div>
	<?php
}
function spe_display_product_categories($product, $prod_id) {
  	$cat_ids = $product->get_category_ids();
	if (!empty($cat_ids)) {
		if (count($cat_ids) > 1) {
		  	?>
	  		<div id="<?= $prod_id; ?>-cat-drop-button" class="spe-dropdown-parent product-cat">Categories &#9660;
				<div id="<?= $prod_id; ?>-cat-dropdown" class="dropdiv-content dropdiv-content-view-only--container dropdiv-content--standalone product-cat center"><?php
				foreach ($cat_ids as $cat_id) {
					$term = get_term_by( 'id', $cat_id, 'product_cat' );
					echo '<span class="dropdiv-content-view-only">' . $term->name . '</span><br />';
				}
				?></div>
	  		</div><?php
		} else {
		  	$term = get_term_by( 'id', $cat_ids[0], 'product_cat' );
			?><div class="product-cat edit">Category: <?= $term->name; ?></div><?php
		}	
		
	} else echo '<div class="product-cat edit">Uncategorized</div>';
}
function spe_display_external_target_url($prod_id, $url) {
	echo '<div class="spe-var-label external-url">External Link: <span id="'. $prod_id . '-external-link" class="spe-prod-info external-link string-val" contentEditable="true">',$url,'</span></div>';
}
function get_product_visibility_terms($db) {
  // We want to determine external product visibility.  WC does this by determining if the external product is hidden from search and if it is hidden from the shop view
  // We need to find out what the term taxonomy ids are for the flags
  $result = array(2);

  $querystr = "SELECT term_id FROM `" . $db->prefix . "terms` WHERE name='exclude-from-search' LIMIT 1";
	$termsearch = $db->get_results($querystr);
  $result[0] = $termsearch[0]->term_id;

  $querystr = "SELECT term_id FROM `" . $db->prefix . "terms` WHERE name='exclude-from-catalog' LIMIT 1";
	$termcat = $db->get_results($querystr);
  $result[1] = $termcat[0]->term_id;

  return $result;
}
function get_product_meta($prod_id, $db, $terms) {
	/**
	*	Returns $result which is an array of commonly used meta values to be used by certain product display functions
	*	$result = array(
	*		_regular_price,
	*		_sale_price,
	*		visibility,			//	Integer from bitwise operation on exclude-from-search and exclude-from-catalog.  0 = Shop and Search, 1 = Shop Only, 2 = Search Only, 3 = Hidden
	*	);
	*/

  if (!is_numeric($prod_id)) {
		echo '<script>console.error("SPE function get_product_meta() was not passed a product id for its first parameter.");</script>';
		return false;
	}

	$result = array(3);

  // Regular Price
  $querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = '_regular_price' LIMIT 1";
	$result[0] = $db->get_results($querystr)[0]->meta_value;

  // EXAMPLE OF RAW
	// echo '<br /><pre>' . var_dump($db->get_results($querystr)) . '</pre><br />';

	// Sale Price
  $querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = '_sale_price' LIMIT 1";
	$resbuffer = $db->get_results($querystr);
	if (!empty($resbuffer)) {
		$result[1] = $resbuffer[0]->meta_value;
	} else $result[1] = '';

  // Visibility
  $querystr = "SELECT EXISTS(SELECT 1 FROM `" . $db->prefix . "term_relationships` WHERE object_id = " . $prod_id . " and term_taxonomy_id = " . $terms[0] . " LIMIT 1)";
  $searchexclude = intval($db->get_var($querystr));

  $querystr = "SELECT EXISTS(SELECT 1 FROM `" . $db->prefix . "term_relationships` WHERE object_id = " . $prod_id . " and term_taxonomy_id = " . $terms[1] . " LIMIT 1)";
  $catexclude = intval($db->get_var($querystr));
	$catexclude *= 2;

	$result[2] = $searchexclude + $catexclude;

  return $result;
}
function get_external_product_meta($prod_id, $db, $return_full_color_url) {
  // attribute_pa_color in variable product view, or the whole url in external product view
  if ($return_full_color_url == 0) {
  		$querystr = "SELECT substring_index(meta_value,'attribute_pa_color=',-1) FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = '_product_url' LIMIT 1";
	} else $querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = '_product_url' LIMIT 1";

	return strval($db->get_var($querystr));
}
