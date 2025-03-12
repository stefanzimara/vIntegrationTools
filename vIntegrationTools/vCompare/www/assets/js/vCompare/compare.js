/**
 * Project: vCompare Nexus 
 * File: compare.js
 * Author: Stefan Zimara <stefan.zimara@valcoba.com>
 * Created: 2025-20-02
 *
 * Description:
 * Javascript / Jquery Function that are used for the settings page
 *
 * Documentation:
 * For detailed documentation, refer to:
 * https://github.com/stefanzimara/vIntegrationTools
 *
 * History:
 * - 2025-20-02: Initial version created by Stefan Zimara
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
	
	requestTenants();
	
});




function requestTenants() {


	// AJAX-Request
	$.ajax({
		url: "/lib/core.ajax.php",
		type: "POST",
		data: {
			action: "getTenantsPerOwner"
		},
		success: function(responseData) {

		    // Group the data by owner
		    var ownerGroups = {};
		    $.each(responseData.data, function(index, item) {
		        var owner = item.owner.toLowerCase();
		        if (!ownerGroups[owner]) {
		            ownerGroups[owner] = [];
		        }
		        ownerGroups[owner].push(item);
		    });

			buildOwnerTenantTables(responseData, ownerGroups);
			
			//Fill Content by Owner
			$.each(ownerGroups, function(owner, items) {
				fillOwnerTenantTables(owner.toLowerCase(), ownerGroups);
			});
		
		},
		error: function(xhr, status, error) {
			console.error("Error by Posting Data:", error);
			showAlert('Error by processing data', 'danger');
		},
	});
}


function buildOwnerTenantTables(responseData, ownerGroups) {
    // Validate response structure
    if (responseData.status !== "success" || !Array.isArray(responseData.data)) {
        console.error("Invalid response data structure:", responseData);
        return;
    }
    
    // Clear the main container
    $("#content").empty();
    
    // Start building the card with tab navigation
 let cardHtml = `
    <div class="card text-center">
        <div class="vc-card-header card-header d-flex align-items-center justify-content-between" style="background-color: #f8f8f8;">
            <ul class="nav nav-tabs card-header-tabs" id="ownerTabs" role="tablist" style="border-bottom: var(--bs-nav-tabs-border-width) solid var(--bs-nav-tabs-border-color);">`;
    
// Build the navigation tabs for each owner
$.each(ownerGroups, function(owner, items) {
    let ownerId = owner.toLowerCase().replace(/\s+/g, '_'); // Ensure a safe ID
    cardHtml += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-${ownerId}" data-bs-toggle="tab" data-bs-target="#pane-${ownerId}" type="button" role="tab" aria-controls="pane-${ownerId}" aria-selected="false">
                            ${owner}
                        </button>
                    </li>`;
});
    
cardHtml += `
            </ul>
            <div>
                <input type="text" id="tableFilter" class="form-control form-control-sm" placeholder="Filter by name">
            </div>
        </div>
        <div class="card-body">
            <div class="tab-content" id="ownerTabsContent">`;
    
// ... Restlicher Code

    
    // Build the tab panes, each with a table for the owner's artifacts
    $.each(ownerGroups, function(owner, items) {
        let ownerId = owner.toLowerCase().replace(/\s+/g, '_');
        cardHtml += `
                    <div class="tab-pane fade" id="pane-${ownerId}" role="tabpanel" aria-labelledby="tab-${ownerId}">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="ps-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">System</th>
                                        <th rowspan="2" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Artifacts</th>`;
        
        // For each tenant for this owner, add a header cell with the tenant name
        $.each(items, function(index, item) {
            cardHtml += `
                                        <th colspan="3" class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            ${item.tenant}
                                        </th>`;
        });
        
        cardHtml += `
                                    </tr>
                                    <tr>`;
        
        // For each tenant, add sub-header cells for Designtime, Runtime, and Status
        $.each(items, function(index, item) {
            cardHtml += `
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Designtime</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Runtime</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>`;
        });
        
        cardHtml += `
                                    </tr>
                                </thead>
                                <tbody id="${ownerId}-body">
                                    <!-- Table rows for artifacts will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>`;
    });
    
    cardHtml += `
                </div>
            </div>
        </div>`;
    
    // Append the entire card to the content container
    $("#content").append(cardHtml);
    
    // Activate the first tab and its pane by default
    $("#ownerTabs button.nav-link:first").addClass("active").attr("aria-selected", "true");
    $("#ownerTabsContent .tab-pane:first").addClass("show active");
}


function fillOwnerTenantTables(owner, ownerGroups) {
	

	
	$.ajax({
		url: "/lib/core.ajax.php",
		type: "POST",
		data: {
			action: "getOwnerArtifacts",
			owner: owner
		},
		success: function(responseData) {

			buildOwnerTenantTableBody(owner, responseData.data, ownerGroups);
		
		},
		error: function(xhr, status, error) {
			console.error("Error by Posting Data:", error);
			showAlert('Error by processing data', 'danger');
		},
	});
}

/**
 * Builds the table body for a given owner.
 *
 * @param {string} owner - The owner name (e.g., "Galenica")
 * @param {Array} responseData - Array of artifact objects from the backend.
 *        Each artifact contains fields such as:
 *        - owner, objId, name
 *        - dynamic fields in the format: 
 *          designtime_<Tenant>_version, designtime_<Tenant>_status,
 *          runtime_<Tenant>_version, runtime_<Tenant>_status
 * @param {Array} ownerGroups - Array of unique tenant names for that owner.
 *        For example: ["OC_Development", "OC_Production", "OC_QualityAssurance"]
 */
