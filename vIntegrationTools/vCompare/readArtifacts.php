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
 * - 2025-01-01: Initial version created by Stefan Zimara
 * - 2025-01-10: DB Export integrated
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

if (file_exists("runs.php")) {
    include("runs.php");
} else {
    // Datei existiert nicht, run_id auf 1 setzen
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

//Check Settings / Parameters
$allowedFormats = ['json', 'db'];

if (!in_array(strtolower(OUTPUT_FORMAT), array_map('strtolower', $allowedFormats), true)) {
    echo "Invalid Output format: " . OUTPUT_FORMAT;
    writeLog("ERROR", "Invalid Output format: " . OUTPUT_FORMAT);
    exit(1);
}

if (strtolower(OUTPUT_FORMAT) == "db") {
    validateDbSettings();
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
    
    //Prepare DB Tables if neccersary
    if (strtolower(OUTPUT_FORMAT) == "db") {
        $delete = "delete from v_IntegrationRuntimeArtifacts where owner = '".$system["owner"]."' and system = '".$system["system"]."'";
        executeQuery($delete);
        
        $delete = "delete from v_IntegrationPackages where owner = '".$system["owner"]."' and system = '".$system["system"]."'";
        executeQuery($delete);
        
        $delete = "delete from v_IntegrationDesigntimeArtifacts where owner = '".$system["owner"]."' and system = '".$system["system"]."'";
        executeQuery($delete);
        
    }
    
    
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
    $system["baseurl"] = preg_replace('/^https?:\/\//', '', $system["baseurl"]);
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
    
    // JSON in ein PHP-Array umwandeln
    $data = json_decode($formattedJson, true);
    
    // Check if result is existing
    if (!isset($data['d']['results'])) {
        writeLog("DEBUG","Read Runtime Integration Artifacts - No Results found");
        die("No Results found.\n");
    }
    
    // Save API response, Runtime
    if (saveDetails) {
        if (strtolower(OUTPUT_FORMAT) == "json") {
            file_put_contents($filePath, $formattedJson);
            echo "Daten erfolgreich gespeichert: $filePath\n";
            writeLog("DEBUG","Read Runtime Integration Artifacts - Data saved");
        }
    }

    
    if (strtolower(OUTPUT_FORMAT) == "db") {
        foreach ($data['d']['results'] as $item) {
        
            $insert = "insert into v_IntegrationRuntimeArtifacts\n" .
                "(`owner`, `system`, `objId`, `Version`, `Name`, `Type`, `DeployedBy`, `DeployedOn`, `Status`".
                ") values \n".
                "('".$system["owner"]."','".$system["system"]."','".$item['Id']."','".$item['Version']."','".$item['Name']."','".$item['Type']."','".$item['DeployedBy']."','".$item['DeployedOn']."','".$item['Status']."'".
                ")\n";
            executeQuery($insert);
            
        }
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
    
    // save API response
    if (saveDetails) {
        
        if (strtolower(OUTPUT_FORMAT) == "json") {
            $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Designtime.json";
            file_put_contents($filePath, $formattedJson);
            echo "Daten erfolgreich gespeichert: $filePath\n";
        }
    }
    
    if (strtolower(OUTPUT_FORMAT) == "db") {
        foreach ($data['d']['results'] as $item) {
            
            $insert = "insert into v_IntegrationPackages\n" .
                "(`owner`, `system`, `objId`".
                ") values \n".
                "('".$system["owner"]."','".$system["system"]."','".$item['Id']."'".
                ")\n";
            executeQuery($insert);
            
        }
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
                 
                 if (strtolower(OUTPUT_FORMAT) == "json") {
                     $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Designtime_".$item["Id"]."_".$artifact.".json";
                    file_put_contents($filePath, $formattedJson);
                
                    if (LOG_LEVEL == "DEBUG") {
                        echo "Data saved: $filePath\n";
                    }
                 }
                 
                 
             }
             
             foreach ($message['d']['results'] as $actual_item) {
               
                  if ($actual_item["Version"] == "Active") {
                      
                      // Request last version
                      $apiUrl = $actual_item["__metadata"]["uri"];
                      
                      // API-request with Token
                      $activeApiResponse = file_get_contents($apiUrl, false, stream_context_create([
                          "http" => [
                              "header" => "Authorization: Bearer $accessToken",
                          ],
                      ]));
                      
                      if (saveDetails) {
                          if (strtolower(OUTPUT_FORMAT) == "json") {
                              $outputFilePath = "data/" . sprintf('%04s', $run_id) . "/response_" . $timestamp . "_" . $system["system"] . "_" . $actual_item["Id"] . "_active_result.xml";
                              file_put_contents($outputFilePath, $activeApiResponse);
                              if (LOG_LEVEL == "DEBUG") {
                                  echo "Data saved: $outputFilePath\n";
                              }
                          }
                      }
                      
                      // Transform XML into a SimpleXMLElement object
                      $xml = simplexml_load_string($activeApiResponse, "SimpleXMLElement", LIBXML_NOCDATA);
                      
                      if ($xml === false) {
                          // Error handling if parsing fails
                          die("Failed loading XML: " . implode(", ", libxml_get_errors()));
                      }
                      
                      // Define namespaces according to XML
                      $xml->registerXPathNamespace('default', 'http://www.w3.org/2005/Atom');
                      $xml->registerXPathNamespace('m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');
                      $xml->registerXPathNamespace('d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
                      
                      // Extract properties and version using the correct namespaces
                      $properties = $xml->xpath('//m:properties/d:Version');
                      if (!empty($properties)) {
                          $version = (string)$properties[0];
                      } else {
                          $version = "Unknown"; // Fallback in case no version is found
                      }
                      
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
                          'Designtime_c' => $status,
                      ];
                      
                      // Store entry in the array
                      $resultsArray[$id] = $newEntry;
                  }
                  
                  //Write Data to DB
                  if (strtolower(OUTPUT_FORMAT) == "db") {
                      $insert = "insert into v_IntegrationDesigntimeArtifacts\n" .
                          "(`owner`, `system`, `objId`, `Name`, `Version`, `PackageId`, `Type`, `Status`".
                          ") values \n".
                          "('".$system["owner"]."','".$system["system"]."','".$actual_item['Id']."','".$actual_item['Name']."','".$version."','".$package."','".$artifact."','".$status."'".
                          ")\n";
                      executeQuery($insert);
                      
           
                      
                  }
              }

         }
        }
    }
    
    // Save Results
    if (strtolower(OUTPUT_FORMAT) == "json") {
        $outputFilePath = "data/".sprintf('%04s', $run_id)."/parsed_results_".$system["system"].".json";
        file_put_contents($outputFilePath, json_encode($resultsArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Analyze Data saved: $outputFilePath\n";
        
        //Run ID only to be increased in case of JSON export
        increaseRunID($run_id);
        
    }
    
    
    if (LOG_LEVEL == "DEBUG") {
        $outputFilePath = "data/".sprintf('%04s', $run_id)."/parsed_urls_".$system["system"]."json";
        file_put_contents($outputFilePath, json_encode($urlArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Parsed URLS saved: $outputFilePath\n";
    }
    
}

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


function getDbConnection() {
    
    $host = DB_SETTINGS["host"];
    $port = DB_SETTINGS["port"];
    $dbname = DB_SETTINGS["dbname"];
    $username = DB_SETTINGS["username"];
    $password = DB_SETTINGS["password"];
    
    try {
        
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true, // Persistente Verbindung
                PDO::ATTR_TIMEOUT => 5       // Timeout in Sekunden
            ]
            );
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Fehler als Exceptions
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch-Modus
        return $pdo;
        
    } catch (PDOException $e) {
        writeLog("ERROR", "Database connection failed: " . $e->getMessage());
        die("Database connection failed: " . $e->getMessage());
    }
}

function executeQuery($sql, $parameters = [], $fetchMode = PDO::FETCH_ASSOC) {
    
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = getDbConnection();
          
        } catch (PDOException $e) {
            writeLog("ERROR", "Database connection failed: " . $e->getMessage());
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);
        return $stmt; // Gibt das Statement zurück für weitere Verarbeitung
    } catch (PDOException $e) {
        writeLog("ERROR", "Query execution failed: " . $e->getMessage());
        echo("Query execution failed: " . $e->getMessage());
        echo("\n");
        echo($sql);
        writeLog("DEBUG", $sql);
        exit(1);
    }
    
}

function validateDbSettings() {
    
    $requiredKeys = ["host", "dbname", "username"];
    foreach ($requiredKeys as $key) {
        if (!isset(DB_SETTINGS[$key]) || empty(DB_SETTINGS[$key])) {
            writeLog("ERROR", "Database configuration error: Missing $key in DB_SETTINGS");
            die("Database configuration error: Missing $key in DB_SETTINGS");
        }
    }
    
}


?>