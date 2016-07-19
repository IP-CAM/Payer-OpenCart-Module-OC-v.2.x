<?php
/** 
 * \mainpage Payer PostAPI helper class
 * <h3>Welcome to the Payer PostAPI helper class</h3>
 * <br>Recommended is to use some of our e-commerce modules to implement this file.
 * <br>You can find the Modules in the startpaket/Modules directory and most of the popular e-commerce platforms are supported.
 * <br>If you have a system not found in the <i>Modules</i> directory you are encouraged to refer to the Payer Post API description and this API guide.
 * <br>This guide will give you information of the functions and parameters used in PostAPI and the <b><i>Payer Post API description</i></b> document will describe the flow and logic of the Post API.
 * <br>
 * <br>
 * <br>payread_post_api.php - class definition of API used to create payment transactions to Payer Financial Services AB.
 * <br>payer_plugin.php - class definition of API used to create fast checkout functionality for your shop.
 * <br>
 * <br>This file contains all the methods you will need to create and send transactions to PAYER payment gateway.
 * <br>
 * <br>This file should NOT be modified since new versions might overwrite changes made by you.
 * <br>
 * <br>Latest change: 2015-03-26
 * <br>
 * <br>Modified by: bihla
 * <br>
 * <br>Verified by: atibb
 * <br>
 * @file		payread_post_api.php
 * @author		bihla <teknik@payer.se>
 * @version		payer_php_0_2_v31
 * optimized for doxygen to make documentation
 */
class payread_post_api {
	var $myAgentId;
	var $myKeys = array ();
	var $myPayerServerUrl;
	var $myPostApiVersion;
	var $myClientVersion;
	var $myCurrency;
	var $myDescription;
	var $myHideDetails;
	var $myReferenceId;
	var $myMessage;
	var $myFreeformPurchases = array ();
	var $myCatalogPurchases = array ();
	var $mySubscriptionPurchases = array ();
	var $myInfoLines = array ();
	var $myBuyerInfo;
	var $myPaymentMethods;
	var $myRedirectBackToShopUrl;
	var $mySuccessRedirectUrl;
	var $myAuthorizeNotificationUrl;
	var $mySettleNotificationUrl;
	var $myDebugMode;
	var $myTestMode;
	var $myLanguage;
	var $myCharSet;
	var $myFirewall;
	var $myXmlData;
	var $myEncryptedXmlData;
	var $myChecksum;
	
	/**
	 * \n URL:authorize => http://www.xyzzy.se/includes/pr_callback/authorize.php?payer_testmode=false&payer_payment_type=invoice&payer_callback_type=auth&md5sum=229131F6AABC89DFC9E38610F4DA6CF3
	 * \n URL:settle => http://www.xyzzy.se/includes/pr_callback/settle.php?payer_testmode=false&payer_payment_type=invoice&payer_callback_type=settle&payer_added_fee=40&payer_payment_id=IFO@XXX60o5c72fii616hloi83n&payread_payment_id=IFO@XXX60o5c72fii616hloi83n&md5sum=28E69FA28EE10A2C40E8E30468FE3E68
	 *
	 * parameters added by payer for authorize callback:
	 * - ?payer_testmode=false
	 * - &payer_payment_type=invoice
	 * - &payer_callback_type=auth
	 * - &md5sum=229131F6AABC89DFC9E38610F4DA6CF3
	 *
	 * parameters added by Payer for settle callbak:
	 * - ?payer_testmode=false
	 * - &payer_payment_type=invoice
	 * - &payer_callback_type=settle
	 * - &payer_added_fee=40
	 * - &payer_payment_id=IFO\@XXX60o5c72fii616hloi83n
	 * - &payread_payment_id=IFO\@XXX60o5c72fii616hloi83n
	 * - &md5sum=28E69FA28EE10A2C40E8E30468FE3E68
	 */
	