function buildOwnerTenantTableBody(owner, responseData, ownerGroups) {
	
	// Build the selector for the table body based on the owner (e.g., "#Galenica-body")
    let tbodyId = "#" + owner + "-body"; 
    
    tbodyId = tbodyId.replace(/\s+/g, '_');
    
    $(tbodyId).empty();

	console.log(tbodyId);

	// Loop through each artifact in the response data that belongs to this owner
    responseData.forEach(function(artifact) {
	
		
		if (artifact.owner.toLowerCase() !== owner) return; // Skip if artifact is not for this owner

        // Begin building the row. The first three cells are static:
        // Companies, System, Artifacts (using owner, objId, and name)
        let rowHtml = `<tr>
			<td style="vertical-align: top;">
			  <div class="d-flex align-items-start px-2">
			    <div>
			      <i class="material-symbols-rounded opacity-5 me-3">source_environment</i>
			    </div>
			    <div class="d-flex flex-column">
			      <h6 class="mb-0 text-sm">${artifact.owner}</h6>
			    </div>
			  </div>
			</td>

           <td>
			  <div class="d-flex flex-column">
			    <div class="d-flex align-items-center">
			      <i class="material-symbols-rounded opacity-5 me-1 vc-icon-fixed">cloud_circle</i>
			      <h6 class="mb-1 text-dark text-sm artifact-name">${artifact.name}</h6>
			    </div>
			    <div class="d-flex align-items-center">
			      <i class="material-symbols-rounded opacity-5 me-1 vc-icon-fixed">emoji_objects</i>
			      <span class="text-xs">${artifact.objId}</span>
			    </div>
			  </div>
			</td>



          `;


		// For each tenant in the group for this owner, create three columns
		ownerGroups[owner].forEach(function(tenantData) {
		    let tenant = tenantData.tenant.replace(/\s+/g, '_'); // tenantData is an object, e.g., { owner: "CUST", tenant: "PROD" }
		    let dtVersionKey = "designtime_" + tenant + "_version";
		    let rtVersionKey = "runtime_" + tenant + "_version";
		    let dtStatusKey = "designtime_" + tenant + "_status";
		    let rtStatusKey = "runtime_" + tenant + "_status";
		
		    let dtVersion = artifact[dtVersionKey] || "";
		    let rtVersion = artifact[rtVersionKey] || "";
		    let status = artifact[rtStatusKey] || artifact[dtStatusKey] || "";
		
		    rowHtml += `
		        <td class="align-top text-center text-sm">
		            <span class="text-xs">${dtVersion}</span>
		        </td>
		        <td class="align-top text-center text-sm">
		            <span class="text-xs">${rtVersion}</span>
		        </td>
		        <td class="align-top text-center text-sm">
		            <span class="text-xs">${status}</span>
		        </td>`;
		});


        rowHtml += `</tr>`;

        // Append the row to the table body
        $(tbodyId).append(rowHtml);
    });
}


$(document).on('keyup', '#tableFilter', function(){
    // Dein Filter-Code hier
    var value = $(this).val().toLowerCase();
    
    console.log(value);
    
    $('#content table tbody tr').filter(function(){
        $(this).toggle($(this).find('.artifact-name').text().toLowerCase().indexOf(value) > -1);
    });
});



