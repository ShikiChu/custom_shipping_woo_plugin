<<<<<<< HEAD
# custom_shipping_woo_plugin
=======
Plugin Development - Shipping Options

Introduction:
This plugin facilitates two key functionalities: 
providing real-time shipment rates on the checkout and subtotal pages, 
and enabling Sharehaus administrators to process shipments directly from the order edit page.

Real-Time Shipment Rates on Checkout/Subtotal Page:
This feature integrates a shipping method within the WooCommerce shipping tab. 
Upon enabling the method, the plugin retrieves real-time rates from the Freightcom API. 
While clients won't see the carrier name, they can view service options and corresponding rates. 
***
Customization, such as adding/removing carrier services or adjusting calculations, requires modifications to the order_shipping_method.php file.
***

Shipment Processing on the Order Edit Page:
Sharehaus administrators can efficiently manage shipments from a single location—the order page.

Usage:
To utilize this feature, users must first establish a connection to the shipper's API in the backend and then click 'update.' Subsequently, all shipping information is automatically populated. 
Once connected, users can view real-time rates and delivery timelines. 
You can select a service, schedule a pickup if desired, and proceed to book the shipment. 
Upon refreshing the page, tracking numbers are displayed. It's crucial to refresh the entire page to ensure visibility of tracking information. 
Once tracking numbers are visible, users can generate shipping labels, which automatically update the order with tracking information and the actual shipping price. 
After completing the shipment, users should disconnect from the shipper and update the page accordingly.

***
For any modifications or cancellations to the shipment, users must access Freightcom as the plugin does not currently support these functionalities. 
Modifications to the shipment functionality should be made within the order_shipping_edit.php file.
For API documentation: https://developer.freightcom.com/
***


Author: Ken Chu
Email: chu00075@algonquinlive.com
>>>>>>> master
