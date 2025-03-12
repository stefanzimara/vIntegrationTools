/**
 * Project: vCompare Nexus 
 * File: settings.js
 * Author: Stefan Zimara <stefan.zimara@valcoba.com>
 * Created: 2025-25-01
 *
 * Description:
 * Javascript / Jquery Function that are used for the settings page
 *
 * Documentation:
 * For detailed documentation, refer to:
 * https://github.com/stefanzimara/vIntegrationTools
 *
 * History:
 * - 2025-25-01: Initial version created by Stefan Zimara
 * 
 * Usage:
 * https://github.com/stefanzimara/vIntegrationTools/wiki
 *    
 * License:
 * This project is licensed under the AGPL License - see the LICENSE file for details.
 * 
 * Layout:
 * Layout is based on Material Dashboard 3 - v3.2.0
 * Product Page: https://www.creative-tim.com/product/material-dashboard
**/

var allTenants = []; 
var allPackages = {}; // Als Objekt, damit der Zugriff schneller ist


function loadArtifactsTable() {
    $.ajax({
        url: '/lib/core.ajax.php',  // Ziel-PHP-Datei
        type: 'POST',
        data: { action: 'getArtifacts' }, // Anpassen an deine API-Logik
        dataType: 'json',
        success: function(response) {
            if (response.status === "success" && Array.isArray(response.data)) {
	
				allPackages = response.data;
	
                let tableBody = $("#artifacts-table tbody");
                tableBody.empty(); // Vorhandene Zeilen löschen
                
 				// Leere das allPackages-Objekt, um alte Daten zu entfernen
                allPackages = {};

                response.data.forEach(function(artifact) {
                    // Speichere das Paket unter einer eindeutigen ID
                    let packageKey = `${artifact.owner}_${artifact.system}_${artifact.objId}`;
                    allPackages[packageKey] = artifact;
                    
                    
                    let row = `
                       <tr>
                          
                        <td class="align-top" style="min-width:70px max-width: 120px; word-wrap: break-word; white-space: normal;">
							 <div class="d-flex flex-column">
							 	<h6 class="mb-1 text-dark text-sm tenant-name">${artifact.system}</h6>
							 <span class="text-xs owner-name">${artifact.owner}</span>
                        	</div>
                            
                          </td>
						<td class="align-top" style="min-width:240px max-width: 320px; word-wrap: break-word; white-space: normal;">
							 <div class="d-flex flex-column">
							 	<h6 class="mb-1 text-dark text-sm artifact-name">${artifact.Name}</h6>
							 <span class="text-xs">${artifact.objId}</span>
                        </div>
                            
                          </td>
                          
                           <td class="align-top">
	                          <button class="btn btn-icon btn-2 btn-primary artifact-detail vc-pointer" 
	                           data-owner="${artifact.owner}" 
	                            data-system="${artifact.system}" 
	                            data-objid="${artifact.objId}"
	                          	type="button">
								<span class="btn-inner--icon"><i class="material-symbols-rounded">info</i>
								</span>
							 </button>
                          </td>
                            
                      </tr>`;
                    
                    tableBody.append(row);
                });
            } else {
                console.error("Fehler beim Laden der Daten:", response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX-Fehler:", error);
        }
    });
}

$(document).on('click', '.artifact-detail', function() {
    let owner = $(this).data('owner');
    let system = $(this).data('system');
    let objId = $(this).data('objid');
    
    console.log("Details aufrufen für:");
    console.log("Owner:", owner);
    console.log("System:", system);
    console.log("ObjId:", objId);

    // Hier könntest du eine Funktion aufrufen, um die Details anzuzeigen
    showArtifactDetails(owner, system, objId);

});


function showArtifactDetails(owner, system, objId) {
    
    let packageKey = `${owner}_${system}_${objId}`;

    if (allPackages[packageKey]) {
        let artifact = allPackages[packageKey];

        // Hier könntest du z. B. ein Modal oder eine Detail-Ansicht befüllen
        console.log("Details anzeigen für:", artifact);
        
        
        $('#packageDetail').removeClass("d-none");
        $('#packageName').text(artifact.Name);
        $('#packageId').text(artifact.objId);
        
    } else {
        console.warn("Paket nicht gefunden!");
    }
    
}


function loadFilterOwner() {
	
    $.ajax({
        url: '/lib/core.ajax.php',  // Ziel-PHP-Datei
        type: 'POST',
        data: {
            action: 'getOwners'      // Passe den action-Parameter nach Bedarf an
        },
    
        dataType: 'json',
        success: function(response) {
    
            if (response.status === "success") {
    
    			console.log("Load Filter - Select Owner");
    
                var select = $("#filterOwner");
                select.empty(); // Leeren des Selects
                select.append('<option value=""></option>'); // Standardoption (z.B. "Bitte auswählen")
                
                // Durch die gelieferten Daten iterieren und Optionen hinzufügen
                $.each(response.data, function(index, item) {
                    select.append($('<option>', {
                        value: item.id,   // value wird aus der id des Datensatzes gesetzt
                        text: item.value  // Der angezeigte Text wird aus dem value-Feld übernommen
                    }));
                });
    
            } else {
                console.error('Fehler beim Laden der Daten:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX-Fehler:', error);
        }
    });
}

// Initialer AJAX-Request, um alle Tenants zu laden
function loadAllTenants() {
	
    $.ajax({
        url: '/lib/core.ajax.php',
        type: 'POST',
        data: { action: 'getAllTenants' },
        dataType: 'json',
       
        success: function(response) {
            if (response.status === "success") {
                allTenants = response.data;
            } else {
                console.error("Fehler beim Laden der Tenants:", response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Fehler:", error);
        }
    });
}

// Filterfunktion, die den Select basierend auf dem ausgewählten Owner füllt
function filterTenantsByOwner(ownerId) {
	
	console.log("Update Tenant Filter by Owner; " + ownerId);

    var filteredTenants = allTenants.filter(function(tenant) {
        return tenant.ownerId == ownerId;
    });
    
    console.log(filteredTenants);
    
    var select = $("#filterTenant");
    
    select.empty();
    select.append('<option value=""></option>');
    
    filteredTenants.forEach(function(tenant) {
	    select.append($('<option>', {
            value: tenant.id,
            text: tenant.name
        }));
    });
}

function filterArtifactsByOwner(ownerName) {
    $("#artifacts-table tbody tr").each(function() {
        // Den Text aus dem span.owner-name holen
        var rowOwner = $(this).find(".owner-name").text().trim();
        
        // Vergleiche den Inhalt (in Kleinbuchstaben) mit dem gewünschten Filterwert
        if (ownerName === "" || rowOwner.toLowerCase().indexOf(ownerName.toLowerCase()) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function filterArtifactsByTenant(tenantName) {
	
    $("#artifacts-table tbody tr").each(function() {
        // Den Text aus dem span.owner-name holen
        var rowTenant = $(this).find(".tenant-name").text().trim();
        
        // Vergleiche den Inhalt (in Kleinbuchstaben) mit dem gewünschten Filterwert
        if (rowTenant === "" || rowTenant.toLowerCase().indexOf(tenantName.toLowerCase()) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
  
}


//Modify Tenant Filter, if necersary
// Delegierte Eventbindung
$(document).on('change', '#filterOwner', function() {
    var ownerId = $(this).val();
    var ownerFilter = $(this).find("option:selected").text();
    
    console.log("Owner Filter changed");
    
    filterTenantsByOwner(ownerId);
    filterArtifactsByOwner(ownerFilter);
    
});

// Delegierte Eventbindung
$(document).on('change', '#filterTenant', function() {
    var tenantFilter = $(this).find("option:selected").text();
    
    console.log("Owner Filter changed");
    filterArtifactsByTenant(tenantFilter);
    
});


// Bei jeder Eingabe im Filterfeld
$(document).on('input', '#filterPackage', function() {
	
	
    // Eingegebenen Suchtext in Kleinbuchstaben
    var searchText = $(this).val().toLowerCase();
    
    // Gehe alle Zeilen im tbody durch
    $("#artifacts-table tbody tr").each(function() {
       
        //Read Text from Name Element
        var packageName = $(this).find(".artifact-name").text().trim();
        
        // Falls der Package-Name den Suchtext enthält oder das Suchfeld leer ist, zeige die Zeile an
        if (packageName.indexOf(searchText) !== -1 || searchText === "") {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Die Funktion beim Laden der Seite aufrufen
$(document).ready(function() {
	loadArtifactsTable();
    loadFilterOwner();
    loadAllTenants();
});


