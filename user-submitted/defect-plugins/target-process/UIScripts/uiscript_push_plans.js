name: Push Plans To TP
description: Pushes TestRail test results to a Target Process entity as a comment
author: Patrick Day
version: 1.0
includes: ^plans/view
excludes: 

js:
$(document).ready(function() {


  /* Create the button */
  var button = $('<div class="toolbar content-header-toolbar"><a class="toolbar-button toolbar-button-last toolbar-button-first content-header-button button-push" href="javascript:void(0)">Push Results</a></div>');

  /* Add the text box for Target Process entity ID */
  var tp_entity_id= $('<input type="text" class="target-process-id-input" placeholder="Target Process Entity ID"></input>');
  button.append(tp_entity_id);
  var tr_plan_id = uiscripts.context.plan.id;
  var pass = uiscripts.context.passed_count;
  var fail = uiscripts.context.failed_count;

  /* Add it to the toolbar */
  $("#content-header .content-header-inner").prepend(button);

  /* Bind the click event to push the results to Target Process */
  $("a", button).click(function() {
    /* Get the Target Process entity ID */
    var targetProcessId = $(".target-process-id-input").val();

    /* Get the test results using TestRail API */
    var results = [];
    $.ajax({
      url: "/index.php?/api/v2/get_plan/" + uiscripts.context.plan.id,
      type: "GET",
      contentType: "application/json",
      dataType: "json",
      success: function(planData) {
        /* Store the test results */
        var plan_id = planData.plan_id;
        $.ajax({
          url: "/index.php?/api/v2/get_plan/" + tr_plan_id,
          type: "GET",
          contentType: "application/json",
          dataType: "json",
          success: function(planData) {
            /* Store the test results */
            results = planData.entries;
console.log(results);
            
            /* Push the results to Target Process */
            $.ajax({
              url: "/custom/plan-results.php",
              type: "POST",
              data: {
		tr_plan_id:  tr_plan_id,
                targetProcessId: targetProcessId,
                results: planData.entries
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
      },
      error: function(error) {
        alert("Error getting test results: " + error);
      }
    });
  });
});