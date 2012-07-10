
IMPORTANT STEPS BEFORE USING THIS SAMPLE:

********************************************************************

STEP 1: Compile the Microsft.Samples.Waad.PS.dll from command line

********************************************************************

In order to use this example correctly, you will need to built the PowerShell DLL to provide the correct loading of the Microsoft.Samples.Waad.PS linka as referred to in the documentation. 

The source for this DLL is included under %ROOT%/csharp/code/libraries/powershell/Microsoft.Samples.Waad.PS

You may either load this in to Visual Studio 2008 / 2010 and build, or you may use the included script as follows:

%ROOT%/csharp/code/libraries/powershell/Microsoft.Samples.Waad.PS/buildWaadPS.bat

It's a simple MSBuild script that will run the default MSBuild with DEBUG configuration and copy the correct .dll in to the \java\
scripts\ directory

********************************************************************

STEP 2: Download the simpleSAMLphp library

********************************************************************

 SimpleSAMLphp is an award-winning application written in native PHP that deals with authentication. The project is led by UNINETT, has a large user base, a helpful user community and a large set of external contributors.

SimpleSAMLphp is having a main focus on providing support for:
 •SAML 2.0 as a Service Provider.
 •SAML 2.0 as a Identity Provider.
 
But also supports some other identity protocols, such as Shibboleth 1.3, A-Select, CAS, OpenID, WS-Federation and OAuth.

You can download this by going to: http://simplesamlphp.org/

and including the base install underneath the root directory such as:

%ROOT/simplesamlphp



Enjoy! 

