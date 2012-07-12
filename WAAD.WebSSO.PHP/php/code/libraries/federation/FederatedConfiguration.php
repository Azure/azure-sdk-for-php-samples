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

class FederatedConfiguration {
	private static $instance;
	private $properties;

	public static function getInstance() {
		if (!isset (FederatedConfiguration :: $instance)) {
			FederatedConfiguration :: $instance = new FederatedConfiguration();
		}
		return FederatedConfiguration :: $instance;
	}

	private function __construct() {
		$this->properties = parse_ini_file('federation.ini');
	}

	public function getStsUrl() {
		return $this->properties['federation.trustedissuers.issuer'];
	}

	public function getStsFriendlyName() {
		return $this->properties['federation.trustedissuers.friendlyname'];
	}

	public function getThumbprint() {
		return $this->properties['federation.trustedissuers.thumbprint'];
	}

	public function getRealm() {
		return $this->properties['federation.realm'];
	}

	public function getReply() {
		return $this->properties['federation.reply'];
	}

	public function getTrustedIssuers() {	
		return explode('|', $this->properties['federation.trustedissuers']);
	}

	public function getAudienceUris() {
		return explode('|', $this->properties['federation.audienceuris']);
	}
}
?>
