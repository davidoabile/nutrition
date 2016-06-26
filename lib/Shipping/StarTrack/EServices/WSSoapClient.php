<?PHP

 namespace Shipping\StarTrack\Eservices;
 use SoapClient, SoapVar, SoapHeader; 

/* Calling sequence (use in place of SoapClient):

  require_once WSSecurity.php;
  $wsdl = "WSDL address";
  $oSC = new WSSoapClient($wsdl, $arguments);
  $oSC->__setUsernameToken('username', 'passphrase');
  $params = array(    ); 		// The service parameters
  $result=$oSC->__soapCall('method_name', $params);
 */

class WSSoapClient extends SoapClient {

    private $username;
    private $password;
    /**
     *
     * @var \StarTrack\StarTrack $starTrack 
     */
    protected $starTrack = null;
// Generates a WS-Security header
    private function WsSecurityHeader() {

        // Use PasswordText authentication
        $created = gmdate('Y-m-d\TH:i:s\Z');
        $nonce = mt_rand();
        $authentication = '
                            <wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                            <wsse:UsernameToken wsu:Id="UsernameToken-1" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                                <wsse:Username>' . $this->username . '</wsse:Username>
                                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' .
                                            $this->password . '</wsse:Password>
                                <wsse:Nonce>' . base64_encode(pack('H*', $nonce)) . '</wsse:Nonce>
                                <wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' . $created . '</wsu:Created>
                               </wsse:UsernameToken>
                            </wsse:Security>
                            ';
      
        $authValues = new SoapVar($authentication, XSD_ANYXML);
        $header = new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authValues, true);

        return $header;
    }

    public function setStarTrack(\StarTrack\StarTrack $starTrack ){
        $this->starTrack = $starTrack;
        return $this;
    }

    // Sets a username and passphrase
    public function __setUsernameToken($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    // Overrides the original method, adding the security header

    public function __soapCall($function_name, $arguments, $options = NULL, $input_headers = NULL, &$output_headers = NULL) {
        try {
            $result = parent::__soapCall($function_name, $arguments, $options, $this->WsSecurityHeader());
            return $result;
        } catch (SoapFault $e) {
            throw new SoapFault($e->faultcode, $e->faultstring, NULL, $e->detail);
        }
    }

    public function __doRequest($request, $location, $action, $version) {
        if ($this->starTrack->getDisplaySoapRequests()) { // Display SOAP request XML prior to call for debugging? Driven by parameter setting in CustomerConnect.php.
            echo '<p>*** Request XML Prior to __doRequest ***</p>'; // Yes
            echo "<p> " . htmlspecialchars($request) . " </p>";
        }
        // var_dump($location); exit;
        if ($this->starTrack->getSslForce() === false) { 
            return parent::__doRequest($request, $location, $action, $version); // No, use default SSL/TLS
        } else {
            return $this->forceSslVersion($request, $location, $action, $this->starTrack->getSslForce());
        }
    }

    private function forceSslVersion($request, $location, $action, $forcedSslVersion) {
    // Executes request while forcing the SSL version
    // Used when unable to connect to server using default SSL/TLS version
    // Returns response object
        
        $h = curl_init($location);  // Init with URL
        curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($h, CURLOPT_HTTPHEADER, Array("SOAPAction: $action", "Content-Type: text/xml; charset=utf-8"));
        curl_setopt($h, CURLOPT_POSTFIELDS, $request);
        curl_setopt($h, CURLOPT_SSLVERSION, 3);
        curl_setopt( $h, CURLOPT_SSL_VERIFYHOST, true );				// Omit validation of the StarTrack server's 
        // Verisign SSL certificate (not recommended)
       
        $caBundle = $this->starTrack->getBasePath() . '/wsdl/cacert.crt';      // Filespec for list of root CAs in user-inacessible directory
        curl_setopt($h, CURLOPT_CAINFO, $caBundle);      // On Windows, cURL needs to be told about Verisign root cert
        $response = curl_exec($h);          // Perform SOAP call
        if (empty($response)) {
           throw new \SoapFault('CURL Error: ' . curl_error($h), curl_errno($h));
        }
        curl_close($h);
        return $response;
    }

}
