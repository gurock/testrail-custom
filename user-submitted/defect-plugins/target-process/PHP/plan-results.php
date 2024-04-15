<?php


// Target Process Credentials
$tp_username = "TP_USERNAME";
$tp_password = "TP_PASSWORD";
$tp_base_url = "https://your.tpondemand.com/api/v1/";

// Testrail Credentials
$testrail_username = "TR_USERNAME";
$testrail_password = "TR_PASSWORD";
$testrail_pview_url = "https://your-testrail-url/index.php?/plans/view/";
$testrail_rview_url = "https://your-testrail-url/index.php?/results/view/";

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
	//  "id" => your id associated with your target process account
        "id" => YOUR_ID
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
