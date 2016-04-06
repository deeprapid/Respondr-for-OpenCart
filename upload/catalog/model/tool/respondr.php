<?php

class ModelToolRespondr extends Model {
	
	/* Variables defined to be used later in the code */
	/* ---------------------------------------------------------------------------------------- */
	private $respondr_url;	// Respondr installation URL.
	private $respondr_site_id;		// The Site ID for the site in Respondr.
	private $respondr_enable;		// The Site ID for the site in Respondr.
						
	/* ---------------------------------------------------------------------------------------- */	
	

	// Function to set various things up
	// Not 100% certain where most efficient to run, so just blanket running before each big block of API code
	// Called internally by other functions
	private function init() {
		// Load config data
		$this->load->model('setting/setting');
				
		$this->model_setting_setting->getSetting('respondr');		
			
		$this->respondr_enable = $this->config->get('respondr_enable');
		
		if ($this->respondr_enable) {
			// If mod enabled then load everything else up.
			$this->respondr_url = $this->config->get('respondr_url');
			$this->respondr_site_id = $this->config->get('respondr_site_id');
					
			$this->load->model('catalog/category');
			$this->load->model('catalog/product');
			$this->load->model('account/order');
			$this->load->model('account/customer');
		}
	}

	private function sanitizeString($string) {
		$string = str_replace("\r\n", '<br>', $string);
		$string = str_replace("\n", '<br>', $string);
		$string = str_replace("\r", '<br>', $string);
		$string = str_replace("\t", '', $string);
		$string = str_replace("\"", '\\"', $string);
		$string = str_replace("&quot;", '\\"', $string);
		$string = html_entity_decode($string);
		$string = str_replace("&#39;", '\'', $string);
		$string = str_replace("&deg;", '°', $string);

		// since an ambersand seems to kill the upload, we remove the remaining ones for now.
		$string = str_replace("&", '', $string);

		return $string;
	}
	
	// Track a page view
	private function trackPageView() {

		return '_raq.push(["trackPageView", {' . "\n" .
				'pageTitle: document.title' . "\n" .
			'}]);' . "\n";
		
	}

	// track a contact
	private function saveContact() {

		/* If the product ID isn't found, then this is a regular page view */
		if (isset($this->session->data['customer_id'])) {
			$customer_id = $this->session->data['customer_id'];

			$customer_info = $this->model_account_customer->getCustomer($customer_id);

			return '_raq.push(["saveContact", {' . "\n" .
					'email: "' . $customer_info['email'] . '",' . "\n" .
					'firstName: "' . $customer_info['firstname'] . '",' . "\n" .
					'lastName: "' . $customer_info['lastname'] . '",' . "\n" .
					'phone: "' . $customer_info['telephone'] . '"' . "\n" .
				'}]);' . "\n";

		} else {
			return '';
		}
		
	}

	// Track product view
	private function trackProductView() {

		/* Get the Product info */
		if (isset($this->request->get['product_id'])) {
			// Read the product ID from the GET variable
			$product_id = $this->request->get['product_id'];
			
			// Look up the product info using the product ID					
			// Uses function from the catalog/product model
			$product_info = $this->model_catalog_product->getProduct($product_id);
			
			// Get the individual pieces of info
			if ($product_info['sku'] !== '') {
				$respondr_sku = $product_info['sku'];
			} else {
				$respondr_sku = $product_id;
			}
			
			$respondr_name = $product_info['name'];
			$respondr_description = $this->sanitizeString($product_info['description']);
			$respondr_image = 'http://" + window.location.host + "/image/' . $product_info['image'];
			$respondr_price = (string)$product_info['price'];
	
			// Return the javascript text to insert into footer	
			return '_raq.push(["trackProductView", {' . "\n" .
					'externalId: "' . $this->request->get['product_id'] . '",' . "\n" .
					'sku: "' . $respondr_sku . '",' . "\n" .
					'name: "' . $respondr_name . '",' . "\n" .
					'categories: "",' . "\n" .
					'price: "' . $respondr_price . '",' . "\n" .
					'imageUrl: "' . $respondr_image . '",' . "\n" .
					'desc: "' . $respondr_description . '"' . "\n" .
				'}]);' . "\n";

		} else {
			return '';
		}
		
	}
	
