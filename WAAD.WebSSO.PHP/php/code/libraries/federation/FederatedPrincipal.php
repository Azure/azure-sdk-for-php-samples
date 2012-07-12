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

class FederatedPrincipal {
	const NameClaimType = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name';
	const EmailClaimType = 'http://schemas.xmlsoap.org/claims/EmailAddress';

	private $claims = array ();

	public function __construct($claims) {
		$this->claims = $claims;
	}

	public function getName() {
		foreach ($this->claims as $claim) {
			if (strcmp($claim->claimType, FederatedPrincipal :: NameClaimType) === 0)
				return $claim->claimValue;
		}

		foreach ($this->claims as $claim) {
			if (strcmp($claim->claimType, FederatedPrincipal :: EmailClaimType) === 0)
				return $claim->claimValue;
		}

		return '';
	}

	public function getClaims() {
		return $this->claims;
	}
}
?>
