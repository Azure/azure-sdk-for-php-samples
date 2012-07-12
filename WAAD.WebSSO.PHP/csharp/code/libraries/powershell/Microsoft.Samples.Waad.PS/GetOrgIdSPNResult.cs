namespace Microsoft.Samples.Waad.PS
{
    using System;
    using System.Globalization;

    public class GetOrgIdSpnResult
    {
        private const string Display = "<issuer name=\"{0}\" displayName=\"{0}\" realm=\"{1}\" />";

        public GetOrgIdSpnResult(Guid appId, string appDomain, Guid idpId, string entityId)
        {
            this.ApplicationId = appId;
            this.ApplicationDomain = appDomain;
            this.Spn = string.Format("spn:{0}@{1}", appId, idpId);
        }

        public Guid ApplicationId { get; internal set; }

        public string ApplicationDomain { get; internal set; }

        public string Spn { get; internal set; }

        public override string ToString()
        {
            return string.Format(CultureInfo.InvariantCulture, Display, this.ApplicationDomain, this.Spn);
        }
    }
}
