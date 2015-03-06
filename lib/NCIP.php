<?php
namespace WMS;

class WMS {

    const XMLNS = 'http://www.niso.org/2008/ncip';
    const XMLNS_NCIP = 'http://www.niso.org/2008/ncip';
    const XMLNS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';
    const NCIP_VERSION = 'http://www.niso.org/schemas/ncip/v2_01/ncip_v2_01.xsd';
    const XSI_SCHEMA_LOCATION = 'http://www.niso.org/2008/ncip http://www.niso.org/schemas/ncip/v2_01/ncip_v2_01.xsd';

    const AGENCY_ID_SCHEME = 'http://oclc.org/ncip/schemes/agencyid.scm';
    const APPLICATION_PROFILE_TYPE_SCHEME = 'http://oclc.org/ncip/schemes/application-profile/platform.scm';
    const REQUEST_TYPE_SCHEME = 'http://www.niso.org/ncip/v1_0/imp1/schemes/requesttype/requesttype.scm';
    const REQUEST_SCOPE_TYPE_SCHEME = 'http://www.niso.org/ncip/v1_0/imp1/schemes/requestscopetype/requestscopetype.scm';

    const APPLICATION_PROFILE_TYPE = 'Version 2011';

    const REQUEST_TYPE_HOLD = 'Hold';
    const REQUEST_SCOPE_TYPE_BIBLIOGRAPHIC = 'Bibliographic Item';
    const REQUEST_SCOPE_TYPE_ITEM = 'Item';
}