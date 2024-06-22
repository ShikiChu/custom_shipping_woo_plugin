<?php
/**
 * /**
 * Plugin Name: Custom Shipping for WooCommerce
 * Description: Integrate with the Shippers API to give dynamically shipping rates and make shipping order easier. *Update accurate rates and add tracking
 * Version: 1.0.1
 * Author: Ken Chu
 * Email: chu00075@algonquinlive.com
 * 
 */
 
 
 //API INFO:
 /*
Developer Resource Link:- https://developer.freightcom.com/
 */

include_once "shipping_order.php";
include_once "my_shipping_method.php";
//API key
$authorization_header = "secret***token";


add_action( 'woocommerce_admin_order_data_after_order_details', 'display_shipping_options' );



function display_shipping_options( $order )
{
    //option to on / off api conneciton, defalut is off
    $is_connected = get_post_meta( $order->id, 'api_connection_check', true );
    if(empty($is_connected))
    {
        $is_connected = "0"; 
        echo'<br><p style="color:red" "weight:bold";>Shippers Disconnected.</p>';
    }
    
    // radio to connect to api
    woocommerce_wp_radio( array(
					'id' => 'api_connection_check',
					'label' => 'Connect to Shippers.',
					'value' => $is_connected,
					'options' => array(
						'0' => 'No',
						'1' => 'Yes'
					),
					'style' => 'width:16px', 
					'wrapper_class' => 'form-field-wide' 
				) );
        
        echo '<br class="clear" />';

		echo '<h3>Shipping options: </h3>';
			/*
			 * get all the meta data values of shipping to:
			 */
            $address_line_1 = $order->shipping_address_1;
            $address_line_2 = $order->shipping_address_2;
            $city = $order->shipping_city;
            $region = $order->shipping_state;
            $postal_code = $order->shipping_postcode;
            $country = $order->shipping_country;
            $unit_number = '';
            $phone = $order->billing_phone;
            $email = $order->billing_email;
            $ClientName = $order->shipping_first_name." ".$order->shipping_last_name;

    

            // display the values automatically
            echo '<div>';
            echo '<p>Destination: </p>';
        
                woocommerce_wp_text_input( array(
                    'id' => 'shipping_name',
                    'label' => 'Company Name:',
                    'value' => $ClientName,
                    'style' => 'width:150%'
                ) );
                
                woocommerce_wp_text_input( array(
                    'id' => 'shipping_address_line_1',
                    'label' => 'Address 1:',
                    'value' => $address_line_1,
                    'style' => 'width:150%'
                ) );
                
                woocommerce_wp_text_input( array(
                    'id' => 'shipping_address_line_2',
                    'label' => 'Address 2:',
                    'value' => $address_line_2,
                    'style' => 'width:150%'
                ) );
                
                woocommerce_wp_text_input( array(
                    'id' => 'shipping_region',
                    'label' => 'Province:',
                    'value' => $region,
                    'style' => 'width:150%'
                ) );
                
                woocommerce_wp_text_input( array(
                    'id' => 'shipping_postal_code',
                    'label' => 'Postal Code:',
                    'value' => $postal_code,
                    'style' => 'width:150%'
                ) );
                
                woocommerce_wp_text_input( array(
                    'id' => 'shipping_country',
                    'label' => 'Country:',
                    'value' => $country,
                    'style' => 'width:150%'
                ) );
                
             echo '</div>';
             // End of Destination Display
             
             
            // Contact
            echo '<br class="clear" />';
            echo '<div>';
            echo '<p><b>Contact: </b></p>';
            
                woocommerce_wp_text_input( array(
                    'id' => 'phone',
                    'label' => 'Phone:',
                    'value' => $phone,
                    'style' => 'width:150%'
                ) );
                
                woocommerce_wp_text_input( array(
                    'id' => 'email',
                    'label' => 'Email:',
                    'value' => $email,
                    'style' => 'width:150%'
                ) );
                
            echo '</div>';
            // End of Contact Display
            
            
            $itemCount = 0;
            foreach ($order->get_items() as $item) 
            {
                $product = wc_get_product($item->get_product_id());
                $product_length = $product->get_length();
                $product_width = $product->get_width();
                $product_height = $product->get_height();
                $product_weight = $product->get_weight();
                $itemCount++;
            }
            
            
            // package dimension and weight:
            // get the  dimension and weight
            $package_weight = get_post_meta( $order->id, 'package_weight', true );
            $package_length = get_post_meta( $order->id, 'package_length', true );
            $package_width = get_post_meta( $order->id, 'package_width', true );
            $package_height = get_post_meta( $order->id, 'package_height', true );
            $package_dimensions = get_post_meta($order->id, '_select',true);
            
        

            echo '<br class="clear" />';
            echo '<div>';
            echo '<p><b>Dimensions & Weight: </b></p>';
        
            
            // the following _select,package_weight,package_length,package_width,package_height should match to _select_quantity.
            // Dropdown list menu
                woocommerce_wp_select( 
                array( 
                	'id'      => '_select', 
                	'label'   => __( 'Dimensions in (L,W,H)', 'woocommerce' ), 
                	'value' => $package_dimensions,
                	'options' => array(
                	    ''   => __( 'Select Dimensions', 'woocommerce' ),
                	    '5x5x5' => __( '5x5x5', 'woocommerce' ),
                		'5x5x9' => __( '5x5x9', 'woocommerce' ),
                		'10x10x5'   => __( '10x10x5', 'woocommerce' ),
                		'10x10x7' => __( '10x10x7', 'woocommerce' ),
                		'10x10x10' => __( '10x10x10', 'woocommerce' ),
        				'13x13x9' => __( '13x13x9', 'woocommerce' ),
                		'13x13x13' => __( '13x13x13', 'woocommerce' ),
                		'16x16x16' => __( '16x16x16', 'woocommerce' ),
        				'18x18x18' => __( '18x18x18', 'woocommerce' )
                		),
                	'style' => ' width:150%;  text-align:center;'
                	)
                );
                
                // extract the number from dimension: 
                preg_match_all('/\d+/', $package_dimensions, $matches);
                
                // put values into L, W, H
                $package_length = $matches[0][0]; 
                $package_width = $matches[0][1];
                $package_height = $matches[0][2];
                            
                woocommerce_wp_text_input( array(
                    'id' => 'package_weight',
                    'label' => 'Package Weight (lbs):',
                    'value' => $package_weight,
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '1',
                        'min' => '0'
                    ),
                    'style' => 'width:150%;  text-align:center;'
                ) );
            
                // radio option for scheduling the pickup.
                // pickup time is always today 15-17:00.
                $pickup_schedule = get_post_meta( $order->id, 'pickup_schedule_radio', true );				
                if(empty($pickup_schedule)){
                    $pickup_schedule = "0";
                }
    				
                // ask for pickup schedule
                 woocommerce_wp_radio( array(
                    'id' => 'pickup_schedule_radio',
                    'label' => 'Schedule A Pick Up Time?',
                    'value' => $pickup_schedule,
                    'options' => array(
                        "0" => 'No',
                        "1" => 'Yes'
                    ),
                    'style' => 'width:16px', 
                    'wrapper_class' => 'form-field-wide' 
                ) );
    
            // End of dimension Display
        $shipment_response_id = get_post_meta( $order->get_id(), 'shipment_response', true );
        
        if($is_connected == '1')
        {
            // Section for JSON formatting
            //Origin
            $origin = array(
                'name' => 'FCC Logistics',
                'address' => array(
                    'address_line_1' => '48 Jamie Avenue',
                    'address_line_2' => '',
                    'unit_number' => '',
                    'city' => 'Nepean',
                    'region' => 'ON',
                    'country' => 'CA',
                    'postal_code' => 'K2E 6T6'
                ),
                'residential' => false,
                'tailgate_required' => false,
                'instructions' => '',
                'contact_name' => 'Peter Comino',
                'phone_number' => array(
                    'number' => '6137617788',
                    'extension' => ''
                ),
                'email_addresses' => array()
            );

            // destination address 
            // Create an array with the desired structure
            $destination = array(
                    'name' => $ClientName,
                    'address' => array(
                        'address_line_1' => $address_line_1,
                        'address_line_2' => $address_line_2,
                        'unit_number' => $unit_number,
                        'city' => $city,
                        'region' => $region,
                        'postal_code' => $postal_code,
                        'country' => $country,
                    ),
                    'residential' => true,
                    'tailgate_required' => false,
                    'instructions' => "",
                    'contact_name' => $ClientName,
                    'phone_number' => array(
                        'number' => $phone,
                        'extension' => ""
                    ),
                    'email_addresses' => array(
                        $email
                    ),
                    'ready_at' => array(
                        'hour' => 15,
                        'minute' => 0
                    ),
                    'ready_until' => array(
                        'hour' => 17,
                        'minute' => 0
                    ),
                    'signature_requirement' => 'not-required'
                        
                    
                );
                
                
            // Get the current date
            $currentDate = new DateTime();
            
            // Extract year, month, and day components
            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');
            
            $expected_ship_date = array(
                'year' => (int)$year,
                'month' => (int)$month,
                'day' => (int)$day,
            );
                
                
                $packaging_properties = array(
                    'dangerous_goods' => false,
                    'packages' => array(
                        array(
                            'measurements' => array(
                                'weight' => array(
                                    'unit' => 'lb',
                                    'value' => (float)$package_weight
                                ),
                                'cuboid' => array(
                                    'unit' => 'in',
                                    'l' => (float)$package_length,
                                    'w' => (float)$package_width,
                                    'h' => (float)$package_height
                                )
                            ),
                            'description' => 'coffee'
                        )
                    )
                );
            
            
                
                 $details = array(
                'origin' => $origin,
                'destination' => $destination,
                'expected_ship_date' => $expected_ship_date,
                'packaging_type' => 'package',
                'packaging_properties' => $packaging_properties
                );
                
                //$details = json_encode($details, JSON_PRETTY_PRINT);
                
                
                // data to api 
                $data = array(
                    'services' => array("ups.standard","purolatorfreight.standard","purolatorcourier.ground","purolatorcourier.standard","purolatorcourier.express","ups.expedited","ups.ground", "fedex.standard"),
                    'excluded_services' => array(),
                    'details' => $details
                );
                
                $data = json_encode($data, JSON_PRETTY_PRINT);

                
            $order_id = $order->id;
            if(empty($shipment_response_id))
            {
                
                //POST to get the rate from freightcom
                $post_rate_url = "https://external-api.freightcom.com/rate";
                // Authorization header value
                $authorization_header = "secret***token";
                
                $ch = curl_init($post_rate_url);
                
                // Set cURL options
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: ' . $authorization_header
                ));
                
                
                // Execute cURL session and get the result
                $result = curl_exec($ch);
                
                // Check for cURL errors
                if (curl_errno($ch)) {
                    echo 'Curl error: ' . curl_error($ch);
                }
                
                
                // Close cURL session
                curl_close($ch);
                
                // Decode the JSON response
                $response = json_decode($result, true);

                // Check if "request_id" exists in the response
                if (isset($response['request_id'])) 
                {
                    // Print the value of "request_id"
                    $rate_id = $response['request_id'];
                } else {
                    // Handle the case where "request_id" is not present in the response
                    echo 'Request ID not found in the response.';
                }
                
                // GET
                // get the rate by id
                $get_rate_url = "https://external-api.freightcom.com/rate/".$rate_id;
                
                // Initiate cURL session
                $ch_get = curl_init($get_rate_url);
                
                // Set cURL options for a GET request
                curl_setopt($ch_get, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_get, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: ' . $authorization_header
                ));
                
                // Initialize a counter for attempts
                $max_attempts = 5; // Set the maximum number of attempts
                $attempts = 0;
                
                do {
                    // Execute cURL session and get the result
                    $get_result = curl_exec($ch_get);
                
                    // Check for cURL errors
                    if (curl_errno($ch_get)) {
                        echo 'Curl error: ' . curl_error($ch_get);
                        break; // exit the loop on error
                    }
                
                    // Decode the JSON response
                    $response = json_decode($get_result, true);
                
                    // Check if the API is done processing
                    if (isset($response['status']['done']) && $response['status']['done']) {
                        // Print the result
                        //echo $get_result;
                        break; // exit the loop when done
                    }
                
                    // Increment the attempts counter
                    $attempts++;
                
                    // Sleep for a short duration before making the next attempt
                    sleep(5); // Adjust the sleep duration as needed
                
                } while ($attempts < $max_attempts);
                
                // Close cURL session
                curl_close($ch_get);
            
            
                echo '<br class="clear" />';
                echo'<h4 style="color:green;" >Shippers Connected</h4>
                        <h3>Instant Rate:</h3> 
                        </br>';

            
       
                echo '<button class="button button-primary" name="get_new_rate">Get New Rate</button>';
   
                    // Check if the response has rates
                if (isset($response['rates']) && is_array($response['rates'])) 
                {
                    $shipping_rates = $response['rates'];
                
                    // Construct options array for WooCommerce radio input
                    $options = array();
                    foreach ($shipping_rates as $rate) 
                    {
                        $service_id = $rate['service_id'];
                        $carrier_name = $rate['carrier_name'];
                        $service_name = $rate['service_name'];
                        $total_value = $rate['total']['value'];
                        $currency = $rate['total']['currency'];
                        $transit_time_days =" - in ".$rate['transit_time_days']." day(s)";
                        
                        // Format total value as currency with cents
                        $formatted_total = number_format($total_value / 100, 2);
                
                        // Construct the option label
                        $option_label = sprintf('%s %s %s %s %s', $formatted_total, $currency, $carrier_name, $service_name,$transit_time_days );
                
                        // Add the option to the array
                        $options[$service_id] = $option_label;
                    }
                    
                
                // echo '<br class="clear" /><h3>Instant Rate:</h3> </br>';
                // echo '<button class="button button-primary" name="get_new_rate">Get New Rate</button>';
                
                    // Output the WooCommerce radio input
                    woocommerce_wp_radio(array(
                        'id' => 'selected_rate',
                        'label' => 'Choose a shipping rate',
                        'options' => $options,
                        'style' => 'width:16px',
                        'wrapper_class' => 'form-field-wide',
                    ));
                } 
                else 
                {
                    // Handle the case where there are no rates in the response
                    echo '<p style="color:red"; >Error: No shipping rates available, save and click get new rate.</p>';
                    $stopBooking = true;
                }
                
                
                 //book shipping
                echo '<br class="clear" />';
            
                if(!$stopBooking)
                {
                    echo '<br><input class="button button-primary" type="submit" name="post_shipping" value="Book Shipping" />';
                }
            
                //get data
          
                $post_shipping = get_post_meta( $order->id, 'post_shipping', true );
                $selected_rate = get_post_meta( $order->id, 'selected_rate', true );
                
                //generate the fcc unique shipping id, 8 char long
                $get_random_id = new SHPC_Shipping_Order();
                $random_id = $get_random_id->generateRandomString(8);
               
               if($pickup_schedule == '1'){
                    $pre_scheduled_pickup = false;
                }else{
                    $pre_scheduled_pickup = true;
                }
                
                //add the pickup details
                // pre_scheduled_pickup is true, all other fields are ignored. Indicates that the customer has their own arrangements with the carrier for scheduling.
                $pickup_details = array(
                    "pre_scheduled_pickup" => $pre_scheduled_pickup,
                    "date" => $expected_ship_date,
                    "ready_at" => array(
                        "hour" => 15,
                        "minute" => 0
                    ),
                    "ready_until" => array(
                        "hour" => 17,
                        "minute" => 0
                    ),
                    "pickup_location" => "48 Jamie Avenue, K2E 6T6",
                    "contact_name" => "Peter Comino",
                    "contact_phone_number" => array(
                        "number" => "6137617788",
                        "extension" => ""
                    )
                );
                json_encode($pickup_details, JSON_PRETTY_PRINT);
                
                  $book_shipping_data = array(
                    'unique_id' => 'FCC_Shipping_'.$random_id,
                    'payment_method_id' => "zoNoBnU904yiQUYdacc093wSdUmdefzv",
                    'service_id' => $selected_rate,
                     'details'=> $details,
                     'pickup_details' => $pickup_details
                    );


                $book_shipping_data_json = json_encode($book_shipping_data, JSON_PRETTY_PRINT);
                //echo $book_shipping_data_json;   
        
                // when ship button is clicked
                
                if(!empty($post_shipping))
                {
                    $shipment_response_id = get_post_meta( $order->get_id(), 'shipment_response', true );
                    
                    // post  when shipment key is not found
                    if(empty($shipment_response_id))
                    {
                        //POST 
                        $post_shipment_url = "https://external-api.freightcom.com/shipment";
                        // Authorization header value
                        $authorization_header = "secret***token";
                        
                        $ch_shipment = curl_init($post_shipment_url);
                        
                        // Set cURL options
                        curl_setopt($ch_shipment, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($ch_shipment, CURLOPT_POSTFIELDS, $book_shipping_data_json);
                        curl_setopt($ch_shipment, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch_shipment, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Authorization: ' . $authorization_header
                        ));
                        
                        
                        // Execute cURL session and get the result
                        $result_shipment = curl_exec($ch_shipment);
                        
                        // Check if cURL request returned an error
                        if ($result_shipment === false) {
                            // Handle cURL error
                            $error_message = curl_error($ch_shipment);
                            // You can log or display the error message as needed
                            echo "cURL Error: " . $error_message;
                        } else {
                            // Get HTTP status code
                            $http_status_code = curl_getinfo($ch_shipment, CURLINFO_HTTP_CODE);

                            // Check if HTTP status code is 400
                            if ($http_status_code == 400) {
                                // Handle 400 error
                                $error_response = json_decode($result_shipment, true);
                                // Extract error message from the response
                                $error_message = isset($error_response['message']) ? $error_response['message'] : 'Unknown error';
                                $error_data = isset($error_response['data']) ? $error_response['data'] : array();
                                // You can log or display the error message and data as needed
                                echo "HTTP Error 400: " . $error_message . "\n";
                                echo 'shipment has not issued.';
                                if (!empty($error_data)) {
                                    foreach ($error_data as $key => $value) {
                                        echo "$key: $value\n";
                                    }
                                }
                            }
                        }
                        
                        
                        // Close cURL session
                        curl_close($ch_shipment);
                        
                         $shipment_response = json_decode($result_shipment, true);
                        //  var_dump($shipment_response['id']);
                         // update shipment key
                        update_post_meta( $order->get_id(), 'shipment_response', wc_sanitize_textarea($shipment_response['id']));
                        header("Location: ".$_SERVER['REQUEST_URI']);
                        exit();
                    }
                    else
                    {
                        $get_booking = new SHPC_Shipping_Order();
                        $booking_details = $get_booking->retrieve_booking_details($shipment_response_id,$authorization_header,$order_id);
                    }
                    
                }
                else
                {
                    echo '<br class="clear" />';
                    echo'<p>Pending Shipment Booking</p>';
                }
            }
            else
            {
                echo '<br class="clear" />';
                echo '<p>This order shipping has been booked.</p>';
                $get_booking = new SHPC_Shipping_Order();
                // Authorization header 
                $authorization_header = "secret***token";
                $booking_details = $get_booking->retrieve_booking_details($shipment_response_id,$authorization_header,$order_id);
            }
        }
                
}


 // updating the package details.
