from testrail import *
from datetime import datetime   # This is used if setting a timestamp for created_before or created_after
import time

##########################
# GENERAL NOTES          #
######################################################################################################################
# Author: Gurock Software
# Purpose: This script will check a test suite for any test cases which have not been tested within any test runs or 
# plans
######################################################################################################################

##########################
# HOW TO USE THIS SCRIPT #
######################################################################################################################
# 1. Edit TESTRAIL, PROJECT_ID, and FILTERS values to match your criteria
# 1a. If you would like to use a timestamp filter, you can uncomment the lines in the code block following FILTERS
# 1b. FILTER values should be set to None if not in use
# 2. Run the script
# 3. The script will output a list of case IDs which have not been tested based on the filters applied
######################################################################################################################

###########################
# NOTES ABOUT THIS SCRIPT #
######################################################################################################################
# - This script has not been fully tested and should be checked against your real data to ensure accuracy
# - This script does handle rate limits (429 errors) with TestRail Cloud
# - This script does not handle other exceptions
# - There are no POST requests made by this script
# - all_case_details is not used in this implementation, but is available for additional case details, if desired
######################################################################################################################

# Details needed to make the API request
TESTRAIL = APIClient('TESTRAIL URL')        # do not include index.php?/api/v2
TESTRAIL.user = 'xxxxxxxxxxxxxxxxxxxxxx'    # TestRail email address
TESTRAIL.password = 'xxxxxxxxxx'            # TestRail password or API token


# Project from which you are retrieving data regarding untested cases
PROJECT_ID = 1      # Integer ID of target project

# Filters to be applied when using 'get_runs' and 'get_plans'
# The suite_id value is used for projects which have multiple suites enabled
# Value should be set to None if filter will not be applied
# offset is not included here since it is handled elsewhere
# created_before and created_after are handled in the next code block
FILTERS = { 'suite_id': 1,             # This is REQUIRED if your project is running in multi-suite mode
            'created_by': None,         # Integer ID of user
            'is_completed': None,       # 0 for active, 1 for completed
            'limit': None,              # Integer between 1 and 249
            'milestone_id': None        # Integer ID of milestone
            }


#########################################################
# THIS CODE BLOCK CAN BE USED TO SET A TIMESTAMP FILTER #
#########################################################
# date_string = '01/10/2019'
# FILTERS['created_after'] = int(datetime.strptime(date_string, '%m/%d/%Y').timestamp())
# date_string = '03/01/2019'
# FILTERS['created_before'] = int(datetime.strptime(date_string, '%m/%d/%Y').timestamp())


# Makes a GET API request using TESTRAIL APIClient declared above
# Pauses and retries if API rate limit exceeded
# RETURNS: the valid api response as received from TestRail, otherwise raises and Exception
def make_api_get_request(uri):
    too_many_requests = False
    while not too_many_requests:
        try:
            response = TESTRAIL.send_get(uri)
            return response
        except APIError as error:
            error_string = str(error)
            if 'Retry after' in error_string:
                # Parse retry after x seconds
                retry_after = error_string.split('Retry after ')[1]
                retry_after = retry_after.split(' ', 1)[0]
                retry_after = int(retry_after)
                print('Pause for %x seconds' % retry_after)
                time.sleep(retry_after)
                too_many_requests = True
            else:
                raise Exception('Unexpected API Error: %s' % error_string)


# Retrieves all test cases from proj_id
# RETURNS: a tuple: list all case details, list of only case IDs
def get_all_cases(proj_id, filters):
    uri = 'get_cases/' + str(proj_id)
    if filters['suite_id']:
        uri += '&suite_id=' + str(filters['suite_id'])

    case_list = make_api_get_request(uri)
    case_ids = list()
    for case in case_list:
        case_ids.append(case['id'])
    return case_list, case_ids


# Retrieves all test runs (including those insides plans) and compiles the run IDs into a list
# Runs retrieved will be based on the values set in filters
# RETURNS: a list of run IDs
def get_all_run_ids(proj_id, filters):
    debug = False
    run_ids = list()
    run_uri = 'get_runs/' + str(proj_id)
    run_filters = ''
    for k, v in filters.items():
        if v:
            run_filters += '&' + k + '=' + str(v)
    run_uri += run_filters
    run_list = make_api_get_request(run_uri)

    # get_runs is limited to 250 entries per response
    offset = 0
    while True:
        for run in run_list:
            run_ids.append(run['id'])
        if len(run_list) != 250:
            break
        else:
            offset += 250
            run_list = make_api_get_request(run_uri + '&offset=' + str(offset))

    # get_plans is limited to 250 entries per response
    plan_uri = 'get_plans/' + str(proj_id)
    plan_filters = ''
    for k, v in filters.items():
        if v and (k != 'suite_id'):
            plan_filters += '&' + k + '=' + str(v)
    plan_uri += plan_filters

    plan_list = make_api_get_request(plan_uri)
    offset = 0
    while True:
        for plan in plan_list:
            plan_details = make_api_get_request('get_plan/' + str(plan['id']))
            for entry in plan_details['entries']:
                for run_in_entry in entry['runs']:
                    run_ids.append(run_in_entry['id'])
        if len(plan_list) != 250:
            break
        else:
            offset += 250
            make_api_get_request(plan_uri + '&offset=' + str(offset))
    if debug:
        print('List of test run IDs to be checked for results:')
        print(run_ids)

    return run_ids


# Retrieves all test runs (including those insides plans) and compiles the run IDs into a list
# Runs retrieved will be based on the values set in filters
# RETURNS: a list of run IDs
def get_tested_case_ids(run_id):
    # Retrieve all tests which are not untested (status_id != 3)
    test_list = make_api_get_request('get_tests/' + str(run_id) + '&status_id=1,2,4,5,6,7,8,9,10,11,12')
    tested_case_ids = list()
    for test in test_list:
        tested_case_ids.append(test['case_id'])

    return tested_case_ids


# Creates a list of all test case IDs within a project, then iterates through available test run IDs
#  to remove tested cases from the list of all case IDs.
# When all run IDs have been exhausted, or all cases have been removed (considered tested), iteration stops
# RETURNS: a list of case IDs which have not been tested. The list can be empty, indicating all cases were tested
def find_untested_case_ids(proj_id, filters):
    debug = False
    # Get case details and case IDs
    all_case_details, all_case_ids = get_all_cases(proj_id, filters)

    # Get all test run IDs within plans and runs
    run_ids = get_all_run_ids(proj_id, filters)
    # Get results for each run ID
    for run in run_ids:
        if not all_case_ids:  # all_case_ids is empty, no more checks needed
            break
        else:
            if debug:
                print('Remaining case IDs to check:')
                print(all_case_ids)
            tested_case_ids = get_tested_case_ids(run)
            # Remove each tested case ID from all_case_ids
            for case_id in tested_case_ids:
                try:
                    all_case_ids.remove(case_id)
                except ValueError:  # No issue, it's already been removed
                    pass
    return all_case_ids


def main():
    untested_case_ids = find_untested_case_ids(PROJECT_ID, FILTERS)
    if untested_case_ids:
        print('There are %s cases which have not been tested:' % str(len(untested_case_ids)))
        print(untested_case_ids)
    else:
        print('There are no untested cases.')


if __name__ == '__main__':
    main()
