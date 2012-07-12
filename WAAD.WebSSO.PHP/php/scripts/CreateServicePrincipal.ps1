#
#  this is a PS1 script that creates a service principal for webSSO and 
# accessing the Windows Azure Active Directory Graph API
#


# add a warning/disclaimer about creation of service principals
""
"--------------------------------------------------------------------"
"WARNING: you are about to create a service principal that allows for this application to access your Azure Active Directory tenant. This includes access to your entire Address Book, staff heirarchy, and license information. Please proceed only if you understand what you are doing and are an Administrator for the account you wish to use"
"-------------------------------------------------------------------"
" "
"NOTE: Once created, you can always view the Service Principals you have created by using the Get-MsolServicePrincipal cmdlet from this PowerShell window. For a full list of commands available, including removing Service Principals, run get-help *-msolserviceprincipal* after this script is complete. "
""

$accept = Read-Host "Do you still wish to proceed?  (Y/N)"
# fill in logic to look for Y,N, Yes, No, or nothing (default is to exit if no resonse)

#ask the user for the Service Principal Name he wants to use. This ensures that during multiple runs we don't run in to conflicts
""
"--------------- Service Principal Name -------------------- "
" "
"Please enter a descriptive name for the Service Principal you wish to create."
""
"If you've created a Service Principal for this account before, you should use a new name or you will get an error that it already exists in this tenant."
""
"Example: IdentityDemo"
""
$servicePrincipalName = Read-Host "Enter a Service Principal Name"


# prompt for Tenant Admin credentials, then connect to the Azure AD tenant, enable PowerShell
# commandlets to support Service prinicpal managementy
#
" "
"--------------- Get Ready To Provide Your Administrator Credentials -------------------"
""
"You will need your Administrator account information for the next step. You will be prompted with a login screen that you will enter these credentials in to."
""
"Hit any key when ready"
" "
$null = Read-Host
$cr=get-credential
connect-msolservice -credential $cr
Import-Module MSOnlineExtended 

# this section is used to create a service principal credential using a symmetric key
" "
"--------------- Symmetric Key ---------------------"
""
"Using a symmetric key to idenify you to Azure Active Directory. This is currently a default key for Demo purposes. You can change it in the PowerShell script and alter the demo application to dymanically query from database."
" "
$credValue = "FStnXT1QON84B5o38aEmFdlNhEnYtzJ91Gg/JH/Jxiw="
$credType = "Symmetric"

# replyURL is used to for configuring webSSO
$replyHost = "aadexpensedemo.cloudapp.net"
$replyAddress = "https://" + $replyHost + "/"
$replyUrl = New-MsolServicePrincipalAddresses –Address $replyAddress
" "
" ----------------- the URL of the application we will return to after SSO -------------------"
""
"Using: $replyAddress as the application endpoint we will redirect to after sigle sign-on is complete."
"This should be the location of the demo app. If this looks wrong you can change it in the PowerShell script."
" "

# creating service principal using 
" "
"--------------- Creating the Service Principal inside of Azure --------------------"
" "
"We are ready to create the Service Principal for your tenant."
""

"Press any key when you are ready to proceed or Cntl-C to end."
""
$null = Read-Host
""
""
"Creating the Service Principal inside your Azure Active Directory tenant"
" "
""
$sp = New-MsolServicePrincipal -ServicePrincipalNames @("$servicePrincipalName/$replyHost") -DisplayName "$ServicePrincipalName" -Addresses $replyUrl -Type $credType -Value $credValue

# grant the Client app calling the Graph, Read or Write permissions
# add the Service Principal to a Role, to enable specific application permissions
# Read-only => "Service Support Administrator"
# Read & Write = > "Company Administrator"
#
$Read = "Service Support Administrator"
$ReadWrite = "Company Administrator"

" "
"Setting permissions to allow the Service Principal to have Read Only access to your Azure Active Directory tenant. See the PowerShell script to see how this is done."
" "
Add-MsolRoleMember -RoleMemberType ServicePrincipal -RoleName $Read -RoleMemberObjectId $sp.objectid

$tenantId = (get-msolcompanyinformation).objectId

"--------------- Script is complete ----------------------"
""
"Company ID (you will need to put this in the portal): " + $tenantId
"AppPrincipal ID(you will need to put this in the portal): " + $sp.AppPrincipalId
if ($credType = "Asymmetric"){"App Principal Secret: " + $credValue}
"Audience URI: " + $sp.AppPrincipalID + "@" + $tenantId
""
