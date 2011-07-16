<?php
/**
 * CyberSource SOAP API Datasource for CakePHP
 *
 * Requires PHP 5.2.1, libxml2 2.6.23, openssl 0.9.8d with SOAP extensions
 * enabled in php.ini. CyberSource's SOAP Toolkits document has up-to-date
 * info on how to make sure your configuration meets these requirements:
 *
 * http://www.cybersource.com/support_center/implementation/downloads/soap_api/
 *
 * @author joe bartlett (xo@jdbartlett.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package CyberSource
 * @subpackage CyberSource.models.datasources
 */
class CyberSourceSource extends DataSource {

/**
 * SOAP client for transactions.
 *
 * @var CyberSourceSoapClient
 * @access public
 */
	public $client = null;

/**
 * Contains methods for building data.
 *
 * @var CyberSourceDataBuilders
 * @access public
 */
	public $dataBuilders = null;

/**
 * Connection status.
 *
 * @var boolean
 * @access public
 */
	public $connected = false;

/**
 * Data for the next transaction is added to this array until the transaction
 * is ready to be sent.
 *
 * @var array
 * @access public
 */
	public $data = array();

/**
 * Description of the DataSource.
 *
 * @var string
 * @access public
 */
	public $description = 'CyberSource DataSource';

/**
 * Cached result of previous transaction.
 *
 * @var stdObject
 * @access public
 */
	public $lastResult = null;

/**
 * Base configuration settings for CyberSource connection.
 *
 * @var array
 * @access protected
 */
	public $_baseConfig = array(
		// Required configuration settings:
		'merchantId' => '',
		'transactionKey' => '',
		
		// Common configuration settings:
		'defaultCurrency' => 'USD',
		'ignoreAvs' => false, # disregard Address Verification results and continue processing
		'ignoreCv' => false, # disregard Card Verification results and continue processsing
		'nexus' => '', # states where you have a physical presence, for tax purposes (e.g., "WI CA QC")
		'sellerRegistration' => '', # your tax registration number
		
		// Other optional configuration settings:
		'test' => false, # use test server instead of creating transactions live
		'useAppendices' => null, # augment results with information in appendices
		'version' => '1.26', # API version
		'wsdl' => '', # force custom WSDL
	);

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function addSubscription($options) {
		$this->dataBuilders->buildRecurringSubscriptionRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * Expects the following:
 *
 * - orderId
 * - amount
 * - subscriptionId
 * - card (only if subscriptionId is not provided)
 * - billTo (only if subscriptionId is not provided)
 * - persist (only if subscriptionId is not provided)
 *
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function authorize($options) {
		$this->dataBuilders->buildAuthRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function calculateTax($options) {
		$this->dataBuilders->buildTaxCalculationRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function capture($options) {
		$this->dataBuilders->buildCaptureRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * Clear the data array for a new query.
 *
 * @access public
 */
	public function clear() {
		$this->data = array();
	}

/**
 * Close SOAP connection.
 *
 * @return boolean true
 * @access public
 */
	public function close() {
		$this->client = null;
		$this->connected = false;
		return true;
	}

/**
 * Initialize the SOAP client for use.
 *
 * @return boolean true if the service started successfully
 * @access public
 */
	public function connect() {
		if (empty($this->config['wsdl'])) {
			$this->_buildWsdl();
		}
		
		try {
			$this->client = new CyberSourceSoapClient($this->config['wsdl'], array(
				'merchantID' => $this->config['merchantId'],
				'transactionKey' => $this->config['transactionKey'],
			), array());
		} catch (SoapFault $fault) {
			trigger_error("CybserSource Error: Couldn't start SOAP service: " . $fault->faultstring, E_USER_WARNING);
		}
		
		if ($this->client) {
			$this->connected = true;
		}
		
		return $this->connected;
	}

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function credit($options) {
		$this->dataBuilders->buildCreditRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * Public method to retrieve result from last transaction.
 *
 * @return stdClass result object from last CyberSourceSoapClient transaction
 * @access public
 */
	public function getLastResult() {
		return $this->lastResult;
	}

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function getSubscription($options) {
		$this->dataBuilders->buildRetrieveRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * Parse result object returned by CyberSourceSoapClient and save to
 * $this->lastResult; return a small array of essential data.
 *
 * @param stdClass $result object from CyberSourceSoapClient transaction
 * @return array
 * @access public
 */
	public function parseResult($result) {
		$success = false;
		$orderId = null;
		$requestId = null;
		$requestToken = null;
		$subscriptionId = null;
		$avsCode = null;
		$cvCode = null;
		
		if (isset($result->ccAuthReply) && $this->config['useAppendices']) {
			
			// Process address verification response code
			if (isset($result->ccAuthReply->avsCode)) {
				$avsCode = $result->ccAuthReply->avsCode;
				$result->ccAuthReply->avsDescription = CyberSourceAppendices::avsDescription($avsCode);
			}
			
			// Process card verification response code
			if (isset($result->ccAuthReply->cvCode)) {
				$cvCode = $result->ccAuthReply->cvCode;
				$result->ccAuthReply->cvDescription = CyberSourceAppendices::cvDescription($cvCode);
			}
			
			// Process auth factor code
			if (isset($resul->ccAuthReply->authFactorCode)) {
				$result->ccAuthReply->authFactorDescription = CyberSourceAppendices::authFactorDescription($result->ccAuthReply->authFactorCode);
			}
		}
		
		// Process transaction request response code
		if (isset($result->reasonCode)) {
			if ($this->config['useAppendices']) {
				$result->reasonDescription = CyberSourceAppendices::reasonDescription($result->reasonCode);
				if (!$result->reasonDescription) {
					trigger_error("CyberSource Error: CyberSource returned unrecognized Reason code: \"{$result->reasonCode}\"", E_USER_WARNING);
				}
			}
			
			if ($result->reasonCode == '100') {
				$success = true;
				
				$orderId = @$result->merchantReferenceCode;
				$requestId = @$result->requestID;
				$requestToken = @$result->requestToken;
				if (isset($result->paySubscriptionCreateReply)) {
					@$subscriptionId = $result->paySubscriptionCreateReply->subscriptionID;
				}
			} else {
				trigger_error("CybserSource Error: CyberSource denied request. Reason: {$result->reasonCode}" .
					(isset($result->reasonDescription) ? ': '. $result->reasonDescription : ' ') . " - Request ID: {$result->requestID}",
					E_USER_NOTICE
				);
			}
		}
		
		// Save the result as lastResult
		$this->lastResult = $result;
		
		// Return essential information
		$parsedResult = array(
			'success' => $success,
			'message' => $result->reasonDescription,
			'orderId' => $orderId,
			'requestId' => $requestId,
			'requestToken' => $requestToken,
			'subscriptionId' => $subscriptionId,
			'avsCode' => $avsCode,
			'cvCode' => $cvCode,
		);
		
		return $parsedResult;
	}

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function purchase($options) {
		$this->dataBuilders->buildPurchaseRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * DataSource query abstraction.
 *
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function query() {
		$params = func_get_args();
		
		$method = array_shift($params);
		$params = array_shift($params);
		
		return call_user_func_array(array(&$this, $method), $params);
	}

/**
 * Run a transaction through CyberSource.
 *
 * @param array $data
 * @return mixed result array on success, false on failure
 */
	public function runTransaction($data = null) {
		if (!is_null($data)) {
			$this->data = $data;
		}
		
		if (!$this->connected) {
			trigger_error("CybserSource Error: Cannot call SOAP service--connection is closed!", E_USER_ERROR);
			return false;
		}
		
		if (!is_array($this->data)) {
			trigger_error("CybserSource Error: Invalid \$data type supplied for SOAP query. Must be array!", E_USER_ERROR);
			return false;
		}
		
		try {
			$result = $this->client->runTransaction($this->data);
		} catch (SoapFault $fault) {
			trigger_error('CybserSource Error: Error in SOAP request: '.$fault->faultstring, E_USER_WARNING);
		}
		
		$this->clear();
		
		return $this->parseResult($result);
	}

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function unsubscribe($options) {
		$this->dataBuilders->buildUnstoreRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * @param array $options
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function updateSubscription($options) {
		$this->dataBuilders->buildUpdateRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * Void a capture or credit.
 *
 * Be aware that CyberSource cannot perform voids while in test mode.
 *
 * @param array $options
 * @access public
 */
	public function void($options) {
		$this->dataBuilders->buildVoidRequest($options);
		return $this->dataBuilders->execute($options);
	}

/**
 * Build destination URL for SOAP messages based on API version number and
 * testing status provided in configuration.
 *
 * @access protected
 */
	protected function _buildWsdl() {
		if ($this->config['test']) {
			$base = 'https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor';
		} else {
			$base = 'https://ics2ws.ic3.com/commerce/1.x/transactionProcessor';
		}
		
		$this->config['wsdl'] = sprintf("%s/CyberSourceTransaction_%s.wsdl", $base, $this->config['version']);
	}

/**
 * Constructor.
 *
 * @param array $config
 * @access private
 */
	public function __construct($config = array()) {
		parent::__construct($config);
		
		if (is_null($this->config['useAppendices']) && Configure::read('debug') > 0) {
			App::import('Core', 'CyberSource.CyberSourceAppendices');
			$this->config['useAppendices'] = true;
		}
		
		App::import('Core', 'CyberSource.CyberSourceDataBuilders');
		$this->dataBuilders = new CyberSourceDataBuilders($this);
		
		App::import('Core', 'CyberSource.CyberSourceSoapClient');
		$this->connect();
	}

}