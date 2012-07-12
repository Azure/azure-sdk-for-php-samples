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

require_once (dirname(__FILE__) . '/TrustedIssuer.php');

class TrustedIssuersRepository {
	private $repositoryFileName;
	
	public function __construct($repositoryFileName = null) {
		if (!isset($repositoryFileName)) {
			$this->repositoryFileName = $this->getBasePath() . "/trustedIssuers.xml";
		} else {
			$this->repositoryFileName = $repositoryFileName;
		}
	}
	
	private function GetBasePath() {
		return substr($_SERVER['SCRIPT_FILENAME'], 0, strlen($_SERVER['SCRIPT_FILENAME']) - strlen(strrchr($_SERVER['SCRIPT_FILENAME'], "\\")));
	}
	
	public function getTrustedIdentityProviderUrls() {
		$xml = new XMLReader();
		$xml->open($this->repositoryFileName);
		
		$trustedIssuers = array ();
		
		while ($xml->read()) {
			if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == "issuer") {				
				array_push($trustedIssuers, new TrustedIssuer($xml->getAttribute("name"), $xml->getAttribute("displayName"), $xml->getAttribute("realm")));
			}			
		}
		
		return $trustedIssuers;
	}
	
	public function getTrustedIdentityProviderUrl($name, $replyUrl) {		
		$xml = new XMLReader();
		$xml->open($this->repositoryFileName);
		
		$trustedIssuers = array ();
		
		while ($xml->read()) {
			if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == "issuer" && $xml->getAttribute("name") == $name) {
				return new TrustedIssuer($xml->getAttribute("name"), $xml->getAttribute("displayName"), $xml->getAttribute("realm"), $replyUrl);
			}
		}
		
		return null;		
	}
}
?>