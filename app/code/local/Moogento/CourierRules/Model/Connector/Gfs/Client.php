<?php


class Moogento_CourierRules_Model_Connector_Gfs_Client extends SoapClient
{
    /**
     *
     * @var array $classmap The defined classes
     * @access private
     */
    private static $classmap = array(
        // Response
        'FindAvailableServicesResponse' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Findavailableservicesresponse',
        //'FoundAvailableServices' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Findavailableservices',
        'StatusResponse' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Status',
        'ResponseDetails' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Responsedetails',

        'ShipReplies' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Shipreplies',
        'ProcessedShipment' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Processedshipment',

        // Requests
        'ShipRequest' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Shiprequest',
        'ShipRequests' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Shiprequests',
        'RequestedDeleteShipments' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Commitshipmentsrequest',
        'ValidateAddressRequest' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Validateaddressrequest',

        //'RequestedLocation' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Requesteddeleteshipments',

        // Common classes
        'Shipment' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Shipment',
        'RequestedShipment' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Requestedshipment',
        'AuthenticationDetails' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Authenticationdetails',
        'RequestedShipmentDetails' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Requestedshipmentdetails',
        'Recipient' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Recipient',
        'Party' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Party',
        'Address' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Address',
        'Person' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Person',
        'ShipReply' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Shipreply',
        'CarrierService' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Carrierservice',
        'Money' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Money',
        'FindAvailableServicesRequest' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Findavailableservicesrequest',
        'CarrierServiceGroup' => 'Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Carrierservicegroup',
    );

    /**
     *
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     * @access public
     */
    public function __construct($wsdl, $options = array())
    {
        foreach (self::$classmap as $key => $value)
        {
            if (!isset($options['classmap'][$key]))
            {
                $options['classmap'][$key] = $value;
            }
        }

        try {
            parent::__construct($wsdl, $options);
        }

        catch (SoapFault $e) {
            parent::__construct($wsdl, $options);
        }

        catch (Exception $e) {
            parent::__construct($wsdl, $options);
        }
    }
}