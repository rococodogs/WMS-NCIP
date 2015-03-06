<?php
namespace WMS\NCIP;

use WMS\NCIP;

class Staff extends BaseRequest {

    /**
     *  checks item in at specified location
     *
     *  @param mixed    item barcode
     *  @param mixed    agencyID (optional: defaults to initialized AgencyID)
     */

    public function checkInItem( $itemID, $agencyID = null ) {
        if ( !$agencyID ) { $agencyID = $this->agencyID; }

        $requestBody = $this->createRequest( 'CheckInItem', function( $xml ) use ( $itemID, $agencyID ) {
            $this->appendItemID( $xml, $itemID, $agencyID );
        } );
    }

    /**
     *  checks item out to patron
     *
     *  @param mixed    user barcode
     *  @param mixed    item barcode
     *  @param array    optional array of extended options:
     *                      'desiredDueDate': modified due date (defaults to null which uses WMS defaults for Library)
     *                      'itemAgencyID': AgencyID for item (defaults to initialized AgencyID)
     *                      'userAgencyID': AgencyID for user (defaults to initialized AgencyID)
     */

    public function checkOutItem( $userID, $itemID, $opts = array() ) {
        $opts = array_merge( array(
            'desiredDueDate' => null,
            'itemAgencyID' => $this->agencyID,
            'userAgencyID' => $this->agencyID,
        ), $opts );

        $requestBody = $this->createRequest( 'CheckOutItem', function( $xml ) use ( $userID, $itemID, $opts ) {
            $this->appendUserID( $xml, $userID, $opts['userAgencyID'] );
            $this->appendItemID( $xml, $itemID, $opts['itemAgencyID'] );

            if ( $opts['desiredDueDate'] ) {
                $date = date( 'c', strtotime( $opts['desiredDueDate']) );
                $xml->writeElement( 'DesiredDueDate', $date );
            }

        } );
    }

    /**
     *  request an item for a patron
     *
     *  @param mixed    user barcode
     *  @param mixed    item barcode
     *  @param array    optional array of extended options
     *                     'itemAgencyID': AgencyID for item (defaults to initialized AgencyID)
     *                     'pickupLocation': branch name for pickup (defaults to 'MAIN')
     *                     'requestScope': what level of request (defaults to 'Item')
     *                     'requestType': what kind of request (defaults to 'Hold')
     *                     'userAgencyID': AgencyID for user (defaults to initialized AgencyID)
     */

    public function requestItem( $userID, $itemID, $opts = array() ) {
        $opts = array_merge( array(
            'itemAgencyID' => $this->agencyID,
            'pickupLocation' => 'MAIN',
            'requestScope' => NCIP::REQUEST_SCOPE_TYPE_ITEM,
            'requestType' => NCIP::REQUEST_TYPE_HOLD,
            'userAgencyID' => $this->agencyID
        ), $opts );

        $requestBody = $this->createRequest( 'RequestItem', function( $xml ) use ( $userID, $itemID, $opts ) {
            $this->appendUserID( $xml, $userID, $opts['userAgencyID'] );
            $this->appendItemID( $xml, $itemID, $opts['itemAgencyID'] );

            $xml->startElement( 'RequestType' );
                $xml->writeAttribute( 'ncip:Scheme', NCIP::REQUEST_TYPE_SCHEME );
                $xml->text( $opts['requestType'] );
            $xml->endElement();

            $xml->startElement( 'RequestScopeType' );
                $xml->writeAttribute( 'ncip:Scheme', NCIP::REQUEST_SCOPE_TYPE_SCHEME );
                $xml->text( $opts['requestScope'] );
            $xml->endElement();

            $xml->writeElement( 'PickupLocation', $opts['pickupLocation'] );
        } );
    }
}