	/**
	 * This is the default constructor, used by the POST API.
	 * The constructor will set the agentid, key1 and key2 here from config file PayReadConf.php
	 * You can use methods setAgent, setKey1 and setKey2 to override any hardcoded values.
	 *
	 * @return void
	 * @see PayReadConf.php
	 */
	function payread_post_api() {
		$PayRead_AgentId = '';
		$PayRead_Key1 = '';
		$PayRead_Key2 = '';
		/**
		 */
		@include ("PayReadConf.php");
		
		// Set defaults
		$this->myPostApiVersion = "payer_php_0_2_v30";
		$this->myClientVersion = null;
		$this->myAgentId = $PayRead_AgentId;
		$this->myKeys ["A"] = $PayRead_Key1;
		$this->myKeys ["B"] = $PayRead_Key2;
		$this->myPayerServerUrl = "https://secure.payer.se/PostAPI_V1/InitPayFlow";
		$this->myCurrency = "SEK";
		$this->myLanguage = "sv";
		$this->myDebugMode = "silent";
		$this->myTestMode = "false";
		$this->myCharSet = null; // Use database value as default
		$this->myFirewall = array (
				"192.168.100.1",
				"192.168.100.20",
				"79.136.103.5",
				"94.140.57.180",
				"94.140.57.181",
				"94.140.57.184" 
		);
	}
	/**
	 * DO NOT USE THIS FUNCTION UNLESS YOU REALLY UNDERSTAND HOW IT WORKS
	 *
	 * @param $agentid string
	 *        	the AgentId given by payer (required)
	 */
	function setAgentId($agentid) {
		/* DO NOT USE THIS FUNCTION UNLESS YOU REALLY UNDERSTAND HOW IT WORKS */
		@include ("PayReadConf.php");
		$this->myAgentId = $agentid;
		$this->myKeys ["A"] = $KeyMap1 [$agentid];
		$this->myKeys ["B"] = $KeyMap2 [$agentid];
	}
	/**
	 * Normally - you dont need to set the agentID - its set automatically through PayReadConf.php
	 *
	 * @param $agentid string
	 *        	the AgentId given by payer (required)
	 */
	function setAgent($agentid) {
		if ($agentid != "*")
			$this->myAgentId = $agentid;
	}
	/**
	 * Normally - you dont need to set the key1 & key2 - its set automatically through PayReadConf.php
	 *
	 * @param $key1 string
	 *        	the key1 generated by Payer
	 * @param $key2 string
	 *        	the key2 generated by Payer
	 */
	function setKeys($key1, $key2) {
		if ($key1 != "*")
			$this->myKeys ["A"] = $key1;
		if ($key2 != "*")
			$this->myKeys ["B"] = $key2;
	}
	/**
	 * Normally - you dont need to set the key1 & key2 - its set automatically through PayReadConf.php
	 *
	 * @param $key string
	 *        	the key1 generated by Payer (KeyA)
	 */
	function setKeyA($key) {
		if ($key != "*")
			$this->myKeys ["A"] = $key;
	}
	/**
	 * Normally - you dont need to set the key1 & key2 - its set automatically through PayReadConf.php
	 *
	 * @param $key string
	 *        	the key2 generated by Payer (KeyB)
	 */
	function setKeyB($key) {
		if ($key != "*")
			$this->myKeys ["B"] = $key;
	}
	/**
	 * @retval string Returns the value of Key1 (KeyA)
	 */
	function getKeyA() {
		return $this->myKeys ["A"];
	}
	/**
	 *
	 * @return string Returns the value of Key2 (KeyB)
	 */
	function getKeyB() {
		return $this->myKeys ["B"];
	}
	/**
	 * Set the charset used at client (your) side of system.
	 * UTF-8 or ISO-8859-1 is common charsets.
	 *
	 * @param $charset string
	 *        	the charset given [UTF-8 or ISO-8859-1]
	 */
	function setCharSet($charset) {
		$this->myCharSet = $charset;
	}
	/**
	 *
	 * @return string Returns the charset used
	 */
	function get_charset() {
		return $this->myCharSet;
	}
	/**
	 * Set the version of the client you are working on.
	 * Example: "wooCommerce2.01-card"
	 *
	 * @param $version string
	 *        	the charset given [UTF-8 or ISO-8859-1]
	 */
	function setClientVersion($version) {
		$this->myClientVersion = $version;
	}
	/**
	 *
	 * @return string Returns the client version previously set
	 */
	function getClientVersion() {
		return $this->myClientVersion;
	}
	
	/**
	 * This is the method that will print out the form data with the necessary parameters as hidden fields.
	 *
	 * \code
	 * <input type="hidden" name="payer_agentid" value="<?=get_agentid()?>" />
	 * <input type="hidden" name="payer_xml_writer" value="<?=get_api_version()?>" />
	 * <input type="hidden" name="payer_data" value="<?=get_xml_data()?>" />
	 * <input type="hidden" name="payer_checksum" value="<?=get_checksum()?>" />
	 * <input type="hidden" name="payer_charset" value="<?=get_charset()?>" />
	 * \endcode
	 * does not return anything but prints form on output channel (to browser output)
	 */
	function generate_form() {
		echo $this->generate_form_str ();
	}
	
	/**
	 * This is the method that will print out the form data with the necessary parameters as hidden fields.
	 *
	 * @return string this will return the contents if generate_form in string format
	 * @see payread_post_api::generate_form()
	 */
	function return_generate_form() {
		return $this->generate_form_str ();
	}
	
	/**
	 * This is the method will return the post-api form in string format
	 *
	 * @see generate_form
	 * @return string this will return the contents if generate_form_str in string format
	 */
	function generate_form_str() {
		return "<input type=\"hidden\" name=\"payer_agentid\" value=\"" . $this->get_agentid () . "\" />\n" . "<input type=\"hidden\" name=\"payer_xml_writer\" value=\"" . $this->get_api_version () . "\" />\n" . "<input type=\"hidden\" name=\"payer_data\" value=\"" . $this->get_xml_data () . "\" />\n" . ($this->get_charset () == null ? "" : "<input type=\"hidden\" name=\"payer_charset\" value=\"" . $this->get_charset () . "\" />\n") . "<input type=\"hidden\" name=\"payer_checksum\" value=\"" . $this->get_checksum () . "\" />\n";
	}
	
	/**
	 * This method will return your agentid (which is the identification id for your shop).
	 * It you want, you can use the generate_form() method instead and then you don't need to call this method. Otherwise you will need to put this in the hidden variable "payread_agentid".
	 *
	 * @return integer agentid
	 */
	function get_agentid() {
		return $this->myAgentId;
	}
	
	/**
	 * This method will return which version of the POST API you are using.
	 * It you want, you can use the generate_form() method instead and then you don't need to call this method.	Otherwise you will need to put this in the hidden variable "payread_xml_writer".
	 *
	 * @return string api version
	 */
	function get_api_version() {
		return $this->do_encode ( $this->myPostApiVersion );
	}
	
	/**
	 * This method will return the XML in base64 format which needs to be posted to PAYER.
	 * It you want, you can use the generate_form() method instead and then you don't need to call this method.
	 * Otherwise you will need to put this in the hidden variable "payread_data".
	 *
	 * @return string xml data
	 */
	function get_xml_data() {
		$this->generate_purchase_xml ();
		$this->encrypt_data ();
		return $this->myEncryptedXmlData;
	}
	
	/**
	 * This method will return the checksum for the postdata.
	 * You need to post this checksum to PAYER.
	 * It you want, you can use the generate_form() method instead and then you don't need to call this method.
	 * Otherwise you will need to put this in the hidden variable "payread_checksum
	 *
	 * @return string Md5 checksum
	 */
	function get_checksum() {
		$this->myChecksum = $this->checksum_data ();
		return $this->myChecksum;
	}
	