add_action( 'woocommerce_process_shop_order_meta', 'custom_woocommerce_update_package_info' );
function custom_woocommerce_update_package_info( $order_id )
{
    // update selected carrier ratre // this is carrier and service string
    update_post_meta( $order_id, 'selected_rate', wc_sanitize_textarea( $_POST[ 'selected_rate' ] ) );
    // update when ship button is clicked
    update_post_meta( $order_id, 'post_shipping', wc_sanitize_textarea( $_POST[ 'post_shipping' ] ) );
    // update each value
    update_post_meta( $order_id, 'package_weight', wc_sanitize_textarea( $_POST[ 'package_weight' ] ) );
    // update_post_meta( $order_id, 'package_length', wc_sanitize_textarea( $_POST[ 'package_length' ] ) );
    // update_post_meta( $order_id, 'package_width', wc_sanitize_textarea( $_POST[ 'package_width' ] ) );
    // update_post_meta( $order_id, 'package_height', wc_sanitize_textarea( $_POST[ 'package_height' ] ) );
    update_post_meta($order_id, '_select',wc_sanitize_textarea($_POST['_select']));
    // update option for api connection boool
    update_post_meta($order_id, 'api_connection_check',wc_sanitize_textarea($_POST['api_connection_check']));
    //update option for pickup bool
    update_post_meta($order_id, 'pickup_schedule_radio',wc_sanitize_textarea($_POST['pickup_schedule_radio']));
}


?>