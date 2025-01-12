DELIMITER $$

DROP PROCEDURE IF EXISTS GenerateDynamicViewForOwner$$

CREATE PROCEDURE GenerateDynamicViewForOwner(IN input_owner VARCHAR(255))
BEGIN
    -- Variablen deklarieren (alle DECLARE-Anweisungen müssen am Anfang stehen)
    DECLARE view_name VARCHAR(255);
    DECLARE drop_view_query TEXT;
    DECLARE dynamic_columns TEXT;
    DECLARE sql_query TEXT;

    -- Maximale Länge für GROUP_CONCAT erhöhen
    SET SESSION group_concat_max_len = 1000000;

    -- Dynamischer View-Name basierend auf dem Owner
    SET view_name = CONCAT('vCompare_', REPLACE(input_owner, ' ', '_'), '_v');

    -- Dynamische Spalten generieren
    SELECT GROUP_CONCAT(
        CONCAT(
            "MAX(CASE WHEN system = '", system, "' THEN designtime_version ELSE NULL END) AS designtime_", REPLACE(system, ' ', '_'), "_version, ",
            "MAX(CASE WHEN system = '", system, "' THEN designtime_status ELSE NULL END) AS designtime_", REPLACE(system, ' ', '_'), "_status, ",
            "MAX(CASE WHEN system = '", system, "' THEN runtime_version ELSE NULL END) AS runtime_", REPLACE(system, ' ', '_'), "_version, ",
            "MAX(CASE WHEN system = '", system, "' THEN runtime_status ELSE NULL END) AS runtime_", REPLACE(system, ' ', '_'), "_status"
        )
    ) INTO dynamic_columns
    FROM (
        SELECT DISTINCT system FROM (
            SELECT system FROM v_IntegrationDesigntimeArtifacts WHERE owner = input_owner
            UNION
            SELECT system FROM v_IntegrationRuntimeArtifacts WHERE owner = input_owner
        ) AS all_systems
        
    ) AS systems;

    -- Dynamisches SQL erstellen
    SET @sql_query = CONCAT(
        "CREATE OR REPLACE VIEW ", view_name, " AS ",
        "SELECT artifacts.owner, artifacts.objId, ", dynamic_columns, " ",
        "FROM ( ",
        "    SELECT owner, system, objId, version AS designtime_version, status AS designtime_status, NULL AS runtime_version, NULL AS runtime_status ",
        "    FROM v_IntegrationDesigntimeArtifacts ",
        "    WHERE owner = '", input_owner, "' ",
        "    UNION ALL ",
        "    SELECT owner, system, objId, NULL AS designtime_version, NULL AS designtime_status, version AS runtime_version, status AS runtime_status ",
        "    FROM v_IntegrationRuntimeArtifacts ",
        "    WHERE owner = '", input_owner, "' ",
        ") AS artifacts ",
        "GROUP BY artifacts.owner, artifacts.objId;"
    );

    -- Vorhandene View löschen, falls sie existiert
    SET @drop_view_query = CONCAT("DROP VIEW IF EXISTS ", view_name, ";");
    PREPARE drop_stmt FROM @drop_view_query;
    EXECUTE drop_stmt;
    DEALLOCATE PREPARE drop_stmt;

    -- SQL ausführen
    PREPARE stmt FROM @sql_query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

