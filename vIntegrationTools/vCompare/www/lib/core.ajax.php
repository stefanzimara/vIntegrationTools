<?php


require_once("../../settings2.php");
require_once("../../vCompare.main.php");


// Fehleranzeige für die Entwicklung (in Produktion deaktivieren)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Überprüfen, ob es sich um einen POST-Request handelt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Aktion aus dem Querystring oder POST-Daten holen
    $action = isset($_POST['action']) ? trim($_POST['action']) : null;
    
    
    // Aktion prüfen
    switch ($action) {
        case 'insertData':
            
            $table = isset($_POST['table']) ? trim($_POST['table']) : null;
            
            if (!$table) {
                $response = [
                    'success' => false,
                    'message' => 'Table Information missing',
                ];
                break;   
            }
            
            if ($table == "v_Owner") {
                // Daten aus dem Request holen
                $ownerName = isset($_POST['name']) ? trim($_POST['name']) : null;
                $ownerInfo = isset($_POST['info']) ? trim($_POST['info']) : null;
                
                // Verarbeiten der Daten mit der Funktion
                $response = saveOwner($ownerName, $ownerInfo);
            
            } else if ($table == "v_Tenant") {

                // Daten aus dem Request holen
                $tenantOwner = isset($_POST['tenantOwner']) ? trim($_POST['tenantOwner']) : null;
                $tenantName = isset($_POST['tenantName']) ? trim($_POST['tenantName']) : null;
                $tenantTiere = isset($_POST['tenantTier']) ? trim($_POST['tenantTier']) : null;
                $tenantHost = isset($_POST['tenantHost']) ? trim($_POST['tenantHost']) : null;
                $tenantClient = isset($_POST['tenantClient']) ? trim($_POST['tenantClient']) : null;
                $tenantSecret = isset($_POST['tenantSecret']) ? trim($_POST['tenantSecret']) : null;
                $tenantInfo = isset($_POST['tenantInfo']) ? trim($_POST['tenantInfo']) : null;
                $tokenUrl = isset($_POST['tokenUrl']) ? trim($_POST['tokenUrl']) : null;
                
                
                // Verarbeiten der Daten mit der Funktion
                $response = saveTenant($tenantOwner, $tenantName, $tenantTiere, $tenantHost, $tenantClient, $tenantSecret, $tenantInfo, $tokenUrl);
                
            }
            
            break;
            
        case 'refreshTable':
            
            $table = isset($_POST['table']) ? trim($_POST['table']) : null;
            

            if (!$table) {
                $response = [
                    'success' => false,
                    'message' => 'Table Information missing',
                ];
                break;
            }
            
            // refresh Table
            $sql = "SELECT * FROM ".$table;
            $response = executeQueryAsJson($sql);
            break;

        case 'deleteTableEntry':
            
            $table = $_POST['table'];
            $id = $_POST['id'];
            
            // delete entry
            $sql = "DELETE FROM ".$table." where id = ".$id;
            $response = executeQueryAsJson($sql);
            break;
            
        case 'insertData':
            // Beispiel: Daten einfügen
            $sql = "INSERT INTO v_Owners (name, info) VALUES (:name, :info)";
            $parameters = [
                ':name' => $_POST['name'] ?? '',
                ':info' => $_POST['info'] ?? '',
            ];
            $response = executeQueryAsJson($sql, $parameters);
            break;

        case 'getArtifacts':
            
            // delete entry
            $sql = "SELECT * FROM v_IntegrationPackages";
            $response = executeQueryAsJson($sql);
            break;
            
        case 'getOwners':
            
            // delete entry
            $sql = "SELECT id, name as value FROM `v_Owner` order by name";
            $response = executeQueryAsJson($sql);
            break;
            
        case 'getAllTenants':
            
            // delete entry
            $sql = "SELECT id, owner as ownerId, name FROM `v_Tenant`";
            $response = executeQueryAsJson($sql);
            break;
            
            
        case 'getTenantsPerOwner':
            
            $sql = "SELECT o.name as owner, t.name as tenant FROM `v_Tenant` t inner join v_Owner o on t.owner = o.id order by o.name";
            $response = executeQueryAsJson($sql);
            break;
            
        case 'getOwnerArtifacts':

            $owner = strtolower($_POST['owner']);
            $result = createView($owner);
            
            $owner = str_replace(" ", "_", $owner);
            $table = "vcompare_" . $owner . "_v";
            
            $sql = "SELECT * from ".$table;
            $response = executeQueryAsJson($sql);
            break;
            
        default:
            // Unbekannte Aktion
            $response = [
            'status' => 'error',
            'message' => 'Unbekannte Aktion: ' . $action,
            ];
    }
    
    // JSON-Antwort senden
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
    
} else {
    
    // Ungültige HTTP-Methode
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Ungültige HTTP-Methode. Nur POST-Requests erlaubt.',
    ]);
    exit;
}


