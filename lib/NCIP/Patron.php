<?php
namespace WMS\NCIP;

use WMS\NCIP;

class Patron extends BaseRequest {

    /**
     *  gets the URL to send the request
     *
     *  @return  string
     */

    protected function getRequestURL() {
        return 'https://' . $this->agencyID . '.share.worldcat.org/ncip/circ-patron';
    }

    /**
     *
     *
     *
     */

    public function lookupUser( $userID, $opts = array() ) {
        $opts = array_merge( array(
            'getFiscalAccount' => array(
                'start' => 1,
                'max' => 10,
                'sortField' => 'Accrual Date',
                'sortOrder' => 'Ascending'
            ),
            
            'getLoanedItems' => array(
                'start' => 1,
                'max' => 10,
                'sortField' => 'Date Due',
                'sortOrder' => 'Ascending'
            ),
            
            'getRequestedItems' => array(
                'start' => 1,
                'max' => 10,
                'sortField' => 'Date Placed',
                'sortOrder' => 'Ascending'
            )
        ), $opts );

        // we need to add the ns2 declaration
        $attributes = array(
            'xmlns'              => NCIP::NISO_NCIP_SCHEME,
            'xmlns:ncip'         => NCIP::NISO_NCIP_SCHEME,
            'xmlns:ns2'          => NCIP::OCLC_EXTENSIONS_SCHEME,
            'xmlns:xsi'          => NCIP::XML_SCHEMA_INSTANCE,
            'ncip:version'       => NCIP::NCIP_VERSION,
            'xsi:schemaLocation' => NCIP::XSI_SCHEMA_LOCATION
        );

        $requestBody = $this->createRequest( 'LookupUser', $attributes, function( $xml ) use( $userID, $opts ) {
            $xml->startElement( 'UserId' );
                $xml->writeElement( 'UserIdentifierValue', $userID );
            $xml->endElement();
            
            if ( is_array( $opts['getFiscalAccount'] ) ) {
                $xml->writeRaw( '<UserFiscalAccountDesired/>' );
            }

            if ( is_array( $opts['getLoanedItems'] ) ) {
                $xml->writeRaw( '<LoanedItemsDesired/>' );
            }

            if ( is_array( $opts['getRequestedItems'] ) ) {
                $xml->writeRaw( '<RequestedItemsDesired/>' );
            }

            $xml->startElement( 'Ext' );
                if ( is_array( $opts['getFiscalAccount'] ) ) {
                    $gfa = $opts['getFiscalAccount'];

                    $xml->startElementNS( 'ns2', 'ElementType', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_ELEMENT_TYPE_SCHEME );
                        $xml->text( 'Account Details' );
                    $xml->endElement();

                    $xml->writeElementNS( 'ns2', 'StartElement', null, $gfa['start'] );
                    $xml->writeElementNS( 'ns2', 'MaximumCount', null, $gfa['max'] );

                    $xml->startElementNS( 'ns2', 'SortField', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_SORT_FIELD_SCHEME );
                        $xml->text( $gfa['sortField'] );
                    $xml->endElement();

                    $xml->startElementNS( 'ns2', 'SortOrderType', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::SORT_ORDER_SCHEME );
                        $xml->text( $gfa['sortOrder'] );
                    $xml->endElement();
                }

                if ( is_array( $opts['getLoanedItems'] ) ) {
                    $gli = $opts['getLoanedItems'];

                    $xml->startElementNS( 'ns2', 'ElementType', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_ELEMENT_TYPE_SCHEME );
                        $xml->text( 'Loaned Item' );
                    $xml->endElement();

                    $xml->writeElementNS( 'ns2', 'StartElement', null, $gli['start'] );
                    $xml->writeElementNS( 'ns2', 'MaximumCount', null, $gli['max'] );

                    $xml->startElementNS( 'ns2', 'SortField', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_SORT_FIELD_SCHEME );
                        $xml->text( $gli['sortField'] );
                    $xml->endElement();

                    $xml->startElementNS( 'ns2', 'SortOrderType', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_SORT_ORDER_SCHEME );
                        $xml->text( $gli['sortOrder'] );
                    $xml->endElement();
                }

                if ( is_array( $opts['getRequestedItems'] ) ) {
                    $gri = $opts['getRequestedItems'];

                    $xml->startElementNS( 'ns2', 'ElementType', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_ELEMENT_TYPE_SCHEME );
                        $xml->text( 'Requested Item' );
                    $xml->endElement();

                    $xml->writeElementNS( 'ns2', 'StartElement', null, $gri['start'] );
                    $xml->writeElementNS( 'ns2', 'MaximumCount', null, $gri['max'] );

                    $xml->startElementNS( 'ns2', 'SortField', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_SORT_FIELD_SCHEME );
                        $xml->text( $gri['sortField'] );
                    $xml->endElement();

                    $xml->startElementNS( 'ns2', 'SortOrderType', null );
                        $xml->writeAttribute( 'ncip:Scheme', NCIP::OCLC_SORT_ORDER_SCHEME );
                        $xml->text( $gri['sortOrder'] );
                    $xml->endElement();
                }

            $xml->endElement();
        });

        return $this->sendRequest( $requestBody );
    }   

