/*
 * Description: Replicates the UI functionality of 'Rerun', creating a new test run based on
 * an existing run.
 * Author: Gurock Software
 */

import gurock.testrail.*;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;

public class Program
{
	public static void main(String[] args) throws Exception
	{
		/**
		 * These values should be updated prior to use
		 */
		APIClient client = new APIClient("TESTRAIL URL");
		client.setUser("TESTRAIL USER");
		client.setPassword("TESTRAIL PASSWORD OR API KEY");
		int run_id = 0;
    String title = "New Test Run Title";
		
		/**
		 * status ID string is for all result statuses. String can be adjusted for specific test IDs.
		 * See http://docs.gurock.com/testrail-api2/reference-statuses for additional information
		 */
		String statuses = "1,2,3,4,5,6,7,8,9,10,11,12";
	
		JSONObject new_run = rerun(client, run_id, title, statuses);
		System.out.println(new_run);
		
	}
	
	/**
	 * Create a new test run using the same details as an existing run in the project. The title of the new
	 * run is not replicated.
	 * 
	 * @param client		The APIClient used to make the API requests. Should be initialized prior to calling
	 * 						this function.
	 * @param run_id		Valid integer ID of an existing test run in TestRail
	 * @param title			Title of the new test run to be created. Cannot be an empty string.
	 * @param statuses		Comma-separated string of integer status IDs in TestRail. These are the status IDs 
	 * 						of the existing tests which will be included in the new test run.
	 * @return				The response data from add_run. See http://docs.gurock.com/testrail-api2/reference-runs#add_run
	 * @throws APIException
	 * @throws IOException
	 */
	public static JSONObject rerun(APIClient client, int run_id, String title, String statuses)
		throws APIException, IOException
	{
		Map post_body = new HashMap();
		
		/**
		 * Get the previous run details
		 */
		JSONObject run_details = (JSONObject) client.sendGet("get_run/" + run_id);

		long project_id = (Long) run_details.get("project_id");

		/**
		 * Store run's properties for the new test run
		 */
		post_body.put("assignedto_id", (Long) run_details.get("assignedto_id"));
		post_body.put("suite_id", (Long) run_details.get("suite_id"));
		post_body.put("milestone_id", (Long) run_details.get("milestone_id"));
		post_body.put("description", (String) run_details.get("description"));
		post_body.put("include_all", (Boolean) run_details.get("include_all"));
		
		/**
		 * If previous run did not include all cases from the project/suite or re-run will not include all statuses
		 * Build a list of test case IDs
		 */
		if(!((Boolean) run_details.get("include_all")) || (statuses != "1,2,3,4,5,6,7,8,9,10,11,12"))
		{
			post_body.put("case_ids", get_case_ids_for_run(client, run_id, statuses));
			post_body.put("include_all", false);
		}
		
		JSONObject response = (JSONObject) client.sendPost("add_run/" + project_id, post_body);
		
		return response;
	}
	
	
	/**
	 * Gets a list of test case IDs which correspond to the tests from an existing test run.
	 * 
	 * @param client		The APIClient used to make the API requests. Should be initialized prior to calling
	 * 						this function.
	 * @param run_id		Valid integer ID of an existing test run in TestRail.
	 * @param statuses		Comma-separated string of integer status IDs in TestRail. These are the status IDs 
	 * 						of the existing tests which will be retrieved from the existing test run.
	 * @return				An array of test case IDs associated with tests in the test run
	 * @throws APIException
	 * @throws IOException
	 */
	public static ArrayList<Long> get_case_ids_for_run(APIClient client, int run_id, String statuses)
		throws APIException, IOException
	{
		ArrayList<Long> case_ids = new ArrayList<Long>();

		JSONArray response = (JSONArray) client.sendGet("get_tests/" + run_id + "&status_id=" + statuses);
		case_ids.addAll(get_values_for_key("case_id", response));
		return case_ids;
	}
	
	/**
	 * Checks an array of JSONObjects for all values corresponding to key_name.
	 * 
	 * @param key_name	The key for which values are sought
	 * @param arr		The array containing JSONObjects from which the key/value pairs will be checked. 
	 * 					This should contain values in which the key_name can be checked.
	 * @return			An array of Long values which correspond to the key parameter
	 */
	public static ArrayList<Long> get_values_for_key(String key_name, JSONArray arr)
	{
		ArrayList<Long>  value_list = new ArrayList<Long>();
		
		for(int i = 0; i < arr.size(); i++)
		{
			JSONObject entry = (JSONObject) arr.get(i);
			value_list.add((Long) entry.get(key_name));
		}
		
		return value_list;
		
	}
}
