/*
* Automatic update of the cart when the number of items in it changes
*/

function wp_footer() {

jQuery( function( $ ) {
 
	$( 'body' ).on( 'click', '.plus, .minus', function() {
 
		// do everything that needs to be done, change the number in the fields
 
		input.val( quantity ).change();
		$( '[name="update_cart"]' ).trigger( 'click' );
	} );
 
} );

	do_action( 'wp_footer' );
}




/*
* Redirect to the checkout page immediately after adding the product to the cart
*/

add_filter( 'woocommerce_add_to_cart_redirect', 'truejb_skip_cart' );
 
function truejb_skip_cart( $redirect ) {
 
	return wc_get_checkout_url();
 
}




/*
* set a minimum order amount
*/

//  Adding Notifications to the Cart and Checkout Pages
add_action( 'woocommerce_before_cart', 'truejb_minimum_order_amount' );
 
function truejb_minimum_order_amount(){
 
	$minimum_amount = 1000;
 
	if ( WC()->cart->subtotal < $minimum_amount ) {
 
		wc_print_notice(
			sprintf(
				'Minimum order amount %s, and you want to order only %s.' ,
				wc_price( $minimum_amount ),
				wc_price( WC()->cart->subtotal )
			),
			'notice'
		);
	}
 
}
add_action( 'woocommerce_before_checkout_form', 'truejb_minimum_order_amount' );

//  We block the possibility of placing an order with a small amount
add_action( 'woocommerce_checkout_process', 'truejb_no_checkout_min_order_amount' );
 
function truejb_no_checkout_min_order_amount() {
 
	$minimum_amount = 1000;
 
	if ( WC()->cart->subtotal < $minimum_amount ) {
 
		wc_add_notice( 
			sprintf( 
				'Minimum order amount %s, and you want to order only %s.',
				wc_price( $minimum_amount ),
				wc_price( WC()->cart->subtotal )
			),
			'error'
		);
 
	}
 
}





/*
* change the price of a product in the cart depending on its quantity in it
*/

add_action( 'woocommerce_before_calculate_totals', 'truejb_quantity_based_price' );
 
function truejb_quantity_based_price( $cart_object ) {
 
 	// you can always do print_r( $cart_object ); exit; 
 
	$product_id = 35; // target product ID with dynamic price
 
	// it is not necessary to twist foreach here, but I decided to do this
	foreach ( $cart_object->get_cart() as $cart_id => $cart_item ) {
 
		if( $cart_item[ 'product_id' ] == $product_id ) {
			$quantity = $cart_item[ 'quantity' ];
			break;
		}
 
	}
 
	// if the quantity of goods is more than three, you can set any value
	if( ! empty( $quantity ) && $quantity > 3 ) {
 
		// cycle again, yes
		foreach ( $cart_object->get_cart() as $cart_id => $cart_item ) {
 
			// if the right product
			if( $cart_item['product_id'] == $product_id ) {
 
				// I decided to make a discount 50%
				$newprice = $cart_item['data']->get_regular_price() / 2;
 
				$cart_item['data']->set_price( $newprice );
 
			}
 
		}
	}
 
}





/*
* change the message about adding the product to the cart
*/

add_filter( 'wc_add_to_cart_message_html', 'truejb_tovar_v_korzine_new', 10, 3 );
 
function truejb_tovar_v_korzine_new( $message, $products, $show_qty ) {
 
	$message = 'Fire! ';
 
	if( 1 < count( $products ) ) {
		$message .= 'Products ';
		$iteration = 0;
		foreach( $products as $product_id => $qty ) {
 
			$iteration++;
			if( $iteration == count( $products ) ) {
				$message .= 'и &laquo;' . get_the_title( $product_id ) . '&raquo;';
			} else {
				$message .= '&laquo;' . get_the_title( $product_id ) . '&raquo;, ';
			}
 
		}
		$message .= ' in your cart!';
	} else {
		$products = array_keys( $products );
		$message .= '&laquo;' . get_the_title( $products[0] ) . '&raquo; in your cart!';
	}
 
	return $message;
 
}




/*
* Set the minimum order quantity
*/

//  By the number of units of goods
// For product page
 
add_filter( 'woocommerce_quantity_input_min', 'truejb_min_kolvo', 20, 2 );
 