    /**
     *
     *
     *
     */

    public function renewAllItem( $userID, $opts = array() ) {
        $opts = array_merge( array(
            'userAgencyID' => $this->agencyID
        ), $opts );
        
        $attributes = array(
            'xmlns'     => NCIP::OCLC_EXTENSIONS_SCHEME,
            'xmlns:ns2' => NCIP::NISO_NCIP_SCHEME,
            'xmlns:ns3' => NCIP::OCLC_RENEW_ALL_SCHEME,
            'xmlns:ns4' => NCIP::OCLC_USER_NOTE_SCHEME,
            'ns2:version' => NCIP::NCIP_VERSION
        );

        /**
         *  the RenewAllItem example is pretty gnarly w/r/t namespacing, so this one's 
         *  being built in its entirety, rather than passing it through `BaseRequest::createRequest`
         *  http://www.oclc.org/content/dam/developer-network/web-services/wms-ncip-api/patron-profile-renew-all-request.xml
         *
         */

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument( '1.0', 'UTF-8' );

        $xml->startElementNS( 'ns2', 'NCIPMessage', null );
        foreach( $attributes as $key => $val ) {
            $xml->writeAttribute( $key, $val );
        }
            // <ns2:Ext>
            $xml->startElementNS( 'ns2', 'Ext', null );
                // <ns3:RenewAllItem>
                $xml->startElementNS( 'ns3', 'RenewAllItem', null );
                    // <ns2:InitiationHeader>
                    $xml->startElementNS( 'ns2', 'InitiationHeader', null );
                        // <ns2:FromAgencyId>
                        $xml->startElementNS( 'ns2', 'FromAgencyId' );
                            // <ns2:AgencyId ...>
                            $xml->startElementNS( 'ns2', 'AgencyId' );
                                $xml->writeAttributeNS( 'ns2', 'Scheme', null, NCIP::OCLC_AGENCY_ID_SCHEME )
                                $xml->text( $this->agencyID );
                            // </ns2:AgencyId>
                            $xml->endElement();
                        // </ns2:FromAgencyId>
                        $xml->endElement();

                        // <ns2:ToAgencyId>
                        $xml->startElementNS( 'ns2', 'ToAgencyId', null );
                            // <ns2:AgencyId>
                            $xml->startElementNS( 'ns2', 'AgencyId', null );
                                $xml->writeAttributeNS( 'ns2' 'Scheme', null, NCIP::OCLC_AGENCY_ID_SCHEME );
                                $xml->text( $this->agencyID );
                            // </ns2:ToAgencyId>
                            $xml->endElement();
                        // <ns2:ToAgencyId>
                        $xml->endElement();
                    // </ns2:InitiationHeader>
                    $xml->endElement();

                    // <ns2:UserId>
                    $xml->startElementNS( 'ns2', 'UserId', null );
                        // <ns2:AgencyId ...>
                        $xml->startElementNS( 'ns2', 'AgencyId', null );
                            $xml->writeAttributeNS( 'ns2', 'Scheme', null, NCIP::OCLC_AGENCY_ID_SCHEME );
                            $xml->text( $opts['userAgencyID'] );
                        // </ns2:AgencyId>
                        $xml->endElement();

                        // <ns2:UserIdentifierValue> ... </...>
                        $xml->writeElementNS( 'ns2', 'UserIdentifierValue', null, $userID );

                    // </ns2:UserId>
                    $xml->endElement();

                // </ns3:RenewAllItem>
                $xml->endElement();
            // </ns2:Ext>
            $xml->endElement();
        // </ns2:NCIPMessage>
        $xml->endElement();

        $requestBody = $xml->outputMemory();

        return $this->sendRequest( $requestBody );

    }

    /**
     *  grammatically-nicer wrapper for `Patron::renewAllItem`
     */

    public function renewAllItems( $userID, $opts = array() ) {
        return $this->renewAllItem( $userID, $opts );
    }

    /**
     *
     *
     *
     */

    public function renewItem( $userID, $itemID, $opts = array() ) {
        $opts = array_merge( array(
            'itemAgencyID' => $this->agencyID,
            'userAgencyID' => $this->agencyID
        ), $opts );

        $requestBody = $this->createRequest( 'RenewItem', function( $xml ) use ( $userID, $itemID, $opts ) {
            $this->appendItemID( $xml, $itemID, $opts['itemAgencyID'] );
            $this->appendUserID( $xml, $userID, $opts['userAgencyID'] );
        } );

        return $this->sendRequest( $requestBody );
    }
}
