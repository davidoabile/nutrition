<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NWH_RetailExpress_Model_Shipping_AusPost {

    protected $baseURl = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/';
    protected $account = "1008602094";
    protected $username = "10e765e1-8385-4eb9-8b9a-e09a3d02909e";
    protected $password = "x496c85f5f3201800a44";
    protected $helper = null;
    private $options = [];
    protected $merchantAccount = [];

    public function __construct() {
        $this->helper = Mage::helper('nwh_retailexpress');
        $this->options = [
            'data' => [
                'username' => $this->username,
                'password' => $this->password,
            ],
            'headers' => array('Content-Type: application/json', 'Account-Number:' . $this->account)
        ];
    }

    public function getAccount() {
        $this->merchantAccount = $this->helper->getAusPost($this->options + ['url' => $this->baseURl . 'accounts/' . $this->account]);
    }

    public function getItemPrice() {

        $data = $this->options;
        $data['data'] = $data['data'] + [ 'from' => array('postcode' => '2170'),
            'to' => array('postcode' => '3000'),
            'items' => array(['length' => 100, 'height' => 50, 'width' => 30, 'weight' => 5])
        ];
        $data['url'] = $this->baseURl . 'prices/items';
        $data['method'] = 'POST';
        $result = $this->helper->getAusPost($data);

        var_dump($result);
        exit;
    }

    public function getShipments() {
        $test = '{  
                    "shipments":[  
                       {  
                          "shipment_reference":"XYZ-001-01",
                          "customer_reference_1":"Order 001",
                          "customer_reference_2":"SKU-1, SKU-2, SKU-3",
                          "from":{  
                             "name":"John Citizen",
                             "lines":[  
                                "1 Main Street"
                             ],
                             "suburb":"MELBOURNE",
                             "state":"VIC",
                             "postcode":"3000"
                          },
                          "to":{  
                             "name":"Jane Smith",
                             "business_name":"Smith Pty Ltd",
                             "lines":[  
                                "123 Centre Road"
                             ],
                             "suburb":"Sydney",
                             "state":"NSW",
                             "postcode":"2000",
                             "phone":"0412345678"
                          },
                          "items":[  
                             {  
                                "item_reference":"SKU-1",
                                "product_id":"T28S",
                                "length":"10",
                                "height":"10",
                                "width":"10",
                                "weight":"1",
                                "authority_to_leave":false,
                                "partial_delivery_allowed":true
                             },
                             {  
                                "item_reference":"SKU-2",
                                "product_id":"T28S",
                                "length":"10",
                                "height":"10",
                                "width":"10",
                                "weight":"1",
                                "authority_to_leave":false,
                                "partial_delivery_allowed":true
                             },
                             {  
                                "item_reference":"SKU-3",
                                "product_id":"T28S",
                                "length":"10",
                                "height":"10",
                                "width":"10",
                                "weight":"1",
                                "authority_to_leave":false,
                                "partial_delivery_allowed":true
                             }
                          ]
                       }
                    ]
                 }';
        $data = $this->options;
        $data['data'] = $data['data'] + json_decode($test, true);
        $data['url'] = $this->baseURl . 'shipments';
        $data['method'] = 'POST';
        $result = $this->helper->getAusPost($data);

        var_dump($result);
        exit;
    }

    public function getTrackProgress() {

        $account = $this->helper->getAusPost($this->options + ['url' => $this->baseURl . 'track?tracking_id=ABC0001285834']);
        var_dump($account);
        exit;
    }

    public function createOrder() {
        $str = '{
    "shipments": [
        {
            "shipment_id": "d666f4556991f7f502ab01ac"
        }]}';
        $data = $this->options;
        $data['data'] = $data['data'] + json_decode($str, true);
        $data['url'] = $this->baseURl . 'orders';
        $data['method'] = 'PUT';
        $result = $this->helper->getAusPost($data);

        var_dump($result);
        exit;
    }

    public function printLables() {
        $str = '{
    "preferences": [
        {
            "type": "PRINT",
            "groups": [
             
                {
                "group": "Express Post",
                "layout": "A4-1pp",
                "branded": false,
                "left_offset": 0,
                "top_offset": 0
               } 
            ]   
        } 
        ],
        "test_print":  true 
        }';
        $data = $this->options;
        $data['data'] = $data['data'] + json_decode($str, true);
        $data['url'] = $this->baseURl . 'labels';
        $data['method'] = 'POST';
        $result = $this->helper->getAusPost($data);

        var_dump($result);
        exit;
    }
    
    public function addressVerify(){
         $account = $this->helper->getAusPost($this->options + ['url' => $this->baseURl . 'address?suburb=Upper+Coomera&state=QLD&postcode=4201']);
        var_dump($account);
        exit;
    }

}
