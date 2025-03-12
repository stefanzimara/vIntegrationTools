<?php

require_once("../../settings2.php");
require_once("../../vCompare.main.php");

// Fehleranzeige für die Entwicklung (in Produktion deaktivieren)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Hole die Process-ID aus dem POST-Request
$processId = $_POST['processId'] ?? '';

// Definiere das Log-Verzeichnis, in dem die Statusdateien gespeichert werden
$logDir = __DIR__ . '/status_logs';

// Definiere den vollständigen Pfad zur Statusdatei
$statusFile = $logDir . "/process_{$processId}.txt";

// Ermittle den absoluten Pfad, an dem die Datei erwartet wird
$filePath = realpath($statusFile);

// Prüfe, ob die Datei existiert
if (file_exists($statusFile)) {
    $statusData = file_get_contents($statusFile);
    header('Content-Type: application/json');
    echo json_encode([
        "filePath"    => $filePath,
        "statusData"  => json_decode($statusData, true),
        "fileNotFound"=> false
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        "filePath"    => $filePath,
        "statusData"  => ["progress" => 0, "completed" => false],
        "fileNotFound"=> true,
        "message"     => "File not found: " . $statusFile
    ]);
}
exit;
?>
