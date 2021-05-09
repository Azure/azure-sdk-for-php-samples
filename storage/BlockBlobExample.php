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
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Common\CloudConfigurationManager;
use WindowsAzure\Blob\Models\Block;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\ListContainersOptions;

define("CONTAINERNAME", "mycontainer");
define("BLOCKBLOBNAME", "myblockblob");

define("BLOCKSIZE", 4 * 1024 * 1024);    // Size of the block, modify if needed.
define("PADLENGTH", 5);                  // Size of the string used for the block ID, modify if needed.

define("FILENAME", "myfile.txt");        // Local file to upload as a block blob.

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

try {

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

    echo "Using the '" . CONTAINERNAME . "' container and the '" . BLOCKBLOBNAME . "' blob.\n";

    echo "Using file '" . FILENAME . "'\n";

    if (!file_exists(FILENAME))
    {
        echo "The '" . FILENAME . "' file does not exist. Exiting program.\n";
        exit();        
    }

    $handle = fopen(FILENAME, "r");

    // Upload the blob using blocks.
    $counter = 1;
    $blockIds = array();

    while ($data = fread($handle, BLOCKSIZE))
    {
        $blockId = str_pad($counter, PADLENGTH, "0", STR_PAD_LEFT);
        echo "Processing block $blockId.\n";
        
        $block = new Block();
        $block->setBlockId(base64_encode($blockId));
        $block->setType("Uncommitted");
        array_push($blockIds, $block);
        
        // Upload the block.
        $blobRestProxy->createBlobBlock(CONTAINERNAME, BLOCKBLOBNAME, base64_encode($blockId), $data);
        $counter++;
    }

    // Done creating the blocks. Close the file and commit the blocks.
    fclose($handle);
    echo "Commiting the blocks.\n";    
    $blobRestProxy->commitBlobBlocks(CONTAINERNAME, BLOCKBLOBNAME, $blockIds);
    
    echo "Done processing.\n";
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
