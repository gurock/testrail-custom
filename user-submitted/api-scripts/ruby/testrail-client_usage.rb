require 'testrail/api'

client = TestRail::Client::Api.new('https://YourTestrailURL')
client.user = 'YourUserName'
client.password = 'YourPassword'

# GET case with ID 1
client.get_case(1)
# GET cases from Project_id 22, limit to 1 case
client.get_cases(22,{:limit => 1})
# POST update to case with ID 1, update case title
client.update_case(1,{:title => "new Name"})
# POST delete to case with ID 1, delete the case
client.delete_case(1)