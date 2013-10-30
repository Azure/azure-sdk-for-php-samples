<?php

/** * Copyright 2013 Microsoft Corporation 
        *  
        * Licensed under the Apache License, Version 2.0 (the "License"); 
        * you may not use this file except in compliance with the License. 
        * You may obtain a copy of the License at 
        * http://www.apache.org/licenses/LICENSE-2.0 
        *  
        * Unless required by applicable law or agreed to in writing, software 
        * distributed under the License is distributed on an "AS IS" BASIS, 
        * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
        * See the License for the specific language governing permissions and 
        * limitations under the License. 
        */

require_once 'WindowsAzure\WindowsAzure.php';

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\CloudConfigurationManager;
use WindowsAzure\Blob\Models\ListContainersOptions;
use WindowsAzure\Blob\Models\CreateBlobPagesOptions;
use WindowsAzure\Blob\Models\PageRange;
use WindowsAzure\Common\ServiceException;

define("CONTAINERNAME", "mycontainer");
define("PAGEBLOBNAME", "mypageblob");

define("PAGESIZE", 512);  // Must be a multiple of 512.

define("NUMPAGES", 6);    // Keeping it small for this example.

function createContainerIfNotExists($blobRestProxy)
{
    // See if the container already exists.
    $listContainersOptions = new ListContainersOptions;
    $listContainersOptions->setPrefix(CONTAINERNAME);
    $listContainersResult = $blobRestProxy->listContainers($listContainersOptions);
    $containerExists = false;
    foreach ($listContainersResult->getContainers() as $container)
    {
        if ($container->getName() == CONTAINERNAME)
        {
            // The container exists.
            $containerExists = true;
            // No need to keep checking.
            break;
        }
    }
    if (!$containerExists)
    {
        echo "Creating container.\n";
        $blobRestProxy->createContainer(CONTAINERNAME);
        echo "Container '" . CONTAINERNAME . "' successfully created.\n";
    }
}

function createPageBlob($blobRestProxy)
{

    echo "Creating page blob.\n";
    $len = NUMPAGES * PAGESIZE;
    $blobRestProxy->createPageBlob(CONTAINERNAME, PAGEBLOBNAME, $len);
    echo "Page blob '" . PAGEBLOBNAME . "' successfully created with length $len.\n";

}

function writeContent($blobRestProxy, $pageIndex, $createBlobPagesOptions)
{
    echo "Writing to blob using page index $pageIndex.\n";

    // Determine the page range.
    $start = $pageIndex * PAGESIZE;
    $end = $start + PAGESIZE - 1;
    $pageRange = new PageRange($start, $end);
    
    // Generate a random string of the desired length.
    $content = "";
    for ($i = 0; $i < PAGESIZE; $i++)
    {
      // Create the string with random lowercase alphabet characters.
      $content .= chr(mt_rand(97, 122));
    }

    $leaseID = $blobRestProxy->acquireLease(CONTAINERNAME, PAGEBLOBNAME)->getLeaseId();
    echo "Acquired lease $leaseID.\n";
    $createBlobPagesOptions->setLeaseId($leaseID);
    $blobRestProxy->createBlobPages(CONTAINERNAME, PAGEBLOBNAME, $pageRange, $content, $createBlobPagesOptions);
    $blobRestProxy->releaseLease(CONTAINERNAME, PAGEBLOBNAME, $leaseID);
    echo "Released lease $leaseID.\n";
    echo "Wrote to blob.\n";
}

function clearPages($blobRestProxy, $pageIndex, $numPages, $createBlobPagesOptions)
{
    echo "Clearing page(s).\n";
    $pageRange = new PageRange($pageIndex * PAGESIZE, ($pageIndex + $numPages) * PAGESIZE - 1);                  
    $leaseID = $blobRestProxy->acquireLease(CONTAINERNAME, PAGEBLOBNAME)->getLeaseId();
    echo "Acquired lease $leaseID.\n";
    $createBlobPagesOptions->setLeaseId($leaseID);
    $blobRestProxy->clearBlobPages(CONTAINERNAME,  PAGEBLOBNAME, $pageRange, $createBlobPagesOptions);
    $blobRestProxy->releaseLease(CONTAINERNAME,  PAGEBLOBNAME, $leaseID);
    echo "Released lease $leaseID.\n";
    echo "Cleared $numPages page(s), beginning with page $pageIndex.\n";
}

function showActiveRanges($blobRestProxy)
{
    echo "Determining active ranges.\n";
    $listPageRangesResult = $blobRestProxy->listPageBlobRanges(CONTAINERNAME, PAGEBLOBNAME);
    $ranges = $listPageRangesResult->getPageRanges();
    if (0 == count($ranges))
    {
        echo "No ranges are active.\n";
    }
    else
    {
        echo "Active ranges: ";
        echo "< ";
        foreach ($ranges as $range)
        {
            echo "[" . $range->getStart() . " - " . $range->getEnd() . "] ";
        }
        echo " >\n";
    }
}