	/**
	 * This method will return the URL to the POST-API located on PAYERs server.
	 *
	 * @return string Target URL to PAYER post-asp .../InitPayFlow
	 */
	function get_server_url() {
		return $this->myPayerServerUrl;
	}
	/**
	 * This method will return the URL to the POST-API located on PAYERs server.
	 *
	 * @param $url URL
	 *        	url to PAYER post-API server endpoint
	 * @return void
	 */
	function set_server_url($url) {
		$this->myPayerServerUrl = $url;
	}
	
	/**
	 * This method will set which currency the transaction is in.
	 * Use 3 letters in uppercase.
	 * ISO 4217 three letter currency code
	 *
	 * @param $theCurrency string
	 *        	3 letter uppercase currency (ie "SEK") (required)
	 * @see http://en.wikipedia.org/wiki/ISO_4217
	 */
	function set_currency($theCurrency) {
		if (strlen ( $theCurrency ) < 4) {
			$this->myCurrency = $theCurrency;
		}
	}
	
	/**
	 * This method will set a general description of the purchase, that will be used in
	 * various situations, e.g.
	 * in the Payer admin web, or communication with the
	 * buyer when its impossible to present the full specification.
	 *
	 * @param $theDescription string
	 *        	is one-line short description of the purchase. Notice that
	 *        	this description may be truncated depending on where it is presented, the maximum length
	 *        	that will be stored by Payer is 255 characters, but try to keep it below 32
	 *        	characters
	 * @return void
	 */
	function set_description($theDescription) {
		$this->myDescription = $theDescription;
	}
	
	/**
	 * This method will hide / unhide purchase details (item lines)
	 *
	 * @param $myHideDetails bool
	 *        	bool true/false (true will hide the details)
	 */
	function set_hide_details($myHideDetails) {
		$this->myHideDetails = $myHideDetails;
	}
	/**
	 * This method will set a reference Id (OrderNumber) for the purchase.
	 *
	 * If set - it must be unique for purchase. A successful payment will "lock" this ID from ever being charged again.
	 *
	 * @param $theReferenceId string
	 *        	is the reference Id. It is possible that this string might be presented to the buyer.
	 * @return void
	 */
	function set_reference_id($theReferenceId) {
		$this->myReferenceId = $theReferenceId;
	}
	/**
	 * This method will set a message for the purchase.
	 *
	 * @param $theMessage string
	 *        	is the message. It is possible that this string might be presented to the buyer and the merchant.
	 */
	function set_message($theMessage) {
		$this->myMessage = $theMessage;
	}
	/**
	 * This method is not yet used (discouraged to be used)
	 *
	 * @deprecated
	 *
	 */
	function add_catalog_purchase($theLineNumber, $theId, $theQuantity) {
		$this->myCatalogPurchases [] = array (
				"LineNo" => $theLineNumber,
				"Id" => $theId,
				"Quantity" => $theQuantity 
		);
	}
	
	/**
	 * This method must be called at least once (or add_freeform_purchase_ex() ) (unless in "store"-mode)
	 *
	 * This method will add a product, use this method multiple times per each product the buyer will pay for.
	 *
	 * @param $theLineNumber string
	 *        	order of the output lines of the products buyed, starting at 1. (required)
	 * @param $theDescription string
	 *        	decription of the product buyed. (required)
	 * @param $thePrice double
	 *        	price of the product buyed. (required)
	 * @param $theVat integer
	 *        	vat of the product purchased. [25, 12, 6 or 0] (required)
	 * @param $theQuantity integer
	 *        	quantity of the product buyed. (required)
	 * @see payread_post_api::add_freeform_purchase_ex()
	 */
	function add_freeform_purchase($theLineNumber, $theDescription, $thePrice, $theVat, $theQuantity) {
		$this->myFreeformPurchases [] = array (
				"LineNo" => $theLineNumber,
				"Description" => $theDescription,
				"Price" => $thePrice,
				"Vat" => $theVat,
				"Quantity" => $theQuantity 
		);
	}
	
	/**
	 * This method must be called at least once (or add_freeform_purchase() ) (unless in "store"-mode)
	 *
	 * This method will add a product, use this method multiple times per each product the buyer will pay for.
	 *
	 * @param $theLineNumber integer
	 *        	order of the output lines of the products buyed, starting at 1. (required)
	 * @param $theDescription string
	 *        	decription of the product buyed. (required)
	 * @param $theItemNumber string
	 *        	item number. (required)
	 * @param $thePrice double
	 *        	price of the product (each) purchased incl vat. (required)
	 * @param $theVat integer
	 *        	vat of the product purchased. [25, 12, 6 or 0] (required)
	 * @param $theQuantity integer
	 *        	quantity of the product buyed. (required)
	 * @param $theUnit string
	 *        	purchase unit "kg", "L", "st", "each", "m" or similar (optional)
	 * @param $theAccount string
	 *        	the account number for accounting - will need special reports (optional)
	 * @param $theDistAgentId string
	 *        	- if using distributed purchase - state AgentID of subcontract
	 * @see payread_post_api::add_freeform_purchase()
	 */
	function add_freeform_purchase_ex($theLineNumber, $theDescription, $theItemNumber, $thePrice, $theVat, $theQuantity, $theUnit = null, $theAccount = null, $theDistAgentId = null) {
		$theArray = array ();
		$theArray ["LineNo"] = $theLineNumber;
		$theArray ["Description"] = $theDescription;
		if ($theItemNumber != null) {
			$theArray ["ItemNumber"] = $theItemNumber;
		}
		$theArray ["Price"] = $thePrice;
		$theArray ["Vat"] = $theVat;
		$theArray ["Quantity"] = $theQuantity;
		if ($theUnit != null) {
			$theArray ["Unit"] = $theUnit;
		}
		if ($theAccount != null) {
			$theArray ["Account"] = $theAccount;
		}
		if ($theDistAgentId != null) {
			$theArray ["AgentId"] = $theDistAgentId;
		}
		
		$this->myFreeformPurchases [] = $theArray;
	}
	function add_subscription_purchase($theLineNumber, $theDescription, $theItemNumber, $theInitialPrice, $theRecurringPrice, $theVat, $theQuantity, $theUnit, $theAccount, $theStartDate, $theStopDate, $theCount, $thePeriodicity, $theCancelDays) {
		$theArray = array ();
		$theArray ["LineNo"] = $theLineNumber;
		$theArray ["Description"] = $theDescription;
		if ($theItemNumber != null) {
			$theArray ["ItemNumber"] = $theItemNumber;
		}
		$theArray ["Price"] = $theInitialPrice;
		$theArray ["Vat"] = $theVat;
		$theArray ["Quantity"] = $theQuantity;
		if ($theUnit != null) {
			$theArray ["Unit"] = $theUnit;
		}
		$theArray ["Account"] = $theAccount;
		
		$theArray ["RecurringPrice"] = $theRecurringPrice;
		$theArray ["StartDate"] = $theStartDate;
		$theArray ["Count"] = $theCount;
		$theArray ["Periodicity"] = $thePeriodicity;
		$theArray ["StopDate"] = $theStopDate;
		$theArray ["CancelDays"] = $theCancelDays;
		
		$this->mySubscriptionPurchases [] = $theArray;
	}
	
