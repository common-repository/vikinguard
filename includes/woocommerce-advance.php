<?php
if (! defined ( 'ABSPATH' )) {
	exit (); // Exit if accessed directly
}



update_option("HEIMDALAPM_ENDPOINT","//eum.vikinguard.com/metrics/data/addToCart");

// Producto simple y cuando añade al carrito
add_action ( "woocommerce_after_single_product", "vikinguard_product_detail_view" );
add_action ( "woocommerce_after_single_product", "vikinguard_product_add_to_cart" );

// // Despues de compra
add_action ( "woocommerce_thankyou", "vikinguard_vg_order" );

// //Cuando borran en el carrito
add_action ( 'woocommerce_after_cart', "vikinguard_remove_from_cart" );
add_action ( 'woocommerce_after_mini_cart', "vikinguard_remove_from_cart" );

// Carrito en Shop, Cat, searrch, home.
add_action ( 'woocommerce_after_shop_loop_item', "vikinguard_bind_product_metadata" );

// //for cat, shop, prod(related),search and home page
add_action ( 'wp_footer', "vikinguard_add_to_cart" );

// // Para el checkout
add_action ( 'woocommerce_after_checkout_form', "vikinguard_vg_checkout" );
function vikinguard_bind_product_metadata() {
	global $product;
	$category = get_the_terms ( $product->ID, "product_cat" );
	$categories = "";
	if ($category) {
		foreach ( $category as $term ) {
			$categories .= $term->name . "#1234#";
		}
	}
	// remove last comma(,) if multiple categories are there
	$categories = rtrim ( $categories, "#1234#" );
	// declare all variable as a global which will used for make json
	global $vg_homepage_json_fp, $vg_homepage_json_ATC_link, $vg_homepage_json_rp, $vg_prodpage_json_relProd, $vg_catpage_json, $vg_prodpage_json_ATC_link, $vg_catpage_json_ATC_link;
	// is home page then make all necessory json
	if (is_home ()) {
		if (! is_array ( $vg_homepage_json_fp ) && ! is_array ( $vg_homepage_json_rp ) && ! is_array ( $vg_homepage_json_ATC_link )) {
			$vg_homepage_json_fp = array ();
			$vg_homepage_json_rp = array ();
			$vg_homepage_json_ATC_link = array ();
		}
		// ATC link Array
		$vg_homepage_json_ATC_link [$product->add_to_cart_url ()] = array (
				"ATC-link" => get_permalink ( $product->id ) 
		);
		// check if product is featured product or not
		if ($product->is_featured ()) {
			// check if product is already exists in homepage featured json
			if (! array_key_exists ( get_permalink ( $product->id ), $vg_homepage_json_fp )) {
				$vg_homepage_json_fp [get_permalink ( $product->id )] = array (
						"id" => esc_html ( $product->get_sku () ? $product->get_sku () : $product->id ),
						"name" => esc_html ( $product->get_title () ),
						"pvp" => esc_html ( $product->get_price () ),
						'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ),
						"category" => esc_html ( $categories ),
						"type" => 'addToCart',
						"ATC-link" => $product->add_to_cart_url () 
				);
				// else add product in homepage recent product json
			} else {
				$vg_homepage_json_rp [get_permalink ( $product->id )] = array (
						"id" => esc_html ( $product->get_sku () ? $product->get_sku () : $product->id ),
						"name" => esc_html ( $product->get_title () ),
						"pvp" => esc_html ( $product->get_price () ),
						'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ),
						"category" => esc_html ( $categories ),
						"type" => 'addToCart' 
				);
			}
		} else {
			// else prod add in homepage recent json
			$vg_homepage_json_rp [get_permalink ( $product->id )] = array (
					"id" => esc_html ( $product->get_sku () ? $product->get_sku () : $product->id ),
					"name" => esc_html ( $product->get_title () ),
					"pvp" => esc_html ( $product->get_price () ),
					'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ),
					"category" => esc_html ( $categories ),
					"type" => 'addToCart' 
			);
		}
	}  // if product page then related product page array
else if (is_product ()) {
		if (! is_array ( $vg_prodpage_json_relProd ) && ! is_array ( $vg_prodpage_json_ATC_link )) {
			$vg_prodpage_json_relProd = array ();
			$vg_prodpage_json_ATC_link = array ();
		}
		// ATC link Array
		$vg_prodpage_json_ATC_link [$product->add_to_cart_url ()] = array (
				"ATC-link" => get_permalink ( $product->id ) 
		);
		
		$vg_prodpage_json_relProd [get_permalink ( $product->id )] = array (
				"id" => esc_html ( $product->get_sku () ? $product->get_sku () : $product->id ),
				"name" => esc_html ( $product->get_title () ),
				"pvp" => esc_html ( $product->get_price () ),
				'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ),
				"category" => esc_html ( $categories ),
				"type" => 'addToCart' 
		);
	}  // category page, search page and shop page json
else if (is_product_category () || is_search () || is_shop ()) {
		if (! is_array ( $vg_catpage_json ) && ! is_array ( $vg_catpage_json_ATC_link )) {
			$vg_catpage_json = array ();
			$vg_catpage_json_ATC_link = array ();
		}
		// cat page ATC array
		$vg_catpage_json_ATC_link [$product->add_to_cart_url ()] = array (
				"ATC-link" => get_permalink ( $product->id ) 
		);
		
		$vg_catpage_json [get_permalink ( $product->id )] = array (
				
				"id" => esc_html ( $product->get_sku () ? $product->get_sku () : $product->id ),
				"name" => esc_html ( $product->get_title () ),
				"pvp" => esc_html ( $product->get_price () ),
				'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ),
				"category" => esc_html ( $categories ),
				"type" => 'addToCart' 
		);
	}
}
function vikinguard_product_add_to_cart() {
	if (! is_single ()) {
		
		return;
	}
	
	global $product;
	
	$category = get_the_terms ( $product->ID, "product_cat" );
	$categories = "";
	if ($category) {
		foreach ( $category as $term ) {
			$categories .= $term->name . "#1234#";
		}
	}
	
	// remove last comma(,) if multiple categories are there
	$categories = rtrim ( $categories, "#1234#" );
	$vg_product = array (
			'id' => esc_html ( $product->get_sku () ),
			'name' => esc_html ( $product->get_title () ),
			'category' => esc_html ( $categories ),
			'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ),
			'pvp' => esc_html ( $product->get_price () ),
			'type' => 'addToCart' 
	);
	// que pasa con el quantity
	
	$code = "
			
			
            jQuery('button[class*=single_add_to_cart_button]').click(function() {
						 vg_qty=$(this).parent().find('input[name=quantity]').val();
						 product=". json_encode ( $vg_product ) .";
						
                             //default quantity 1 if quantity box is not there             
                            if(vg_qty=='' || vg_qty===undefined){
                                vg_qty=1;
                            }
						  product.quantity=vg_qty;
						  product.shop='".( string ) get_option ( 'HEIMDALAPM_SHOP' )."';
						  product.customer='".( string ) get_option ( 'HEIMDALAPM_CUSTOMER' )."';

                             $.ajax({
    					// la URL para la petición
   							 url : '".( string ) get_option ( 'HEIMDALAPM_ENDPOINT' )."',
   						 // la información a enviar
   						data : product,
    					// especifica si será una petición POST o GET
    					type : 'POST',
   							 		
   						xhrFields: {
      						withCredentials: true
  						 }
						});
			
			});
			
		";
	// check woocommerce version
	
	wc_enqueue_js ( $code );
}
function vikinguard_product_detail_view() {
	global $product;
	$category = get_the_terms ( $product->ID, "product_cat" );
	$categories = "";
	if ($category) {
		foreach ( $category as $term ) {
			$categories .= $term->name . "#1234#";
		}
	}
	// remove last comma(,) if multiple categories are there
	$categories = rtrim ( $categories, "#1234#" );
	$vg_product = array (
			'id' => esc_html ( $product->get_sku () ),
			'name' => esc_html ( $product->get_title () ),
			'category' => esc_html ( $categories ),
			'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ),
			'pvp' => esc_html ( $product->get_price () ) 
	);
	
	vikinguard_sendInfo ( "detail", json_encode ( $vg_product ) );
}
function vikinguard_add_to_cart() {
	
	// get impression threshold
	$impression_threshold = 3500;
	
	// Product impression on Home Page
	global $vg_homepage_json_fp, $vg_homepage_json_ATC_link, $vg_homepage_json_rp, $vg_prodpage_json_relProd, $vg_catpage_json, $vg_prodpage_json_ATC_link, $vg_catpage_json_ATC_link;
	// home page json for featured products and recent product sections
	// check if php array is empty
	if (empty ( $vg_homepage_json_ATC_link )) {
		$vg_homepage_json_ATC_link = array (); // define empty array so if empty then in json will be []
	}
	if (empty ( $vg_homepage_json_fp )) {
		$vg_homepage_json_fp = array (); // define empty array so if empty then in json will be []
	}
	if (empty ( $vg_homepage_json_rp )) { // home page recent product array
		$vg_homepage_json_rp = array ();
	}
	if (empty ( $vg_prodpage_json_relProd )) { // prod page related section array
		$vg_prodpage_json_relProd = array ();
	}
	if (empty ( $vg_prodpage_json_ATC_link )) {
		$vg_prodpage_json_ATC_link = array (); // prod page ATC link json
	}
	if (empty ( $vg_catpage_json )) { // category page array
		$vg_catpage_json = array ();
	}
	if (empty ( $vg_catpage_json_ATC_link )) { // category page array
		$vg_catpage_json_ATC_link = array ();
	}
	if (is_home () || is_product () || is_product_category () || is_search () || is_shop ()) {
		
		wc_enqueue_js ( "vg_homepage_json_ATC_link=" . json_encode ( $vg_homepage_json_ATC_link ) . ";" );
		wc_enqueue_js ( "vg_tvc_fp=" . json_encode ( $vg_homepage_json_fp ) . ";" );
		wc_enqueue_js ( "vg_tvc_rcp=" . json_encode ( $vg_homepage_json_rp ) . ";" );
		// product page json
		wc_enqueue_js ( "vg_tvc_rdp=" . json_encode ( $vg_prodpage_json_relProd ) . ";" );
		wc_enqueue_js ( "vg_prodpage_json_ATC_link=" . json_encode ( $vg_prodpage_json_ATC_link ) . ";" );
		// category page json
		wc_enqueue_js ( "vg_tvc_pgc=" . json_encode ( $vg_catpage_json ) . ";" );
		wc_enqueue_js ( "vg_catpage_json_ATC_link=" . json_encode ( $vg_catpage_json_ATC_link ) . ";" );
	}
	$hmpg_impressions_jQ = '
                function vikinguard_ATC(t_url,t_ATC_json_name,t_prod_data_json,t_qty){
                    t_prod_url_key=t_ATC_json_name[t_url]["ATC-link"];
	
                         if(t_prod_data_json.hasOwnProperty(t_prod_url_key)){
                                t_call_fired=true;
                            		
                            		
				      $.ajax({
    					// la URL para la petición
   							 url : "'.( string ) get_option ( 'HEIMDALAPM_ENDPOINT' ).'",
   						 // la información a enviar
   						data : {
                               "id": t_prod_data_json[t_prod_url_key].id,
                               "name": t_prod_data_json[t_prod_url_key].name,
                               "category": t_prod_data_json[t_prod_url_key].category,
                               "pvp": t_prod_data_json[t_prod_url_key].pvp,
                               "currency":t_prod_data_json[t_prod_url_key].currency,
                               "quantity" : t_qty,
   							   "shop":"'.( string ) get_option ( 'HEIMDALAPM_SHOP' ).'",
   							   "customer":"'.( string ) get_option ( 'HEIMDALAPM_CUSTOMER' ).'",
   							   "type": "addToCart"
                              },
    					// especifica si será una petición POST o GET
    					type : "POST",
   						xhrFields: {
      						withCredentials: true
  						 }
						});
                           


                         }else{
                                   t_call_fired=false;
		}
                         return t_call_fired;
         
                }
	
                ';
	
	if (is_home ()) {
		$hmpg_impressions_jQ .= '
              
               
                //ATC click
                jQuery("a[href*=add-to-cart]").on("click",function(){
			t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                        t_call_fired=vikinguard_ATC(t_url,vg_homepage_json_ATC_link,vg_tvc_fp,t_qty);
                        if(!t_call_fired){
                            vikinguard_ATC(t_url,vg_homepage_json_ATC_link,vg_tvc_rcp,t_qty);
                        }
                    });
       
                ';
	} else if (is_product ()) {
		// product page releted products
		$hmpg_impressions_jQ .= '
                
             
                //Prod ATC link click in related product section
                jQuery("a[href*=add-to-cart]").on("click",function(){
			t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                vikinguard_ATC(t_url,vg_prodpage_json_ATC_link,vg_tvc_rdp,t_qty);
                });
            ';
	}
	// common ATC link for Category page , Shop Page and Search Page
	if (is_product_category () || is_shop () || is_search ()) {
		$hmpg_impressions_jQ .= '
                     //ATC link click
                jQuery("a[href*=add-to-cart]").on("click",function(){
			t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                       vikinguard_ATC(t_url,vg_catpage_json_ATC_link,vg_tvc_pgc,t_qty);
                    });
                    ';
	}
	
	// //on home page, product page , category page
	if (is_home () || is_product () || is_product_category () || is_search () || is_shop ()) {
		wc_enqueue_js ( $hmpg_impressions_jQ );
	}
}
function vikinguard_remove_from_cart() {
	global $woocommerce;
	$cartpage_prod_array_main = array ();
	// echo "<pre>".print_r($woocommerce->cart->cart_contents,TRUE)."</pre>";
	foreach ( $woocommerce->cart->cart_contents as $key => $item ) {
		$prod_meta = get_product ( $item ["product_id"] );
		
		$cart_remove_link = html_entity_decode ( $woocommerce->cart->get_remove_url ( $key ) );
		
		$category = get_the_terms ( $item ["product_id"], "product_cat" );
		$categories = "";
		if ($category) {
			foreach ( $category as $term ) {
				$categories .= $term->name . "#1234¢";
			}
		}
		// remove last comma(,) if multiple categories are there
		$categories = rtrim ( $categories, "#1234#" );
		$cartpage_prod_array_main [$cart_remove_link] = array (
				"id" => esc_html ( $prod_meta->get_sku () ? $prod_meta->get_sku () : $prod_meta->id ),
				"name" => esc_html ( $prod_meta->get_title () ),
				"pvp" => esc_html ( $prod_meta->get_price () ),
				"category" => esc_html ( $categories ),
				"quantity" => $woocommerce->cart->cart_contents [$key] ["quantity"],
				'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ) 
		);
	}
	
	// Cart Page item Array to Json
	wc_enqueue_js ( "vg_tvc_cc=" . json_encode ( $cartpage_prod_array_main ) . ";" );
	
	$code = '
       
        $("a[href*=\"?remove_item\"]").click(function(){
           vg_t_url=jQuery(this).attr("href");
 				
 				 $.ajax({
    					// la URL para la petición
   							 url : "'.( string ) get_option ( 'HEIMDALAPM_ENDPOINT' ).'",
   						 // la información a enviar
   						data : {
              				  "id":vg_tvc_cc[vg_t_url].id,
              				  "name": vg_tvc_cc[vg_t_url].name,
             			      "category":vg_tvc_cc[vg_t_url].category,
             			      "pvp": vg_tvc_cc[vg_t_url].pvp,
             			      "quantity": vg_tvc_cc[vg_t_url].quantity,
 						      "currency":  vg_tvc_cc[vg_t_url].currency,
 							  "type": "removeFromCart",
							  "shop":"'.( string ) get_option ( 'HEIMDALAPM_SHOP' ).'",
   							  "customer":"'.( string ) get_option ( 'HEIMDALAPM_CUSTOMER' ).'",
              },
    					// especifica si será una petición POST o GET
    					type : "POST",
   					    xhrFields: {
      						withCredentials: true
  						 }
						 
						});
 	
      
            
              });
            ';
	// check woocommerce version
	wc_enqueue_js ( $code );
}
function vikinguard_vg_checkout() {
	vikinguard_vg_ordered_items ();
}
function vikinguard_vg_order($order_id) {

	global $woocommerce;
	
	// if (current_user_can("manage_options") )
	// return;
	
	// Get the order and output tracking code
	$order = new WC_Order ( $order_id );
	
	// Order items
	if ($order->get_items ()) {
		$i=0;
		foreach ( $order->get_items () as $item ) {
			$_product = $order->get_product_from_item ( $item );
			
			if (isset ( $_product->variation_data )) {
				$categories = esc_js ( woocommerce_get_formatted_variation ( $_product->variation_data, true ) );
			} else {
				$out = array ();
				$categories = get_the_terms ( $_product->id, "product_cat" );
				if ($categories) {
					foreach ( $categories as $category ) {
						$out [] = $category->name;
					}
				}
				$categories = esc_js ( join ( ",", $out ) );
			}
			
			// orderpage Prod json
			$orderpage_prod_Array [get_permalink ("productOrder[".$i."]")] = array (
					"id" => esc_js ( $_product->get_sku () ? $_product->get_sku () : $_product->id ),
					"name" => esc_js ( $item ["name"] ),
					"pvp" => esc_js ( $order->get_item_total ( $item ) ),
					"category" => $categories,
					"quantity" => esc_js ( $item ["qty"] ),
					'currency' => esc_html ( get_option ( 'woocommerce_currency' ) )
			);
			
			
			$vg_product= array (
					"id" => esc_js ( $_product->get_sku () ? $_product->get_sku () : $_product->id ),
					"name" => esc_js ( $item ["name"] ),
					"pvp" => esc_js ( $order->get_item_total ( $item ) ),
					"category" => $categories,
					"quantity" => esc_js ( $item ["qty"] ),
					'currency' => esc_html ( get_option ( 'woocommerce_currency' ) )
			);
			
			vikinguard_sendInfo("productOrder[".$i."]", json_encode ( $vg_product));
			$i++;
		}
		// make json for prod meta data on order page
		
		//vikinguard_sendInfo("ORDERCART", json_encode ( $orderpage_prod_Array ));
	}
	
	
	// orderpage transcation data json
	$orderpage_trans_Array = array (
			"id" => esc_js ( $order->get_order_number () ), // Transaction ID. Required
			"revenue" => esc_js ( $order->get_total () ), // Grand Total
			"tax" => esc_js ( $order->get_total_tax () ), // Tax
	) // Shipping

	;
	// make json for trans data on order page
	
	vikinguard_sendInfo("totalOrder", json_encode($orderpage_trans_Array));
	
}
function vikinguard_vg_ordered_items() {
	global $woocommerce;
	$code = "";
	$i=0;
	// get all items added into the cart
	foreach ( $woocommerce->cart->cart_contents as $item ) {
		$p = get_product ( $item ["product_id"] );
		
		$category = get_the_terms ( $item ["product_id"], "product_cat" );
		$categories = "";
		if ($category) {
			foreach ( $category as $term ) {
				$categories .= $term->name . "#1234#";
			}
		}
		// remove last comma(,) if multiple categories are there
		$categories = rtrim ( $categories, "#1234#" );
		$chkout_json  = array (
				"id" => esc_js ( $p->get_sku () ? $p->get_sku () : $p->id ),
				"name" => esc_js ( $p->get_title () ),
				"pvp" => esc_js ( $p->get_price () ),
				"category" => $categories,
				"quantity" => esc_js ( $item ["quantity"] ),
				'currency' => esc_html ( get_option ( 'woocommerce_currency' ) ) 
		);
		
		vikinguard_sendInfo("productCheckout[".$i."]",json_encode ( $chkout_json ));
		$i++;
		
	}
	// return $code;
	// make product data json on check out page
	
	vikinguard_sendInfo ( "checkout", 0  );
}
function vikinguard_sendInfo($type, $code) {
	return wc_enqueue_js ( '
					heimdaladdVar("' . $type . '",\'' . $code . '\');
				' );
}

?>