function truejb_min_kolvo( $min, $product ){
 
	if ( 500 == $product->get_id() ) { // only for goods with ID 500
		$min = 2; // there must be at least 2 items in the cart
	}
	return $min;
}
 
//  Для корзины

add_filter( 'woocommerce_cart_item_quantity', 'truejb_min_kolvo_cart', 20, 3 );
 
function truejb_min_kolvo_cart( $product_quantity, $cart_item_key, $cart_item ) {
 
	$product = $cart_item['data'];
	$min = 0;
 
	if ( 500 === $product->get_id() ) { // product with с ID 500
		$min = 2;
	}
 
	return woocommerce_quantity_input(
		array(
			'input_name'   => "cart[{$cart_item_key}][qty]",
			'input_value'  => $cart_item['quantity'],
			'max_value'    => $product->get_max_purchase_quantity(),
			'min_value'    => $min,
			'product_name' => $product->get_name(),
		),
		$product,
		false
	);
 
}


//  Does not allow you to place an order if the quantity of a certain product is less than necessary
add_action( 'woocommerce_checkout_process', 'truejb_min_tovar_qty' );
 
function truejb_min_tovar_qty(){
 
	$qty = 0; // be sure to set first 0
 
	foreach ( WC()->cart->get_cart() as $cart_item ) { 
		if( 500 == $cart_item[ 'product_id' ] ){
			$qty = $cart_item[ 'quantity' ];
			break;
		}
	}
 
	if( $qty && $qty < 2 ) {
		wc_add_notice( 'Not enough items to order!', 'error' );
	}
 
}






/*
* Change the add to cart button if the product is already in it
*/

//  changing button text
// for the product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'truejb_single_product_btn_text' );
 
function truejb_single_product_btn_text( $text ) {
 
	if( WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( get_the_ID() ) ) ) {
		$text = 'Already in the cart, add again?';
	}
 
	return $text;
 
}
 
// for product catalog pages, product categories, etc.
add_filter( 'woocommerce_product_add_to_cart_text', 'truejb_product_btn_text', 20, 2 );
 
function truejb_product_btn_text( $text, $product ) {
 
	if( 
	   $product->is_type( 'simple' )
	   && $product->is_purchasable()
	   && $product->is_in_stock()
	   && WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product->get_id() ) )
	) {
 
		$text = 'Already in the cart, add again?';
 
	}
 
	return $text;
 
}


//  We also change the URL of the button
add_filter( 'woocommerce_product_add_to_cart_url', 'truejb_product_cart_url', 20, 2 );
 
function truejb_product_cart_url( $url, $product ) {
 
	if( 
	   $product->is_type( 'simple' )
	   && $product->is_purchasable()
	   && $product->is_in_stock()
	   && WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product->get_id() ) )
	) {
 
		$url = wc_get_cart_url();
 
	}
 
	return $url;
 
}






/*
* display the articles in the cart
*/

add_action( 'woocommerce_after_cart_item_name', 'truejb_artikul_in_cart', 25 );
 
function truejb_artikul_in_cart( $cart_item ) {
 
	$sku = $cart_item['data']->get_sku();
 
	if( $sku ) { // if it is filled, then output
		echo '<p><small>Article: ' . $sku . '</small></p>';
	}
 
}




/*
* Change the price programmatically (we make a discount for authorized users)
*/

add_filter( 'woocommerce_get_price_html', 'truejb_display_price', 25, 2 );
 
function truejb_display_price( $price_html, $product ) {
 
	// do nothing in the admin panel
	if ( is_admin() ) {
		return $price_html;
	}
 
	// if the price is empty, score too
	if ( '' === $product->get_price() ) {
		return $price_html;
	}
 
	// class, this is our site user, we give him a 20% discount
	if ( wc_current_user_has_role( 'customer' ) ) {
		$price_html = wc_price( wc_get_price_to_display( $product ) * 0.80 );
	}
 
	return $price_html;
 
}
 
 
add_action( 'woocommerce_before_calculate_totals', 'truejb_alter_price', 25 );
 