/**
 * Function to store Owner data
 *
 * @param string|null $name Name des Owners
 * @param string|null $info Info zum Owner
 * @return array Response-Daten
 */

function saveOwner($name, $info) {
    
    // Validierung der Eingaben
    if (empty($name) || empty($info)) {
        return [
            'success' => false,
            'message' => 'Bitte alle Felder ausfüllen.',
        ];
    }
    
    $insert = "INSERT INTO `v_Owner` (`name`, `info`) VALUES (:owner, :info);";
    
    $params = [
        ':owner' => $name,
        ':info' => $info
    ];
    
    $result = executeQuery($insert, $params);
    
    if (!$result) {
        echo "Fehler beim Einfügen der Daten.";
    } else {
        return [
            'success' => true,
            'message' => 'Owner saved',
        ];
    }
    
    
    
}

/**
 * Function to save Tenant data
 *
 * @param string|null $tenatOwner Name of Owner
 * @param string|null $tenantName Name of Tenanz e.g. Development
 * @param string|null $tenantTiere Tier of Tenant Development, Quality. Production
 * @param string|null $tenantHost Host of Tenant
 * @param string|null $tenantClient Client ID for Connection
 * @param string|null $tenantSecret Cleint Secret for Connection
 * @param string|null $tenantInfo Info / Remarks of Tenant
 * @return array Response-Daten
 */

function saveTenant($tenantOwner, $tenantName, $tenantTier, $tenantHost, $tenantClient, $tenantSecret, $tenantInfo, $tokenUrl) {
    
    // Validierung der Eingaben

    // Pflichtfelder definieren
    $requiredFields = [
        'tenantOwner' => $tenantOwner,
        'tenantName' => $tenantName,
        'tenantTier' => $tenantTier,
        'tenantHost' => $tenantHost,
        'tenantClient' => $tenantClient,
        'tenantSecret' => $tenantSecret,
        'tokenUrl' => $tokenUrl
    ];
    
    // Fehlende Felder sammeln
    $missingFields = [];
    foreach ($requiredFields as $field => $value) {
        if (empty($value)) {
            $missingFields[] = $field;
        }
    }
    
    // Wenn Pflichtfelder fehlen, Fehler zurückgeben
    if (!empty($missingFields)) {
        return [
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missingFields),
        ];
    }
    
    $insert = "INSERT INTO `v_Tenant` (`owner`, `name`, `tier`, `host`, `client`, `secret`, `tokenurl`, `info`)
            VALUES (:owner, :name, :tier, :host, :client, :secret, :tokenUrl, :info);";
    
    $params = [
        ':owner' => $tenantOwner,
        ':name' => $tenantName,
        ':tier' => $tenantTier,
        ':host' => $tenantHost,
        ':client' => $tenantClient,
        ':secret' => $tenantSecret,
        ':info' => $tenantInfo,
        ':tokenUrl' => $tokenUrl,
        
    ];
    
    $result = executeQuery($insert, $params);
    
    if (!$result) {
        echo "Errro saving Tenant data";
    } else {
        return [
            'success' => true,
            'message' => 'Tenant saved',
        ];
    }
    
    
    
}

/**
 * Führt eine SQL-Abfrage aus und gibt das Ergebnis als JSON aus.
 *
 * @param string $sql Die SQL-Abfrage
 * @param array $parameters Optional: Parameter für Prepared Statements
 * @return void Gibt JSON direkt aus
 */

function executeQueryAsJson($sql, $parameters = []) {
    try {
        // Verwende die vorhandene executeQuery-Funktion
        $stmt = executeQuery($sql, $parameters);
        
        // Überprüfen, ob es sich um eine SELECT-Abfrage handelt
        if (str_starts_with(strtoupper(trim($sql)), 'SELECT')) {
            return [
                'status' => 'success',
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } else {
            // Für andere Abfragen (INSERT, UPDATE, DELETE)
            return [
                'status' => 'success',
                'message' => 'Abfrage erfolgreich ausgeführt.'
            ];
        }
    } catch (Exception $e) {
        // Fehlerbehandlung
        return [
            'status' => 'error',
            'message' => 'Fehler bei der Ausführung: ' . $e->getMessage()
        ];
    }
}

function createView($owner) {
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
        // Prepared Statement mit Platzhalter
        $stmt = $pdo->prepare("CALL GenerateDynamicViewForOwner(?)");
        $stmt->execute([$owner]);
        
        return $stmt; // Gibt das Statement zurück für weitere Verarbeitung
        
    } catch (PDOException $e) {
        writeLog("ERROR", "Query execution failed: " . $e->getMessage());
        echo "Query execution failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}




?>
