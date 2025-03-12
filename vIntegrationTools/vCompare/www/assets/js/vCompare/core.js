/**
 * Project: vCompare Nexus 
 * File: core.js
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

function showAlert(message, type = 'success') {

	const $alert = $('#dynamic-alert');

	$("#dynamic-alert").removeClass();

	// Set the message and alert type
	$alert.addClass(`alert alert-${type} alert-dismissible text-white fade show`) // Fügt neue Klassen hinzu
		.find('.alert-text')
		.html(`<strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}`);

	// Zeigt den Alert (falls er ausgeblendet war)
	$alert.removeClass('d-none');

	// Optionally hide the alert after a few seconds
	setTimeout(() => {
		$alert.removeClass('show');
		setTimeout(() => $alert.addClass('d-none'), 150); // Wartet auf die fade-out Animation
	}, 3000); // Zeit einstellen

}



function refreshTable(refreshTable, callback = null) {

	console.log('select data from ' + refreshTable);

	$.ajax({
		url: '/lib/core.ajax.php',  // Ziel-PHP-Datei
		type: 'POST',          // Methode (oder 'GET', falls bevorzugt)
		data: {
			action: 'refreshTable',
			table: refreshTable
		},
		dataType: 'json',      // Erwartetes Antwortformat
		success: function(response) {

			console.log('Daten empfangen:', response); // Debugging-Ausgabe

			if (response.status === "success" && Array.isArray(response.data)) {
				updateTable(response.data, refreshTable, callback); // Übergibt nur das Array
			} else {
				console.error('Fehlerhafte Datenstruktur:', response);
			}
		},

		error: function(xhr, status, error) {
			console.error('AJAX-Fehler:', status, error);
			console.log('Antwort-Text:', xhr.responseText); // Zeigt den tatsächlichen Text der Antwort
		}
	});
}

function updateTable(data, refreshTable, callback = null) {
	
	console.log('Start Table update');
	console.log($('#' + refreshTable + '-body').length);

	// Table Body auswählen
	let tableBody = $('#' + refreshTable + '-body');
	let tableHeader = $('#' + refreshTable + '-head tr');

	// Array, um die IDs der Spalten zu speichern
	let columnIds = [];

	// Durch die Spalten im Header (thead) iterieren und IDs sammeln
	tableHeader.find('th').each(function() {
		let id = $(this).attr('id'); // ID des aktuellen th-Elements holen
		if (id && id.length > 0) {    // Prüfen, ob ID vorhanden ist und nicht leer
			columnIds.push(id.replace('f-', '')); // 'f-' entfernen
		}
	});

	console.log(columnIds); // Zum Debuggen die IDs ausgeben

	// Alten Inhalt im Table Body löschen
	tableBody.empty();

	// Durch die Daten iterieren und die entsprechenden Spalten dynamisch hinzufügen
	data.forEach(row => {
		let rowHtml = '<tr>';

		// Die Werte entsprechend der Reihenfolge der Spalten-IDs in der row hinzufügen
		columnIds.forEach(id => {
			// Debugging: Prüfen, ob der Wert existiert
			//console.log(`Checking row value for id: ${id}, value: ${row[id]}`);

			let value = row[id] || ''; // Sicherstellen, dass ein leerer Wert verwendet wird, wenn kein Wert vorhanden ist

			//Enhance Value
			if (id == "id") {
				value = "#" + value;
			}

			value = '<div class="d-flex flex-column justify-content-center">' +
				'<h6 class="mb-0 text-sm">' + value + '</h6>' +
				'</div>';

			rowHtml += "<td>" + value + "</td>"; // Für jedes Feld, das eine ID hat
		});

		rowHtml += '<td>' +
			'<div table="' + refreshTable + '" t-id="' + row.id + '" class="deleteAction vc-pointer">' +
			'<i class="material-symbols-rounded opacity-5 me-3">delete</i>' +
			'</div>'
		'</td>';
		rowHtml += '</tr>';

		tableBody.append(rowHtml); // Zeile zum Table Body hinzufügen
	});
	
	if (typeof callback === "function") {
        callback();
    }
}

function setDeletEvent() {

	$(document).on('click', '.deleteAction', function() {

		// Die Tabelle und die ID des zu löschenden Datensatzes holen
		let tableName = $(this).attr('table');  // z.B. 'v_Owner'
		let rowId = $(this).attr('t-id');  // z.B. '1'

		// Optional: Bestätigungsdialog für das Löschen
		if (confirm('Do you want to delete this entry?')) {
			// Hier kannst du den Löschprozess einleiten, z.B. den Eintrag aus dem DOM entfernen
			// Zum Beispiel:
			console.log('Lösche Eintrag aus Tabelle ' + tableName + ' mit ID ' + rowId);

			// Hier kannst du den tatsächlichen Löschprozess implementieren (z.B. API-Aufruf oder DOM-Manipulation)
			// Beispiel: Entfernen der Zeile aus der Tabelle
			$(`#${tableName}-body tr`).each(function() {
				if ($(this).find('td').first().text() == "#" + rowId) {
					$(this).remove(); // Zeile aus der Tabelle entfernen
				}
			});

			$.ajax({
				url: '/lib/core.ajax.php',  // Ziel-PHP-Datei
				type: 'POST',          // Methode (oder 'GET', falls bevorzugt)
				data: {
					action: 'deleteTableEntry',
					table: tableName,
					id: rowId
				},
				dataType: 'json',      // Erwartetes Antwortformat
				success: function(response) {

					console.log('Daten empfangen:', response); // Debugging-Ausgabe
					toggleAddTenantButton();
				},

				error: function(xhr, status, error) {
					console.error('AJAX-Fehler:', status, error);
				}
			});

		}

	});

}



function loadSelectOptions(selectId, tableName) {
	
    $.ajax({
        url: '/lib/core.ajax.php',
        type: 'POST',
        data: {
            action: 'refreshTable',
            table: tableName
        },
        
        dataType: 'json',
        
        success: function(response) {
	    
            if (response.status === "success") {  // Hier auf "status" statt "success" prüfen
        
		        console.log("Liste: " + selectId + " ergänzen um Einträge");
        
                let select = $('#' + selectId);
                
                select.empty();
                select.append('<option value="">&nbsp;Please Choose</option>');

                $.each(response.data, function(index, item) {
                    select.append($('<option>', {
                        value: item.id,
                        text: item.value
                    }));
                });
        
            } else {
                console.error('Error loading select options:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
}