function truejb_alter_price( $cart ) {
 
	// do nothing in the admin panel and if it is not a calculation via ajax
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
 
	// not our user
	if ( ! wc_current_user_has_role( 'customer' ) ) {
		return;
	}
 
	// run a cycle for the entire basket and add an additional 20% to each product
	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		$price = $cart_item['data']->get_price();
		$cart_item['data']->set_price( $price * 0.80 );
	}
 
}





/*
* Adding a product as a gift when buying a specific product
*/

add_action( 'template_redirect', 'truejb_auto_gift', 25 );
 
function truejb_auto_gift() {
 
	// do nothing in the admin panel
	if ( is_admin() ) {
		return;
	}
 
	// do nothing if cart is empty
	if ( WC()->cart->is_empty() ) {
		return;
	}
 
	$product_id = 35; // Product ID, upon purchase of which we give a gift
	$gift_id = 25; // ID of the product-gift
 
	// moved the presence of a gift in the basket into a separate variable
	$is_gift_in_cart = WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $gift_id ) );
 
	// if the target product is not in the cart
	if ( ! WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product_id ) ) ) {
 
		// if the gift in cart, then remove it from it
		if ( $is_gift_in_cart ) {
			WC()->cart->remove_cart_item( WC()->cart->generate_cart_id( $gift_id ) );
		}
 
	} else { // if the target product in cart
 
		// just check, if the gift is not in cart, then adding
		if ( ! $is_gift_in_cart ) {
			WC()->cart->add_to_cart( $gift_id );
		}
 
	}
}




/*
* turn the product quantity field into a dropdown list
*/

function woocommerce_quantity_input( $args = array(), $product = null, $echo = true ) {
 
	if ( is_null( $product ) ) {
		$product = $GLOBALS[ 'product' ];
	}
 
	// default values
	$defaults = array(
		'input_name' => 'quantity',
		'input_value' => '1',
		'max_value' => apply_filters( 'woocommerce_quantity_input_max', -1, $product ),
		'min_value' => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
		'step' => 1,
	);
 
	// do not remove this filter hook
	$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $product );
 
	// some validations
	$args[ 'min_value' ] = max( $args[ 'min_value' ], 0 );
	$args[ 'max_value' ] = 0 < $args[ 'max_value' ] ? $args[ 'max_value' ] : 20;
 
	if ( '' !== $args['max_value'] && $args[ 'max_value' ] < $args[ 'min_value' ] ) {
		$args['max_value'] = $args[ 'min_value' ];
	}
 
	$options = '';
 
	// start the cycle for creating the dropdown select
	for ( $count = $args[ 'min_value' ]; $count <= $args[ 'max_value' ]; $count = $count + $args[ 'step' ] ) {
 
		// Cart item quantity defined?
		if ( '' !== $args[ 'input_value' ] && $args[ 'input_value' ] >= 1 && $count == $args[ 'input_value' ] ) {
			$selected = 'selected';
		} else {
			$selected = '';
		}
 
		$options .= '<option value="' . $count . '"' . $selected . '>' . $count . '</option>';
 
	}
 
	$html = '<div class="quantity"><select name="' . $args[ 'input_name' ] . '">' . $options . '</select></div>';
 
	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
 
}




/*
* link the quantity of one product to the quantity of another
*/

add_action( 'template_redirect', 'truejb_sync_cart_quantities', 25 );
 
function truejb_sync_cart_quantities() {
 
	// if cart is empty, then do nothing
	if ( WC()->cart->is_empty() ) {
		return;
	}
 
	// ID of the target product, the binding will be based on its quantity
	$product_id = 32;
 
	// we run a cycle of all products in the cart and check the target
	foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		// if the product is a target, then we find out its quantity and throw it into a variable
		if ( $product_id === $cart_item[ 'product_id' ] ) {
			$qty = $cart_item[ 'quantity' ];
			break;
		}
	}
 
	// if the quantity variable does not exist, then do nothing
	if ( empty( $qty ) ) {
		return;
	}
 
	// set the same quantity value for all other products
	foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( $product_id !== $cart_item[ 'product_id' ] ) {
			WC()->cart->set_quantity( $cart_item_key, $qty );
		}
	}
}




/*
* Display the number of items in the cart
*/

add_action( 'woocommerce_after_cart_item_name', 'truejb_product_in_stock_in_cart', 25, 2 );
 