	// Tracks a cart update
	public function trackEcommerceCartUpdate() {	

		$this->init();
		$returnString = '';
		
		if ($this->cart->hasProducts()) {	
			
			// Read all the info about items in the cart
			$cart_info = $this->cart->getProducts();
			
			// For product in the cart...
			foreach ($cart_info as $cart_item) {
				// Get the info for this product ID					
				$product_id = $cart_item['product_id'];

				// Uses function from the catalog/product model
				$product_info = $this->model_catalog_product->getProduct($product_id);
				
				if ($product_info['sku'] !== '') {
					$respondr_sku = $product_info['sku'];
				} else {
					$respondr_sku = $product_id;
				}
				$respondr_name = $product_info['name'];
				$respondr_description = $this->sanitizeString($product_info['description']);
				$respondr_image = 'http://" + window.location.host + "/image/' . $product_info['image'];
				$respondr_price = (string)$product_info['price'];

				$returnString .= '_raq.push(["updateEcommerceItem", {' . "\n" .
						'externalId: "' . $product_id . '",' . "\n" .
						'sku: "' . $respondr_sku . '",' . "\n" .
						'name: "' . $respondr_name . '",' . "\n" .
						'categories: "",' . "\n" .
						'price: "' . $respondr_price . '",' . "\n" .
						'imageUrl: "' . $respondr_image . '",' . "\n" .
						'desc: "' . $respondr_description . '",' . "\n" .
						'qty: "' . $cart_item['quantity'] . '"' . "\n" .
					'}]);' . "\n";

			}
		}

		return $returnString;
	}

	// Tracks a Site Search
	public function trackSiteSearch() {
		
		$this->init();
		
		if (isset($this->request->get['search'])) {
			// If on a search page, return a bit of javascript to set the number of results on respondr.
			return '_raq.push(["trackSiteSearch", {' . "\n" .
                    'searchKeyword: "' . $this->request->get['search'] . '",' . "\n" .
				'}]);' . "\n";

		} else {
			return '';
		}

	}

