<?php


/**
 * File: readArtifacts.php
 * Author: Stefan Zimara <stefan.zimara@valcoba.com>
 * Created: 2025-01-01
 * Updated: 2025-01-01
 *
 * Description:
 * This PHP Script can be used to get Version Information about Artifacts in a SAP Integration Suite.
 * It analyze Designtime and Runtie
 *
 * Documentation:
 * For detailed documentation, refer to:
 * https://github.com/stefanzimara/vIntegrationTools
 *
 * History:
 * - 2025-01-01: Initial version created by Stefan Zimara.
 *
 * Usage:
 * Take care that PHP is available on your system
 * Run script via command promt e.g. 
 *    php readArtifacts -c default
 *    php readArtifacts help
 *    
 * License:
 * This project is licensed under the AGPL License - see the LICENSE file for details.
 */



$i = 1;

include("settings.php");
include("runs.php");

if(!isset($run_id)) {
    $run_id = 1;
}

// define Options
$options = getopt("vhc:", ["version", "help", "config:"]);

// Analyze Options
if (isset($options['v']) || isset($options['version'])) {
    
    writeLog("DEBUG","Version info requested");
    
    echo header;
    echo "Version ".version."\n\n";
    exit;
}

// Show help context
if ($argc == 1 || isset($options['h']) || isset($options['help'])) {

    writeLog("DEBUG","Help requested");
    
    echo header;
    echo "Usage: php readArtifacts.php [OPTIONEN]\n\n";
    echo "  -v, --version   Show version\n";
    echo "  -h, --help      shows help\n";
    echo "  -c, --config    Load defined config file\n";
    echo "\n";
    echo "Usage: php readArtifacts.php \n\n";
    echo "Runs script with default config file (default.config.php) \n\n";
    
    exit;

}

// Load defined configuration or 
if (isset($options['c']) || isset($options['config'])) {
    writeLog("INFO", "Load Data");
    $urlArray = array();
    
    // Define config file
    $configFile = $options['c'] ?? $options['config'];
    $configFile = $configFile . ".config.php";
    
    // Check if config file exists
    if (!file_exists($configFile)) {
        writeLog("ERROR", "Config file not found: " . $configFile);
        echo "Error: Config file not found: " . $configFile . "\n";
        exit(1);
    }
    
} else {
    
    // Use default config file
    writeLog("INFO", "Load default config");
    $configFile = "default.config.php";
    
    // Check if file exists
    if (!file_exists($configFile)) {
        writeLog("ERROR", "Default config file not found: " . $configFile);
        echo "Error: Default config file not found: " . $configFile . "\n";
        exit(1);
    }
}

//load config file    
require_once $configFile;

// Check data directory exists or not
$directory = __DIR__ . "/data/".sprintf('%04s', $run_id);
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

//DesignTime Artifacts
$artifacts = array("IntegrationDesigntimeArtifacts","ValueMappingDesigntimeArtifacts","MessageMappingDesigntimeArtifacts","ScriptCollectionDesigntimeArtifacts");

