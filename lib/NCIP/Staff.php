<?php
namespace WMS\NCIP;

use WMS\NCIP;

class Staff extends BaseRequest {

    /**
     *  build the Staff object; calls the constructor of BaseRequest after 
     *  setting the datacenter (if provided)
     *
     *  @param  mixed   either (string) the datacenter or (array) an array of settings
     *  @param  array   optional array of settings (can just be the first param)
     */

    public function __construct( $datacenter, $settings = array() ) {
        if ( is_array( $datacenter ) && empty( $settings ) ) {
            $settings = $datacenter;
        }

        if ( isset($settings['datacenter']) ) {
            $this->setDatacenter( $settings['datacenter'] );
        }

        parent::__construct( $settings );
    }

    protected function getRequestURL() {
        if ( !isset( $this->datacenter) ) { throw new \Exception( "No datacenter set" ); }
        return 'https://circ.' . $this->datacenter . 'worldcat.org';
    }

    /**
     *  wrapper for `Staff::cancelRequestItem` that pushes the 'requestScope' option
     *  to be 'Bibliographic item'
     *
     *  @param mixed    user barcode
     *  @param string   unique identifier for request
     *  @param array    options
     *                     'itemAgencyID': AgencyID for item (defaults to initialized AgencyID)
     *                     'requestAgencyID': AgencyID for request (defaults to initialized AgencyID)
     *                     'requestType': what kind of request (defaults to 'Hold')
     *                     'userAgencyID': AgencyID for user (defaults to initialized AgencyID)
     */

    public function cancelBibItem( $userID, $requestIdentifier, $opts = array() ) {
        $opts = array_merge( $opts, array(
            'requestScope' => NCIP::REQUEST_SCOPE_TYPE_BIBLIOGRAPHIC
        ) );

        return $this->cancelRequestItem( $userID, $requestIdentifier, $opts );
    }


    /**
     *  cancels an item hold request
     * 
     *  @param mixed    user barcode
     *  @param string   unique identifier for request
     *  @param array    options
     *                     'itemAgencyID': AgencyID for item (defaults to initialized AgencyID)
     *                     'requestAgencyID': AgencyID for request (defaults to initialized AgencyID)
     *                     'requestScope': what level of request (defaults to 'Item')
     *                     'requestType': what kind of request (defaults to 'Hold')
     *                     'userAgencyID': AgencyID for user (defaults to initialized AgencyID)
     */

    public function cancelRequestItem( $userID, $requestIdentifier, $opts = array() ) {
        $opts = array_merge( array(
            'itemAgencyID' => $this->agencyID,
            'requestAgencyID' => $this->agencyID,
            'requestScope' => NCIP::REQUEST_SCOPE_TYPE_ITEM,
            'requestType' => NCIP::REQUEST_TYPE_HOLD,
            'userAgencyID' => $this->agencyID
        ), $opts );

        $requestBody = $this->createRequest( 'CancelRequestItem', function( $xml ) use ( $userID, $requestIdentifier, $opts ) {
            $this->appendUserID( $xml, $userID, $opts['userAgencyID'] );
            
            $xml->startElement( 'RequestId' );
                $xml->writeElement( 'AgencyId', $opts['requestAgencyID'] );
                $xml->writeElement( 'RequestIdentifierValue', $requestIdentifier );
            $xml->endElement();

            $xml->startElement( 'RequestType' );
                $xml->writeAttribute( 'ncip:Scheme', NCIP::REQUEST_TYPE_SCHEME );
                $xml->text( $opts['requestType'] );
            $xml->endElement();

            $xml->startElement( 'RequestScopeType' );
                $xml->writeAttribute( 'ncip:Scheme', NCIP::REQUEST_SCOPE_TYPE_SCHEME );
                $xml->text( $opts['requestScope'] );
            $xml->endElement();
        });
    }

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
     *  @param array    options
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
     *  @param array    options
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

    /**
     *  wrapper for `Staff::requestItem` that pushes the 'requestScope' option
     *  to be 'Bibliographic item'
     *
     *  @param mixed    user barcode
     *  @param mixed    bibliographic ID
     *  @param array    options
     *                     'itemAgencyID': AgencyID for item (defaults to initialized AgencyID)
     *                     'pickupLocation': branch name for pickup (defaults to 'MAIN')
     *                     'requestType': what kind of request (defaults to 'Hold')
     *                     'userAgencyID': AgencyID for user (defaults to initialized AgencyID)
     */

    public function requestBibItem( $userID, $bib, $opts = array() ) {
        $opts = array_merge( $opts, array(
            'requestScope' => NCIP:: REQUEST_SCOPE_TYPE_BIBLIOGRAPHIC
        ) );

        return $this->requestItem( $userID, $bib, $opts );
    }

    /**
     *  sets the datacenter to make the request to
     *
     *  @param string   datacenter
     */

    public function setDatacenter( $datacenter ) {
        $this->datacenter = $datacenter;
    }
}