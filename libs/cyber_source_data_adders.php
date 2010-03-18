<?php
/**
 * Methods used to add data directly to the CyberSource Data Source.
 *
 * @package CyberSource
 * @subpackage CyberSource.libs
 */
class CyberSourceDataAdders extends Object {

/**
 * CyberSource data source object.
 *
 * @var CyberSourceSource
 * @access public
 */
	public $dataSource = null;

/**
 * Add address information to this request. Address information should include:
 *
 * - firstName
 * - lastName
 * - street1
 * - street2
 * - city
 * - state
 * - postalCode
 * - country
 * - email
 *
 * @param $address array of address information
 * @param $shipping true if this is a shipping address, otherwise false
 * @access public
 */
	public function addAddress($address, $shipping = false) {
		$this->dataSource->data = array_merge(array(
			($shipping ? 'shipTo' : 'billTo') => $address,
		), $this->dataSource->data);
	}

/**
 * Add authorization service to the request.
 *
 * @access public
 */
	public function addAuthService() {
		$this->dataSource->data = array_merge(array(
			'ccAuthService' => array('run' => 'true'),
		), $this->dataSource->data);
	}

/**
 * Add business rules (ignore AVS/CV, etc.)
 *
 * @access public
 */
	public function addBusinessRules() {
		$data = array();

		if ($this->dataSource->config['ignoreAvs']) {
			$data['ignoreAVSResult'] = 'true';
		}

		if ($this->dataSource->config['ignoreCv']) {
			$data['ignoreCVResult'] = 'true';
		}

		if (!empty($data)) {
			if (!isset($this->dataSource->data['businessRules'])) {
				$this->dataSource->data['businessRules'] = array();
			}
			$this->dataSource->data['businessRules'] = array_merge($data, $this->dataSource->data['businessRules']);
		}
	}

/**
 * Capture an authorized transaction.
 *
 * @param $requestId authorized request ID
 * @param $requestToken token for authorization
 * @access public
 */
	public function addCaptureService($requestId, $requestToken) {
		$this->dataSource->data = array_merge(array(
			'ccCaptureService' => array(
				'run' => 'true',
				'authRequestID' => $requestId,
				'authRequestToken' => $requestToken,
			),
		), $this->dataSource->data);
	}

/**
 * Add subscription creation service to the request based on an authorization.
 *
 * @access public
 */
	public function addCreateFromAuthService($requestId, $requestToken) {
		if (!isset($this->dataSource->data['paySubscriptionCreateService'])) {
			$this->dataSource->data['paySubscriptionCreateService'] = array();
		}

		$data = array(
			'run' => 'true',
			'paymentRequestID' => $requestId,
			'paymentRequestToken' => $requestToken,
		);

		$this->dataSource->data['paySubscriptionCreateService'] = array_merge($data, $this->dataSource->data['paySubscriptionCreateService']);
	}

/**
 * Add subscription creation service to the request.
 *
 * @access public
 */
	public function addCreateService() {
		$this->dataSource->data = array_merge(array(
			'paySubscriptionCreateService' => array('run' => 'true'),
		), $this->dataSource->data);
	}

/**
 * Add credit card information to this request.
 *
 * Credit card data should be as follows:
 *
 * - number (valid credit card number; required)
 * - month (1-12, month credit card expires; required)
 * - year (full year credit card expires; required)
 * - csc (credit card security code; include for verification checks)
 * - type (numeric, or "visa", "mastercard", "american_express", "discover", "diners_club", "jcb")
 *
 * @param $card card information
 * @access public
 */
	public function addCreditCard($card) {
		if (!isset($this->dataSource->data['card'])) {
			$this->dataSource->data['card'] = array();
		}

		$data = array(
			'accountNumber' => $card['number'],
			'expirationMonth' => str_pad($card['month'], 2, "0", STR_PAD_LEFT),
			'expirationYear' => $card['year'],
		);

		if (isset($card['csc']) && !$this->dataSource->config['ignoreCv']) {
			$data['cvNumber'] = $card['csc'];
		}

		if (isset($card['type'])) {
			$types = array(
				'visa' => 1,
				'mastercard' => 2,
				'american_express' => 3,
				'discover' => 4,
				'diners_club' => 5,
				'jcb' => 7,
			);

			if (isset($types[$card['type']])) {
				$card['type'] = $types[$card['type']];
			}

			$data['cardType'] = str_pad($card['type'], 3, "0", STR_PAD_LEFT);
		}

		$this->dataSource->data['card'] = array_merge($data, $this->dataSource->data['card']);
	}

/**
 * Add credit service to this transaction.
 *
 * @param $requestId authorized request ID
 * @param $requestToken token for authorization
 * @access public
 */
	public function addCreditService($requestId = false, $requestToken = false) {
		$data = array('ccCreditService' => array(
			'run' => 'true',
		));

		if ($requestId) {
			$data['captureRequestID'] = $requestId;
		}

		if ($requestId) {
			$data['captureRequestToken'] = $requestToken;
		}

		$this->dataSource->data = array_merge($data, $this->dataSource->data);
	}

/**
 * Add a single item to this request.
 *
 * Item data should be as follows:
 *
 * - code (optional)
 * - name (required if you use a non-default product code)
 * - sku (required if you use a non-default product code)
 * - quantity (optional; numeric)
 * - tax (amount of tax to add to your request)
 * - price (price of item)
 *
 * @param $item details for one item
 * @access public
 */
	public function addItem($item) {
		$data = array(
			'productCode' => isset($item['code']) ? $item['code'] : 'default',
			'quantity' => isset($item['quantity']) ? $item['quantity'] : 1,
		);

		if (isset($item['name']) && !empty($item['name'])) {
			$data['productName'] = $item['name'];
		}

		if (isset($item['sku']) && !empty($item['sku'])) {
			$data['productSKU'] = $item['sku'];
		}

		if (isset($item['tax']) && !empty($item['tax'])) {
			$data['taxAmount'] = $item['tax'];
		}

		if (isset($item['price']) && $item['price'] >= 0) {
			$data['unitPrice'] = $item['price'];
		}

		if (!isset($this->dataSource->data['item'])) {
			$this->dataSource->data['item'] = array();
		}
		$this->dataSource->data['item'][] = $data;
	}

/**
 * Add an array of items to this request. Each item should conform to the
 * layout described in "items".
 *
 * @param $items array list of items
 * @access public
 * @see CyberSourceSource::addItem
 */
	public function addItems($items) {
		if (!isset($items[0])) {
			$items = array($items);
		}
		
		foreach ($items as $item) {
			$this->dataSource->addItem($item);
		}
	}

/**
 * Add merchant information to data.
 *
 * @access public
 */
	public function addMerchantData($orderId = false) {
		if ($orderId) {
			$this->dataSource->data['merchantReferenceCode'] = $orderId;
		}

		if (!isset($this->dataSource->data['merchantReferenceCode'])) {
			$this->dataSource->data['merchantReferenceCode'] = String::uuid();
		}

		$this->dataSource->data = array_merge(array(
			'merchantID' => $this->dataSource->config['merchantId'],
			'clientLibrary' => 'PHP',
			'clientLibraryVersion' => phpversion(),
			'clientEnvironment' => php_uname(),
		), $this->dataSource->data);
	}

/**
 * Add services for a complete purchase request.
 *
 * @access public
 */
	public function addPurchaseService() {
		$this->dataSource->data = array_merge(array(
			'ccAuthService' => array('run' => 'true'),
			'ccCaptureService' => array('run' => 'true'),
		), $this->dataSource->data);
	}

/**
 * Add appropriate purchase totals to data.
 *
 * @param $total grand total including tax
 * @access public
 */
	public function addPurchaseTotals($total = 0) {
		if (!isset($this->dataSource->data['purchaseTotals'])) {
			$this->dataSource->data['purchaseTotals'] = array();
		}

		$data = array(
			'currency' => $this->dataSource->config['defaultCurrency'],
		);

		if ($total > 0) {
			$data['grandTotalAmount'] = $total;
		}

		$this->dataSource->data['purchaseTotals'] = array_merge($data, $this->dataSource->data['purchaseTotals']);
	}

/**
 * Add information about an _existing_ recurring subscription.
 *
 * @param $identification string identifying this subscription
 * @access public
 */
	public function addRecurringSubscriptionInfo($subscriptionId, $cancel = false, $includeAmount = false) {
		if (!isset($this->dataSource->data['recurringSubscriptionInfo'])) {
			$this->dataSource->data['recurringSubscriptionInfo'] = array();
		}

		$data = array(
			'subscriptionID' => $subscriptionId,
		);

		if ($cancel) {
			$data['status'] = 'cancel';
			$includeAmount = true;
		}

		if ($includeAmount) {
			$data['amount'] = '0.00';
		}

		$this->dataSource->data = array_merge($data, $this->dataSource->data['recurringSubscriptionInfo']);
	}

/**
 * Add information about a _new_ recurring subscription.
 *
 * @access public
 */
	public function addRecurringSubscription() {
		if (!isset($this->dataSource->data['recurringSubscriptionInfo'])) {
			$this->dataSource->data['recurringSubscriptionInfo'] = array();
		}

		$data = array(
			'frequency' => 'on-demand',
		);

		$this->dataSource->data = array_merge($data, $this->dataSource->data['recurringSubscriptionInfo']);
	}

/**
 * Add subscription retrieval service to the request.
 *
 * @access public
 */
	public function addRetrieveService() {
		$this->dataSource->data = array_merge(array(
			'paySubscriptionRetrieveService' => array('run' => 'true'),
		), $this->dataSource->data);
	}

/**
 * Trigger tax service.
 *
 * @access public
 */
	public function addTaxService() {
		if (!isset($this->dataSource->data['taxService'])) {
			$this->dataSource->data['taxService'] = array();
		}

		$data = array(
			'run' => 'true',
		);

		if (!empty($this->dataSource->config['nexus'])) {
			$data['nexus'] = $this->dataSource->config['nexus'];
		}

		if (!empty($this->dataSource->config['sellerRegistration'])) {
			$data['sellerRegistration'] = $this->dataSource->config['sellerRegistration'];
		}

		$this->dataSource->data['taxService'] = array_merge($data, $this->dataSource->data['taxService']);
	}

/**
 * Add subscription update service to the request.
 *
 * @access public
 */
	public function addUpdateService() {
		$this->dataSource->data = array_merge(array(
			'paySubscriptionUpdateService' => array('run' => 'true'),
		), $this->dataSource->data);
	}

/**
 * Void a captured transaction.
 *
 * @param $requestId authorized request ID
 * @param $requestToken token for authorization
 * @access public
 */
	public function addVoidService($requestId, $requestToken) {
		$this->dataSource->data = array_merge(array(
			'voidService' => array(
				'run' => 'true',
				'authRequestID' => $requestId,
				'authRequestToken' => $requestToken,
			),
		), $this->dataSource->data);
	}

/**
 * Constructor.
 *
 * @param array $config
 * @access private
 */
	public function __construct($dataSource) {
		$this->dataSource = $dataSource;
	}

}
?>