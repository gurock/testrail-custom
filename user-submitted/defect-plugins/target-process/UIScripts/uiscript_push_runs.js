name: Push Runs To TP
description: Pushes TestRail test results to a Target Process entity as a comment
author: Patrick Day
version: 1.0
includes: ^runs/view
excludes: 

js:
$(document).ready(function() {
  /* Create the button */
  var button = $('<div class="toolbar content-header-toolbar"><a class="toolbar-button toolbar-button-last toolbar-button-first content-header-button button-push" href="javascript:void(0)">Push Results</a></div>');

  /* Add the text box for Target Process entity ID */
  var tp_entity_id = $('<input type="text" class="target-process-id-input" placeholder="Target Process Entity ID"></input>');
  button.append(tp_entity_id);

  /* Get the test run ID */
  var tr_run_id = uiscripts.context.run.id;

  /* Add the button to the toolbar */
  $("#content-header .content-header-inner").prepend(button);

  /* Bind the click event to push the results to Target Process */
  $("a", button).click(function() {
    /* Get the Target Process entity ID */
    var targetProcessId = $(".target-process-id-input").val();
    
   var results = [];
    /* Get the test results using TestRail API */
    $.ajax({
      url: "/index.php?/api/v2/get_run/" + tr_run_id,
      type: "GET",
      contentType: "application/json",
      dataType: "json",
      success: function(runData) {
        /* Store the test results */
        var results = uiscripts.context;
            
        /* Push the results to Target Process */
        $.ajax({
          url: "/custom/run-results.php",
          type: "POST",
          data: {
            tr_run_id: tr_run_id,
            targetProcessId: targetProcessId,
            results: uiscripts.context
          },
          success: function(data) {
            alert("Test results pushed to Target Process successfully!");
          },
          error: function(error) {
            alert("Error pushing test results to Target Process: " + error);
          }
        });
      },
      error: function(error) {
        alert("Error getting test results: " + error);
      }
    });
  });
});