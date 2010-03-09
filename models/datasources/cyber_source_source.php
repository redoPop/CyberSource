<?php
/**
 * CyberSource SOAP API Datasource for CakePHP
 *
 * Requires PHP 5.2.1, libxml2 2.6.23, openssl 0.9.8d with SOAP extensions
 * enabled in php.ini. CyberSource's SOAP Toolkits document has up-to-date
 * info on how to make sure your configuration meets these requirements:
 * http://www.cybersource.com/support_center/implementation/downloads/soap_api/
 *
 * @author joe bartlett (xo@jdbartlett.com)
 * @package CyberSource
 * @subpackage CyberSource.models.datasources
 */

/**
 * CyberSource datasource.
 *
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
 * Connection status.
 *
 * @var boolean
 * @access public
 */
	public $connected = false;

/**
 * Description.
 *
 * @var string
 * @access public
 */
	public $description = 'CyberSource DataSource';

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
		'ignoreAvs' => true, # disregard Address Verification results and continue processing
		'ignoreCv' => true, # disregard Card Verification results and continue processsing
		'nexus' => '', # states where you have a physical presence, for tax purposes (e.g., "WI CA QC")
		'vatRegNumber' => '', # VAT registration number for tax purposes
		
		// Other optional configuration settings:
		'test' => false, # use test server instead of creating transactions live
		'useAppendices' => null, # augment results with information in appendices
		'version' => '1.26', # API version
		'wsdl' => '', # force custom WSDL
	);

/**
 * Close SOAP connection.
 *
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
 * Parse result object returned by CyberSourceSoapClient.
 *
 * @param $result Result
 * @return Result
 * @access public
 */
	public function parseResult($result) {
		
		if (isset($result->ccAuthReply) && $this->config['useAppendices']) {
			
			// Process address verification response code
			if (isset($result->ccAuthReply->avsCode)) {
				$result->ccAuthReply->avsDescription = CyberSourceAppendices::avsDescription($result->ccAuthReply->avsCode);
			}
			
			// Process card verification response code
			if (isset($result->ccAuthReply->cvCode)) {
				$result->ccAuthReply->cvDescription = CyberSourceAppendices::cvDescription($result->ccAuthReply->cvCode);
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
					trigger_error("CuberSource Error: CyberSource returned unrecognized Reason code: \"{$result->reasonCode}\"", E_USER_WARNING);
				}
			}
			
			if ($result->reasonCode != '100') {
				trigger_error("CybserSource Error: CyberSource denied request. Reason: {$result->reasonCode}" .
					(isset($result->reasonDescription) ? ': '. $result->reasonDescription : ' ') . " - Request ID: {$result->requestID}",
					E_USER_NOTICE
				);
			}
		}
		
		return $result;
	}

/**
 * DataSource query abstraction.
 *
 * @return mixed Result on success, false on failure
 * @access public
 */
	public function query() {
		$params = func_get_args();
		
		$method = array_shift($params);
		if (count($params) && is_array($params[0])) {
			$params[0] = $params[0][0];
		}
		
		return call_user_func_array(array(&$this, $method), $params);
	}

/**
 * Run a transaction through CyberSource.
 *
 * @param $data array
 * @return mixed Result on success, false on failure
 */
	public function runTransaction($data) {
		if (!$this->connected) {
			trigger_error("CybserSource Error: Cannot call SOAP service--connection is closed!", E_USER_ERROR);
			return false;
		}
		
		if (!is_array($data)) {
			trigger_error("CybserSource Error: Invalid \$data type supplied for SOAP query. Must be array!", E_USER_ERROR);
			return false;
		}
		
		$data = array_merge(array(
			'merchantID' => $this->config['merchantId'],
			'clientLibrary' => 'PHP',
			'clientLibraryVersion' => phpversion(),
			'clientEnvironment' => php_uname(),
		), $data);
		
		try {
			$result = $this->client->runTransaction($data);
		} catch (SoapFault $fault) {
			trigger_error('CybserSource Error: Error in SOAP request: '.$fault->faultstring, E_USER_WARNING);
		}
		
		if ($result) {
			$result = $this->parseResult($result);
		}
		
		return $result;
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
		
		App::import('Core', 'CyberSource.CyberSourceSoapClient');
		$this->connect();
	}

}
?>