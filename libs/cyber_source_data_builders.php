<?php
/**
 * Methods used to build CyberSource requests.
 *
 * @package CyberSource
 * @subpackage CyberSource.libs
 */
class CyberSourceDataBuilders extends Object {

/**
 * Contains methods for building data.
 *
 * @var CyberSourceDataAdders
 * @access public
 */
	public $dataAdders = null;

/**
 * CyberSource data source object
 *
 * @var CyberSourceSource
 * @access public
 */
	public $dataSource = null;

/**
 * Expects the following:
 *
 * - amount
 * - token
 * - card (only if token is not provided)
 * - billTo (only if token is not provided)
 * - persist (only if token is not provided)
 *
 * @param $options
 * @access public
 */
	public function buildAuthRequest($options) {
		extract($options);

		if (isset($card)) {
			$this->buildAuthRequestFromCreditCard($options);
		} else {
			$this->buildAuthRequestFromProfile($options);
		}
	}

/**
 * Expects the following:
 *
 * - amount
 * - card
 * - billTo
 * - persist
 *
 * @param $options
 * @access public
 */
	public function buildAuthRequestFromCreditCard($options) {
		extract($options);

		$this->dataAdders->addAddress($billTo);
		$this->dataAdders->addPurchaseTotals($amount);
		$this->dataAdders->addCreditCard($card);
		$this->dataAdders->addAuthService();
		$this->dataAdders->addBusinessRules();

		if (isset($persist) && $persist) {
			$this->dataAdders->addRecurringSubscription();
			$this->dataAdders->addCreateService();
		}
	}

/**
 * Expects the following:
 *
 * - amount
 * - subscriptionId
 * - address
 * - persist
 *
 * @param $options
 * @access public
 */
	public function buildAuthRequestFromProfile($options) {
		extract($options);

		$this->dataAdders->addPurchaseTotals($amount);
		$this->dataAdders->addRecurringSubscriptionInfo($subscriptionId);
		$this->dataAdders->addAuthService();
	}

	public function buildCaptureRequest($options) {
		extract($options);
		
		$this->dataAdders->addPurchaseTotals($amount);
		$this->dataAdders->addCaptureService($requestId, $requestToken);
		$this->dataAdders->addBusinessRules();
	}

	public function buildCreditRequest($options) {
		extract($options);
		
		if (isset($subscriptionId)) {
			$this->buildCreditRequestFromProfile($options);
		} else {
			$this->buildCreditRequestFromAuthorization($options);
		}
	}

	public function buildCreditRequestFromAuthorization($options) {
		extract($options);
		
		$this->dataAdders->addPurchaseTotals($amount);
		$this->dataAdders->addCreditService($requestId, $requestToken);
	}
	
	public function buildCreditRequestFromProfile($options) {
		extract($options);
		
		$this->dataAdders->addPurchaseData($amount);
		$this->dataAdders->addRecurringSubscriptionInfo($subscriptionId);
		$this->dataAdders->addCreditService();
	}

	public function buildPurchaseRequest($options) {
		extract($options);
		
		if (isset($card)) {
			$this->buildPurchaseRequestFromCreditCard($options);
		} else {
			$this->buildPurchaseRequestFromProfile($options);
		}
	}

	public function buildPurchaseRequestFromCreditCard($options) {
		extract($options);
		
		$this->dataAdders->addAddress($billTo);
		$this->dataAdders->addPurchaseData($amount);
		$this->dataAdders->addCreditCard($card);
		$this->dataAdders->addPurchaseService();
		$this->dataAdders->addBusinessRules();
		
		if (isset($persist) && $persist) {
			$this->dataAdders->addRecurringSubscription();
			$this->dataAdders->addCreateService();
		}
	}
	
	public function buildPurchaseRequestFromProfile($options) {
		extract($options);
		
		$this->dataAdders->addPurchaseData($amount);
		$this->dataAdders->addRecurringSubscriptionInfo($subscriptionId);
		$this->dataAdders->addPurchaseService();
	}

/**
 * Expects the following:
 *
 * - requestId
 * - requestToken
 *
 * @param $options
 * @access public
 */
	public function buildRecurringSubscriptionRequest($options) {
		extract($options);

		if (isset($card)) {
			$this->buildRecurringSubscriptionRequestFromCreditCard($options);
		} else {
			$this->buildRecurringSubscriptionRequestFromAuthorization($options);
		}
	}

	public function buildRecurringSubscriptionRequestFromAuthorization($options) {
		extract($options);
		
		$this->dataAdders->addRecurringSubscription();
		$this->dataAdders->addCreateFromAuthService($requestId, $requestToken);
	}

	public function buildRecurringSubscriptionRequestFromCreditCard($options) {
		extract($options);
		
		$this->dataAdders->addAddress($billTo);
		$this->dataAdders->addPurchaseTotals();
		$this->dataAdders->addCreditCard($card);
		$this->dataAdders->addRecurringSubscription();
		$this->dataAdders->addCreateService();
	}

	public function buildRetrieveRequest($options) {
		extract($options);
		
		$this->dataAdders->addRecurringSubscriptionInfo($subscriptionId);
		$this->dataAdders->addRetrieveService();
	}

	public function buildTaxCalculationRequest($options) {
		extract($options);
		
		$this->dataAdders->addAddress($billTo);
		$this->dataAdders->addAddress($shipTo, true);
		$this->dataAdders->addItems($items);
		$this->dataAdders->addPurchaseTotals();
		$this->dataAdders->addTaxService();
		$this->dataAdders->addBusinessRules();
	}

	public function buildUnstoreRequest($options) {
		extract($options);
		
		$this->dataAdders->addRecurringSubscriptionInfo($subscriptionId, true, true);
		$this->dataAdders->addUpdateService();
	}

	public function buildUpdateRequest($options) {
		extract($options);
		
		if (isset($billTo)) $this->dataAdders->addAddress($billTo);
		if (isset($shipTo)) $this->dataAdders->addAddress($shipTo, true);
		
		$this->dataAdders->addCreditCard($card);
		$this->dataAdders->addRecurringSubscriptionInfo($subscriptionId);
		$this->dataAdders->addUpdateService();
	}

	public function buildVoidRequest($options) {
		extract($options);
	
		$this->dataAdders->addVoidService($requestId, $requestToken);
	}

/**
 * Completes a data request and executes it.
 *
 * @param $options
 * @access public
 */
	public function execute($options) {
		$this->dataAdders->addMerchantData(isset($options['orderId']) ? $options['orderId'] : false);
		return $this->dataSource->runTransaction();
	}

/**
 * Constructor.
 *
 * @param array $config
 * @access private
 */
	public function __construct($dataSource) {
		$this->dataSource = $dataSource;
		
		App::import('Core', 'CyberSource.CyberSourceDataAdders');
		$this->dataAdders = new CyberSourceDataAdders($dataSource);
	}

}
?>