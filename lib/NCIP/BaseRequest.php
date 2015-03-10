<?php
/**
 *  abstract  function getRequestURL();
 *  public    function __construct( array $settings );
 *  public    function createRequest( string $actionName, callable $callback );
 *  protected function appendInitiationHeader( \XMLWriter $xml )
 *  protected function appendItemID( \XMLWriter $xml, mixed $itemID[, $agencyID = null] )
 *  protected function appendUserID( \XMLWriter $xml, mixed $userID[, $agencyID = null] )
 *  protected function xmlToObject( string $xmlDocument )
 */

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
     *  @param mixed    options array
     */

    public function __construct( $settings = array()  ) {
        if ( isset( $settings['wskey'] ) ) { $this->setWSKey( $settings['wskey'] ); }
        if ( isset( $settings['user'] ) ) { $this->setUser( $settings['user'] ); }
        if ( isset( $settings['agencyID'] ) ) {
            $this->setAgencyID( $settings['agencyID'] );
        } elseif ( $this->user ) {
            $this->setAgencyID( $this->user->getAuthenticatingInstitutionID() );
        }
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

    /**
     *  sends a request to the NCIP server
     *
     *  @param  string    XML document body
     *  @param  boolean   return the response as a StdObject (true) or XML string
     *  @return mixed     the response, either as a StdObject or a string
     */

    public function sendRequest( $body, $asObject = true ) {
        if ( !isset( $this->user ) ) {
            throw new \Exception( 'No user defined' );
        }

        if ( !isset( $this->wskey ) ) {
            throw new \Exception( 'No WSKey provided' );
        }

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

            $body = $resonse->getBody( true );

            return $asObject ? $this->xmlToObject($body) : $body;

        } catch( Exception $e ) {
            // TODO
        }
    }

    /**
     *  setter for AgencyID
     *
     *  @param mixed
     */

    public function setAgencyID( $agencyID ) {
        $this->agencyID = $agencyID;
    }

    /**
     *  setter for Request User
     *
     *  @param OCLC\User
     */

    public function setUser( \OCLC\User $user ) {
        $this->user = $user;
    }

    /**
     *  setter for Request WSKey
     *
     *  @param OCLC\Auth\WSKey
     */

    public function setWSKey( \OCLC\Auth\WSKey $wskey ) {
        $this->wskey = $wskey;
    }

    /**
     *  appends the <InitiationHeader> element to the document
     *
     *  @param  XMLWriter object
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

    /**
     *  append the common <ItemId> node to the document
     *
     *  @param XMLWriter    XML dom
     *  @param mixed        item identifier value
     *  @param mixed        (optional) item AgencyId (default is initialized AgencyId)
     */

    protected function appendItemID( $xml, $itemID, $agencyID = null ) {
        if ( !$agencyID ) { $agencyID = $this->agencyID; }

        $xml->startElement( 'ItemId' );
            $xml->writeElement( 'AgencyId', $agencyID );
            $xml->writeElement( 'ItemIdentifierValue', $itemID );
        $xml->endElement();
    }

    /**
     *  append the common <UserId> node to the document
     *
     *  @param XMLWriter    XML dom
     *  @param mixed        User identifier value
     *  @param mixed        (optional) user AgencyId (default is initialized AgencyId)
     */


    protected function appendUserID( $xml, $userID, $agencyID = null ) {
        if ( !$agencyID ) { $agencyID = $this->agencyID; }

        $xml->startElement( 'UserId' );
            $xml->writeElement( 'AgencyId', $agencyID );
            $xml->writeElement( 'UserIdentifierValue', $userID );
        $xml->endElement();
    }


    /**
     *  parses an XML document into an object
     *
     *  @param  string      the XML string to convert
     *  @param  boolean     should the entire document be returned (if false, NCIPMessage field is parent)
     *  @return StdClass
     */

    protected function xmlToObject( $output, $returnFullOutput = false ) {
        if ( is_string( $output ) ) {
            $xml = new XMLReader;
            $xml->xml( $output );
        }

        $out = new StdClass();
        $level = -1;
        $currentNodeName = array();

        while ( $xml->read() ) {
            $nodeType = $xml->nodeType;

            if ( $nodeType === XMLReader::END_ELEMENT ) {
                unset( $currentNodeName[$level] );
                $level--;
                continue;
            }

            if ( $nodeType === XMLReader::ELEMENT ) {
                $level++;
                $currentNodeName[$level] = $xml->localName;
                continue;
            }

            if ( $nodeType === XMLReader::TEXT ) {
                $el = $out;
                $lastEl = end( $currentNodeName );
                foreach( $currentNodeName as $node ) { 
                    if ( $node === $lastEl ) {
                        $el->$node = $xml->value;
                    } else {
                        if ( !isset( $el->$node) ) { $el->$node = new StdClass(); }
                        $el = $el->$node;
                    }
                }
            }
        }

        return $returnFullOutput ? $out : $out->NCIPMessage;
    }
