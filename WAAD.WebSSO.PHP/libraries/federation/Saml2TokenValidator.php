/*-----------------------------------------------------------------------

    Copyright (c) Microsoft Corporation.  All rights reserved.

 
    Copyright 2012 Microsoft Corporation
    All rights reserved.

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at
      http://www.apache.org/licenses/LICENSE-2.0

 THIS CODE IS PROVIDED *AS IS* BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, 
 EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION ANY IMPLIED WARRANTIES OR 
 CONDITIONS OF TITLE, FITNESS FOR A PARTICULAR PURPOSE, MERCHANTABLITY OR NON-INFRINGEMENT.

 See the Apache Version 2.0 License for specific language governing 
 permissions and limitations under the License.

--------------------------------------------------------------------------- */
<?php

require_once (dirname(__FILE__) . '/../simplesamlphp/lib/xmlseclibs.php');

require_once (dirname(__FILE__) . '/../simplesamlphp/lib/SAML2/XML/saml/SubjectConfirmationData.php');
require_once (dirname(__FILE__) . '/../simplesamlphp/lib/SAML2/XML/saml/SubjectConfirmation.php');
require_once (dirname(__FILE__) . '/../simplesamlphp/lib/SAML2/Utils.php');
require_once (dirname(__FILE__) . '/../simplesamlphp/lib/SAML2/Const.php');
require_once (dirname(__FILE__) . '/../simplesamlphp/lib/SAML2/SignedElement.php');
require_once (dirname(__FILE__) . '/../simplesamlphp/lib/SAML2/Assertion.php');

require_once (dirname(__FILE__) . '/../simplesamlphp/lib/SimpleSAML/Utilities.php');

require_once (dirname(__FILE__) . '/Claim.php');

class Saml2TokenValidator {
	const NS_WS_TRUST = 'http://schemas.xmlsoap.org/ws/2005/02/trust';

	public $validateExpiration = true;
	public $validateIssuer = true;
	public $validateAudiences = true;

	public $thumbprints = array ();
	public $allowedAudiences = array ();
	public $trustedIssuers = array ();

	public function __construct() {
	}

	public function validate($token) {
		$data = $this->parseToken($token);

		// validate digest and thumbprint
		$assertion = new SAML2_Assertion($data['Assertion']);
		$certificates = $assertion->getCertificates();
		$this->validateCertificateThumbprint($certificates[0]);

		// validate issuer
		if ($this->validateIssuer) {
			$this->validateIssuer($assertion->getIssuer());
		}

		// validate audiences
		if ($this->validateAudiences) {
			$this->validateAudiences($assertion->getValidAudiences(), $assertion->getNotBefore(), $assertion->getNotOnOrAfter());
		}

		return $this->getClaims($data);
	}

	private function parseToken($token) {
		$dom = new DOMDocument();
		$token = str_replace('\"', '"', $token);
		$dom->loadXML(str_replace("\r", "", $token));

		$xpath = new DOMXpath($dom);
		$xpath->registerNamespace('wst', self :: NS_WS_TRUST);
		$xpath->registerNamespace('saml', SAML2_Const :: NS_SAML);

		$assertions = $xpath->query('/wst:RequestSecurityTokenResponse/wst:RequestedSecurityToken/saml:Assertion');
		if ($assertions->length === 0) {
			$this->error('Received a response without an assertion on the WS-Fed PRP handler.');
		}
		if ($assertions->length > 1) {
			$this->error('The WS-Fed PRP handler currently only supports a single assertion in a response.');
		}
		$assertion = $assertions->item(0);

		return array (
			'Assertion' => $assertion,
			'XPath' => $xpath
		);
	}

	private function validateCertificateThumbprint($certificate) {
		$certFingerprint = strtolower(sha1(base64_decode($certificate)));

		foreach ($this->thumbprints as $tp) {
			if ($tp === $certFingerprint) {
				return;
			}
		}

		$this->error('Invalid fingerprint of certificate. Expected one of [' . implode('], [', $this->thumbprints) . '], but got [' . $certFingerprint . ']');
	}

	private function validateIssuer($tokenIssuer) {
		$trustedIssuerOk = false;

		foreach ($this->trustedIssuers as $issuer) {
			$trustedIssuerOk = $trustedIssuerOk || (strcmp($tokenIssuer, $issuer) === 0);
		}

		if (!$trustedIssuerOk)
			$this->error('Invalid trusted issuer');
	}

	private function validateAudiences($tokenAudiences, $notBefore, $notOnOrAfter) {

		if ($this->validateExpiration && !$this->checkDateIfExpired($notBefore, $notOnOrAfter)) {
			
			$this->error('The response has expired.');
		}

		$audienceOk = false;

		foreach ($tokenAudiences as $tokenAudience) {
			foreach ($this->allowedAudiences as $allowedAudience) {				
				$audienceOk = $audienceOk || (strcmp($allowedAudience, $tokenAudience) === 0);
				if ($audienceOk)
					break;
			}
			if ($audienceOk)
				break;
		}

		if (!$audienceOk)
			$this->error('Invalid audience');
	}

	private function checkDateIfExpired($start = NULL, $end = NULL) {
		$currentTime = time();
		$start -= 300;
		$end += 300;
		
		if (isset ($start)) {
			if (($start < 0) || ($start > $currentTime))
				return false;
		}

		if (isset ($end)) {
			if (($end < 0) || ($end <= $currentTime))
				return false;
		}

		return true;
	}

	private function getClaims($data) {
		$attributes = $data['XPath']->query('./saml:AttributeStatement/saml:Attribute', $data['Assertion']);

		$claims = array ();
		foreach ($attributes as $attribute) {
			array_push($claims, new Claim($attribute->getAttribute('Name'), $attribute->textContent));
		}

		return $claims;
	}

	private function error($error) {
		throw new Exception("Error: " . $error);
	}
}
?>
