namespace Microsoft.Samples.Waad.PS
{
    using System;
    using System.Management.Automation;
    using System.Xml;

    [Cmdlet(VerbsCommon.Get, "OrgIdConfig")]
    public class GetOrgIdSpn : PSCmdlet
    {
        private const string FederationMetadata = "https://accounts.accesscontrol.windows.net/FederationMetadata/2007-06/FederationMetadata.xml?realm={0}";
        private const string EntityDescriptor = "EntityDescriptor";
        private const string EntityId = "entityID";

        [Parameter(Mandatory = true, ValueFromPipelineByPropertyName = true, ParameterSetName = "default", HelpMessage = "The Application Principal Identifier returned when registering a new principal.")]
        [ValidateNotNullOrEmpty]
        public string AppPrincipalId { get; set; }

        [Parameter(Mandatory = true, ValueFromPipelineByPropertyName = true, ParameterSetName = "default", HelpMessage = "The Application Domain used when registering a new principal.")]
        [ValidateNotNullOrEmpty]
        public string ApplicationDomain { get; set; }

        protected override void ProcessRecord()
        {
            try
            {
                base.ProcessRecord();

                var result = this.GetSpn();

                Console.Write("\nAdd the following issuer entry to the XML file");
                Console.Write("\n==============================================");
                Console.Write('\n' + result.ToString() + "\n\n");
            }
            catch (Exception ex)
            {
                WriteError(new ErrorRecord(ex, string.Empty, ErrorCategory.CloseError, null));
            }
        }

        private GetOrgIdSpnResult GetSpn()
        {
            string entityDescriptor = string.Empty;
            Guid idpIdentifier = Guid.Empty;

            using (var reader = new XmlTextReader(string.Format(FederationMetadata, this.ApplicationDomain)))
            {
                var xml = new XmlDocument();
                xml.Load(reader);
                var descriptor = xml.GetElementsByTagName(EntityDescriptor);

                if ((descriptor != null) && (descriptor.Count > 0))
                {
                    entityDescriptor = descriptor[0].Attributes[EntityId].Value;
                    idpIdentifier = new Guid(entityDescriptor.Split('@')[1]);
                }

                return new GetOrgIdSpnResult(new Guid(this.AppPrincipalId), this.ApplicationDomain, idpIdentifier, entityDescriptor);
            }
        }
    }
}