	/**
	 * set_fee
	 * This method is optional
	 *
	 * This method will add (and override) any fixed fees set from Payer website.
	 *
	 * @param $theDescription string
	 *        	Is the fee (or discount) description
	 * @param $thePrice string
	 *        	the fee or discount amount charged
	 * @param $theItemNumber string
	 *        	The ItemNumber that will show up in the item number column
	 * @param $theVat integer
	 *        	The VAT percentage used to add VAT from the net price (25 std Seden)
	 * @param $theQuantity integer
	 *        	The Quantity added - normally and default "1"
	 * @return void
	 */
	function set_fee($theDescription, $thePrice, $theItemNumber = "", $theVat = 25, $theQuantity = 1) {
		$this->add_freeform_purchase_ex ( 99999, $theDescription, $theItemNumber, $thePrice, $theVat, $theQuantity, $theUnit = null, $theAccount = null, $theDistAgentId = null );
	}
	
	/**
	 * This method is optional
	 *
	 * This method will add a line with information text that the buyer will see in the purchase process
	 *
	 * @param $theLineNumber integer
	 *        	line number on receipt or payment window. (required)
	 * @param $theText string
	 *        	the additional decription of the product purchased. (required)
	 */
	function add_info_line($theLineNumber, $theText) {
		$this->myInfoLines [] = array (
				"LineNo" => $theLineNumber,
				"Text" => $theText 
		);
	}
	
	/**
	 * This method must be called once.
	 *
	 * This method set the buyer information that will be posted to Payer payment gateway
	 *
	 * @param $theFirstName string
	 *        	buyers firstname (optional)
	 * @param $theLastName string
	 *        	buyers lastname (optional)
	 * @param $theAddressLine1 string
	 *        	buyers adressline1 (optional)
	 * @param $theAddressLine2 string
	 *        	buyers adressline1 (optional)
	 * @param $thePostalcode string
	 *        	buyers postalcode (optional)
	 * @param $theCity string
	 *        	buyers city (optional)
	 * @param $theCountryCode string
	 *        	buyers countrycode <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO_3166-1_alpha-2 codes</a>(optional)
	 * @param $thePhoneHome string
	 *        	buyers phonenumber home (optional)
	 * @param $thePhoneWork string
	 *        	buyers phonenumber work (optional)
	 * @param $thePhoneMobile string
	 *        	buyers phonenumber mobile (optional)
	 * @param $theEmail string
	 *        	buyers email (optional)
	 * @param $theOrganisation string
	 *        	name of the organisation (optional)
	 * @param $theOrgNr string
	 *        	organisation number or social security number (personummer) (optional)
	 * @param $theCustomerId string
	 *        	UserId at Payer (optional) - recommended to set empty or as null
	 * @param $theYourReference string
	 *        	Contact person at organisation/company (optional) - if empty the FirstName + LastName will be used as YourReference.
	 *        	
	 * @param $theOptions string
	 *        	key1=value1,key2=value2 comma separated key->value pairs for special purposes (optional)
	 *        	
	 *        	
	 * @return void
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
	 */
	function add_buyer_info($theFirstName, $theLastName, $theAddressLine1, $theAddressLine2, $thePostalcode, $theCity, $theCountryCode, $thePhoneHome, $thePhoneWork, $thePhoneMobile, $theEmail, $theOrganisation = null, $theOrgNr = null, $theCustomerId = null, $theYourReference = null, $theOptions = null) {
		$this->myBuyerInfo ["FirstName"] = $theFirstName;
		$this->myBuyerInfo ["LastName"] = $theLastName;
		$this->myBuyerInfo ["AddressLine1"] = $theAddressLine1;
		$this->myBuyerInfo ["AddressLine2"] = $theAddressLine2;
		$this->myBuyerInfo ["Postalcode"] = $thePostalcode;
		$this->myBuyerInfo ["City"] = $theCity;
		$this->myBuyerInfo ["CountryCode"] = $theCountryCode == "" ? "SE" : $theCountryCode;
		$this->myBuyerInfo ["PhoneHome"] = $thePhoneHome;
		$this->myBuyerInfo ["PhoneWork"] = $thePhoneWork;
		$this->myBuyerInfo ["PhoneMobile"] = $thePhoneMobile;
		$this->myBuyerInfo ["Email"] = $theEmail;
		$this->myBuyerInfo ["Organisation"] = $theOrganisation;
		$this->myBuyerInfo ["OrgNr"] = $theOrgNr;
		$this->myBuyerInfo ["CustomerId"] = $theCustomerId;
		$this->myBuyerInfo ["YourReference"] = $theYourReference;
		$this->myBuyerInfo ["Options"] = $theOptions; // Silly position of Options
	}
	/**
	 *
	 * @param $theOptions string
	 *        	key1=value1,key2=value2 comma separated key->value pairs for special purposes (optional)
	 *        	options values can be any of [store,fastcard,authonly,settle,warpspeed,no_warpspeed,viewport_meta]
	 *        	all these keys can be entered as true/false
	 */
	function set_options($theOptions) {
		$this->myBuyerInfo ["Options"] = $theOptions;
	}
	/**
	 *
	 * @param $theKey string        	
	 * @param $theValue string
	 *        	add one key=value pair to Options parameter (optional)
	 */
	function add_option($theKey, $theValue) {
		if (empty ( $this->myBuyerInfo ["Options"] )) {
			$this->myBuyerInfo ["Options"] = "$theKey=$theValue";
		} else {
			$options = explode ( ",", $this->myBuyerInfo ["Options"] );
			$options [] = "$theKey=$theValue";
			$this->myBuyerInfo ["Options"] = implode ( ",", $options );
		}
	}
	/**
	 *
	 * @param $theAuthOnly bool
	 *        	set the option auth_only to true or false
	 *        	if auth_only is set to true - the payment type [card] will and after a successful auth and will return a token at authorize callback.
	 *        	You kan use this token to settle the transaction at a later point - either by klicking button in merchant web or by calling web service API.
	 *        	The opposite to 'auth_only=true' is 'settle=true'. The keyword 'settle=true' will override the default setting that might apply globally to merchant.
	 * @return void
	 */
	function set_authOnly($theAuthOnly) {
		if ($theAuthOnly === true || strtolower ( $theAuthOnly ) == "true")
			$this->add_option ( "auth_only=true" );
		else
			$this->add_option ( "settle=true" );
	}
	function set_auth_only($theAuthOnly) {
		$this->set_authOnly($theAuthOnly);
	}
	
