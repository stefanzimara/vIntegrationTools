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

require_once("settings.php");
require_once("vCompare.main.php");

writeLog("INFO","Skript called");

echo "Start";

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

writeLog("DEBUG","Config File - " . $configFile);

//load config file    
require_once $configFile;


readArtifacts($config);

writeLog("INFO","Processing finished");




?>