function displayContents($blobRestProxy)
{
    $getBlobResult = $blobRestProxy->getBlob(CONTAINERNAME, PAGEBLOBNAME);
    $stream = $getBlobResult->getContentStream();
    echo "Displaying the blob contents.\n";
    fpassthru($stream);
    fclose($stream);
    echo "\n";
}

function deleteBlob($blobRestProxy)
{
    echo "Deleting the blob.\n";
    $blobRestProxy->deleteBlob(CONTAINERNAME, PAGEBLOBNAME);
    echo "Deleted the blob named '" . PAGEBLOBNAME . "' from the '" . CONTAINERNAME . "' container.\n";
}

function waitForEnterKey()
{
    // Prompt the user to press the Enter key.
    echo "Press Enter to continue. ";
    fgets(STDIN);
  
    // Add a blank link.
    echo "\n";
}

try 
{
    echo "Beginning processing.\n";
  
    /* 
        Use CloudConfigurationManager::getConnectionString to retrieve 
        the connection string whose name (in this example) is 
        "StorageConnectionString".
        
        By default, the CloudConfigurationManager::getConnectionString method
        will look for an environment variable with the name that is passed in
        as the method parameter, and then assign the environment variable's 
        value as the return value.
        
        For example, if you want to use the storage emulator, start
        the storage emulator, set an environment variable through a technique 
        such as 
        
            set StorageConnectionString=UseDevelopmentStorage=true
            
        and then run this sample at a command prompt that has the 
        StorageConnectionString as an active environment variable.

        If you want to use a production storage account, set the 
        environment variable through a technique such as
        
            set StorageConnectionString=DefaultEndpointsProtocol=http;AccountName=your_account_name;AccountKey=your_account_key
            
        (Substitute your storage account name and account key for 
        your_account_name and your_account_key, respectively.) 
        Then run this sample at a command prompt that has the 
        StorageConnectionString as an active environment variable.

        The format for the storage connection string itself is documented at        
        http://msdn.microsoft.com/en-us/library/windowsazure/ee758697.aspx

        If you do not want to use an environment variable as the source
        for the connection string name, you can register other sources 
        via the CloudCofigurationManager::registerSource method.
        
    */
    $connectionString = CloudConfigurationManager::getConnectionString("StorageConnectionString");
    
    if (null == $connectionString || "" == $connectionString)
    {
        echo "Did not find a connection string whose name is 'StorageConnectionString'.";
        exit();
    }
   
    $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);

    createContainerIfNotExists($blobRestProxy);

    echo "Using the '" . CONTAINERNAME . "' container and the '" . PAGEBLOBNAME . "' blob.\n";
        
    $createBlobPagesOptions = new CreateBlobPagesOptions();

    // Create the page blob.
    createPageBlob($blobRestProxy);
    waitForEnterKey();

    // Show active ranges (there won't be any for a newly created blob).
    showActiveRanges($blobRestProxy);
    waitForEnterKey();

    // Write to the blob, using the third page. The page index is zero-based.
    writeContent($blobRestProxy, 2, $createBlobPagesOptions);
    waitForEnterKey();

    // Show active ranges.
    showActiveRanges($blobRestProxy);
    waitForEnterKey();

    // Display the contents of the blob.
    displayContents($blobRestProxy);
    waitForEnterKey();

    // Write to the blob again, using the first page.
    writeContent($blobRestProxy, 0, $createBlobPagesOptions);
    waitForEnterKey();
        
    // Show active ranges.
    showActiveRanges($blobRestProxy);
    waitForEnterKey();

    // Display the contents of the blob.
    displayContents($blobRestProxy);
    waitForEnterKey();

    // Starting at the third page, clear one page.
    clearPages($blobRestProxy, 2, 1, $createBlobPagesOptions);
    waitForEnterKey();

    // Show active ranges.
    showActiveRanges($blobRestProxy);
    waitForEnterKey();

    // Display the contents of the blob.
    displayContents($blobRestProxy);
    waitForEnterKey();
        
    // Delete the blob.
    // Comment this line out if you want to keep the blob.
    deleteBlob($blobRestProxy);

    echo "Exiting application.\n";
}
catch(ServiceException $serviceException)
{
    // Handle exception based on error codes and messages.
    // Error codes and messages are here: 
    // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
    echo "ServiceException encountered.\n";
    $code = $serviceException->getCode();
    $error_message = $serviceException->getMessage();
    echo "$code: $error_message";
}
catch (Exception $exception) 
{
    echo "Exception encountered.\n";
    $code = $exception->getCode();
    $error_message = $exception->getMessage();
    echo "$code: $error_message";
}

?>