	/**
	 * This method must be called.
	 *
	 * This method will set the payment method the buyer can use to pay with.
	 *
	 * @param $theMethod string
	 *        	Can be set to sms, card, bank, phone, invoice & auto (required)
	 * @return void
	 */
	function add_payment_method($theMethod) {
		$this->myPaymentMethods [] = $theMethod;
	}
	
	/**
	 * This method must be called.
	 *
	 * This method will set the URL where your Authorize webpage is located, remember that you will need to respond "TRUE" if everything is ok, or "FALSE" if something goes wrong, on your page.
	 * If "options" is set to "store=true" then this URL will be used to send back the uniqReferenceId
	 *
	 * Example: http://www.xyzzy.se/pr_callback/authorize.php?order_id=636762
	 *
	 * any parameter added in above URL will be returned back + som extra parameters.
	 * parameters added by payer for authorize callback:
	 * - &payer_testmode=false
	 * - &payer_payment_type=invoice
	 * - &payer_callback_type=auth
	 * - &uniqReferenceId=FFFFGGGGGGSSSSSSS (if in "store"-mode)
	 * - &md5sum=229131F6AABC89DFC9E38610F4DA6CF3
	 *
	 * @param $theUrl string
	 *        	URL to your authorize notification page. (required)
	 * @return void
	 * @see set_settle_notification_url()
	 * @example auth.php
	 */
	function set_authorize_notification_url($theUrl) {
		$this->myAuthorizeNotificationUrl = $theUrl;
	}
	
	/**
	 * This method must be called.
	 *
	 * This method will set the URL where your Settle webpage is located, remember that you will need to respond "TRUE" if everything is ok, or "FALSE" if something goes wrong, on your page.
	 *
	 * Example: http://www.xyzzy.se/pr_callback/settle.php?order_id=636762
	 *
	 * parameters added by Payer for settle callbak:
	 * - &payer_testmode=false
	 * - &payer_payment_type=invoice
	 * - &payer_callback_type=settle
	 * - &payer_added_fee=40
	 * - &payer_payment_id=IFO\@XXX60o5c72fii616hloi83n
	 * - &payread_payment_id=IFO\@XXX60o5c72fii616hloi83n
	 * - &md5sum=28E69FA28EE10A2C40E8E30468FE3E68
	 *
	 * @param $theUrl URL|string
	 *        	URL to your settle notification page. (required)
	 * @return void
	 * @see set_authorize_notification_url()
	 * @example settle.php
	 *          \include settle.php
	 */
	function set_settle_notification_url($theUrl) {
		$this->mySettleNotificationUrl = $theUrl;
	}
	
	/**
	 * This method must be called.
	 *
	 * This method will set the URL where your frontpage of the shop is located.
	 *
	 * @param $theUrl string
	 *        	URL to your frontpage of the shop. (required)
	 * @return void
	 */
	function set_redirect_back_to_shop_url($theUrl) {
		$this->myRedirectBackToShopUrl = $theUrl;
	}
	
	/**
	 * This method is optional
	 *
	 * If you want the receipt to be handled by your shop, this method will set the URL where the buyer will be redirected.
	 * If you don't use this method the buyer will get a receipt on PAYER server. This should be poingint to the shops "tank you" page.
	 *
	 * @param $theUrl URL
	 *        	URL to your receipt if handled by shop.	(optional)
	 * @return void
	 */
	function set_success_redirect_url($theUrl) {
		$this->mySuccessRedirectUrl = $theUrl;
	}
	
