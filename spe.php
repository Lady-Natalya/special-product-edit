// Special Product Edit
// Do things like applying discounts across an entire product line
// Add or remove some arbitrary number of sales unit across a product line

// ################
// Add Link to Menu
// ################

// Last save 2025 March 06

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

  	$style_settings_arr = array(
		"typeColumnWidth" => ($type_column_width ?? 3) 
	);
	spe_load_stylesheet($style_settings_arr);
	?>

	<h2 style="margin-bottom:0;">Special Product Edit Tool</h2>
	<div class="spe-main-container">
		When complete, this tool will allow for quick editing of products.<br /><br />
		Choose type of products to display:<br />

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
		</form>
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
						$save_prod = true;
						switch ($property_name) {
							case 'stock':
								$new_stock = wc_update_product_stock( $product, $edited_value, 'set' );
								if ( is_wp_error( $new_stock ) ) {
									echo '<span class="bold red">ERROR:</span> could not set <span class="bold">' . $product_id . '</span> <span class="bold">' . $property_name . '</span> to <span class="bold">' . $edited_value . '</span><br /><span class="bold">Message:</span> ' . $new_stock->get_error_message() . '<br />';
									$save_prod = false;
									break;
								} else {
									$product->set_stock_quantity($edited_value);
								}
								break;
							case 'sku': $product->set_sku($edited_value); break;
							case 'menuOrder': $product->set_menu_order($edited_value); break;
							case 'manageStock': $product->set_manage_stock($edited_value); break;
							case 'name': $product->set_name($edited_value); break;
							case 'stockStatus': $product->set_stock_status($edited_value); break;
							case 'status': $product->set_status($edited_value); break;
							case 'excludeFromSearch': $exclude_from_search = $edited_value; $save_prod = false; break;
							case 'excludeFromCatalog': $exclude_from_catalog = $edited_value; $save_prod = false; break;
							case 'externalLink': $product->set_product_url($edited_value); break;
							case 'height': $product->set_height($edited_value); break;
							case 'imageId': set_post_thumbnail($product_id, $edited_value); break;
							case 'length': $product->set_length($edited_value); break;
							case 'linkedVariationId': $product->update_meta_data('linked_variation_id', $edited_value); $product->save_meta_data(); break;
							case 'productTags': wp_set_object_terms($product_id, explode(",",$edited_value), 'product_tag');
							case 'slug': $product->set_slug($edited_value); break;
							case 'salePrice': $product->set_sale_price($edited_value); break;
							case 'regularPrice': $product->set_regular_price($edited_value); break;
							case 'superProductId': $product->update_meta_data('super_product_id', $edited_value); $product->save_meta_data(); break;
							case 'weight': $product->set_weight($edited_value); break;
							case 'width': $product->set_width($edited_value); break;
							/*case 'discountType': $product->update_meta_data('discount_type_string', $edited_value); $product->save_meta_data(); break;*/
						}
						if ($save_prod) {
							$product->save();
						}
						echo '<span class="bold">' . $product_id . '</span> had <span class="bold">' . $property_name . '</span> set to <span class="bold">' . $edited_value . '</span><br />';
					}
					if (($exclude_from_search != -1) && ($exclude_from_catalog != -1)) {
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
  	// unset($value);	// 2023 Sept 10 - Candidate for Deletion
  	// unset($sub_val); // 2023 Sept 10 - Candidate for Deletion
	}
?>
	</div><br />
	<div class="spe-main-container">
