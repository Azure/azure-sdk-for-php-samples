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

require_once (dirname(__FILE__) . '/Saml2TokenValidator.php');
require_once (dirname(__FILE__) . '/FederatedPrincipal.php');
require_once (dirname(__FILE__) . '/FederatedConfiguration.php');
require_once (dirname(__FILE__) . '/IFederatedAuthenticationObserver.php');

class FederatedLoginManager {
	const PRINCIPAL_SESSION_VARIABLE = '_FederatedPrincipal_';

	public $validateExpiration = true;
	public $validateIssuer = false;
	public $validateAudiences = true;
	public $thumbprint = null;
	public $audience = null;
	public $trustedIssuer = null;

	private $authenticationObserver;

	public function __construct($authenticationObserver = null) {
		$this->authenticationObserver = $authenticationObserver;
	}

	public static function getFederatedLoginUrl($returnUrl) {
		return FederatedLoginManager :: getFederatedCustomLoginUrl(null, null, $returnUrl);
	}

	public static function getFederatedCustomLoginUrl($realm, $replyUrl, $returnUrl) {
		if ($realm == null) {
			$realm = FederatedConfiguration :: getInstance()->getRealm();
		}

		if ($replyUrl == null) {
			$replyUrl = FederatedConfiguration :: getInstance()->getReply();
		}

		return FederatedConfiguration :: getInstance()->getStsUrl() . '?wa=wsignin1.0&wtrealm=' . urlencode($realm) . '&wctx=' . urlencode($returnUrl) . '&id=passive&wreply=' . urlencode($replyUrl);
	}

	public function authenticate($token) {
		$validator = new Saml2TokenValidator();

		$validator->allowedAudiences = $this->getAudienceUris();
		$validator->trustedIssuers = $this->getTrustedIssuers();
		$validator->thumbprints = $this->getThumprints();
		$validator->validateAudiences = $this->validateAudiences;
		$validator->validateIssuer = $this->validateIssuer;
		$validator->validateExpiration = $this->validateExpiration;
		$claims = $validator->validate($token);

		$principal = new FederatedPrincipal($claims);
		$_SESSION[self::PRINCIPAL_SESSION_VARIABLE] = $principal;

		if (isset ($this->authenticationObserver))
			$this->authenticationObserver->onAuthenticationSucceed($principal);

		
		header('Pragma: no-cache');
		header('Cache-Control: no-cache, must-revalidate');
		header("Location: " . $_POST['wctx'], true, 302);
		
	}

	public function getPrincipal() {
		return $_SESSION[self::PRINCIPAL_SESSION_VARIABLE];
	}

	public function getClaims() {
		if ($this->isAuthenticated())
			return $this->normalizeClaimList($this->getPrincipal()->getClaims());
	}

	public function isAuthenticated() {
		return isset ($_SESSION[self::PRINCIPAL_SESSION_VARIABLE]);
	}

	protected function getAudienceUris() {
		if ($this->audience !== null)
			return array (
				$this->audience
			);
		else
			return FederatedConfiguration :: getInstance()->getAudienceUris();
	}

	protected function getTrustedIssuers() {
		if ($this->trustedIssuer !== null)
			return array (
				$this->trustedIssuer
			);
		else
			return FederatedConfiguration :: getInstance()->getTrustedIssuers();
	}

	protected function getThumprints() {
		if ($this->thumbprint !== null)
			return array (
				$this->thumbprint
			);
		else
			return array (
				FederatedConfiguration :: getInstance()->getThumbprint()
			);
	}

	private function normalizeClaimList($originalClaims) {
		assert('is_array($originalClaims)');

		$claims = array ();
		if ($originalClaims !== null) {
			foreach ($originalClaims as $originalClaim) {
				foreach ($originalClaim->getClaimValues() as $claimValue) {
					array_push($claims, new Claim($originalClaim->claimType, $claimValue));
				}
			}
		}

		return $claims;
	}
}
?>