	// Tracks an order 
	public function trackEcommerceOrder($order_id) {
	
		$this->init();
		$returnString = '';

		if ($order_id !== '' || isset($this->session->data['last_order_id'])) {
			$order_id = ($order_id !== '') ? $order_id : $this->session->data['last_order_id'];
			$order_info = $this->model_account_order->getOrder($order_id);
			$order_info_products = $this->model_account_order->getOrderProducts($order_id);
			$order_info_totals = $this->model_account_order->getOrderTotals($order_id);

			$returnString .= '_raq.push(["saveContact", {' . "\n" .
					'email: "' . $order_info['email'] . '",' . "\n" .
					'firstName: "' . $order_info['firstname'] . '",' . "\n" .
					'lastName: "' . $order_info['lastname'] . '",' . "\n" .
					'phone: "' . $order_info['telephone'] . '"' . "\n" .
				'}]);' . "\n";

			// Add ecommerce items for each product in the order before tracking
			foreach ($order_info_products as $order_product) {
				// Get the info for this product ID
				$product_id = $order_product['product_id'];

				$product_info = $this->model_catalog_product->getProduct($product_id);
				
				if ($product_info['sku'] !== '') {
					$respondr_sku = $product_info['sku'];
				} else {
					$respondr_sku = $product_id;
				}
				$respondr_name = $product_info['name'];
				$respondr_description = $this->sanitizeString($product_info['description']);
				$respondr_image = 'http://" + window.location.host + "/image/' . $product_info['image'];
				$respondr_price = (string)$product_info['price'];

				$returnString .= '_raq.push(["updateEcommerceItem", {' . "\n" .
						'externalId: "' . $product_id . '",' . "\n" .
						'sku: "' . $respondr_sku . '",' . "\n" .
						'name: "' . $respondr_name . '",' . "\n" .
						'categories: "",' . "\n" .
						'price: "' . $respondr_price . '",' . "\n" .
						'imageUrl: "' . $respondr_image . '",' . "\n" .
						'desc: "' . $respondr_description . '",' . "\n" .
						'qty: "' . $order_product['quantity'] . '"' . "\n" .
					'}]);' . "\n";
			}
			
			// Set everything to zero to start with
			$order_shipping = 0;
			$order_subtotal = 0;
			$order_taxes = 0;
			$order_grandtotal = 0;
			$order_discount = 0;
			
			// Find out shipping / taxes / total values
			foreach ($order_info_totals as $order_totals) {
				switch ($order_totals['code']) {
					case "shipping":
						$order_shipping += $order_totals['value'];
						break;
					case "sub_total":
						$order_subtotal += $order_totals['value'];
						break;
					case "tax":
						$order_taxes += $order_totals['value'];
						break;
					case "total":
						$order_grandtotal += $order_totals['value'];
						break;
					case "coupon":
						$order_discount += $order_totals['value'];
						break;
					case "voucher":
						$order_discount += $order_totals['value'];
						break;
					default:
						$this->log->write("Respondr OpenCart mod: unknown order total code '" .
						$order_totals['code'] . "'.");
						break;
				}
			}

			$returnString .= '_raq.push(["trackEcommerceOrder", {' . "\n" .
					'id: "' . $order_id . '",' . "\n" .
					'total: "' . $order_grandtotal . '",' . "\n" .
					'subTotal: "' . $order_subtotal . '",' . "\n" .
					'tax: "' . $order_taxes . '",' . "\n" .
					'shipping: "' . $order_shipping . '",' . "\n" .
					'discount: "' . $order_discount . '"' . "\n" .
				'}]);' . "\n";
		}

		return $returnString;
	}
	
	// Returns the Javascript to place at the page footer
	public function getFooterText() {
		
		$this->init();
		
		$respondr_footer = ' ';
		
		if ($this->respondr_enable) {
			$respondr_footer .= '<!-- Respondr -->' . "\n";

			$respondr_footer .= '<script type="text/javascript">' . "\n" . 
			        'var _raq = _raq || [];' . "\n" . 
			        '_raq.push([\'trackSession\', "' . $this->respondr_site_id . '"]); // SiteId' . "\n" . 
			        '(function() {' . "\n" . 
			            'var u=(("https:" == document.location.protocol) ? "https" : "http") + "://analytics.respondr.io/static/";' . "\n" . 
			            'var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";' . "\n" . 
			            'g.defer=true; g.async=true; g.src=u+"respondr.js"; s.parentNode.insertBefore(g,s);' . "\n" . 
			        '})();' . "\n"; 

			// doesn't not count as a page view
			$respondr_footer .= $this->saveContact();
			$respondr_footer .= $this->trackEcommerceCartUpdate();
			$respondr_footer .= $this->trackEcommerceOrder('');

			// tracks page views
			if ($this->trackProductView()) {
				$respondr_footer .= $this->trackProductView();

			} else if ($this->trackSiteSearch()) {
				$respondr_footer .= $this->trackSiteSearch();

			} else {
				$respondr_footer .= $this->trackPageView();
			}
				
			$respondr_footer .= '</script>' . "\n" . 
			    '<!-- End Respondr Code -->' . "\n";

		} else {
			$respondr_footer .= '<!-- Respondr -->' . "\n" .
					'<!-- Respondr not enabled! Enter site Id on admin site and enable it :) -->' . "\n" .
					'<script type="text/javascript">' . "\n" . 
			        'console.log("Respondr not enabled! Enter site Id on admin site and enable it :)");' . "\n" .
					'</script>' . "\n" . 
			    	'<!-- End Respondr Code -->' . "\n";
		}

		return $respondr_footer;
	}
	
	
}
?>