<?php
	//	#####################################
  	//	Display all Products of Selected Type
  	//	#####################################
	if(isset($_GET['producttype']) || isset($starter_page))
	{
		$selected_product_type = (array_key_exists('producttype', $_GET) ? $_GET['producttype'] : $starter_page);

		// Pagination -- Terrible but works
		if (isset($_GET['pagenumber'])) {
			// After isset to prevent PHP error
			if (((is_int($_GET['pagenumber'])) || ctype_digit($_GET['pagenumber'])) && ((int)$_GET['pagenumber'] > 1)) {
				$offset = ((int)$_GET['pagenumber'] * $product_limit) - $product_limit;
				$page_num = (int)$_GET['pagenumber'];
				$pagination_str = " LIMIT ". $offset . ", " . ($product_limit + 1);
			} else {
				$pagination_str = " LIMIT ". ($product_limit + 1);
				$page_num = 1;
			}
		} else {
			$pagination_str = " LIMIT ". ($product_limit + 1);
			$page_num = 1;
		}
	  
		// User has selected a product type from the dropdown.
		if($selected_product_type != "all") {

			$typequerystr = "SELECT term_id FROM `" . $wpdb->prefix . "terms` where name='" . $selected_product_type . "';";

			$product_type_map = $wpdb->get_results($typequerystr);

			$querystr = "SELECT ID,post_title FROM `" . $wpdb->prefix . "posts` where post_type='product' and ID in (SELECT object_id FROM `" . $wpdb->prefix . "term_relationships` where term_taxonomy_id = '" . $product_type_map[0]->term_id . "') ".$pagination_str. ";";
			$countquerystr = "SELECT COUNT(*) FROM `" . $wpdb->prefix . "posts` where post_type='product' and ID in (SELECT object_id FROM `" . $wpdb->prefix . "term_relationships` where term_taxonomy_id = '" . $product_type_map[0]->term_id . "')";

		} else {
			$querystr = "SELECT ID,post_title FROM `" . $wpdb->prefix . "posts` where post_type='product' " .$pagination_str. ";";
			$countquerystr = "SELECT COUNT(*) FROM `" . $wpdb->prefix . "posts` where post_type='product'";
		};
		
		$returned_product_data = $wpdb->get_results($querystr);
		$returned_product_count = $wpdb->get_results($countquerystr)[0]->{"COUNT(*)"};

		$prod_count = count($returned_product_data);
	  if ($prod_count >= 1) {
			?>
			<span class="load-status-text load-status-text__normal">Loaded <?php echo ucfirst($selected_product_type); ?> Product List</span><br /><br >
			<?php
			spe_display_save_button();
			spe_initialize_window_values();
			?>
			<div class="spe-pt__cell spe-pt__cell--id">Product ID</div><div class="spe-pt__cell spe-pt__cell--type">Type</div><div class="spe-pt__cell spe-pt__cell--status">Status</div><div class="spe-pt__cell spe-pt__cell--title">post_title</div><?php
			
			if (($selected_product_type != 'external') && ($selected_product_type != 'variable')) {
				echo '<div class="spe-pt__cell center man-stock">Manage Stock?</div><div class="spe-pt__cell center stock">Stock</div>';
			}
			?><br /><?php
			$count = 0;
			$more_prods = false;
			foreach($returned_product_data as $row) {
				$count += 1;
				if ($count > $product_limit) {
					$more_prods = true;
					break;
				}
				$product = wc_get_product($row->ID);
				$prod_status = $product->get_status();
				$prod_vis_status = evaluate_prod_vis_style($prod_status, ' ');
				$type = $product->get_type();
				
				$initial_value_array = array(
					"status" => ($prod_status ?? '')
				);
			  
				echo '<div class="spe-pt__cell spe-pt__cell--id">', spe_product_link($row->ID, $row->ID), '</a></div>';
				echo '<div class="spe-pt__cell spe-pt__cell--type ',$prod_vis_status,'">', $type, '</div>';
				echo '<div class="spe-pt__cell spe-pt__cell--status ',$prod_vis_status,'">';
				spe_display_product_post_status($row->ID, $prod_status, 0, '');
				echo '</div>';
				echo '<div class="spe-pt__cell spe-pt__cell--title ',$prod_vis_status,'">', spe_product_link($row->ID, $row->post_title), '</div>';

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
				} else {
					$prod_html = '<div class="spe-pt__cell center man-stock"></div>';
					$prod_html .= '<div class="spe-pt__cell stock stock-val center bold"></div>';
				}
				echo $prod_html;

				echo '<br />';

				if (!empty($initial_value_array)) {
					spe_initial_value_setup_script($type, $initial_value_array, $row->ID);
				}
			}
			generate_product_edit_script();
			if (($more_prods) || ($page_num >= 2)) {
				echo '<br />
					<span>Page:</span><br />
					';
				
				$max_page = (intdiv((int)$returned_product_count, $product_limit) + (($returned_product_count % $product_limit) ? 1 : 0));
				if ($max_page > 8) {
					$mid_page = (intdiv($max_page, 2));
				}
				
				if ($page_num == 1) {
					spe_display_page_num(1, true);
					if ($max_page == 2) {
						spe_display_page_num(2, false);
					} else if ($max_page < 8) {
						spe_display_page_num(2, false);
						spe_display_page_num($max_page, false);
					} else if ($max_page > 8) {
						spe_display_page_num(2, false);
						spe_display_page_num($mid_page, false);
						spe_display_page_num($max_page, false);						
					}
				} else if ($page_num == 2) {
					spe_display_page_num(1, false);
					if ($max_page == 2) {
						spe_display_page_num(2, true);
					} else if ($max_page < 8) {
						spe_display_page_num(2, true);
						spe_display_page_num(3, false);
						spe_display_page_num($max_page, false);
					} else if ($max_page > 8) {
						spe_display_page_num(2, true);
						spe_display_page_num(3, false);
						spe_display_page_num($mid_page, false);
						spe_display_page_num($max_page, false);						
					}
				}
				
				
				if ($page_num > 2) {
					spe_display_page_num(1, false);
					
					if ($max_page <= 8) {
						spe_display_page_num(($page_num - 1), false);
						spe_display_page_num($page_num, true);
						if (($page_num + 1) < $max_page) {
							spe_display_page_num(($page_num + 1), false);
							spe_display_page_num($max_page, false);
						} else if (($page_num + 1) == $max_page) {
							spe_display_page_num($max_page, false);
						}
					} else if ($max_page > 8) {
						if (($page_num + 1) < $mid_page) {
							spe_display_page_num(($page_num - 1), false);
							spe_display_page_num($page_num, true);
							spe_display_page_num(($page_num + 1), false);
							spe_display_page_num(($mid_page), false);
							spe_display_page_num($max_page, false);
						} else if (($page_num + 1) == $mid_page) {
							spe_display_page_num(($page_num - 1), false);
							spe_display_page_num($page_num, true);
							spe_display_page_num($mid_page, false);
							spe_display_page_num($max_page, false);
						} else if ($page_num == $mid_page) {
							spe_display_page_num(($page_num - 1), false);
							spe_display_page_num($page_num, true);
							spe_display_page_num(($page_num + 1), false);
							spe_display_page_num($max_page, false);
						} else if (($page_num -1) == $mid_page) {
							spe_display_page_num(($page_num - 1), false);
							spe_display_page_num($page_num, true);
							spe_display_page_num(($page_num + 1), false);
							spe_display_page_num($max_page, false);
						} else if (($page_num -1) > $mid_page) {
							spe_display_page_num(($mid_page), false);
							spe_display_page_num(($page_num - 1), false);
							spe_display_page_num($page_num, true);
							if (($page_num + 1) < $max_page) {
								spe_display_page_num(($page_num + 1), false);
								spe_display_page_num($max_page, false);
							} else if (($page_num + 1) == $max_page) {
								spe_display_page_num($max_page, false);
							}
						}
					} else {
						if ($more_prods) spe_display_page_num(($page_num + 1), false);
						if (($page_num + 1) < $max_page) spe_display_page_num($max_page, false);					
					}
					
				}	
			}
			?><br /><?php
			spe_display_save_button();
			spe_initialize_save_buttons();
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
	  	if ((is_numeric($_GET['product_id'])) && ($_GET['product_id'] > 0)) {
		  	if ($product = wc_get_product($_GET['product_id'])) {
			  	// The product selected is valid.
			  	$prod_id = $_GET['product_id'];
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
			  		spe_initialize_window_values();
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

						//$querystr = "SELECT ID FROM `" . $wpdb->prefix . "posts` where post_parent=".$product->get_id(). ";"; // Old Version (Before August 2023)
						$querystr = "SELECT ID FROM `" . $wpdb->prefix . "posts` where post_parent=".$product->get_id(). " AND post_type='product_variation';"; // Without post_type='product_variation' weird results can be returned
						$results = $wpdb->get_results($querystr);
						$variations = [];
						foreach($results as $res) {
						  array_push($variations, (int)$res->ID);
						}

						if (!empty($variations)) {
						  ?><div class="spe-prod-table"><?php // Overall variation table div

							display_variable_product_table_header();

							foreach($variations as $variation) {
								$editable = "true";

								if ($var_prod = wc_get_product($variation)) {
									$sku = $var_prod->get_sku();
									$reg_price = $var_prod->get_regular_price();
									$sale_price = $var_prod->get_sale_price();
									$var_status = $var_prod->get_status();
									$visstyle = evaluate_prod_vis_style($var_status, ' ');
									$stock = evaluate_stock($variation, $wpdb); // $stock[0] is if stock is managed, $stock[1] is stock quantity, $stock[2] is stock status
									$externals = get_linked_externals($variation, $wpdb);
								} else {
									$sku = "";
									$reg_price = "";
								  	$sale_price = "";
									$visstyle = 'spe-prod-table__trash';
									$var_status = 'trash';
									$stock = array("no"," ", "N/A");
									$editable = "false";
								}
								
							  ?>
			  				<div class="spe-pt__row <?php echo $externals ? 'no-border': ''; ?>">
								<?php
								$variation_html = '<div id="'.$variation.'-post-status" class="spe-pt__cell spe-dropdown-parent spe-prod-table--info-label id '.$visstyle.' spe-dropdown--status">Variation &#35;'.$variation;
								$variation_html .= '<div id="'.$variation.'-post-status-dropdown" class="dropdiv-content spe-dropdown--status center">';
								$variation_html .= '<span class="dropdiv-content-option">publish</span><br /><span class="dropdiv-content-option">draft</span><br/><span class="dropdiv-content-option">private</span><br/><span class="dropdiv-content-option">trash</span>';
								$variation_html .= '</div>';
		  						$variation_html .= '</div>';
								$variation_html .= '<div id="'. $variation . '-sku" class="spe-pt__cell sku string-val" contentEditable="'.$editable.'" spellcheck="false">'.$sku.'</div>';
							 	$variation_html .= '<div id="'. $variation . '-reg-price" class="spe-pt__cell center price float-val" contentEditable="'.$editable.'">'.($reg_price ? $reg_price : 'N/A').'</div>';
								$variation_html .= '<div id="'. $variation . '-sales-price" class="spe-pt__cell center price sales-price float-val" contentEditable="'.$editable.'">'.($sale_price ? $sale_price : '&nbsp;').'</div>';
								$variation_html .= '<div id="'. $variation . '-managestock-parentdiv" class="spe-pt__cell spe-dropdown-parent center man-stock">'.(($stock[0] == 1) ? 'yes' : 'no');
							  	if ($var_prod) {
									$variation_html .= '<div id="'.$variation.'-managestock-dropdown" class="dropdiv-content man-stock center">';
							  		$variation_html .= '<span class="dropdiv-content-option">yes</span><br/><span class="dropdiv-content-option">no</span>';
							  		$variation_html .= '</div>';
								}
								$variation_html .= '</div>';
								$variation_html .= '<div id="'. $variation . '-stock" class="spe-pt__cell stock stock-val '.(($stock[0] == 1) ? 'integer-val' : 'string-val').' center bold'.set_bg($stock). '" contentEditable="'.$editable.'">'.(($stock[0] == 1) ? $stock[1] : $stock[2]).'</div>';
								echo $variation_html;
								?>
							  </div><br />
			  				<?php

							  // If there's a linked external, display it
							  display_external_product_rows($externals, $wpdb, $variation);

								$initial_value_array = array(
									"sku" => ($sku ?? ''),
									"manageStock" => ($stock[0] ?? 'no'), // Could also be set to 'yes' as default.  Could add as optional setting for the user.
									"stock" => ($stock[1] ?? 0),
									"stockStatus" => ($stock[2] ?? 'outofstock'),
									"regularPrice" => ($reg_price ?? ''),
									"salePrice" => ($sale_price ?? ''),
									"status" => ($var_status ?? '')
								);

								if (!empty($initial_value_array)) {
									spe_initial_value_setup_script($type, $initial_value_array, $variation);
								}
							}
						  	echo '</div>'; // Close overall variation table div
							generate_product_edit_script();
						} else {
							echo '<br />variations empty';
						}
						break;
				  	case 'external':
						$prod_info = spe_get_basic_product_info($product, $prod_id, $wpdb);
						$terms = get_product_visibility_terms($wpdb);
						$meta = get_product_meta($prod_id, $wpdb, $terms, true);
						$slug = $product->get_slug();
						$prod_tags_str = spe_get_product_tags($prod_id);
					
						spe_display_product_image($prod_info['imageId']);
						
						$prod_info_rows = Array(
							Array('idSuffix' => '-image-id', 'name' => "Image ID", 'classes' => "image-id integer-val", 'val'=> spe_get_product_image_id($product)),
							Array('idSuffix' => '-menu-order', 'name' => "Menu Order", 'classes' => "spe-menu-order integer-val", 'val'=> $prod_info['menuOrder'])
						);
						$product_url = get_product_url_or_color($prod_id, $wpdb, 1);
						array_push($prod_info_rows, Array('idSuffix' => '-external-link', 'name' => "External Link", 'classes' => "external-link string-val", 'val' => $product_url));
						array_push($prod_info_rows, Array('idSuffix' => '-super-product-id', 'name' => "Super Product ID", 'classes' => "integer-val", 'val'=> $meta[3]));
						array_push($prod_info_rows, Array('idSuffix' => '-linked-variation-id', 'name' => "Linked Variation ID", 'classes' => "integer-val", 'val'=> $meta[4]));
						array_push($prod_info_rows, Array('idSuffix' => '-slug', 'name' => "Slug", 'classes' => "string-val", 'val'=> $slug));
						array_push($prod_info_rows, Array('idSuffix' => '-tags', 'name' => "Product Tags", 'classes' => "string-val", 'val'=> $prod_tags_str));
						spe_display_single_product_info_table($prod_id, $prod_info_rows);
					
						spe_display_basic_product_info($prod_id, $prod_info);
						$visvar = evaluate_visibility_vars($meta[2]);
					

						$initial_value_array = array(
							"discountType" => ($prod_info['discountType'] ?? ''),
							"excludeFromSearch" => ($visvar[0] ?? 0),
							"excludeFromCatalog" => ($visvar[1] ?? 0),
							"externalLink" => ($product_url ?? ''),
							"imageId" => ($prod_info['imageId'] ?? ''),
							"linkedVariationId" => ($meta[4] ?? ''),
							"menuOrder" => ($prod_info['menuOrder'] ?? 0),
							"name" => ($product_name ?? ''),
							"productTags" => ($prod_tags_str ?? ''),
							"slug" => ($slug ?? ''),
							"regularPrice" => ($prod_info['regularPrice'] ?? ''),
							"salePrice" => ($prod_info['salePrice'] ?? ''),
							"status" => ($prod_status ?? ''),
							"superProductId" => ($meta[3] ?? '')
						);

						spe_finish_product_setup($product, $prod_id, $visvar, $initial_value_array, $type);
						break;
				  	default:
						$prod_info = spe_get_basic_product_info($product, $prod_id, $wpdb);
						spe_display_product_image($prod_info['imageId']);
						
						$prod_info_rows = Array(
							Array('idSuffix' => '-image-id', 'name' => "Image ID", 'classes' => "image-id integer-val", 'val'=> spe_get_product_image_id($product)),
							Array('idSuffix' => '-menu-order', 'name' => "Menu Order", 'classes' => "spe-menu-order integer-val", 'val'=> $prod_info['menuOrder'])
						);
						spe_display_single_product_info_table($prod_id, $prod_info_rows);
					
						spe_display_basic_product_info($prod_id, $prod_info);
					
						$terms = get_product_visibility_terms($wpdb);
						$meta = get_product_meta($prod_id, $wpdb, $terms);
						$visvar = evaluate_visibility_vars($meta[2]);
						
						$stock = evaluate_stock($prod_id, $wpdb);
						spe_display_product_stock($product, $prod_id, $stock);
					
						// Product weight and dimensions not needed for external or variable products.
						$weight = empty($product->get_weight()) ? 0 : $product->get_weight();
						$length = empty($product->get_length()) ? 0 : $product->get_length();
						$width = empty($product->get_width()) ? 0 : $product->get_width();
						$height = empty($product->get_height()) ? 0 : $product->get_height();
						spe_display_product_weight($prod_id, $weight);
						spe_display_product_dimensions($prod_id, $length, $width, $height);

						$initial_value_array = array(
							"discountType" => ($prod_info['discountType'] ?? ''),
							"excludeFromCatalog" => ($visvar[1] ?? 0),
							"excludeFromSearch" => ($visvar[0] ?? 0),
							"height" => ($height ?? 0),
							"imageId" => ($prod_info['imageId'] ?? ''),
							"length" => ($length ?? 0),
							"manageStock" => ($stock[0] ?? 'no'),
							"menuOrder" => ($prod_info['menuOrder'] ?? 0),
							"name" => ($product_name ?? ''),
							"regularPrice" => ($prod_info['regularPrice'] ?? ''),
							"salePrice" => ($prod_info['salePrice'] ?? ''),
							"sku" => (($product->get_sku()) ?? ''),
							"status" => ($prod_status ?? ''),
							"stock" => ($stock[1] ?? 0),
							"stockStatus" => ($stock[2] ?? 'outofstock'),
							"weight" => ($weight ?? 0),
							"width" => ($width ?? 0)
						);

						spe_finish_product_setup($product, $prod_id, $visvar, $initial_value_array, $type);
						break;
				}
				?><br /><?php
				spe_display_save_button();
				spe_initialize_save_buttons();
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
	</div>
	<div class="spe-settings-container">
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
function spe_initialize_window_values() {
	?>
		<script>
		window.initialValues = {productsDisplayed: 0};
		window.modifiedValues = {};
		</script>
	<?php
}
function spe_initialize_save_buttons() {
	?>
	<script>
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
			if (Object.keys(window.modifiedValues).length > 0) {
				console.log('Save Button Clicked -- window.modifiedValues:', window.modifiedValues);
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
		array('label_for' => 'spe_tool_setting_products_per_page', 'description' => '&nbsp;Recommendation:  Keep at 500 or less.  Must be a positive integer.  If empty or given an invalid value it will default to 500.') );
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
  return '<a href="'. admin_url( 'post.php?post=' . absint( $prod_id ) . '&action=edit' ) .'" >#' . strval($prod_id) . '</a>';
}
function spe_product_link($prod_id, $inner_html, $leading_hash = false) {
  return '<a href="?page=special-product-page&product_id=' . absint( $prod_id ) . '" >' . ($leading_hash ? '#' : '') . $inner_html . '</a>';
}
function search_orders_url($search_term) {
	return admin_url( 'edit.php?s=' . strval( $search_term )) . '&post_status=all&post_type=shop_order&action=-1&m=0&_customer_user&paged=1&action2=-1';
	//https://mosaicartsupply.com/wp-admin/edit.php?s=cyan-blue-jy16063&post_status=all&post_type=shop_order&action=-1&m=0&_customer_user&paged=1&action2=-1
}

function evaluate_stock($var, $db) {
  	// This function retrieves all 3 stock values for a product
	$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $var . " and meta_key = '_manage_stock' LIMIT 1";
	$man_stock = $db->get_results($querystr);
  	$ret = array(3);

  	if (array_key_exists(0, $man_stock)) {
		if (($man_stock[0]->meta_value) == 'yes') {
	  		$ret[0] = 1;
		} else {
	  		$ret[0] = 0;
		}
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

					if (e.target.className.includes('stock-val')) {
						dataKey = 'stock';
					} else if (e.target.className.includes('sku')) {
						dataKey = 'sku';
					} else if (e.target.className.includes('discount-type')) {
						dataKey = 'discountType';
					} else if (e.target.className.includes('external-link')) {
						dataKey = 'externalLink';
					} else if (e.target.className.includes('image-id')) {
						dataKey = 'imageId';
					} else if (e.target.id.includes('linked-variation-id')) {
						dataKey = 'linkedVariationId';
					} else if (e.target.className.includes('menu-order')) {
						dataKey = 'menuOrder';
					} else if (e.target.className.includes('name')) {
						dataKey = 'name';
					} else if (e.target.id.includes('tags')) {
						dataKey = 'productTags';
					} else if (e.target.className.includes('sales-price')) {
						dataKey = 'salePrice';
					} else if (e.target.id.includes('-slug')) {
						dataKey = 'slug';
					} else if (e.target.id.includes('super-product-id')) {
						dataKey = 'superProductId';
					} else if (e.target.className.includes('price')) {
						dataKey = 'regularPrice';
					} else dataKey = e.target.id.split('-')[1];

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
							if (window.modifiedValues[prodId] == undefined) {

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
							setNumberBGColor(e.target, newValue, origPrice);
							if (origPrice == newValue) {
								removeModifiedProductValue(prodId, dataKey);
							} else {
								evaluateModifiedValue(prodId, dataKey, newValue);
							}
							break;

						case 'regularPrice':
							origPrice = window.initialValues[prodId].regularPrice;
							setNumberBGColor(e.target, newValue, origPrice);
							if (origPrice == newValue) {
								removeModifiedProductValue(prodId, dataKey);
							} else {
								evaluateModifiedValue(prodId, dataKey, newValue);
							}
							break;
						default:
							origVal = window.initialValues[prodId][dataKey];
							console.log('orig', origVal, 'new', newValue, 'key', dataKey);
							if (origVal == newValue) {
								//console.log('orig = new');
								setEditedClass(e.target, false);
								removeModifiedProductValue(prodId, dataKey);
							} else {
								//console.log('orig != new');
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
				  	if (!e.target.className.includes('selectable')) {
						e.target.parentNode.classList.remove("show");
						e.target.parentNode.parentNode.innerHTML = e.target.parentNode.parentNode.innerHTML.replace('▲', '▼');
					}
				} else if ((e.target.className.includes('dd-c-vo__copy-button')) && (!e.target.id.includes('-search-color'))) {
							data = e.target.value;
				  			console.log(data);
							var tempInput = document.createElement("input");
							tempInput.value = data;
							document.body.appendChild(tempInput);
							tempInput.select();
							document.execCommand("copy");
							document.body.removeChild(tempInput);
							e.target.parentNode.classList.remove("show");
				} else if (e.target.className.includes('dd-c-vo__close-button')) {
					e.target.parentNode.classList.remove("show");
				} else if (e.target.className.includes('spe-dropdown-parent')) {
					if (e.target.firstElementChild) {	// May not exist if displaying a trashed product variation
						e.target.firstElementChild.classList.toggle("show");
						if (e.target.firstElementChild.classList.contains("show")) {
							e.target.innerHTML = e.target.innerHTML.replace('▼', '▲');
						} else e.target.innerHTML = e.target.innerHTML.replace('▲', '▼');
					}
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
				foreach ($res as $key => $linked_var) {
				  	//echo $linked_var->post_id, " ";
					$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $linked_var->post_id . " and meta_key = 'linked_variation_id' LIMIT 1";
					$linked_var_id = $db->get_results($querystr);
					//echo " ", $linked_var_id[0]->meta_value;
					if ($linked_var_id[0]->meta_value) {
						if ($linked_var_id[0]->meta_value != $var) {
							unset($res[$key]);	//	There already was a linked_variation_id present for product with id $linked_var_id[0]->meta_value and it was not the same as $var
						}
					}
				}
				return $res;
			}
		}
	}
  	return false;
}
function evaluate_visibility_vars($visstr) {
	switch ($visstr) {
		// WooCommerce makes this a little confusing by using a negative word (exclude) instead of an affirmative word (include)
		// $result[0] is Exclude from Search, $result[1] is Exclude from Catalog, and $result[2] is the string version
		case 0: $result = array(0,0,'Shop and Search'); break;
		case 1: $result = array(1,0,'Shop Only'); break;
		case 2: $result = array(0,1,'Search Only'); break;
		case 3: $result = array(1,1,'Hidden'); break;
	}
	return $result;
}
function evaluate_prod_vis_style($prod_status, $vis_str) {
	/**
	*	Returns $result which is a string containing either a blank space or a class name to be aplied to a div element
	*/
	$result = ' ';

	switch ($prod_status) {
		case 'trash': $result = 'spe-prod-table__trash'; break;
		case 'private': $result = 'spe-prod-table__private'; break;
		case 'draft': $result = 'spe-prod-table__draft'; break;
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
function display_external_product_rows($res, $db, $variation_id) {
	if (!$res) {
		return;
	}
	// There may be more than 1 result, so we have to loop through them all
	$i = 0;
 	$terms = get_product_visibility_terms($db);

	foreach($res as $row) {
		$prod_id = $row->post_id;
		if ($prod_id) {
			if (!($product = wc_get_product($prod_id))) {
				continue;
			}
		 	$type = $product->get_type();

			$meta = get_product_meta($prod_id, $db, $terms, true);
			$product_color_from_url = get_product_url_or_color($prod_id, $db, 0);
			$product_url = get_product_url_or_color($prod_id, $db, 1);
			$product_img_url = wp_get_attachment_image_url($product->get_image_id(), 'full');
			$product_additional_images_url = get_variation_additional_images_formatted_url_list($variation_id, $db);
			
			if (!$product = wc_get_product( $prod_id )) {
				echo '<span class="spe_error desc-text">DEBUG: External ID #', $prod_id, ' is invalid.  Skipping.</span><br />';
				continue 1;
			}

			$prod_status = $product->get_status();
			$cat_ids = $product->get_category_ids();
			$visstyle = ' ';

			$visvar = evaluate_visibility_vars($meta[2]);
			$visstyle = evaluate_prod_vis_style($prod_status, $visvar[2]);

			?>
		  	<div class="spe-pt__row<?php echo ($i == (count($res) - 1)) ? '' : ' no-border'; ?>">
			<?php
		 		$linked_prod_html = '<div class="spe-pt__cell spe-prod-table--info-label ' . $prod_status . ' id">'. ucfirst($type) .' ' . spe_product_link($prod_id, $prod_id, true) . '</div>';

		  		$linked_prod_html .= '<div id="'.$prod_id.'-visibility" class="spe-pt__cell spe-dropdown-parent '.$visstyle.' spe-dropdown--vis edit">'.$visvar[2];
		  		$linked_prod_html .= '<div id="'.$prod_id.'-visibility-dropdown" class="dropdiv-content spe-dropdown--vis center">';
				$linked_prod_html .= '<span class="dropdiv-content-option">Shop and Search</span><br /><span class="dropdiv-content-option">Shop Only</span><br/><span class="dropdiv-content-option">Search Only</span><br/><span class="dropdiv-content-option">Hidden</span>';
				$linked_prod_html .= '</div>';
		  		$linked_prod_html .= '</div>';

				$linked_prod_html .= '<div id="'.$prod_id.'-post-status" class="spe-pt__cell spe-dropdown-parent '.$visstyle.' spe-dropdown--status">'.$prod_status;
		  		$linked_prod_html .= '<div id="'.$prod_id.'-post-status-dropdown" class="dropdiv-content spe-dropdown--status center">';
					$linked_prod_html .= '<span class="dropdiv-content-option">publish</span><br /><span class="dropdiv-content-option">draft</span><br/><span class="dropdiv-content-option">private</span><br/><span class="dropdiv-content-option">trash</span>';
					$linked_prod_html .= '</div>';
		  		$linked_prod_html .= '</div>';

				$linked_prod_html .= '<div class="spe-pt__cell '.$visstyle.' spe-dropdown-parent attribute">'.$product_color_from_url;
				$linked_prod_html .= '<div id="' . $prod_id . '-cat-dropdown" class="dropdiv-content dropdiv-content-view-only--container product-cat center selectable">';
				$linked_prod_html .= '<button id="'.$prod_id.'-copy-color" class="dd-c-vo__copy-button" value="' . $product_color_from_url . '">COPY</button><div class="dropdiv-content-view-only selectable dd-c-vo--copyable"><a href="' . search_orders_url($product_color_from_url) . '" target="_blank"><button id="'.$prod_id.'-search-color" class="dd-c-vo__copy-button">SEARCH IN ORDERS</button></a>'. $product_color_from_url .'</div><button class="dd-c-vo__close-button">&times;</button><br style="clear:both;" />';
				$linked_prod_html .= '<button id="'.$prod_id.'-copy-url" class="dd-c-vo__copy-button" value="' . $product_url . '">COPY</button><div class="dropdiv-content-view-only selectable dd-c-vo--copyable">'. $product_url .'</div><br style="clear:both;" />';
		  		$linked_prod_html .= '<button id="'.$prod_id.'-copy-img" class="dd-c-vo__copy-button" value="' . $product_img_url . '">COPY</button><div class="dropdiv-content-view-only selectable dd-c-vo--copyable">'. $product_img_url .'</div><br style="clear:both;" />';
		  		$linked_prod_html .= '<button id="'.$prod_id.'-copy-additional-imgs" class="dd-c-vo__copy-button'. ($product_additional_images_url ? '' : ' dd-c-vo__copy-button--disabled') .'" value="' . $product_additional_images_url . '">COPY</button><div class="dropdiv-content-view-only selectable dd-c-vo--copyable">'. ($product_additional_images_url ? $product_additional_images_url : 'none found') .'</div>';
				$linked_prod_html .= '</div>';
		  		$linked_prod_html .= '</div>';
		  
		  		$linked_prod_html .= '<div id="' . $prod_id . '-reg-price" class="spe-pt__cell center '.$visstyle.' price float-val" contentEditable="true">'.($meta[0] ? $meta[0] : 'N/A').'</div>';
				$linked_prod_html .= '<div id="' . $prod_id . '-sales-price" class="spe-pt__cell center '.$visstyle.' price sales-price float-val" contentEditable="true">'.($meta[1] ? $meta[1] : '&nbsp;').'</div>';
				$linked_prod_html .= '<div id="' . $prod_id . '-cat-drop-button" class="spe-pt__cell spe-dropdown-parent '.$visstyle.' product-cat edit">';
		  	if (!empty($cat_ids)) {
						if (count($cat_ids) > 1) {
							$linked_prod_html .= 'Categories &#9660;';
						} else $linked_prod_html .= 'Category &#9660;';
						$linked_prod_html .= '<div id="' . $prod_id . '-cat-dropdown" class="dropdiv-content dropdiv-content-view-only--container product-cat center">';
						foreach ($cat_ids as $cat_id) {
								$term = get_term_by( 'id', $cat_id, 'product_cat' );
								$linked_prod_html .= '<span class="dropdiv-content-view-only">' . $term->name . '</span><br />';
						}
						$linked_prod_html .= '</div>';
				} else $linked_prod_html .= 'uncategorized';
		  	$linked_prod_html .= '</div>';
		  	echo $linked_prod_html;
			?>
			</div><br />
			<script>
				// Set up inititial value info for this external product
				window.initialValues.productsDisplayed += 1;
	  			externalId = <?php echo $prod_id; ?>;
				status = "<?php echo $prod_status; ?>";
				
	  			window.initialValues[externalId] = {
	  				'excludeFromSearch': <?php echo $visvar[0]; ?>,
	  				'excludeFromCatalog': <?php echo $visvar[1]; ?>,
					'regularPrice' : <?php echo $meta[0]; ?>,
					'salePrice' : <?php echo ($meta[1] ? $meta[1] : "''"); ?>,
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
	
	if ($display_mode == 1) {echo '<div class="spe-var-label">Product Status:</div>
	';}
	?>	<div id="<?= $prod_id; ?>-post-status" class="<?php if ($display_mode == 2) echo 'spe-pt__cell ' . $visstyle . ' '; ?>spe-dropdown-parent spe-dropdown--status edit"><?= $status; ?>
			<div id="<?= $prod_id; ?>-post-status-dropdown" class="dropdiv-content spe-dropdown--status">
				<span class="dropdiv-content-option">publish</span><br/>
				<span class="dropdiv-content-option">draft</span><br/>
				<span class="dropdiv-content-option">private</span><br/>
				<span class="dropdiv-content-option">trash</span><br/>  
			</div>
	 	</div>
	<?php
	// if ($display_title) { echo '</div>';}
}
function spe_get_basic_product_info($product, $prod_id, $db) {
	$prices = spe_get_product_prices($product, $prod_id, $db);
	return array(
		'discountType' => $prices[2],
		'imageId' => spe_get_product_image_id($product),
		'menuOrder' => $product->get_menu_order(),
		'regularPrice' => $prices[0],
		'salePrice' => $prices[1],
	  	'status' => $product->get_status()
	);
}
function spe_get_product_tags ($prod_id) {
	$prod_tags = (array) wp_get_post_terms($prod_id, 'product_tag', array('fields' => 'names') );
	$ret = "";
	if (isset($prod_tags)) {
		foreach ($prod_tags as $prod_tag) {
			$prod_tags_str .= ($prod_tag . ',');
		}
		$ret = substr($prod_tags_str, 0, -1);
	}
	return $ret;
}
function spe_display_basic_product_info($prod_id, $prod_info) {
	spe_display_product_prices($prod_id, $prod_info);
	spe_display_product_post_status($prod_id, $prod_info['status'], 1, '');
}
function spe_finish_product_setup($product, $prod_id, $visvar, $initial_value_array, $type) {
	if (!empty($initial_value_array)) {
		spe_initial_value_setup_script($type, $initial_value_array, $prod_id);
	}

	spe_display_product_visibility($prod_id, $visvar);
	spe_display_product_categories($product, $prod_id);

	generate_product_edit_script();
}
function spe_get_product_image_id($product) {
	$image_id  = $product->get_image_id();
	return $image_id;
}
function spe_display_product_image($image_id) {
	$image_url = wp_get_attachment_image_url( $image_id, 'full' );
	?>
	<a href="<?php echo $image_url; ?>" target="_blank"><img src="<?php echo $image_url; ?>" width="300"/></a><br />
	<?php
}
function spe_display_single_product_info_table($prod_id, $prod_info_rows) {
	?><br>
	<div class="spe-single-prod-table"><?php
  	foreach ($prod_info_rows as $prod_info_row) {
		?><div class="spe-pt__row<?php if ($prod_info_row === end($prod_info_rows)) {echo " no-border";} ?>">
			<div class="spe-pt__cell spe-prod-table--info-label var-name"><?= $prod_info_row['name'] ?></div><div id="<?php echo $prod_id , $prod_info_row['idSuffix']; ?>" class="spe-single-pt__cell--val spe-pt__cell <?= $prod_info_row['classes'] ?>" contentEditable="true"><?= $prod_info_row['val'] ?></div>
		</div><br><?php
	}
	?><br>
	</div><?php
}
function spe_get_product_prices($product, $prod_id, $db) {
	$result = array(3);
	$result[0] = $product->get_regular_price();
	$result[1] = $product->get_sale_price();
	$result[2] = spe_get_discount_type_string($prod_id, $db);
	return $result;
}
function spe_display_product_prices($prod_id, $prod_info) {
  	?>
	<div id="<?= $prod_id; ?>-cat-display" class="spe-var-label">Price Info:</div>
	<table class="spe-var-list-container">
		<tr>
			<td class="price">Regular Price:</td>
	  		<td id="<?=$prod_id;?>-reg-price" class="spe-prod-info price float-val" contentEditable="true"><?=$prod_info['regularPrice'] ? $prod_info['regularPrice'] : '&nbsp;&nbsp;'?></td>
	  	</tr>
		<tr>
			<td class="price">Sale Price:</td>
	  		<td id="<?=$prod_id;?>-sales-price" class="spe-prod-info price sales-price float-val" contentEditable="true"><?=$prod_info['salePrice'] ? $prod_info['salePrice'] : '&nbsp;&nbsp;'?></td>
	  	</tr>
		<tr>
			<td class="price">Discount Type Display Name:</td>
	  		<td id="<?=$prod_id;?>-discount-type" class="spe-prod-info price discount-type string-val" contentEditable="true"><?=$prod_info['discountType']?></td>
	  	</tr>
	</table>
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
			<span id="<?= $prod_id; ?>-stock" class="spe-prod-info stock stock-val integer-val<?= set_bg($stock); ?>" contentEditable="true">
				<?= ($product->get_stock_quantity() ? $product->get_stock_quantity() : '0'); ?>
			</span>
			<?php
		} else {
			?>
			<div id="<?= $prod_id; ?>-stock-status-parentdiv" class="spe-prod-info spe-dropdown-parent stock-status spe-prod-selection<?= set_bg($stock); ?>"><?= $product->get_stock_status(); ?>
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
function spe_display_product_weight($prod_id, $weight) {
	?>
	<div class="spe-var-label">Product Weight in Pounds:
		<span id="<?= $prod_id; ?>-weight" class="spe-prod-info float-val" contentEditable="true"><?= $weight ?></span>
	</div>
	<?php
}
function spe_display_product_dimensions($prod_id, $p_l, $p_w, $p_h) {
	?>
	<div class="spe-var-label">Product Dimensions in Inches:<br />
		Length <span id="<?= $prod_id; ?>-length" class="spe-prod-info float-val" contentEditable="true"><?= $p_l ?></span>
		Width <span id="<?= $prod_id; ?>-width" class="spe-prod-info float-val" contentEditable="true"><?= $p_w ?></span>
		Height <span id="<?= $prod_id; ?>-height" class="spe-prod-info float-val" contentEditable="true"><?= $p_h ?></span>
	</div>
	<?php
}
function spe_display_product_visibility($product, $visvar) {
	?>
		<div class="spe-var-label">Visibility:</div>
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
		  	/*?>
	  		<div id="<?= $prod_id; ?>-cat-drop-button" class="spe-dropdown-parent product-cat">Categories &#9660;
				<div id="<?= $prod_id; ?>-cat-dropdown" class="dropdiv-content dropdiv-content-view-only--container dropdiv-content--standalone product-cat center"><?php
				foreach ($cat_ids as $cat_id) {
					$term = get_term_by( 'id', $cat_id, 'product_cat' );
					echo '<span class="dropdiv-content-view-only">' . $term->name . '</span><br />';
				}
				*/ ?>
	  		<div id="<?= $prod_id; ?>-cat-display" class="spe-var-label">Categories:</div>
	  			<div class="spe-var-list-container">
					<?php
					foreach ($cat_ids as $cat_id) {
						$term = get_term_by( 'id', $cat_id, 'product_cat' );
						echo '<span class="spe-var-list-item">' . $term->name . '</span><br />';
					}
					?>
				</div>
	  		<?php
		} else {
		  	$term = get_term_by( 'id', $cat_ids[0], 'product_cat' );
			?><div class="product-cat edit spe-var-label">Category: <?= $term->name; ?></div><?php
		}	
		
	} else echo '<div class="product-cat edit">Uncategorized</div>';
}
function spe_display_external_target_url($prod_id, $url) {
	echo '<div class="spe-var-label external-url">External Link: <span id="'. $prod_id . '-external-link" class="spe-prod-info external-link string-val" contentEditable="true">',$url,'</span></div>';
}
function spe_display_page_num($page_num, $is_current_page) {
	echo '<div class="page-num' . ($is_current_page ? ' current-page' : '') . '">' . spe_format_page_url($page_num) . '</div 77>';
}
function spe_format_page_url($page_num) {
	$query_params_raw = explode('?', $_SERVER['QUERY_STRING']);
	parse_str($_SERVER['QUERY_STRING'], $query_param_array);
	unset($query_param_array['pagenumber']);
	if (count($query_param_array)) {
		return '<a href="'. admin_url( 'admin.php?' . http_build_query($query_param_array) . '&pagenumber=' . $page_num ) .'" >' . $page_num . '</a>';
	} else return $page_num;
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
function spe_get_discount_type_string($prod_id, $db) {
	$querystr = "SELECT post_id FROM `" . $db->prefix . "postmeta` WHERE meta_value = " . $prod_id . " and meta_key = 'discount_type_string'";
	$res = $db->get_results($querystr);
  	if ($res) {
	  	return $res;
	} else {
		return "";
	}
}
function get_product_meta($prod_id, $db, $terms, $external = false) {
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
	
  	$result = NULL;
	$resbuffer = NULL;
	$querystr = NULL;

	$result = array(5);

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

	if ($external) {
		$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = 'super_product_id' LIMIT 1";
		$resbuffer = $db->get_results($querystr);
		if (!empty($resbuffer)) {
			$result[3] = $resbuffer[0]->meta_value;
		} else $result[3] = '';
	  
		$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = 'linked_variation_id' LIMIT 1";
		$resbuffer = $db->get_results($querystr);
		if (!empty($resbuffer)) {
			$result[4] = $resbuffer[0]->meta_value;
		} else $result[4] = '';
	}
  
  return $result;
}
function get_product_url_or_color($prod_id, $db, $return_full_color_url) {
  // attribute_pa_color in variable product view, or the whole url in external product view
  if ($return_full_color_url == 0) {
  		$querystr = "SELECT substring_index(meta_value,'attribute_pa_color=',-1) FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = '_product_url' LIMIT 1";
	} else $querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $prod_id . " and meta_key = '_product_url' LIMIT 1";

	return strval($db->get_var($querystr));
}
function get_variation_additional_images_formatted_url_list($variation_id, $db) {
	$querystr = "SELECT meta_value FROM `" . $db->prefix . "postmeta` WHERE post_id = " . $variation_id . " and meta_key = '_wc_additional_variation_images' LIMIT 1";
	$resbuffer = $db->get_var($querystr);
	$ret = '';
	if (!empty($resbuffer)) {
		$resbufferarray = explode(',', $resbuffer);
		foreach ($resbufferarray as $res) {
			$ret .= wp_get_attachment_image_url($res, 'full');
			$ret .= ',';
		}
	}
  	return substr($ret, 0, -1);;
}
