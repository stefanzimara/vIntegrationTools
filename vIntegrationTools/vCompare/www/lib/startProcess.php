<?php

require_once("../../settings2.php");
require_once("../../vCompare.main.php");

// Fehleranzeige für die Entwicklung (in Produktion deaktivieren)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// startProcess.php

// Generate a unique process ID (e.g., using a timestamp or a UUID)
$processId = uniqid("proc_", true);

// Definiere das Verzeichnis, in dem die Statusdateien gespeichert werden sollen
$logDir = __DIR__ . '/status_logs';

// Erstelle das Verzeichnis, falls es noch nicht existiert
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0777, true)) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Could not create log directory: $logDir"
        ]);
        exit(1);
    }
}

// Definiere den Dateinamen, z. B. mit einer eindeutigen Prozess-ID
$statusFile = $logDir . "/process_{$processId}.txt";

// Schreibe einen initialen Status in die Datei (zum Beispiel progress 0)
$initialStatus = json_encode([
    "progress" => 0,
    "completed" => false,
    "started" => true
]);

$result = file_put_contents($statusFile, $initialStatus);

if ($result === false) {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => "Could not create status file: $statusFile"
    ]);
    exit(1);
}

$sql = "SELECT t.owner, t.name as system, t.host as baseurl, t.tier, t.client as clientid, t.secret as clientsecret, t.tokenurl FROM vcompare_tenant_owner_v t;";
$result = executeQueryAsJson($sql);

// JSON-Daten in ein assoziatives Array umwandeln
$tenants = $result["data"];

// Prüfen, ob die Umwandlung erfolgreich war und ob Tenants vorhanden sind
if (!$tenants || !is_array($tenants)) {
    die(json_encode(["status" => "error", "message" => "Invalid JSON or no tenants found in database"]));
}

// **Konfigurationsdatei `vnexus.config.php` erstellen**
$configFile = "../../vnexus.config.php";
$configContent = "<?php\n\n";

//var_dump(__DIR__);


foreach ($tenants as $index => $tenant) {
 
    $configContent .= "    \$config[$index][\"owner\"] = \"" . escapeSpecialChars($tenant['owner'] ?? "") . "\";\n";
    $configContent .= "    \$config[$index][\"system\"] = \"" . escapeSpecialChars($tenant['system']) . "\";\n";
    $configContent .= "    \$config[$index][\"clientid\"] = \"" . escapeSpecialChars($tenant['clientid']) . "\";\n";
    $configContent .= "    \$config[$index][\"clientsecret\"] = \"" . escapeSpecialChars($tenant['clientsecret']) . "\";\n";
    $configContent .= "    \$config[$index][\"tokenurl\"] = \"" . escapeSpecialChars($tenant['tokenurl'] ?? "") . "\";\n";
    $configContent .= "    \$config[$index][\"baseurl\"] = \"" . escapeSpecialChars($tenant['baseurl']) . "\";\n\n";
    
}

$configContent .= "?>";

// Datei speichern
$result = file_put_contents($configFile, $configContent);
if ($result === false) {
    die(json_encode(["status" => "error", "message" => "Could not create config file: $configFile"]));
}


// Starte den Hintergrundprozess asynchron (exec() with &)
$phpPaths = [
    "/opt/homebrew/bin/php",  // macOS Apple Silicon (Homebrew)
    "/usr/local/bin/php",     // macOS Intel (Homebrew) oder Linux
    "/usr/bin/php",           // Standard für Linux
    "/bin/php",               // Manche Linux-Setups
    "C:\\xampp\\php\\php.exe", // Windows (XAMPP)
    "C:\\Program Files\\PHP\\php.exe" // Windows eigene Installation
];

// Versuche, eine gültige PHP-Binary zu finden
$phpPath = null;
foreach ($phpPaths as $path) {
    if (file_exists($path) && is_executable($path)) {
        $phpPath = $path;
        break;
    }
}

// Falls keine PHP-Binary gefunden wurde, breche ab
if (!$phpPath) {
    die(json_encode(["status" => "error", "message" => "PHP binary not found"]));
}
    
//$cmd = "$phpPath backgroundProcess.php " . escapeshellarg($processId) . " > /dev/null 2> /tmp/bg_error_{$processId}.txt &";
$cmd = "$phpPath backgroundProcess.php " . escapeshellarg($processId) . " > /dev/null 2> /dev/null 2>&1 &";
exec($cmd, $output, $return_var);


// Return the process ID along with additional info (e.g., status file path) as JSON
header('Content-Type: application/json');
echo json_encode([
    "status" => "started",
    "processId" => $processId,
    "statusFile" => $statusFile,
    "outputCmd" => $output,
    "statusCmd" => $return_var,
]);
exit;


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

function escapeSpecialChars($value) {
    return str_replace('$', '\$', $value);
}


?>