function truejb_product_in_stock_in_cart( $cart_item, $cart_item_key ) {
 
	$product = $cart_item[ 'data' ];
 
	if ( $product->backorders_require_notification() && $product->is_on_backorder( $cart_item['quantity'] ) ) {
		return;
	}
 
	echo wc_get_stock_html( $product );
 
}




/*
* Display a message for users who have spent a certain amount
*/

add_action( 'woocommerce_before_cart', 'true_cart_message_if_user_spent_100', 25 );
 
function true_cart_message_if_user_spent_100() {
 
	// do nothing if the user is not authorized
	if( ! is_user_logged_in() ) {
		return;
	}
 
	// now we add a check of how much the user has spent
	if ( 100 < wc_get_customer_total_spent( get_current_user_id() ) ) {
		echo '<div class="woocommerce-info">Congratulations, you've unlocked the coupon! Use the code <i><b>VLBLEUSR5</b></i> to get 5% off everything.</div>';
   	}
 
}




/*
* add "Empty Cart" button
*/

add_action( 'woocommerce_cart_actions', 'true_empty_cart_btn' );
 
function true_empty_cart_btn(){
 
	echo '<a class="button" href="' . WC()->cart->get_cart_url() . '?empty-cart">Empty Cart</a>';
 
}
 
add_action( 'init', 'true_empty_cart' );
function true_empty_cart() {
 
	if ( isset( $_GET[ 'empty-cart' ] ) ) {
		WC()->cart->empty_cart();
	}
 
}




/*
* change the text of the "Confirm order" button
*/

add_filter( 'woocommerce_order_button_text', 'truejb_order_button_text' );
 
function truejb_order_button_text( $button_text ) {
	return 'Order';
}




/*
* rename fields on checkout page
*/

add_filter( 'woocommerce_checkout_fields', 'truejb_fio_field', 25 );
 
function truejb_fio_field( $fields ) {
 
	// first rename the Name field
	$fields[ 'billing' ][ 'billing_first_name' ][ 'label' ] = 'Full Name';
	// add a placeholder to the Name field
	$fields[ 'billing' ][ 'billing_first_name' ][ 'placeholder' ] = 'Your full first and last Name';
	//remove last name field
	unset( $fields[ 'billing' ][ 'billing_last_name' ] ); // last name
	// also change the class of the field so that it becomes full width
	$fields[ 'billing' ][ 'billing_first_name' ][ 'class' ][ 0 ] = 'form-row-wide';
 
	return $fields;
 
}




/*
* Change the text "Already bought?" on the checkout page
*/

add_filter( 'woocommerce_checkout_login_message', 'truejb_checkout_login_message', 25 );
 
function truejb_checkout_login_message() {
 
	return 'We recommend you to login, it will obviously be more convenient for you!';
 
}




/*
* Displaying a field on the checkout page depending on the completion of another
*/

function wp_footer() {

jQuery( function( $ ) {
 
	$('#billing_company').keyup(function() {
		if ( $(this).val().length == 0 ) {
			$('#billing_phone_field').hide();
		} else {
			$('#billing_phone_field').show();
		}
	});
 
} );
	do_action( 'wp_footer' );
}




/*
* Adding additional paid options to the checkout page
*/

// Adding radio buttons
add_action( 'woocommerce_review_order_before_payment', 'truejb_checkout_options', 25 );
 
function truejb_checkout_options() {
 
	// first get object from sessions
	$selected = WC()->session->get( 'gift_wrap' );
	// if empty, then set the value to 0
	$selected = empty( $selected ) ? '0' : $selected;
 
	// display radio buttons
	echo '<div id="truemisha-checkout-radio"><h3>Gift wrap</h3>';
 
	woocommerce_form_field(
		'gift_wrap',
		array(
			'type' => 'radio',
			'class' => array( 'form-row-wide', 'update_totals_on_change' ),
			'options' => array(
				'0' => 'Normal',
				'10' => 'Velvet (1$)',
				'1000' => 'From gold (10$)',
			),
		),
		$selected 
	);
 
	echo '</div>';
 
}
 
// recalculate the order and adding the fee, if necessary
add_action( 'woocommerce_cart_calculate_fees', 'truejb_radio_choice_fee', 25 );
 
