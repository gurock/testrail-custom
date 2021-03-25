from testrail import *
from datetime import datetime   # This is used if setting a timestamp for created_before or created_after
import time
# This script is based on new endpoint structures with TestRail 6.7.
# Enable the x-api-ident: beta header if necessary.

#########################################################
# THIS SCRIPT DELETES TEST RUNS AND PLANS FROM TESTRAIL #
# BASED ON PROJECTS AND FILTERS PROVIDED                #
# USE WITH CAUTION!!!                                   #
#########################################################


# Details needed to make the API request
TESTRAIL = APIClient('')
TESTRAIL.user = ''
TESTRAIL.password = ''
PROJECT_IDS = []       # Integer IDs of target projects for cleaning


# Filters to be applied when using 'get_runs' and 'get_plans'
# Value should be set to None if filter will not be applied
# offset is not included here since it is handled elsewhere
# created_before and created_after are handled in the next code block
FILTERS = { 'created_by': None,         # Integer ID of user
            'is_completed': 1,          # 0 for active, 1 for completed
            'limit': None,              # Integer between 1 and 249
            'milestone_id': None        # Integer ID of milestone
            }


#########################################################
# THIS CODE BLOCK CAN BE USED TO SET A TIMESTAMP FILTER #
#########################################################
# date_string = 'mm/dd/yyyy'
# FILTERS['created_after'] = int(datetime.strptime(date_string, '%m/%d/%Y').timestamp())
# date_string = '03/01/2019'
# FILTERS['created_before'] = int(datetime.strptime(date_string, '%m/%d/%Y').timestamp())


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


def make_api_post_request(uri, data={}):
    too_many_requests = False
    while not too_many_requests:
        try:
            response = TESTRAIL.send_post(uri, data)
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


def get_all_run_ids(proj_id, filters):
    debug = False
    run_ids = list()
    run_uri = 'get_runs/' + str(proj_id)
    run_filters = ''
    for k, v in filters.items():
        if v:
            run_filters += '&' + k + '=' + str(v)
    run_uri += run_filters
    run_data = make_api_get_request(run_uri)
    run_list = run_data['runs']
    # get_runs is limited to 250 entries per response
    offset = 0
    while True:
        for run in run_list:
            run_ids.append(run['id'])
        if not run_data['_links']['next']:
            break
        else:
            offset += 250
            run_data = make_api_get_request(run_uri + '&offset=' + str(offset))
            run_list = run_data['runs']
    return run_ids


def get_all_plan_ids(proj_id, filters):
    debug = False
    plan_ids = list()
    # get_plans is limited to 250 entries per response
    plan_uri = 'get_plans/' + str(proj_id)
    plan_filters = ''
    for k, v in filters.items():
        if v and (k != 'suite_id'):
            plan_filters += '&' + k + '=' + str(v)
    plan_uri += plan_filters

    plan_data = make_api_get_request(plan_uri)
    plan_list = plan_data['plans']
    offset = 0
    while True:
        for plan in plan_list:
            plan_ids.append(plan['id'])
        if not plan_data['_links']['next']:
            break
        else:
            offset += 250
            plan_data = make_api_get_request(plan_uri + '&offset=' + str(offset))
            plan_list = plan_data['plans']
    if debug:
        print('List of test plans IDs to be deleted')
        print(plan_ids)

    return plan_ids


def delete_run(run_id):
    response = make_api_post_request('delete_run/' + str(run_id))
    return


def delete_plan(plan_id):
    response = make_api_post_request('delete_plan/' + str(plan_id))
    return


def clean_up(project_list, filters):
    debug = True
    deletes_made = 0

    for project_id in project_list:
        run_ids = get_all_run_ids(project_id, filters)
        plan_ids = get_all_plan_ids(project_id, filters)

        for run in run_ids:
            if debug:
                print('Deleting Run: %s' % str(run))
            delete_run(run)
            deletes_made += 1
        for plan in plan_ids:
            if debug:
                print('Deleting Plan: %s' % str(plan))
            delete_plan(plan)
            deletes_made += 1
    return deletes_made


def main():
    number_of_deletes = clean_up(PROJECT_IDS, FILTERS)
    print('There were %s runs and plans deleted.' % str(number_of_deletes))


if __name__ == '__main__':
    main()
