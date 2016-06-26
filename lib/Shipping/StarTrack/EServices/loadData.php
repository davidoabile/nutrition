<?php

namespace Shipping\StarTrack\EServices;

class LoadData {

    protected $data = array("Depots.json" => // Filename
        array("operation" => "getDepots", // eServices operation
            "name" => "depot", // Top-level XML element in response
            "keyvalue" => "depotCode", // Second -level XML element (key)
            "data0" => "depotName", // Second-level XML element (value)
            "data1" => "",
            "data2" => "",
            "body" => "", // SOAP Request XML (following common header)
        ),
        "Locations.json" =>
        array("operation" => "getLocations",
            "name" => "location",
            "keyvalue" => "suburb",
            "data0" => "postCode",
            "data1" => "state",
            "data2" => "nearestDepotCode",
            "body" =>
            array("locationDetails" =>
                array("locationStandard" => "TEAM")
            ),
        ),
        "QCCodes.json" =>
        array("operation" => "getQCCodes",
            "name" => "qcCodes",
            "keyvalue" => "qcCode",
            "data0" => "qcDescription",
            "data1" => "",
            "data2" => "",
            "body" => "",
        ),
        "ServiceCodes.json" =>
        array("operation" => "getServiceCodes",
            "name" => "codes",
            "keyvalue" => "serviceCode",
            "data0" => "serviceDescription",
            "data1" => "",
            "data2" => "",
            "body" => "",
        ),
        "FastServiceCodes.json" =>
        array("operation" => "getServiceCodes",
            "name" => "codes",
            "keyvalue" => "serviceCode",
            "data0" => "fastServiceCode",
            "data1" => "",
            "data2" => "",
            "body" => "",
        )
    );

    /**
     *
     * @var \StarTrack\StarTrack $starTrack startTrack object
     */
    protected $startTrack = null;

    public function __construct(\StarTrack\StarTrack $starTrack) {
        $this->startTrack = $starTrack;
    }

    public function load() {
        //Create the request, as per Request Schema described in eServices - Usage Guide.xls
        $connection = $this->startTrack->getConfig();
        $parameters = array(
            'header' => array(
                'source' => 'TEAM',
                'userAccessKey' => $connection['userAccessKey']
            )
        );

        // Iterate through the files

        foreach ($this->data as $fileName => $spec) {
            $body = $spec['body'];
            if ($body != "") {        // If non-null SOAP body, append it to header
                $parameters = array_merge($parameters, $body);
            }
            $request = array('parameters' => $parameters);
            $fileSpec = $this->startTrack->getCacheDir() . $fileName;
            $fileHandle = fopen($fileSpec, 'w') or die("Can't open file $fileSpec for writing");
            $success = $this->writeData($fileHandle, $this->startTrack->getCacheDir(), $fileName, $connection, $request, $spec);  // Write the file contents 
            if ($success) {
                echo "<p>" . $fileName . "</p>";
            }
            fclose($fileHandle) or die("Can't close file $fileSpec after writing");
        }
    }

    /** Writes the contents of a file, retrieving the data via eServices
     * Input parameters:
     * Output parameter:
     * $success = true if no error
     * 
     * @param object $fileHandle = file to be written to
     * @param string $destinationDirectory = directory to be written into
     * @param string $fileName = name of file to write
     * @param array $connection = eServices connection details
     * @param string  $request = As required by eServices
     * @param array  $spec array containing operation, name, keyvalue and data
     * @return boolean
     */
    public function writeData($fileHandle, $destinationDirectory, $fileName, $connection, $request, $spec) {


        $fileSpec = $destinationDirectory . $fileName;

        // Invoke StarTrack eServices
        try {
            $eService = $this->startTrack->get('EServices');
            $response = $eService->invokeWebService($connection, $spec['operation'], $request); // $response is as per Response Schema
            // var_dump((array)$response);
            //exit;
            // described in eServices - Usage Guide.xls.
            // Returned value is a stdClass object.
            // Faults to be handled as appropriate.
        } catch (SoapFault $e) {
            throw new \SoapFault($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        // Step through top-level elements, extracting key and value(s)
        $pairList = array();
        foreach ($response->{$spec['name']} as $k => $result) {
            $data = (array) $result;
            if ($spec['data2'] === "") {
                $pairList += [$data[$spec['keyvalue']] => $data[$spec['data0']]];
            } else {
                $pairList += [
                    $data[$spec['keyvalue']] => array(
                        $data[$spec['data0']],
                        $eService->stateAbbreviation($spec['data1']), // Convert State code (e.g. 2) to State abbreviation (e.g. NSW)
                        $data[$spec['data2']],
                    )
                ];
            }
        }
        ksort($pairList);
        var_dump($pairList);
        exit;
        // Set $items to array of top-level elements, for example 'depot' or 'location'
        eval("\$items = \$response->" . $spec['name'] . ";");


        foreach ($items as $item) {
            eval("\$keyValue = \$item->" . $spec['keyvalue'] . ";");
            eval("\$data0 = \$item->" . $spec['data0'] . ";");


            if ($spec['data2'] == "") { // If only one value is associated (usual case)
                $pairList += array($keyValue => $data0);
            } else { // Locations.json is the only case
                eval("\$data1 = \$item->" . $spec['data1'] . ";");   // State
                eval("\$data2 = \$item->" . $spec['data2'] . ";");   // Postcode
                $data1 = $oC->stateAbbreviation($data1);     // Convert State code (e.g. 2) to State abbreviation (e.g. NSW)
                $pairList += array($keyValue => array(
                        $data0, // Postcode
                        $data1, // State
                        $data2    // Depot nearest to postcode
                    )
                );
            }
        }

        // Sort the array
        ksort($pairList);

        // If Locations, special treatment is required to avoid a second web service call.
        // Two files must be written, Locations.json and NearestDepot.json

        if ($fileName == 'Locations.json') {
            $pairList = $this->extractNearestDepot($pairList, $destinationDirectory); // Write NearestDepot.json and
            // remove nearest depots from $pairList for compactness
        }

        // Write in JSON format
        fwrite($fileHandle, json_encode($pairList)) or die("Can't write file $fileSpec");


        return true;
    }

    public function extractNearestDepot($pairList, $destinationDirectory) {
// Writes NearestDepot.json and then
// removes nearest depots from $pairList for compactness
        // Create array of postcodes to nearest depot, with duplicates
        $nearestDepotDuplicates = array();
        foreach ($pairList as $suburb => $params) {
            $nearestDepotDuplicates += array($params[0] => $params[2]);  // Append  (postcode => nearest depot) 
        }
        ksort($nearestDepotDuplicates);

        // Remove duplicates
        $nearestDepots = array();
        $previousPostCode = "";
        foreach ($nearestDepotDuplicates as $postCode => $depot) {
            if ($postCode != $previousPostCode) {
                $nearestDepots += array($postCode => $depot);
            }
        }

        // Write NearestDepots.json
        $fName = "NearestDepots.json";
        $fSpec = $destinationDirectory . $fName;
        $fHandle = fopen($fSpec, 'w') or die("Can't open file $fSpec for writing");
        fwrite($fHandle, json_encode($nearestDepots)) or die("Can't write file $fSpec");
        fclose($fHandle) or die("Can't close file $fSpec after writing");
        echo "<p>" . $fName . "</p>";

        // Strip nearest depots from $pairList
        $pList = array();
        foreach ($pairList as $suburb => $params) {
            $pList += array(
                $suburb => array($params[0], $params[1])     // Append (Post Code, State
            );
        }
        return $pList;
    }

}
