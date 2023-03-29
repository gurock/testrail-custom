<?php

//$tp_username = "pday@wowza.com";
$tp_username = "qa+testrail@wowza.com";

//$tp_password = "S3pt3mb3r25!";
$tp_password = "Aut0mat!on";

//$tp_base_url = "https://wowzasandbox.tpondemand.com/api/v1/";
$tp_base_url = "https://wowza.tpondemand.com/api/v1/";

//$testrail_username = "patrick.day@wowza.com";
//$testrail_password = "S3pt3mb3r!";

$testrail_username = "qa@wowza.com";
$testrail_password = "2021Quality";
//$testrail_base_url = "https://testrail.wowza.com/index.php?/api/v2/";
$testrail_pview_url = "https://testrail.wowza.com/index.php?/plans/view/";
$testrail_rview_url = "https://testrail.wowza.com/index.php?/runs/view/";

// Get the Target Process entity ID

$targetProcessId = $_POST['targetProcessId'];
// Get the test results from TestRail

$testResults = $_POST['results'];
// Loop through all test results

$resultsTable .= "<table border=1>";
$resultsTable .="<tr><th>Milestone ID</th><th><span style=\"color:blue\">Plan Name</span></th><th><span style=\"color:orange\">Run Name</span></th><th><span style=\"color:green\">Passed</span></th><th>Blocked</th><th>Untested</th><th>Retest</th><th><span style=\"color:red\">Failed</span></th></tr>";
        foreach ($testResults as $result) {
          foreach ($result as $runs) {
            foreach ($runs as $run) {
$resultsTable .= "<tr><td>" . $run['milestone_id'] . "</td><td><b><a href=" . $testrail_rview_url . $run['id'] . ">" . $run['name'] . "</a></b></td><td><span style=\"color:orange\"><b>" . $run['name'] .  "</b></span></td><td><span style=\"color:green\"><b>" . $run['passed_count'] . "</b></span></td><td><span style=\"color:blue\"><b>" . $run['blocked_count'] . "</b></span></td><td><span style=\"color:grey\"><b>" . $run['untested_count'] . "</b></span></td><td><span style=\"color:silver\"><b>" . $run['retest_count'] . "</b></span></td><td><span style=\"color:red\"><b>" . $run['failed_count'] . "</b></span></td></tr>";
}
}
}
$resultsTable .= "</table>";


// Build the payload for the comment

$payload = array(
    "description" => $resultsTable,
    "owner" => array(
        "id" => 581
//          "id" => 89
    ),
    "General" => array(
        "id" => $targetProcessId
    ) ); // Encode the payload as JSON

$payload = json_encode($payload); // Make the API call to Target Process
$tp_url = $tp_base_url . "Comments?include=[Description,General]&format=json&take=100";


$ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $tp_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_USERPWD, "$tp_username:$tp_password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Content-Type: application/json", "Content-Length: " . strlen($payload) ));

$result = curl_exec($ch);
curl_close($ch); // Check the result

if ($result === false) {
    // Handle error
} else {
    // Handle success
}
?>
