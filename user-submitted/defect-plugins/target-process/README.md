# Testrail to Target Process Integration
--------------------------------------
These scripts  add the ability to push test results to any Target Process User Story, Feature, Release, etc... from the view details screen of any **Test Plan** or **Test Run** in Testrail

##### *test plan integration*
![](tr-tp-integration.png)


##### *Test run integration*
![](tp-test-runs.png)

*Actual Results may lois ok different ( for now )*


##### *Milestone Integration*

> --TODO not implemented yet!




### Script Details

#### UI Script // Javascript 
Configurable via the **customizations** section of **Administration settings** in Testrail.

* **uiscript_push_plans.js**
* **uiscript_push_runs.js**
* **uiscript_push_milestones.js**


#### PHP Server scripts 
These scripts are located on server in **/var/www/html/tetrail/custom/**

* **plan-results.php**
* **run-results.php**
* **milestone-results.php**
