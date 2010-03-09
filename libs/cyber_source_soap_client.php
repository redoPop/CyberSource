<?php
/**
 * CyberSource verison of SoapClient.
 *
 * @package CyberSource
 * @subpackage CyberSource.libs
 */
class CyberSourceSoapClient extends SoapClient {

/**
 * CyberSource-specific configuration settings.
 *
 * @var array
 * @access protected
 */
	protected $_cyberSourceOptions = null;

/**
 * Constructor.
 *
 * @param array $cyberSourceOptions
 * @param array $soapOptions
 * @access private
 */
	public function __construct($wsdl, $cyberSourceOptions = null, $soapOptions = null) {
		parent::__construct($wsdl, $soapOptions);
		
		$this->_cyberSourceOptions = array_merge(array(
			'merchantID' => '',
			'transactionKey' => '',
		), is_array($cyberSourceOptions) ? $cyberSourceOptions : array());
	}

/**
 * Transport layer for SOAP request.
 *
 * @param string $request
 * @param string $location
 * @param string $action
 * @param string $version
 * @param int $one_way
 * @access private
 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		$soapHeader = sprintf("<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>%s</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">%s</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>", $this->_cyberSourceOptions['merchantID'], $this->_cyberSourceOptions['transactionKey']);
		
		$requestDOM = new DOMDocument('1.0');
		$soapHeaderDOM = new DOMDocument('1.0');
		
		try {
			$requestDOM->loadXML($request);
			$soapHeaderDOM->loadXML($soapHeader);
			$node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
			$requestDOM->firstChild->insertBefore(
			$node, $requestDOM->firstChild->firstChild);
			
			$request = $requestDOM->saveXML();
		} catch (DOMException $e) {
			trigger_error("CybserSource Error: Couldn't add token: " . $e->code, E_USER_WARNING);
		}
		
		return parent::__doRequest($request, $location, $action, $version);
	}

}
?>