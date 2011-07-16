<?php
/**
 * Model for use with the CyberSource DataSource
 *
 * Feel free to use this model directly in your applications or move it to
 * your /app/models/ path and adapt it to suit your needs.
 *
 * Instead of using the multidimensional arrays the DataSource normally
 * expects, this model uses underscores in its "field names" to delineate
 * array levels, breaking them apart with the __dataToOptions method. So
 * instead of an array like this:
 *
 *   array('billTo' => array('firstName' => 'John' , ... ) , ... )
 *
 * You send simpler single level arrays like this:
 *
 *   array('billTo_firstName' => 'John' , ...)
 *
 * This makes it much easier to work with CakePHP's built-in validation.
 *
 * @author joe bartlett (xo@jdbartlett.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package CyberSource
 * @subpackage CyberSource.models
 */
class CyberSource extends AppModel {

/**
 * False to indicate no database table is in use.
 *
 * @var boolean false
 * @access public
 */
	var $useTable = false;

/**
 * The name of the DataSource connection that this Model uses.
 *
 * @var string
 * @access public
 */
	var $useDbConfig = 'cyberSource';

/**
 * List of validation rules.
 *
 * @var array
 * @access public
 */
	var $validate = array(
		'billTo_firstName' => array(
			array('rule' => 'notEmpty', 'message' => "Please enter"),
		),
		'billTo_lastName' => array(
			array('rule' => 'notEmpty', 'message' => "Please enter"),
		),
		'billTo_street1' => array(
			array('rule' => 'notEmpty', 'message' => "Where do you live?"),
		),
		'billTo_city' => array(
			array('rule' => 'notEmpty', 'message' => "Please enter"),
		),
		'billTo_state' => array(
			array('rule' => 'notEmpty', 'message' => "Please enter"),
		),
		'billTo_postalCode' => array(
			array('rule' => 'notEmpty', 'message' => "Please enter"),
		),
		'billTo_country' => array(
			array('rule' => 'notEmpty', 'message' => "Please enter"),
		),
		'billTo_email' => array(
			array('rule' => 'notEmpty', 'message' => "Please enter"),
			array('rule' => 'email', 'message' => "Must be a valid email"),
		),
		'card_month' => array(
			array('rule' => array('range', 0, 13), 'message' => 'Please select'),
		),
		'card_year' => array(
			array('rule' => '/^[0-9]{4}$/', 'message' => "Please select"),
		),
		'card_number' => array(
			array('rule' => array('cc', array('visa', 'mc', 'amex', 'disc', 'diners', 'jcb')), 'message' => 'Not a valid card number'),
		),
		'card_type' => array(
			array('rule' => array('inList', array('visa', 'mc', 'amex', 'disc', 'diners', 'jcb')), 'message' => 'Invalid card type'),
		),
		'card_csc' => array(
			array('rule' => array('minLength', 3), 'message' => "Invalid security code"),
			array('rule' => 'numeric', 'message' => "Invalid security code"),
		),
	);

/**
 * List of card types that can be used with CyberSource. (Handy for building dropdowns.)
 *
 * @var array
 * @access public
 */
	var $cardTypes = array(
		'visa' => 'VISA',
		'mc' => 'MasterCard',
		'amex' => 'American Express',
		'disc' => 'Discover',
		'diners' => 'Diners Club',
		'jcb' => 'JCB',
	);

/**
 * List of state codes. (Handy for building dropdowns.)
 *
 * @access public
 */
	var $states = array(
		'AK' => 'Alaska', 'AS' => 'American Samoa', 'AZ' => 'Arizona',
		'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado',
		'CT' => 'Connecticut', 'DE' => 'Delaware',
		'DC' => 'District of Columbia', 'FL' => 'Florida',
		'GA' => 'Georgia', 'GU' => 'Guam', 'HI' => 'Hawaii', 'ID' => 'Idaho',
		'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
		'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
		'ME' => 'Maine', 'MH' => 'Marshall Islands', 'MD' => 'Maryland',
		'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
		'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana',
		'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire',
		'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
		'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
		'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PW' => 'Palau',
		'PA' => 'Pennsylvania', 'PR' => 'Puerto Rico', 'RI' => 'Rhode Island',
		'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee',
		'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
		'VI' => 'Virgin Islands', 'VA' => 'Virginia', 'WA' => 'Washington',
		'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
	);

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function addSubscription($data) {
		return $this->query('addSubscription', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function authorize($data) {
		return $this->query('authorize', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function getSubscription($data) {
		return $this->query('getSubscription', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function unsubscribe($data) {
		return $this->query('unsubscribe', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function updateSubscription($data) {
		return $this->query('updateSubscription', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function calculateTax($data) {
		return $this->query('calculateTax', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function capture($data) {
		return $this->query('capture', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function credit($data) {
		return $this->query('credit', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function purchase($data) {
		return $this->query('purchase', $data);
	}

/**
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function void($data) {
		return $this->query('void', $data);
	}

/**
 * @return boolean true if validate operation should continue, false to abort
 * @access public
 */
	public function beforeValidate() {
		
		// Make sure card numbers are numeric before we validate them
		if (isset($this->data[$this->name]['card_number'])) {
			$this->data[$this->name]['card_number'] = preg_replace('/[^0-9]*/', '', $this->data[$this->name]['card_number']);
		}
		
		return parent::beforeValidate();
	}

/**
 * Perform a query against the CyberSourceSource DataSource.
 *
 * @param string $method name of CyberSourceSource DataSource method to request
 * @param array $data underscore-delineated array of request data
 * @return mixed result array on success, false on failure
 * @access public
 */
	public function query($method, $data) {
		$result = false;
		
		$this->data = $data;
		if ($this->validates()) {
			$options = $this->__dataToOptions();
			
			if ($options) {
				$result = parent::query($method, array($options));
				
				if (!$result['success']) {
					$this->validateLastResult();
					
					$result = false;
				}
			}
		}
		
		return $result;
	}

/**
 * Augment the Model's validation errors with information received after
 * sending a transaction to CyberSource for processing.
 *
 * @access public
 */
	public function validateLastResult() {
		$lastResult = $this->getLastResult();
		$reasonCode = $lastResult->reasonCode;
		
		switch ($reasonCode) {
			case '202': $this->validationErrors['card_month'] = "This card has expired or the expiration date is wrong."; break;
			case '203': $this->validationErrors['card_number'] = "The card was declined."; break;
			case '204': $this->validationErrors['card_number'] = "This card's account has insufficient funds."; break;
			case '205': $this->validationErrors['card_number'] = "This card was reported stolen."; break;
			case '208': $this->validationErrors['card_number'] = "This card cannot be used online."; break;
			case '205': $this->validationErrors['card_number'] = "This card has reached its credit limit."; break;
			case '211': $this->validationErrors['card_csc'] = "Invalid verification number."; break;
			case '220': $this->validationErrors['card_number'] = "This card was declined."; break;
			case '221': $this->validationErrors['card_number'] = "This card was declined."; break;
			case '222': $this->validationErrors['card_number'] = "This card's account is frozen."; break;
			case '231': $this->validationErrors['card_number'] = "That's not a valid card number!"; break;
			case '232': $this->validationErrors['card_type'] = "This card type isn't accepted."; break;
			case '240': $this->validationErrors['card_type'] = "Invalid card type."; break;
		}
	}

/**
 * Convert model-formatted data to the multi-dimensional array style required
 * by the CyberSource DataSource.
 *
 * @return array multi-dimensional array of request data
 * @access private
 */
	private function __dataToOptions() {
		if (!empty($this->data[$this->alias])) {
			$modelData = $this->data[$this->alias];
		}
		
		if (empty($modelData)) {
			$modelData = $this->data;
		}
		
		if (empty($modelData)) {
			trigger_error("CybserSource Error: no data provided", E_USER_NOTICE);
			return false;
		}
		
		$options = array();
		
		foreach ($modelData as $key => $value) {
			$key = str_replace('_', '.', $key);
			$options = Set::insert($options, $key, $value);
		}
		
		return $options;
	}

}