	/**
	 * This method is optional, default value is "silent"
	 *
	 * This method will set the debug mode, if set to verbose you will be able to see the parameters posted at the page where you enter bankcard information
	 *
	 * @param $theDebugMode string
	 *        	debug mode, set as [silent, brief, verbose] (required)
	 * @return void
	 */
	function set_debug_mode($theDebugMode) {
		if (in_array ( strtolower ( $theDebugMode ), array (
				"silent",
				"brief",
				"verbose" 
		) )) {
			$this->myDebugMode = $theDebugMode;
		}
	}
	
	/**
	 * This method is optional, default value is false
	 *
	 * This method will set the testmode, if set to true, PAYER will not contact the bank and no money will be taken from the bank account connected to the bankcard, otherwise everything will act like a real transaction.
	 *
	 * @param $theTestMode string|bool
	 *        	test mode, set as true/false (required)
	 * @return void
	 */
	function set_test_mode($theTestMode) {
		$lc = strtolower ( $theTestMode );
		$this->myTestMode = (($theTestMode === true || $lc == "true") ? "true" : "false");
	}
	
	/**
	 * This method is optional, default value is "sv"
	 *
	 * This method will set which language the buyer will see when he enters bankcard information.
	 * The input should be in lowercase and you should enter language code (ISO_639-1: 2 letters) not countrycode ie "sv" not "se".
	 *
	 * @param $theLanguage string
	 *        	2 letter uppercase language (ie "sv") (required)
	 * @return void
	 * @see http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
	 */
	function set_language($theLanguage) {
		if (strlen ( $theLanguage ) == 2) {
			$this->myLanguage = $theLanguage;
		}
	}
	/**
	 * This method is optional, default value is "sv"
	 *
	 * @return URL|string Returns the assembled request URL as string
	 */
	function get_request_url() {
		$protocol = ($this->isSecure() == true ? "https://" : "http://");
		if (empty ( $_SERVER ["REQUEST_URI"] )) {
			return $protocol . $_SERVER ["HTTP_HOST"] . $_SERVER ["PHP_SELF"] . "?" . $_SERVER ["QUERY_STRING"];
		}
		return $protocol . $_SERVER ["HTTP_HOST"] . $_SERVER ["REQUEST_URI"];
	}
	
