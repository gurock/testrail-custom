require 'net/http'
require 'uri'
require 'json'

tp_username = "qa+testrail@wowza.com"
tp_password = "Aut0mat!on"
tp_base_url = "https://wowza.tpondemand.com/api/v1/"

testrail_username = "qa@wowza.com"
testrail_password = "2021Quality"
testrail_base_url = "https://testrail.wowza.com/index.php?/api/v2/"
testrail_pview_url = "https://testrail.wowza.com/index.php?/plans/view/"
testrail_rview_url = "https://testrail.wowza.com/index.php?/runs/view/"

# Get the Target Process entity ID
	target_process_id = params[:targetProcessId]

# Get the test results from TestRail
	test_results = params[:results]

# Loop through all test results
	results_table = "<table border=0>"
	results_table += "<tr><th><span style=\"color:blue\">Run Name</span></th><th><span style=\"color:green\">Passed</span></th><th>Blocked</th><th>Untested</th><th>Retest</th><th><span style=\"color:red\">Failed</span></th></tr>"
test_results.each do |results|
  results.each do |result|
    result.each do |run|
    end
  end
	  results_table += "<tr><b><td><b><a href=" + testrail_rview_url + results['id'] + ">" + results['name'] + "-" + results['config'] + "</a></b></td><td><span style=\"color:green\"><b>" + results['passed_count'] + "</span></td><td><span style=\"color:blue\"><b>" + results['blocked_count'] + "</span></td><td><span style=\"color:grey\"><b>" + results['untested_count'] + "</span></td><td><span style=\"color:silver\"><b>" + results['retest_count'] + " </span></td><td><span style=\"color:red\"><b>" + results['failed_count'] + "</span></td></b></tr>"
	  results_table += "</table><noscript>"
end

# Build the payload for the comment
payload = {
  "description" => results_table,
  "owner" => {
    "id" => 581
  },
  "General" => {
    "id" => target_process_id
  }
}.to_json

# Make the API call to Target Process
	tp_url = tp_base_url + "Comments?include=[Description,General]&format=json&take=100"
	uri = URI.parse(tp_url)
	request = Net::HTTP::Post.new(uri)
	request.content_type = "application/json"
	request.basic_auth(tp_username, tp_password)
	request.body = payload
	response = Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
	  http.request(request)
end

# Check the result
if response.is_a?(Net::HTTPSuccess)
  # Handle success
  result = response.body
else
  # Handle error
end
