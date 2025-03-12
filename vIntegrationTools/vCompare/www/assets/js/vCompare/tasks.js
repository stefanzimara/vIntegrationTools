/**
 * Project: vCompare Nexus 
 * File: tasks.js
 * Author: Stefan Zimara <stefan.zimara@valcoba.com>
 * Created: 2025-28-02
 *
 * Description:
 * Javascript / Jquery Function that are used for the settings page
 *
 * Documentation:
 * For detailed documentation, refer to:
 * https://github.com/stefanzimara/vIntegrationTools
 *
 * History:
 * - 2025-28-02: Initial version created by Stefan Zimara
 * 
 * Usage:
 * https://github.com/stefanzimara/vIntegrationTools/wiki
 *    
 * License:
 * This project is licensed under the AGPL License - see the LICENSE file for details.
 * 
**/




$(document).ready(function(){
  $("#startButton").on("click", function(){
    startProcess();
  });
});




// Start the background process
function startProcess() {
    $.ajax({
	
        url: "/lib/startProcess.php",
        type: 'POST',
        dataType: 'json',
        
        success: function(response) {
            if(response.status === "started") {
                var processId = response.processId;
                console.log("Process started with ID:", processId);
                // Start polling for the process status
                pollProcessStatus(processId);
            }
        },
        
        error: function(xhr, status, error) {
            console.error("Error starting process:", error);
        }
    });
}

// Poll the process status every 5 seconds
function pollProcessStatus(processId) {
    var interval = setInterval(function() {
        $.ajax({
            url: '/lib/checkProcessStatus.php',
            type: 'POST',
            data: { processId: processId },
            dataType: 'json',
            success: function(status) {
                console.log("Current status:", status);
                // Update your UI based on the status.progress value, e.g. a progress bar.
                if(status.completed) {
                    clearInterval(interval);
                    console.log("Process completed!");
                    // Optionally, update the UI to show completion
                }
            },
            error: function(xhr, status, error) {
                console.error("Error checking process status:", error);
            }
        });
    }, 5000); // Poll every 5000ms (5 seconds)
}



