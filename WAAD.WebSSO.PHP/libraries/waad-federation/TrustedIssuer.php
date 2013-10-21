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

require_once (dirname(__FILE__) . '/../federation/FederatedLoginManager.php');

class TrustedIssuer {
	public $name;
	public $displayName;
	public $spn;
	public $replyUrl;
		
	public function __construct($name, $displayName, $spn, $replyUrl = null) {
		$this->name = $name;
		$this->displayName = $displayName;
		$this->spn = $spn;
		$this->replyUrl = $replyUrl;
	}
	
	public function getLoginUrl($returnUrl) {
		return FederatedLoginManager :: getFederatedCustomLoginUrl($this->spn, $this->replyUrl, $returnUrl);
	}
}
?>