foreach($config as $system) {
    
    writeLog("INFO","Process: ".$system["system"]);
    
    // Timestamp for datanames
    $timestamp = date("Y-m-d_H-i-s");
        
    // Token-endpoint and client
    $tokenUrl = $system["tokenurl"];
    $clientId = $system["clientid"];;
    $clientSecret = $system["clientsecret"];
    
    $scope = ""; // z. B. 'read', 'write', etc.
    
    // Request accss token
    $tokenResponse = file_get_contents($tokenUrl, false, stream_context_create([
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded",
            "content" => http_build_query([
                "grant_type" => "client_credentials",
                "client_id" => $clientId,
                "client_secret" => $clientSecret
            ]),
        ],
    ]));
    
    if ($tokenResponse === false) {
        writeLog("Fehler beim Abrufen des Tokens.\n");
        die("Fehler beim Abrufen des Tokens.\n");
    }
    
    // Extract Token from response
    $tokenData = json_decode($tokenResponse, true);
    if (!isset($tokenData['access_token'])) {
        writeLog("Kein Access Token erhalten.\n");
        die("Kein Access Token erhalten.\n");
    }
    
    $accessToken = $tokenData['access_token'];
    
    writeLog("DEBUG","Accesstoken: $accessToken");
    
    // API-endpint
    $apiUrl = "https://".$system["baseurl"]."/api/v1/IntegrationRuntimeArtifacts?\$format=json";
    writeLog("INFO","Read Runtime Integration Artifacts");
    
    // API-request with Access Token
    $apiResponse = file_get_contents($apiUrl, false, stream_context_create([
        "http" => [
            "header" => "Authorization: Bearer $accessToken",
        ],
    ]));
    
    if ($apiResponse === false) {
        writeLog("ERROR","Read Runtime Integration Artifacts - Error by data request");
        die("Error by data request.\n");
    }
    
    // JSON format
    $formattedJson = json_encode(json_decode($apiResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Runtime.json";
    
    // Save API response
    if (saveDetails) {
        file_put_contents($filePath, $formattedJson);
        echo "Daten erfolgreich gespeichert: $filePath\n";
        writeLog("DEBUG","Read Runtime Integration Artifacts - Data saved");
    }
    
    // JSON in ein PHP-Array umwandeln
    $data = json_decode($formattedJson, true);
    
    // Check if result is existing
    if (!isset($data['d']['results'])) {
        writeLog("DEBUG","Read Runtime Integration Artifacts - No Results found");
        die("No Results found.\n");
    }
    
    // Check results and store results in an array
    $resultsArray = [];
    foreach ($data['d']['results'] as $item) {
        $id = $item['Id'];
        $resultsArray[$id] = [
            'Runtime_Version' => $item['Version'],
            'Runtime_Name' => $item['Name'],
            'Runtime_Type' => $item['Type'],
            'Runtime_DeployedBy' => $item['DeployedBy'],
            'Runtime_DeployedOn' => $item['DeployedOn'],
            'Runtime_Status' => $item['Status'],
        ];
    }
   
    
    // define API-endpoint
    $apiUrl = "https://".$system["baseurl"]."/api/v1/IntegrationPackages?\$format=json";
    writeLog("DEBUG","Read Integration Packages - Read packages");
    
    // API-request with Access Token
    $apiResponse = file_get_contents($apiUrl, false, stream_context_create([
        "http" => [
            "header" => "Authorization: Bearer $accessToken",
        ],
    ]));
    
    if ($apiResponse === false) {
        writeLog("INFO","Read Integration Packages - Keine Ergebnisse gefunden");
        die("Fehler beim Abrufen der Daten.\n");
    }
    
    // JSON formatieren
    $formattedJson = json_encode(json_decode($apiResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // save API respnse
    if (saveDetails) {
        $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Designtime.json";
        file_put_contents($filePath, $formattedJson);
        echo "Daten erfolgreich gespeichert: $filePath\n";
    }
    
    //Read single artifacts
    $data = json_decode($formattedJson, true);
    
    foreach ($data['d']['results'] as $item) {

        foreach ($artifacts as $artifact) {
        
         $apiUrl = $item[$artifact]["__deferred"]["uri"]."?\$format=json";
         array_push($urlArray, $apiUrl);
         
         // API-request Access Token
         $apiResponse = file_get_contents($apiUrl, false, stream_context_create([
             "http" => [
                 "header" => "Authorization: Bearer $accessToken",
             ],
         ]));
        
         if ($apiResponse === false) {
             die("Error by requesting the data.\n");
         }
        
         // JSON format
         $formattedJson = json_encode(json_decode($apiResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
         
         
         // Transform JSON into PHP-Array
         $message = json_decode($formattedJson, true);
         
         if (count($message['d']['results']) > 0) {
             
             // Save API-response
             if (saveDetails) {
                $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Designtime_".$item["Id"]."_".$artifact.".json";
                file_put_contents($filePath, $formattedJson);
                
                if (LOG_LEVEL == "DEBUG") {
                    echo "Data saved: $filePath\n";
                }
             }
             
             foreach ($message['d']['results'] as $actual_item) {
               
                  if ($actual_item["Version"] == "Active") {
                      
                      //Request last version
                      $apiUrl = $actual_item["__metadata"]["uri"];
                      
                      // API-request with Token
                      $activeApiResponse = file_get_contents($apiUrl, false, stream_context_create([
                          "http" => [
                              "header" => "Authorization: Bearer $accessToken",
                          ],
                      ]));
                      
                      if (saveDetails) {
                        $outputFilePath = "data/".sprintf('%04s', $run_id)."/response_".$timestamp."_".$system["system"]."_".$actual_item["Id"]."_active_result.xml";
                        file_put_contents($outputFilePath, $activeApiResponse);
                        if (LOG_LEVEL == "DEBUG") {
                            echo "Data saved: $outputFilePath\n";
                        }
                      }
                      
                      // Transform XML in a SimpleXMLElement object
                      $xml = simplexml_load_string($activeApiResponse, "SimpleXMLElement", LIBXML_NOCDATA);
                      
                      // define namespaces according XML
                      $namespaces = $xml->getNamespaces(true);
                      
                      // Use corret namespace to request version
                      $properties = $xml->children($namespaces['m'])->properties->children($namespaces['d']);
                      $version = (string)$properties->Version;
                      $status = "Active";
                      
                  } else {
                      $version = $actual_item['Version'];
                      $status = "";
                  }

                  $package = $actual_item['PackageId'] ?? "";
                  $modified = $actual_item['ModifiedDate'] ?? $actual_item['ModifiedAt'] ?? "";
                  
                  $id = $actual_item['Id'];
                  
                  if (isset($resultsArray[$id])) { // Prüfen, ob Eintrag existiert
                      
                      //Write Log
                      writeLog("DEBUG","Entry for ".$actual_item['Id']." alreday available");
                      
                      // Read existing entry
                      $existingEntry = $resultsArray[$id];
                      
                      // Add or replace old data
                      $updatedEntry = [
                          'Designtime_Version' => $version,
                          'Designtime_Name' => $actual_item['Name'],
                          'Designtime_PackageId' => $package,
                          //'Designtime_ModifiedBy' => $actual_item['ModifiedBy'],
                          'Designtime_ModifiedAt' => $modified,
                          'Designtime_Status' => $status,
                      ];
                      
                      // Merge old and new data
                      $resultsArray[$id] = array_merge($existingEntry, $updatedEntry);
                      
                  } else {
                      
                      //Write Log
                      writeLog("DEBUG","Entry for ".$actual_item['Id']." not found");
                      
                      // Create new entry
                      $newEntry = [
                          'Designtime_Version' => $actual_item['Version'],
                          'Designtime_Name' => $actual_item['Name'],
                          'Designtime_PackageId' => $package,
                          //'Runtime_Type' => $item['Type'],
                          //'Runtime_DeployedBy' => $item['DeployedBy'],
                          //'Runtime_DeployedOn' => $item['DeployedOn'],
                          //'Runtime_Status' => $item['Status'],
                          'Designtime_Status' => $status,
                      ];
                      
                      // Store entry in the array
                      $resultsArray[$id] = $newEntry;
                  }
                  
                  
                  
             
              }
             
             
         }
        }
    }
    
    // Save Results
    $outputFilePath = "data/".sprintf('%04s', $run_id)."/parsed_results_".$system["system"].".json";
    file_put_contents($outputFilePath, json_encode($resultsArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Analyze Data saved: $outputFilePath\n";
    
    if (LOG_LEVEL == "DEBUG") {
        $outputFilePath = "data/".sprintf('%04s', $run_id)."/parsed_urls_".$system["system"]."json";
        file_put_contents($outputFilePath, json_encode($urlArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Parsed URLS saved: $outputFilePath\n";
    }
    
}

increaseRunID($run_id);
writeLog("INFO","Processing finished");




// Logging Function
function writeLog($level, $message) {
    
    // Define log level hirachy
    $logLevels = [
        "DEBUG" => 1,
        "INFO" => 2,
        "WARN" => 3,
        "ERROR" => 4,
    ];
    
    // Check log level of message with set level
    if (!isset($logLevels[$level]) || !isset($logLevels[LOG_LEVEL])) {
        return; // no further action, return
    }
    
    if ($logLevels[$level] < $logLevels[LOG_LEVEL]) {
        return; // Log level to low, no further processing
    }
    
    // actual time
    $date = new DateTime();
    $timestamp = $date->format('Ymd H:i:s');
    $logDir = __DIR__ . '/log/' . $date->format('Y') . '/' . $date->format('m');
    $logFile = $logDir . '/' . $date->format('d') . '.log';
    
    // create log directory if nevversary
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // request IP adress 
    $ip = php_sapi_name() === 'cli' ? 'CLI' : ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN');
    $logEntry = $timestamp . ' ' . $ip . ' ' . $level . ' ' . $message . PHP_EOL;
    
    // Save entry in log file
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Increase Run ID
function increaseRunID($run_id) {
    
    $run_id++;
    $command = "\$run_id = ".$run_id.";\n";
    
    $content = "<?php\n";
    $content .= $command;
    $content .= "?>\n";
    
    file_put_contents("runs.php", $content, LOCK_EX);
}







?>