function truejb_radio_choice_fee( $cart ) {
 
	// we do nothing in the admin panel and if not an AJAX request
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
 
	// get data from sessions
	$value = WC()->session->get( 'gift_wrap' );
 
	// add the corresponding fee
	if ( $value ) {
		$cart->add_fee( 'Gift wrap', $value );
	}
 
}
 
// save the radio button selection in session
add_action( 'woocommerce_checkout_update_order_review', 'truejb_set_session' );
 
function truejb_set_session( $posted_data ) {
 
	parse_str( $posted_data, $output );
 
	if ( isset( $output[ 'gift_wrap' ] ) ){
		WC()->session->set( 'gift_wrap', $output[ 'gift_wrap' ] );
	}
 
}




/*
* Add an email address verification field to the checkout page
*/

//  Adding a field
add_filter( 'woocommerce_checkout_fields' , 'truejb_confirm_email_field', 25 );
 
function truejb_confirm_email_field( $fields ) {
 
	// assign the first email field an additional class
	$fields[ 'billing' ][ 'billing_email' ][ 'class' ] = array( 'form-row-first' );
 
	$fields[ 'billing' ][ 'billing_email_2' ] = array(
		'label' => 'Confirm email',
		'required' => true,
		'class' => array( 'form-row-last' ),
		'clear' => true,
		'priority' => 990,
	);
 
	return $fields;
}

//  Checking that both email fields are the same
add_action( 'woocommerce_after_checkout_validation', 'truejb_emails_match', 25, 2 );
 
function truejb_emails_match( $fields, $errors ){
 
	if ( $fields[ 'billing_email' ] !== $fields[ 'billing_email_2' ] ){
		$errors->add( 'validation', 'Email addresses don't match!' );
	}
 
}




/*
* Prohibit checkout if the total weight of the items in the cart is too large
*/

add_action( 'woocommerce_after_checkout_validation', 'truejb_validate_weight', 25, 2 );
 
function truejb_validate_weight( $data, $errors ) {
 
	if ( WC()->cart->cart_contents_weight > 99 ) {
		$errors->add( 
			'validation', 
			'The weight of your goods is too large, it is more than the allowable 99kg' 
		);
	}
 
}




/*
* move labels inside fields on checkout page
*/

add_filter( 'woocommerce_checkout_fields', 'true_labels_as_placeholders', 25 );
 
function true_labels_as_placeholders( $checkout_fields ) {
 
	// for each section of fields
	foreach ( $checkout_fields as $section => $section_fields ) {
		// for each field inside the section
		foreach ( $section_fields as $section_field => $section_field_settings ) {
			// consider required fields
			if( ! empty( $checkout_fields[ $section ][ $section_field ][ 'required' ] ) ) {
				$checkout_fields[ $section ][ $section_field ][ 'label' ] .= ' *';
			} 
			// and labels starting to be placeholders
			$checkout_fields[ $section ][ $section_field ][ 'placeholder' ] = $checkout_fields[ $section ][ $section_field ][ 'label' ];
			$checkout_fields[ $section ][ $section_field ][ 'label' ] = '';
		}
	}
	// returns result
	return $checkout_fields;
}




/*
* Disappearing error messages on the Checkout page
*/

add_action( 'wp_enqueue_scripts', 'true_checkout_error_fade_out', 25 );
 
function true_checkout_error_fade_out() {
 
	// if not on checkout page, then do nothing
	if( ! is_checkout() ) {
		return;
	}
 
	// if you wish, than can check the order payment page from your personal account
	// if( is_wc_endpoint_url( 'order-pay' ) ) {
	// 	return;
	// }
 
	wc_enqueue_js( "
		$( document.body ).on( 'checkout_error', function(){
			setTimeout( function(){
				$('.woocommerce-error').fadeOut( 300 );
			}, 2000);
		})
	" );
 
}




/*
* add product images to the Checkout page
*/

add_filter( 'woocommerce_cart_item_name', 'true_checkout_product_images', 25, 2 );
 
function true_checkout_product_images( $name, $cart_item ) {
 
	// if not on checkout page, then do nothing
	if ( ! is_checkout() ) {
		return $name;
	}
 
	$product = $cart_item[ 'data' ];
	$image = $product->get_image( array( 50, 50 ), array( 'class' => 'alignleft' ) );
 
	// combine image with product name
	return $image . $name;
 
}

