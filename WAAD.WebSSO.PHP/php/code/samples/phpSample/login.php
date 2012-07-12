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
// uncomment this to display internal server errors.
//error_reporting(E_ALL);
//ini_set('display_errors', 'On');
ini_set('include_path', ini_get('include_path').';../../libraries/;');
require_once ('waad-federation/TrustedIssuersRepository.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Login Page</title>
</head>
<body>
	<h2>Login Page</h2>	
	<ul>
<?php 
	$repository = new TrustedIssuersRepository();
	$trustedIssuers = $repository->getTrustedIdentityProviderUrls();

	foreach ($trustedIssuers as $trustedIssuer) {
		$returnUrl = $_GET['returnUrl'];
		print_r('<li><a href="' . $trustedIssuer->getLoginUrl($returnUrl) . '">' . $trustedIssuer->displayName . '</a></li>');
	}
?>
	</ul>
</body>
</html>