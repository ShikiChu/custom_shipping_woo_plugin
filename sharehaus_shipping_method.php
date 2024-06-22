<?php
/*

This is used to generate the real-time shipping prices on the checkout / cart pages. 
Defualt shipping carriers are: UPS, Canada Post, Purolator and Fedex
To use the shipping methods, move to WooCommerce Settings, Shipping, Add shipping methods, then choose my shipping. 
Once the shipping method is added and turned on, you will see the new options for shipping on checkout page.
*/



add_filter('woocommerce_shipping_methods', 'register_my_method');
function register_my_method($methods)
{
    $methods['my_shipping_method'] = 'WC_my_Shipping';
    return $methods;
}

// add constrctor
// Constructor
add_action('woocommerce_shipping_init', 'my_shipping_method_init');


function my_shipping_method_init(){
    
    class WC_my_Shipping extends WC_Shipping_Method {

        public function __construct( $instance_id = 0 ) {
            $this->id = 'my_shipping_method';
            $this->instance_id = absint($instance_id);
            $this->method_title = __('my Shipping');
            $this->method_description = __('my Shipping obtains the best rates and shows on the checkout pages.');
            $this->title = __('my Shipping');
            $this->supports = array(
                'shipping-zones',
                'instance-settings'
            );

            $this->instance_form_fields = array(
                'enabled' => array(
                    'title'         => __( 'Enable/Disable' ),
                    'type'             => 'checkbox',
                    'label'         => __( 'Enable my Shipping Method' ),
                    'default'         => 'yes',
                ),
                'title' => array(
                    'title'         => __( 'Method Title' ),
                    'type'             => 'text',
                    'description'     => __( 'This controls the title which the user sees during checkout.' ),
                    'default'        => __( 'my Shipping' ),
                    'desc_tip'        => true
                )
            );
            $this->enabled              = $this->get_option( 'enabled' );
            $this->title                = $this->get_option( 'title' );
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        // get the rates:
        public function calculate_shipping($package = array())
        {
            // get the total weight from cart
            $all_order_items = $package['contents'];
            $total_weight = 0;
            foreach ($all_order_items as $orderline) 
            {
                $product_weight = $orderline['data']->weight;
                $product_quantity =  $orderline['quantity'];
                
                //for each order item weight
                $each_weight = $product_weight * $product_quantity;
                
                // total weight of the order: (kg)
                $total_weight += $each_weight;
            };
            //echo '<h3> order total weight: '.$total_weight.'</h3>';
            
            //get the address 
            $destination_address_line_1 = WC()->customer->get_shipping_address_1();
            $destination_address_line_2 = WC()->customer->get_shipping_address_2();
            $destination_city = WC()->customer->get_shipping_city();
            $destination_region = WC()->customer->get_shipping_state();
            $destination_country = WC()->customer->get_shipping_country();
            $destination_postalCode = WC()->customer->get_shipping_postcode();
            
            // JSON 
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
            
            
            $destination = array(
                    'name' => 'client',
                    'address' => array(
                        'address_line_1' => $destination_address_line_1,
                        'address_line_2' => $destination_address_line_2,
                        'unit_number' => '',
                        'city' => $destination_city,
                        'region' => $destination_region,
                        'postal_code' => $destination_postalCode,
                        'country' => $destination_country,
                    ),
                    'residential' => true,
                    'tailgate_required' => false,
                    'instructions' => "",
                    'contact_name' => 'Peter Comino',
                    'phone_number' => array(
                        'number' => '6137617788',
                        'extension' => ""
                    ),
                    'email_addresses' => array(),
                    'ready_at' => array(
                        'hour' => 10,
                        'minute' => 0
                    ),
                    'ready_until' => array(
                        'hour' => 16,
                        'minute' => 30
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
                                    'unit' => 'kg',
                                    'value' => (float)$total_weight
                                ),
                                'cuboid' => array(
                                    'unit' => 'in',
                                    'l' => (float)9,
                                    'w' => (float)9,
                                    'h' => (float)9
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
                
                // data to api 
                $data = array(
                    'services' => array("ups.standard","purolatorfreight.standard","purolatorcourier.ground","purolatorcourier.standard","purolatorcourier.express","ups.expedited","ups.ground", "fedex.standard"),
                    'excluded_services' => array(),
                    'details' => $details
                );
                
                // json data to api 
                $data = json_encode($data, JSON_PRETTY_PRINT);
                
                // sending data to post a rate 
                //POST 
                // endpoint , please refer to the api documentation : 
                // https://developer.freightcom.com/#tag/rate/paths/~1rate~1%7Brate_id%7D/get
                
                $post_rate_url = "https://external-api.freightcom.com/rate";
                // for staging: 
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
                
                // To retrieve the rate from the rate-id, rate_id is generated from the post api methods above. 
                // GET
                // // get the rate by id
                // $get_rate_url = "https://customer-external-api.ssd-test.freightcom.com/rate/".$rate_id;
                
                // // Initiate cURL session
                // $ch_get = curl_init($get_rate_url);
                
                // // Set cURL options for a GET request
                // curl_setopt($ch_get, CURLOPT_RETURNTRANSFER, true);
                // curl_setopt($ch_get, CURLOPT_HTTPHEADER, array(
                //     'Content-Type: application/json',
                //     'Authorization: ' . $authorization_header
                // ));
                
                // // Initialize a counter for attempts
                // $max_attempts = 10; // Set the maximum number of attempts
                // $attempts = 0;
                
                // do {
                //     // Execute cURL session and get the result
                //     $get_result = curl_exec($ch_get);
                
                //     // Check for cURL errors
                //     if (curl_errno($ch_get)) {
                //         echo 'Curl error: ' . curl_error($ch_get);
                //         break; // exit the loop on error
                //     }
                
                //     // Decode the JSON response
                //     $response = json_decode($get_result, true);
                
                //     // Check if the API is done processing
                //     if (isset($response['status']['done']) && $response['status']['done'] == true) {
                //         // Print the result
                //         //echo $get_result;
                //         break; // exit the loop when done
                //     }
                //     var_dump($response);
                
                //     // Increment the attempts counter
                //     $attempts++;
                
                //     // Sleep for a short duration before making the next attempt
                //     sleep(1); // Adjust the sleep duration as needed
                
                // } while ($attempts < $max_attempts && $response['status']['done'] == false);
                
                // // Close cURL session
                // curl_close($ch_get);
            
            
                
                
                // URL for the GET request
                $get_rate_url = "https://external-api.freightcom.com/rate/".$rate_id;
                
                // Initialize curl_multi handle
                $multi_handle = curl_multi_init();
                
                // Initialize an array to store individual cURL handles
                $curl_handles = [];
                
                $max_attempts = 10;
                
                // Set up multiple asynchronous GET requests
                for ($i = 0; $i < $max_attempts; $i++) {
                    // Initiate cURL session for each request
                    $ch = curl_init($get_rate_url);
                
                    // Set cURL options for a GET request
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Authorization: ' . $authorization_header
                    ));
                
                    // Add cURL handle to the multi handle
                    curl_multi_add_handle($multi_handle, $ch);
                
                    // Store the cURL handle for later use
                    $curl_handles[] = $ch;
                }
                
                // Execute all requests simultaneously
                $active = null;
                do {
                    $mrc = curl_multi_exec($multi_handle, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                
                while ($active && $mrc == CURLM_OK) {
                    if (curl_multi_select($multi_handle) == -1) {
                        usleep(1);
                    }
                    do {
                        $mrc = curl_multi_exec($multi_handle, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }
                
                // Process each request's response
                $responses = [];
                foreach ($curl_handles as $ch) {
                    $response = curl_multi_getcontent($ch);
                
                    // Decode the JSON response
                    $decoded_response = json_decode($response, true);
                
                    // Check for errors
                    if (curl_errno($ch)) {
                        $decoded_response['error'] = curl_error($ch);
                    }
                
                    // Close the handle
                    curl_multi_remove_handle($multi_handle, $ch);
                    curl_close($ch);
                
                    // Store the response
                    $responses[] = $decoded_response;
                }
                
                
                // Close the multi handle
                curl_multi_close($multi_handle);
                
                // Now $responses array contains the responses of all requests
                foreach ($responses as $response) {
                    // Process each response as needed
                    if (isset($response['status']['done']) && $response['status']['done']) {
                        // Process completed response
                    } else {
                        // Handle incomplete or failed response
                    }
                }
                
             
                
                
                                    
                $rates = array();

                if (isset($response['rates']) && is_array($response['rates']))
                {
                    $shipping_rates = $response['rates'];
                
                    // Loop through each rate
                    foreach ($shipping_rates as $rate) {
                        // Get the carrier name and value
                        $carrier_name = $rate['service_name'];
                        $value = $rate['total']['value']/100;
                        $service_id = ['service_id'];
                
                        // Append the rate to the $rates array
                        $rates[] = array(
                            'id' => $this->id.'_'.$service_id,
                            'label' => $carrier_name,
                            'cost' => $value
                        );
                    }
                } 
                else {
                    // Handle the case where there are no rates in the response
                    echo '<h4> No shipping rates available.</h4>';
                }
                
                $index = 0;
                   // Add  rates at once
                foreach($rates as $rate)
                {
                    $this->add_rate(array(
                        'id' => $rate['id'].'_'.$index,
                        'label' =>$rate['label'],
                        'cost' => $rate['cost'],
                    ));
                     $index++; 
                }
                
            }
        }
    }