	/**
	 * This method determines if secire (http or https)
	 *
	 * @return bool|bool 
	 */
	function isSecure() {
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== 'OFF') {
			return true;
		}
		if ($_SERVER['SERVER_PORT'] == 443) {
			return true;
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
			return true;
		}
		return false;
	}
	
	/**
	 * This method will validate that the callback orginates from PAYERs server.
	 * This method should be called from your authorize and settle pages.
	 *
	 * @param $theUrl URL|string
	 *        	URL to be validated as a string (required)
	 * @return bool true/false
	 */
	function validate_callback_url($theUrl) {
		// strip the &md5sum from url
		$pos = strpos ( $theUrl, "&md5sum" );
		if ($pos === false) {
			// this case handles opencart and other manipulating $_SERVER vars
			$theUrl = htmlspecialchars_decode ( $theUrl );
			$strippedUrl = substr ( $theUrl, 0, strpos ( $theUrl, "&md5sum" ) );
		} else {
			$strippedUrl = substr ( $theUrl, 0, $pos );
		}
		// add the Key1 and Key2 from the stripped url and calculate checksum
		$keyA = $this->myKeys ["A"];
		$keyB = $this->myKeys ["B"];
		
		$md5 = strtolower ( md5 ( $keyA . $strippedUrl . $keyB ) );
		
		// do we find the calculated checksum in in the original URL somewhere ?
		if (strpos ( strtolower ( $theUrl ), $md5 ) >= 7) {
			return true; // yes - this is authentic
		}
		return false; // no - this is not a properly signed URL
	}
	
	/**
	 * This method is a VERY SIMPLE firewall on application level
	 *
	 * This method will validate that the callback orginates from PAYERs server. This method should be called from your authorize and settle pages.
	 *
	 * @see add_valid_ip()
	 * @return bool true/false
	 */
	function is_valid_ip() {
		$ip = $_SERVER ["REMOTE_ADDR"];
		return in_array ( $ip, $this->myFirewall );
	}
	
	/**
	 * This method is used to add a ip address that you want to treat as valid
	 *
	 * This method could be called from your authorize and settle pages and will add that ip as "is_valid_op"
	 *
	 * @param $ip URL
	 *        	string ip-address to be added as legal ip-address by is_valid_ip-firewall
	 * @see is_valid_ip()
	 * @return void
	 */
	function add_valid_ip($ip) {
		$this->myFirewall [] = $ip;
	}
	/**
	 * Validates the Callback-URL
	 *
	 * This method is used to calidate the callback url and to see if the checksum is correct
	 *
	 * @return bool returns true or false indicating a legal(true) callback checksum
	 */
	function is_valid_callback() {
		return $this->validate_callback_url ( $this->get_request_url () );
	}
	
	/**
	 * encrypt_data
	 * This method will "encrypt" the data using base64 - not used for protecting of data - https is responsible for that
	 *
	 * @param $theEncryptionMethod string
	 *        	the encyption method requested
	 * @return s void
	 */
	function encrypt_data($theEncryptionMethod = "base64") {
		switch (strtolower ( $theEncryptionMethod )) {
			case "base64" :
				$this->myEncryptedXmlData = base64_encode ( $this->myXmlData );
				break;
		}
	}
	
	/**
	 * checksum_data
	 * This method will set the checksum
	 *
	 * @param $theAuthMethod string
	 *        	select the checksum method [md5|sha1]
	 * @return void
	 */
	function checksum_data($theAuthMethod = "md5") {
		switch (strtolower ( $theAuthMethod )) {
			case "sha1" :
				return sha1 ( $this->myKeys ["A"] . $this->myEncryptedXmlData . $this->myKeys ["B"] );
				break;
			
			case "sha256" :
				return sha256 ( $this->myKeys ["A"] . $this->myEncryptedXmlData . $this->myKeys ["B"] );
				break;
			
			case "md5" :
				return md5 ( $this->myKeys ["A"] . $this->myEncryptedXmlData . $this->myKeys ["B"] );
				break;
		}
	}
	
	/**
	 * This method will generate the xml data that you need to post from the Post-API.
	 * The generated data is stored in the class global variable myXmlData.
	 *
	 * @return void
	 */
	function generate_purchase_xml() {
		// Header
		$charset = $this->myCharSet == null ? "iso-8859-1" : $this->myCharSet;
		
		$this->myXmlData = "<?xml version=\"1.0\" encoding=\"$charset\"?>\n";
		$this->myXmlData .= "<payread_post_api_0_2 " . "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " . "xsi:noNamespaceSchemaLocation=\"payread_post_api_0_2.xsd\"" . ">\n";
		
		// Seller details
		$this->myXmlData .= "<seller_details>\n" . " <agent_id>" . $this->do_encode ( $this->myAgentId ) . "</agent_id>\n";
		
		if ($this->myClientVersion != null) {
			$this->myXmlData .= " <client_version>" . $this->do_encode ( $this->myClientVersion ) . "</client_version>\n";
		}
		
		$this->myXmlData .= "</seller_details>\n";
		
		// Buyer details
		$this->myXmlData .= "<buyer_details>\n" . " <first_name>" . $this->do_encode ( $this->myBuyerInfo ["FirstName"] ) . "</first_name>\n" . " <last_name>" . $this->do_encode ( $this->myBuyerInfo ["LastName"] ) . "</last_name>\n" . " <address_line_1>" . $this->do_encode ( $this->myBuyerInfo ["AddressLine1"] ) . "</address_line_1>\n" . " <address_line_2>" . $this->do_encode ( $this->myBuyerInfo ["AddressLine2"] ) . "</address_line_2>\n" . " <postal_code>" . $this->do_encode ( $this->myBuyerInfo ["Postalcode"] ) . "</postal_code>\n" . " <city>" . $this->do_encode ( $this->myBuyerInfo ["City"] ) . "</city>\n" . " <country_code>" . $this->do_encode ( $this->myBuyerInfo ["CountryCode"] ) . "</country_code>\n" . " <phone_home>" . $this->do_encode ( $this->myBuyerInfo ["PhoneHome"] ) . "</phone_home>\n" . " <phone_work>" . $this->do_encode ( $this->myBuyerInfo ["PhoneWork"] ) . "</phone_work>\n" . " <phone_mobile>" . $this->do_encode ( $this->myBuyerInfo ["PhoneMobile"] ) . "</phone_mobile>\n" . " <email>" . $this->do_encode ( $this->myBuyerInfo ["Email"] ) . "</email>\n" . " <organisation>" . $this->do_encode ( $this->myBuyerInfo ["Organisation"] ) . "</organisation>\n" . " <orgnr>" . $this->do_encode ( $this->myBuyerInfo ["OrgNr"] ) . "</orgnr>\n" . " <customer_id>" . $this->do_encode ( $this->myBuyerInfo ["CustomerId"] ) . "</customer_id>\n" . (! empty ( $this->myBuyerInfo ["YourReference"] ) ? " <your_reference>" . $this->do_encode ( $this->myBuyerInfo ["YourReference"] ) . "</your_reference>\n" : "") . (! empty ( $this->myBuyerInfo ["Options"] ) ? " <options>" . $this->do_encode ( $this->myBuyerInfo ["Options"] ) . "</options>\n" : "") . "</buyer_details>\n";
		$this->myXmlData .= "<purchase>\n";
		// Purchase
		$this->myXmlData .= "<currency>" . $this->myCurrency . "</currency>\n";
		// Add RefId if used
		if (! empty ( $this->myReferenceId )) {
			$this->myXmlData .= "<reference_id>" . $this->do_encode ( $this->myReferenceId ) . "</reference_id>\n";
		}
		// Add Descr if used
		if (! empty ( $this->myDescription )) {
			$this->myXmlData .= "<description>" . $this->do_encode ( $this->myDescription ) . "</description>\n";
		}
		// Add Message if used
		if (! empty ( $this->myMessage )) {
			$this->myXmlData .= "<message>" . $this->do_encode ( $this->myMessage ) . "</message>\n";
		}
		// Add myHideDetails
		if (! empty ( $this->myHideDetails )) {
			$this->myXmlData .= "<hide_details>" . ($this->myHideDetails ? "true" : "false") . "</hide_details>\n";
		}
		// Start the Purchase list
		$this->myXmlData .= "<purchase_list>\n";
		
		// Purchase list (catalog purchases)
		@reset ( $this->myCatalogPurchases );
		while ( list ( , $thePurchase ) = @each ( $this->myCatalogPurchases ) ) {
			$this->myXmlData .= "<catalog_purchase>" . "<line_number>" . $this->do_encode ( $thePurchase ["LineNo"] ) . "</line_number>" . "<id>" . $this->do_encode ( $thePurchase ["Id"] ) . "</id>" . "<quantity>" . $this->do_encode ( $thePurchase ["Quantity"] ) . "</quantity>" . "</catalog_purchase>\n";
		}
		
		// Purchase list (freeform purchases)
		@reset ( $this->myFreeformPurchases );
		while ( list ( , $thePurchase ) = @each ( $this->myFreeformPurchases ) ) {
			$this->myXmlData .= "<freeform_purchase>" . " <line_number>" . $this->do_encode ( $thePurchase ["LineNo"] ) . "</line_number>\n" . " <description>" . $this->do_encode ( $thePurchase ["Description"] ) . "</description>\n" . (! empty ( $thePurchase ["ItemNumber"] ) ? " <item_number>" . $this->do_encode ( $thePurchase ["ItemNumber"] ) . "</item_number>\n" : "") . " <price_including_vat>" . $this->do_encode ( $thePurchase ["Price"] ) . "</price_including_vat>\n" . " <vat_percentage>" . $this->do_encode ( $thePurchase ["Vat"] ) . "</vat_percentage>\n" . " <quantity>" . $this->do_encode ( $thePurchase ["Quantity"] ) . "</quantity>\n" . (! empty ( $thePurchase ["Unit"] ) ? " <unit>" . $this->do_encode ( $thePurchase ["Unit"] ) . "</unit>\n" : "") . (! empty ( $thePurchase ["Account"] ) ? " <account>" . $this->do_encode ( $thePurchase ["Account"] ) . "</account>\n" : "") . (! empty ( $thePurchase ["AgentId"] ) ? " <agent_id>" . $this->do_encode ( $thePurchase ["AgentId"] ) . "</agent_id>\n" : "") . "</freeform_purchase>\n";
		}
		
		foreach ( $this->mySubscriptionPurchases as $thePurchase ) {
			$this->myXmlData .= " <subscription_purchase>\n" . "  <line_number>" . $this->do_encode ( $thePurchase ["LineNo"] ) . "</line_number>\n" . "  <description>" . $this->do_encode ( $thePurchase ["Description"] ) . "</description>\n" . "  <price_including_vat>" . $this->do_encode ( $thePurchase ["Price"] ) . "</price_including_vat>\n" . "  <vat_percentage>" . $this->do_encode ( $thePurchase ["Vat"] ) . "</vat_percentage>\n" . "  <quantity>" . $this->do_encode ( $thePurchase ["Quantity"] ) . "</quantity>\n" . (! empty ( $thePurchase ["ItemNumber"] ) ? "  <item_number>" . $this->do_encode ( $thePurchase ["ItemNumber"] ) . "</item_number>\n" : "") . (! empty ( $thePurchase ["Unit"] ) ? "  <unit>" . $this->do_encode ( $thePurchase ["Unit"] ) . "</unit>\n" : "") . (! empty ( $thePurchase ["Account"] ) ? "  <account>" . $this->do_encode ( $thePurchase ["Account"] ) . "</account>\n" : "") . "  <recurring_price_including_vat>" . $this->do_encode ( $thePurchase ["RecurringPrice"] ) . "</recurring_price_including_vat>\n" . "  <start_date>" . $this->do_encode ( $thePurchase ["StartDate"] ) . "</start_date>\n" . "  <stop_date>" . $this->do_encode ( $thePurchase ["StopDate"] ) . "</stop_date>\n" . "  <count>" . $this->do_encode ( $thePurchase ["Count"] ) . "</count>\n" . "  <periodicity>" . $this->do_encode ( $thePurchase ["Periodicity"] ) . "</periodicity>\n" . "  <cancel_days>" . $this->do_encode ( $thePurchase ["CancelDays"] ) . "</cancel_days>\n" . " </subscription_purchase>\n";
		}
		
		// Purchase list (info lines)
		@reset ( $this->myInfoLines );
		while ( list ( , $theValues ) = @each ( $this->myInfoLines ) ) {
			$this->myXmlData .= "<info_line>" . "<line_number>" . $this->do_encode ( $theValues ["LineNo"] ) . "</line_number>" . "<text>" . $this->do_encode ( $theValues ["Text"] ) . "</text>" . "</info_line>\n";
		}
		
		$this->myXmlData .= "</purchase_list>\n" . "</purchase>\n";
		
		// Processing control
		$this->myXmlData .= "<processing_control>\n";
		if (! empty ( $this->mySuccessRedirectUrl ))
			$this->myXmlData .= "<success_redirect_url>" . $this->do_encode ( $this->mySuccessRedirectUrl ) . "</success_redirect_url>\n";
		$this->myXmlData .= " <authorize_notification_url>" . $this->do_encode ( $this->myAuthorizeNotificationUrl ) . "</authorize_notification_url>\n" . " <settle_notification_url>" . $this->do_encode ( $this->mySettleNotificationUrl ) . "</settle_notification_url>\n" . " <redirect_back_to_shop_url>" . $this->do_encode ( $this->myRedirectBackToShopUrl ) . "</redirect_back_to_shop_url>\n" . "</processing_control>\n";
		
		// Database overrides
		$this->myXmlData .= "<database_overrides>\n";
		
		// Payment methods
		$this->myXmlData .= "<accepted_payment_methods>\n";
		@reset ( $this->myPaymentMethods );
		while ( list ( , $thePaymentMethod ) = @each ( $this->myPaymentMethods ) ) {
			$this->myXmlData .= "<payment_method>" . $thePaymentMethod . "</payment_method>\n";
		}
		$this->myXmlData .= "</accepted_payment_methods>\n";
		
		// Debug mode
		$this->myXmlData .= "<debug_mode>" . $this->myDebugMode . "</debug_mode>\n";
		
		// Test mode
		$this->myXmlData .= "<test_mode>" . $this->myTestMode . "</test_mode>\n";
		
		// Language ISO_639-1
		$this->myXmlData .= "<language>" . $this->myLanguage . "</language>\n";
		
		$this->myXmlData .= "</database_overrides>\n";
		
		// Footer
		$this->myXmlData .= "</payread_post_api_0_2>\n";
	}
	function getChallangeResponse($challange) {
		return md5 ( $this->getKeyA () . "$challange" );
	}
	function do_encode($data) {
		$charset = $this->myCharSet == null ? "ISO-8859-1" : $this->myCharSet;
		
		// return htmlspecialchars($data);WORKS FOR PHP PRIOR TO VERSION 5.4.0
		
		// Default value changed in PHP 5.4.0 from ISO-8859-1 to UTF-8
		return htmlspecialchars ( $data, ENT_COMPAT | ENT_HTML401, $charset );
	}
}
?>
