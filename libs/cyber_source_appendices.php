<?php
/**
 * CyberSource appendices for debug info.
 *
 * @package CyberSource
 * @subpackage CyberSource.libs
 */
class CyberSourceAppendices extends Object {

/**
 * Lookup table pairing authorization factor codes with descriptions.
 *
 * @var array
 * @access public
 */
	public $authFactorDescriptions = array(
		"J" => "Billing and shipping address do not match.",
		"M" => "Cost of the order exceeds the maximum transaction amount.",
		"N" => "Nonsensical input in the customer name or address fields.",
		"O" => "Obscenities in the order form.",
		"U" => "Unverifiable billing or shipping address.",
		"X" => "Order does not comply with the USA PATRIOT Act.",
	);

/**
 * Lookup table pairing authorization factor codes with descriptions.
 *
 * @var array
 * @access public
 */
	public $avsDescriptions = array(
		"A" => "Partial match: Street address matches, but 5- and 9-digit postal codes do not match.",
		"B" => "Partial match: Street address matches, but postal code not verified.",
		"C" => "No match: Street address and postal code do not match.",
		"D" => "Match: Street address and postal code match.",
		"E" => "Invalid: AVS data is invalid or AVS is not allowed for this card type.",
		"F" => "Partial match: Card member’s name does not match, but postal code matches.",
		"G" => "Not supported: Non-U.S. issuing bank does not support AVS.",
		"H" => "Card member’s name does not match. Street address and postal code match.",
		"I" => "No match: Address not verified.",
		"K" => "Partial match: Card member’s name matches but billing address and billing postal code do not match.",
		"L" => "Partial match: Card member’s name and billing postal code match, but billing address does not match.",
		"N" => "No match: Street address and postal code do not match. Check cardholder's name if American Express.",
		"O" => "Partial match: Card member’s name and billing address match, but billing postal code does not match.",
		"P" => "Partial match: Postal code matches, but street address not verified.",
		"R" => "System unavailable: System unavailable.",
		"S" => "Not supported: U.S.-issuing bank does not support AVS.",
		"T" => "Partial match: Card member’s name does not match, but street address matches.",
		"U" => "System unavailable: Address information unavailable.",
		"V" => "Partial match: Card member’s name, billing address, and billing postal code match.",
		"W" => "Partial match: Street address does not match, but 9-digit postal code matches.",
		"X" => "Match: Exact match. Street address and 9-digit postal code match.",
		"Y" => "Match: Exact match. Street address and 5-digit postal code match.",
		"Z" => "Partial match: Street address does not match, but 5-digit postal code matches.",
		"1" => "Not supported: CyberSource AVS is not supported for this processor or card type.",
		"2" => "Invalid: The CyberSource processor returned an unrecognized value for the AVS response.",
	);

/**
 * Lookup table pairing card verification codes with descriptions.
 *
 * @var array
 * @access public
 */
	public $cvDescriptions = array(
		"D" => "The transaction was determined to be suspicious by the issuing bank.",
		"I" => "The CVN failed the processor's data validation check.",
		"M" => "The CVN matched.",
		"N" => "The CVN did not match.",
		"P" => "The CVN was not processed by the processor for an unspecified reason.",
		"S" => "The CVN is on the card but was not included in the request.",
		"U" => "Card verification is not supported by the issuing bank.",
		"X" => "Card verification is not supported by the card association.",
		"1" => "Card verification is not supported for this processor or card type.",
		"2" => "An unrecognized result code was returned by the processor for the card verification response.",
		"3" => "No result code was returned by the processor.",
	);

/**
 * Lookup table pairing Simple Order API response reason codes with
 * descriptions.
 *
 * @var array
 * @access public
 */
	public $reasonDescriptions = array(
		"100" => "Successful transaction.",
		"101" => "The request is missing one or more required fields.",
		"102" => "One or more fields in the request contains invalid data.",
		"104" => "The merchantReferenceCode sent with this authorization request matches the merchantReferenceCode of another authorization request that you sent in the last 15 minutes.",
		"150" => "Error: General system failure.",
		"151" => "Error: The request was received but there was a processing timeout.",
		"152" => "Error: The request was received, but a service did not finish running in time.",
		"201" => "The issuing bank has questions about the request.",
		"202" => "Expired card. You might also receive this if the expiration date you provided does not match the date the issuing bank has on file.",
		"203" => "General decline of the card. No other information provided by the issuing bank.",
		"204" => "Insufficient funds in the account.",
		"205" => "Stolen or lost card.",
		"207" => "Issuing bank unavailable.",
		"208" => "Inactive card or card not authorized for card-not-present transactions.",
		"210" => "The card has reached the credit limit.",
		"211" => "Invalid card verification number.",
		"220" => "The processor declined the request based on a general issue with the customer’s account.",
		"221" => "The customer matched an entry on the processor’s negative file.",
		"222" => "The customer’s bank account is frozen.",
		"231" => "Invalid account number.",
		"232" => "The card type is not accepted by the payment processor.",
		"233" => "General decline by the processor.",
		"234" => "There is a problem with your CyberSource merchant configuration.",
		"235" => "The requested amount exceeds the originally authorized amount.",
		"236" => "Processor failure.",
		"238" => "The authorization has already been captured.",
		"239" => "The requested transaction amount must match the previous transaction amount.",
		"240" => "The card type sent is invalid or does not correlate with the credit card number.",
		"241" => "The request ID is invalid.",
		"242" => "You requested a capture through the API, but there is no corresponding, unused authorization record.",
		"243" => "The transaction has already been settled or reversed.",
		"246" => "The capture or credit is not voidable because the capture or credit information has already been submitted to your processor.",
		"247" => "You requested a credit for a capture that was previously voided.",
		"250" => "Error: The request was received, but there was a timeout at the payment processor.",
		"256" => "The authorization request was approved by the issuing bank but declined by CyberSource based on your Smart Authorization settings.",
	);

/**
 * Look up a Smart Authorization factor code.
 *
 * @param string $code
 * @return mixed string description of code if valid, otherwise false
 * @access public
 */
	public function authFactorDescription($code) {
		$_this =& CyberSourceAppendices::getInstance();
		return $_this->_lookup('authFactorDescriptions', $code);
	}

/**
 * Look up an Address Verification code.
 *
 * @param string $code
 * @return mixed string description of code if valid, otherwise false
 * @access public
 */
	public function avsDescription($code) {
		$_this =& CyberSourceAppendices::getInstance();
		return $_this->_lookup('avsDescriptions', $code);
	}

/**
 * Look up a Card Verification result code.
 *
 * @param string $code
 * @return mixed string description of code if valid, otherwise false
 * @access public
 */
	public function cvDescription($code) {
		$_this =& CyberSourceAppendices::getInstance();
		return $_this->_lookup('cvDescriptions', $code);
	}

/**
 * Returns a singleton instance of the CyberSourceAppendices class.
 *
 * @return CyberSourceAppendices instance
 * @access public
 */
	public function &getInstance($boot = true) {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new CyberSourceAppendices();
		}
		return $instance[0];
	}

/**
 * Look up a reason code for a Simple Order API response.
 *
 * @param string $code
 * @return mixed description of response code if valid, otherwise false
 * @access public
 */
	public function reasonDescription($code) {
		$_this =& CyberSourceAppendices::getInstance();
		return $_this->_lookup('reasonDescriptions', $code);
	}

/**
 * Abstract method to find matches in lookup tables.
 *
 * @param string $table
 * @param string $code
 * @return mixed description of response code if valid, otherwise false
 * @access protected
 */
	protected function _lookup($table, $code) {
		$_this =& CyberSourceAppendices::getInstance();

		if (isset($_this->{$table}[$code])) {
			$code = $_this->{$table}[$code];
		} else {
			$code = false;
		}
		return $code;
	}

}
?>