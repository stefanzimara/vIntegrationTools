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



$(document).ready(function() {
	// Event, wenn "addOwner" geklickt wird
	$('#addOwner').on('click', function() {
		$("#ownerName").val('');
		$("#ownerInfo").val('');
		$('#ownerForm').removeClass('d-none'); // Entfernt die Klasse "d-none"
	});

	// Event, wenn "ownerCancel" geklickt wird
	$('#ownerCancel').on('click', function() {
		$('#ownerForm').addClass('d-none'); // Fügt die Klasse "d-none" wieder hinzu
	});

	// Event, wenn "addTenant" geklickt wird
	$('#addTenant').on('click', function() {
		$("#tenantOwner").val('');
		$("#tenantName").val('');
		$("#tenantTier").val('');
		$("#tenantHost").val('');
		$("#tenantClient").val('');
		$("#tenantSecret").val('');
		$("#tenantInfo").val('');
		$("#tokenUrl").val('');
		$('#tenantForm').removeClass('d-none'); // Entfernt die Klasse "d-none"
	});

	// Event, wenn "ownerCancel" geklickt wird
	$('#tenantCancel').on('click', function() {
		$('#tenantForm').addClass('d-none'); // Fügt die Klasse "d-none" wieder hinzu
	});

});


$(document).ready(function() {

	// Klick-Event für den Button
	$("#saveOwner").click(function() {
		// Daten aus den Feldern sammeln
		const ownerName = $("#ownerName").val();
		const ownerInfo = $("#ownerInfo").val();

		// Funktion aufrufen
		processOwner(ownerName, ownerInfo);
	});

	// Klick-Event für den Button
	$("#saveTenant").click(function() {
		// Daten aus den Feldern sammeln
		
		const tenantOwner = $("#tenantOwner").val();
		const tenantName = $("#tenantName").val();
		const tenantTier = $("#tenantTier").val();
		const tenantHost = $("#tenantHost").val();
		const tenantClient = $("#tenantClient").val();
		const tenantSecret = $("#tenantSecret").val();
		const tenantInfo = $("#tenantInfo").val();
		const tokenUrl = $("#tokenUrl").val();
		
		
		// Funktion aufrufen
		processTenant(tenantOwner, tenantName, tenantTier, tenantHost, tenantClient, tenantSecret, tenantInfo, tokenUrl);
	});
	
	//Prepare Table Contents 
	refreshTable('v_Owner','toggleAddTenantButton');
	refreshTable('vcompare_tenant_v');
	loadSelectOptions('tenantOwner', 'vcompare_owners');
	setDeletEvent();
	
});

/**
 * Funktion zum Verarbeiten und Speichern des Owners
 * @param {string} name - Der Name des Owners
 * @param {string} info - Die Info zum Owner
 */

function processOwner(name, info) {

	// Validierung der Eingaben (optional)
	if (!name) {
		alert("Please fill in all fields");
		return;
	}

	// AJAX-Request
	$.ajax({
		url: "/lib/core.ajax.php",
		type: "POST",
		data: {
			name: name,
			info: info,
			action: "insertData",
			table: "v_Owner",
		},
		success: function(response) {
			console.log("Data successfully send:", response);

			$("#ownerName").val('');
			$("#ownerInfo").val('');
			$('#ownerForm').addClass('d-none');

			showAlert('Owner has been saved successful!', 'success');

			refreshTable('v_Owner','toggleAddTenantButton');
			loadSelectOptions('tenantOwner', 'vcompare_tenant_owner_v');
			
		},
		error: function(xhr, status, error) {
			console.error("Error by Posting Data:", error);
			showAlert('Error by processing data', 'danger');
		},
	});
}

/**
 * Funktion zum Verarbeiten und Speichern des Owners
 * @param {string} tenatOwner - Der Name des Owners
 * @param {string} tenantName - Die Info zum Owner
 * @param {string} tenantTier - Der Name des Owners
 * @param {string} tenantHost - Die Info zum Owner
 * @param {string} tenantClient - Der Name des Owners
 * @param {string} tenantSecret - Die Info zum Owner
 * @param {string} tenantInfo - Der Name des Owners   
 */

function processTenant(tenantOwner, tenantName, tenantTier, tenantHost, tenantClient, tenantSecret, tenantInfo, tokenUrl) {

	// Validierung der Eingaben (optional)
	if (!tenantOwner || !tenantName || !tenantTier || !tenantHost || !tenantClient || !tenantSecret) {
		
		alert("Please fill in all fields");
		return;
	}

	// AJAX-Request
	$.ajax({
		url: "/lib/core.ajax.php",
		type: "POST",
		data: {
			tenantOwner: tenantOwner,
			tenantName: tenantName,
			tenantTier: tenantTier,
			tenantHost: tenantHost,
			tenantClient: tenantClient,
			tenantSecret: tenantSecret,
			tenantInfo: tenantInfo,
			tokenUrl: tokenUrl,
			action: "insertData",
			table: "v_Tenant",
		},
		success: function(response) {
		    console.log("Data successfully sent:", response);
		
		    // JSON-Response prüfen
		    if (response.success) {
		        $("#tenantOwner").val('');
		        $("#tenantName").val('');
		        $("#tenantTier").val('');
		        $("#tenantHost").val('');
		        $("#tenantClient").val('');
		        $("#tenantSecret").val('');
		        $("#tenantInfo").val('');
		
		        $('#tenantForm').addClass('d-none');
		
		        showAlert('Tenant has been saved successfully!', 'success');
		        refreshTable('vcompare_tenant_v');
		    } else {
		        console.error("Server returned an error:", response.message);
		        showAlert(response.message, 'danger');
		    }
		},		
		error: function(xhr, status, error) {
			console.error("Error by Posting Data:", error);
			showAlert('Error by processing data', 'danger');
		},
	});
}


/**
 * Function to Toogle Add Tenant Button
 * Button should only display if there is an Owner
 */

function toggleAddTenantButton() {
	
	let tableBody = $("#v_Owner-body");
	let button = $("#addTenant");

	let rows = 0;

	$("#v_Owner-body tr").each(()=>{
                        rows++;
                    });

	console.log(tableBody);
	console.log("Rows found:", tableBody.children.length);

	if (tableBody.children("tr").length > 0) {
		console.log('Show Add Tenant Button');
		button.show(); // show button
	} else {
		console.log('Hide Add Tenant Button');
		button.hide(); // hide button
	}
}



