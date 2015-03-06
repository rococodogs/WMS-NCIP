<?php
namespace WMS\NCIP;

use WMS\NCIP;
use XMLWriter;
use OCLC\Auth\WSKey, OCLC\User;
use Guzzle\Http\Client, GuzzleHttp\Exception\RequestException;

abstract class BaseRequest {

    /**
     *  method to create the request URL for the NCIP request
     */

    abstract protected function getRequestURL();

    /**
     *  Though a request's <InitiationHeader> element needs a To _and_ From AgencyID,
     *  the documentation states that the ToAgencyID must match the FromAgencyID. So
     *  we'll only be working with one AgencyID at the moment.
     */

    protected $agencyID;

    /**
     *  The XMLWriter object used to construct the body of the NCIP request
     */

    protected $xml;

    /**
     *  constructor for NCIP request.  
     *
     *  @param mixed    agency ID to use
     */

    public function __construct( $agencyID, \OCLC\Auth\WSKey $wskey, \OCLC\User $user ) {
        $this->agencyID = $agencyID;
        $this->wskey = $wskey;
        $this->user = $user;
    }

    /**
     *  creates the XML body of the request
     *
     *  @param  string       action name
     *  @param  callable     callable function to append elements to the XML document
     *  @return string       XML output
     */

    public function createRequest( $actionName, $callback = null ) {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument( '1.0', 'UTF-8' );

        // <NCIPMessage ...>
        $xml->startElement( 'NCIPMessage' );
        $xml->writeAttribute( 'xmlns', NCIP::XMLNS );
        $xml->writeAttribute( 'xmlns:ncip', NCIP::XMLNS_NCIP );
        $xml->writeAttribute( 'xmlns:xsi', NCIP::XMLNS_XSI );
        $xml->writeAttribute( 'ncip:version', NCIP::NCIP_VERSION );
        $xml->writeAttribute( 'xsi:schemaLocation', NCIP::XSI_SCHEMA_LOCATION );

            // <CheckInItem>, for instance
            $xml->startElement( $actionName );
                
                // <InitiationHeader></InitiationHeader>
                $this->appendInitiationHeader( $xml );

                // Action-specific information
                if ( is_callable($callback) ) { $callback( $xml ); }

            // </CheckInItem>, for instance
            $xml->endElement();

        // </NCIPMessage>
        $xml->endElement();

        return $xml->outputMemory();
    }

    public function sendRequest( $body ) {
        $url = $this->getRequestURL();
        $options = array( 'user' => $this->user );
        $headers = array(
            'Authorization' => $this->wskey->getHMACSignature( 'POST', $url, $options ),
            'Content-type' => 'application/xml'
        );

        $client = new Client();
        $client->getClient()->setDefaultOption( 'config/curl/' . CURLOPT_SSLVERSION, 3 );
        
        try {
            $response = $client->post( $url, array(
                'headers' => $headers,
                'body' => $body
            ) );

        } catch( Exception $e ) {}
    }

    /**
     *  appends the <InitiationHeader> element to the document
     *
     *  @param  XMLWriter object (mutable)
     *  @return void
     */

    protected function appendInitiationHeader( $xml ) {
        // <InitiationHeader>
        $xml->startElement( 'InitiationHeader' );
            // <FromAgencyID>
            $xml->startElement( 'FromAgencyID' );
                // <AgencyID ncip:Scheme='...'>
                $xml->startElement( 'AgencyID' );
                    $xml->writeAttribute( 'ncip:Scheme', NCIP::AGENCY_ID_SCHEME );
                    $xml->text( $this->agencyID );
                // </AgencyID>
                $xml->endElement();
            // </FromAgencyID>
            $xml->endElement();

            // <ToAgencyID>
            $xml->startElement( 'ToAgencyID' );
                // <AgencyID>...</AgencyID>
                $xml->writeElement( 'AgencyID', $this->agencyID );
            // </ToAgencyID>
            $xml->endElement();

            // <ApplicationProfileType ncip:Scheme='...'>
            $xml->startElement( 'ApplicationProfileType' );
                $xml->writeAttribute( 'ncip:Scheme', NCIP::APPLICATION_PROFILE_TYPE_SCHEME );
                $xml->text( NCIP::APPLICATION_PROFILE_TYPE );
            // </ApplicationProfileType>
            $xml->endElement();

        // </InitializationHeader>
        $xml->endElement();
    }

    protected function appendItemID( $xml, $itemID, $agencyID = null ) {
        if ( !$agencyID ) { $agencyID = $this->agencyID; }

        $xml->startElement( 'ItemId' );
            $xml->writeElement( 'AgencyId', $agencyID );
            $xml->writeElement( 'ItemIdentifierValue', $itemID );
        $xml->endElement();
    }

    protected function appendUserID( $xml, $userID, $agencyID = null ) {
        if ( !$agencyID ) { $agencyID = $this->agencyID; }

        $xml->startElement( 'UserId' );
            $xml->writeElement( 'AgencyId', $agencyID );
            $xml->writeElement( 'UserIdentifierValue', $userID );
        $xml->endElement();
    }

    /**
     *  constructs an XML document from an associative array
     *  (taken from http://php.net/manual/en/ref.xmlwriter.php#89047)
     *
     *  @param array    associative array
     *
     */

    protected function xmlFromArray( $input ) {
        $xml = $this->xml;
        if ( is_array($input) ) {
            foreach( $input as $key => $val ) {
                if ( is_array($val) ) {
                    $xml->startElement( $key );
                    $this->xmlFromArray( $val );
                    $xml->endElement();
                } else {
                    $xml->setElement( $key, $val );
                }
            }
        }
    }
}