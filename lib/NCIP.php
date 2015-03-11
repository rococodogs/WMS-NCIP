<?php
namespace WMS;

class NCIP {
    /**
     *  scheme urls
     */

    const NISO_NCIP_SCHEME = 'http://www.niso.org/2008/ncip';
    const NISO_REQUEST_SCOPE_TYPE_SCHEME = 'http://www.niso.org/ncip/v1_0/imp1/schemes/requestscopetype/requestscopetype.scm';
    const NISO_REQUEST_TYPE_SCHEME = 'http://www.niso.org/ncip/v1_0/imp1/schemes/requesttype/requesttype.scm';
    const OCLC_AGENCY_ID_SCHEME = 'http://oclc.org/ncip/schemes/agencyid.scm';
    const OCLC_APPLICATION_PROFILE_SCHEME = 'http://oclc.org/ncip/schemes/application-profile/platform.scm';
    const OCLC_ELEMENT_TYPE_SCHEME = 'http://worldcat.org/ncip/schemes/v2/extensions/elementtype.scm';
    const OCLC_EXTENSIONS_SCHEME = 'http://oclc.org/WCL/ncip/2011/extensions';
    const OCLC_RENEW_ALL_SCHEME = 'http://www.oclc.org/ncip/renewall/2014';
    const OCLC_SORT_FIELD_SCHEME = 'http://worldcat.org/ncip/schemes/v2/extensions/accountdetailselementtype.scm';
    const OCLC_SORT_ORDER_SCHEME = 'http://worldcat.org/ncip/schemes/v2/extensions/sortordertype.scm';
    const OCLC_USER_NOTE_SCHEME = 'http://www.oclc.org/ncip/usernote/2012';
    const XML_SCHEMA_INSTANCE = 'http://www.w3.org/2001/XMLSchema-instance';
    const XSI_SCHEMA_LOCATION = 'http://www.niso.org/2008/ncip http://www.niso.org/schemas/ncip/v2_01/ncip_v2_01.xsd';


    const APPLICATION_PROFILE_TYPE = 'Version 2011';
    const NCIP_VERSION = 'http://www.niso.org/schemas/ncip/v2_01/ncip_v2_01.xsd';



    const REQUEST_TYPE_HOLD = 'Hold';
    const REQUEST_SCOPE_TYPE_BIBLIOGRAPHIC = 'Bibliographic Item';
    const REQUEST_SCOPE_TYPE_ITEM = 'Item';
}
