<?php



$i = 1;

include("settings.php");
include("runs.php");

if(!isset($run_id)) {
    $run_id = 1;
}

// Optionen definieren
$options = getopt("vhc:", ["version", "help", "config:"]);

// Optionen auswerten
if (isset($options['v']) || isset($options['version'])) {
    
    writeLog("Version info requested");
    
    echo header;
    echo "Version ".version."\n\n";
}

if ($argc == 1 || isset($options['h']) || isset($options['help'])) {

    writeLog("Help requested");
    
    echo header;
    echo "Verwendung: php readArtifacts.php [OPTIONEN]\n";
    echo "  -v, --version   Zeigt die Version an\n";
    echo "  -h, --help      Zeigt diese Hilfe an\n";
    echo "  -c, --config    Lädt die angegebene Konfigurationsdatei\n";
    exit;

}

if (isset($options['c']) || isset($options['config'])) {
    
    writeLog("Load Data");
    
    $urlArray = array();
    
    $configFile = $options['c'] ?? $options['config'];
    $configFile = $configFile.".config.php";
    
    echo "Lade Konfigurationsdatei: $configFile\n";
    writeLog("Lade Konfigurationsdatei: $configFile");
    
    include($configFile);
    
    // Sicherstellen, dass das Verzeichnis existiert
    $directory = __DIR__ . "/data/".sprintf('%04s', $run_id);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    
    //DesignTime Artifacts
    $artifacts = array("IntegrationDesigntimeArtifacts","ValueMappingDesigntimeArtifacts","MessageMappingDesigntimeArtifacts","ScriptCollectionDesigntimeArtifacts");
    
    foreach($config as $system) {
        
        writeLog("Process: ".$system["system"]);
        
        // Timestamp für den Dateinamen
        $timestamp = date("Y-m-d_H-i-s");
            
        // Token-Endpunkt und Client-Daten
        $tokenUrl = $system["tokenurl"];
        $clientId = $system["clientid"];;
        $clientSecret = $system["clientsecret"];
        
        $scope = ""; // z. B. 'read', 'write', etc.
        
        // Zugriffstoken abrufen
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
        
        // Token aus der Antwort extrahieren
        $tokenData = json_decode($tokenResponse, true);
        if (!isset($tokenData['access_token'])) {
            writeLog("Kein Access Token erhalten.\n");
            die("Kein Access Token erhalten.\n");
        }
        $accessToken = $tokenData['access_token'];
        
        writeLog("Accesstoken: $accessToken");
        
        // API-Endpunkt
        $apiUrl = "https://".$system["baseurl"]."/api/v1/IntegrationRuntimeArtifacts?\$format=json";
        writeLog("Read Runtime Integration Artifacts");
        
        // API-Anfrage mit Access Token
        $apiResponse = file_get_contents($apiUrl, false, stream_context_create([
            "http" => [
                "header" => "Authorization: Bearer $accessToken",
            ],
        ]));
        
        if ($apiResponse === false) {
            writeLog("Read Runtime Integration Artifacts - Fehler beim Abrufen der Daten");
            die("Fehler beim Abrufen der Daten.\n");
        }
        
        // JSON formatieren
        $formattedJson = json_encode(json_decode($apiResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Runtime.json";
        
        // API-Antwort speichern
        file_put_contents($filePath, $formattedJson);
        echo "Daten erfolgreich gespeichert: $filePath\n";
        writeLog("Read Runtime Integration Artifacts - Data saved");
        
        // JSON in ein PHP-Array umwandeln
        $data = json_decode($formattedJson, true);
        
        // Sicherstellen, dass die Ergebnisse existieren
        if (!isset($data['d']['results'])) {
            writeLog("Read Runtime Integration Artifacts - Keine Ergebnisse gefunden");
            die("Keine Ergebnisse gefunden.\n");
        }
        
        // Ergebnisse durchgehen und in ein assoziatives Array übertragen
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
       
        
        // API-Endpunkt
        $apiUrl = "https://".$system["baseurl"]."/api/v1/IntegrationPackages?\$format=json";
        writeLog("Read Integration Packages - Read packages");
        
        // API-Anfrage mit Access Token
        $apiResponse = file_get_contents($apiUrl, false, stream_context_create([
            "http" => [
                "header" => "Authorization: Bearer $accessToken",
            ],
        ]));
        
        if ($apiResponse === false) {
            writeLog("Read Integration Packages - Keine Ergebnisse gefunden");
            die("Fehler beim Abrufen der Daten.\n");
        }
        
        // JSON formatieren
        $formattedJson = json_encode(json_decode($apiResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Designtime.json";
        
        // API-Antwort speichern
        file_put_contents($filePath, $formattedJson);
        echo "Daten erfolgreich gespeichert: $filePath\n";
        
        
        //Einzelne Artifacts auslesen
        $data = json_decode($formattedJson, true);
        
        foreach ($data['d']['results'] as $item) {
  
            foreach ($artifacts as $artifact) {
            
             $apiUrl = $item[$artifact]["__deferred"]["uri"]."?\$format=json";
             array_push($urlArray, $apiUrl);
             
             // API-Anfrage mit Access Token
             $apiResponse = file_get_contents($apiUrl, false, stream_context_create([
                 "http" => [
                     "header" => "Authorization: Bearer $accessToken",
                 ],
             ]));
            
             if ($apiResponse === false) {
                 die("Fehler beim Abrufen der Daten.\n");
             }
            
             // JSON formatieren
             $formattedJson = json_encode(json_decode($apiResponse, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
             
             
             // JSON in ein PHP-Array umwandeln
             $message = json_decode($formattedJson, true);
             
             if (count($message['d']['results']) > 0) {
                 $filePath = $directory . "/response_".$timestamp."_".$system["system"]."_Designtime_".$item["Id"]."_".$artifact.".json";
                 
                 // API-Antwort speichern
                 file_put_contents($filePath, $formattedJson);
    //           echo "Daten erfolgreich gespeichert: $filePath\n";
    
                 
                  foreach ($message['d']['results'] as $actual_item) {
                   
                      if ($actual_item["Version"] == "Active") {
                          
                          //Request last version
                          $apiUrl = $actual_item["__metadata"]["uri"];
                          
                          // API-Anfrage mit Access Token
                          $activeApiResponse = file_get_contents($apiUrl, false, stream_context_create([
                              "http" => [
                                  "header" => "Authorization: Bearer $accessToken",
                              ],
                          ]));
                          
                          //$outputFilePath = "data/".sprintf('%04s', $run_id)."/response_".$timestamp."_".$system["system"]."_".$actual_item["Id"]."_active_result.xml";
                          //file_put_contents($outputFilePath, $activeApiResponse);
                          //echo "Daten erfolgreich gespeichert: $outputFilePath\n";
                          
                          // XML in ein SimpleXMLElement-Objekt laden
                          $xml = simplexml_load_string($activeApiResponse, "SimpleXMLElement", LIBXML_NOCDATA);
                          
                          // Namespace definieren (entsprechend dem XML)
                          $namespaces = $xml->getNamespaces(true);
                          
                          // Den richtigen Namespace verwenden, um auf die Version zuzugreifen
                          $properties = $xml->children($namespaces['m'])->properties->children($namespaces['d']);
                          $version = (string)$properties->Version." / Active";
                          
                          
                      } else {
                          $version = $actual_item['Version'];
                      }

                      $package = $actual_item['PackageId'] ?? "";
                      $modified = $actual_item['ModifiedDate'] ?? $actual_item['ModifiedAt'] ?? "";
                      
                      $id = $actual_item['Id'];
                      
                      if (isset($resultsArray[$id])) { // Prüfen, ob Eintrag existiert
                          
                          //Write Log
                          writeLog("Entry for ".$actual_item['Id']." alreday available");
                          
                          // Den bestehenden Eintrag auslesen
                          $existingEntry = $resultsArray[$id];
                          
                          // Neue Daten hinzufügen oder vorhandene überschreiben
                          $updatedEntry = [
                              'Designtime_Version' => $version,
                              'Designtime_Name' => $actual_item['Name'],
                              'Designtime_PackageId' => $package,
                              //'Designtime_ModifiedBy' => $actual_item['ModifiedBy'],
                              'Designtime_ModifiedAt' => $modified,
                          ];
                          
                          // Merge der alten und neuen Daten
                          $resultsArray[$id] = array_merge($existingEntry, $updatedEntry);
                          
                      } else {
                          
                          //Write Log
                          writeLog("Entry for ".$actual_item['Id']." not found");
                          
                          // Neuer Eintrag mit Runtime-Daten
                          $newEntry = [
                              'Designtime_Version' => $actual_item['Version'],
                              'Designtime_Name' => $actual_item['Name'],
                              'Designtime_PackageId' => $package,
                              //'Runtime_Type' => $item['Type'],
                              //'Runtime_DeployedBy' => $item['DeployedBy'],
                              //'Runtime_DeployedOn' => $item['DeployedOn'],
                              //'Runtime_Status' => $item['Status'],
                          ];
                          
                          // Eintrag im Array speichern
                          $resultsArray[$id] = $newEntry;
                      }
                      
                      
                      
                 
                  }
                 
                 
             }
            }
        }
        
        // Wenn nötig, das Array in einer Datei speichern
        $outputFilePath = "data/".sprintf('%04s', $run_id)."/parsed_results.json";
        file_put_contents($outputFilePath, json_encode($resultsArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Daten erfolgreich gespeichert: $outputFilePath\n";
        
        $outputFilePath = "data/".sprintf('%04s', $run_id)."/parsed_urls.json";
        file_put_contents($outputFilePath, json_encode($urlArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Daten erfolgreich gespeichert: $outputFilePath\n";
        
        
    }

    increaseRunID($run_id);
    
    writeLog("Processing finished");

}


// Logging Funktion
function writeLog($message) {
    
    $date = new DateTime();
    $timestamp = $date->format('Ymd H:i:s');
    $logDir = __DIR__ . '/log/' . $date->format('Y') . '/' . $date->format('m');
    $logFile = $logDir . '/' . $date->format('d') . '.log';
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $logEntry = $timestamp . ' ' . $ip . ' ' . $message . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);

}

// Logging Funktion
function increaseRunID($run_id) {
    
    $run_id++;
    $command = "\$run_id = ".$run_id.";\n";
    
    $content = "<?php\n";
    $content .= $command;
    $content .= "?>\n";
    
    file_put_contents("runs.php", $content, LOCK_EX);
}







?>