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
// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

ini_set('include_path', ini_get('include_path').';../../libraries/;');
require_once ('waad-federation/ConfigurableFederatedLoginManager.php');

session_start();
$token = $_POST['wresult'];
$loginManager = new ConfigurableFederatedLoginManager();

if (!$loginManager->isAuthenticated()) {
	if (isset ($token)) {
		try {
			$loginManager->authenticate($token);			
		} catch (Exception $e) {
			print_r($e->getMessage());
		}
	} else {
		$returnUrl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

		header('Pragma: no-cache');
		header('Cache-Control: no-cache, must-revalidate');
		header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/login.php?returnUrl=" . $returnUrl, true, 302);
		exit();
	}
}
?>