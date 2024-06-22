<?php

class SHPC_Shipping_Order
{
    function generateRandomString($length = 10) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) 
        {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    function retrieve_booking_details($shipment_response_id,$authorization_header,$order_id)
    {
        
        echo '<p>Shipment Booked with key: '.$shipment_response_id.'</p>';
        
        // Get tracking number and shipment label pdf
        // get shipment/id
        $get_shipment_url = "https://external-api.freightcom.com/shipment/".$shipment_response_id;
        // initiate cURL session 
        $ch_get_shipment = curl_init($get_shipment_url);
        // Set cURL options for a GET request
        curl_setopt($ch_get_shipment, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_get_shipment, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: ' . $authorization_header
        ));
        
        // Execute cURL session and get the result
        $get_shipment_result = curl_exec($ch_get_shipment);
        
         // Check for cURL errors
        if (curl_errno($ch_get_shipment)) 
        {
            echo 'Curl error: ' . curl_error($ch_get_shipment);
        }
        
        // Decode the JSON response
        $get_shipment_response = json_decode($get_shipment_result, true);
        
        $tracking_numbers = $get_shipment_response['shipment']['tracking_numbers'][0];
        // get the confirmation number:
        $pickup_confirmation_number = $get_shipment_response['shipment']['pickup_confirmation_number'] ==''?" N/A" : $get_shipment_response['shipment']['pickup_confirmation_number'] ;
        // get the shipping toal and format it.
        $save_actual_price = $get_shipment_response['shipment']['rate']['total']['value'];
        $formatted_save_actual_price = number_format($save_actual_price / 100, 2);
  
        $actual_shipping_price = get_post_meta($order_id, 'actual_shipping_price', true);
        
        // save the actual_shipping_price for order
        if ($actual_shipping_price === "" || $actual_shipping_price === NULL || $actual_shipping_price === "0.00" || $actual_shipping_price == false) {
            //Add tracking - getting variables from the response to the wc shippoing tracking
            $provider = $get_shipment_response['shipment']['rate']['carrier_name'];
            // var_dump('$provider: '.$provider);
            $expected_ship_date = $get_shipment_response['shipment']['details']['expected_ship_date'];
            $date_string = sprintf("%04d-%02d-%02d", $expected_ship_date["year"], $expected_ship_date["month"], $expected_ship_date["day"]);
            $formatted_date = date('Y-m-d', strtotime($date_string));
            // var_dump($formatted_date);
            $custom_url = $get_shipment_response['shipment']['tracking_url'];
            // var_dump('$custom_url: '.$custom_url);
            // check and revoke wc function to add shipping tracking
             if ( function_exists( 'wc_st_add_tracking_number' )) {
                	wc_st_add_tracking_number( $order_id, $tracking_numbers, $provider, $date_shipped, $custom_url );
                	echo '<br class="clear" /><br/>';
                	echo'Shipment Tracking is added to this order automatically.';
                }
            update_post_meta( $order_id, 'actual_shipping_price',$formatted_save_actual_price );
        }
        
        // Get label:
        // all the available labels:
        $All_labels = $get_shipment_response['shipment']['labels'];
        
        $shipment_label = null;
        $target_label = null;

        foreach ($All_labels as $label) {
            if ($label['format'] === 'pdf' && $label['size'] === 'letter') {
                $target_label = $label;
                break; 
            }
        }
        // Now $target_label contains the desired label if found
        if ($target_label !== null) {
            // Process $target_label
            $shipment_label = $target_label['url'];

        } else {
            'Error: PDF label not found.';
        }
    
        
        echo '<p>Tracking Number: '.$tracking_numbers.'</p>';
        echo '<p>Pickup Confirmation Code:'.$pickup_confirmation_number.'</p>';
         
        echo '<a href="'.$shipment_label.'" class="button button-primary" target="_blank">Get Label</a>';
        
        // Close cURL session
        curl_close($ch_get_shipment);
        
    }
    
}

?>