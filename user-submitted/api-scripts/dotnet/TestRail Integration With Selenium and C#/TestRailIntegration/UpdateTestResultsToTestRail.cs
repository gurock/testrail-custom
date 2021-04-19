using Gurock.TestRail;
using Newtonsoft.Json.Linq;
using System;
using System.Collections;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Xml.Linq;

namespace TestRailIntegration
{
    class TestResults
    {
        private string testTitle, testResult, error;

        public TestResults(string title, string result, string errorMessage)
        {
            testTitle = title;
            testResult = result;
            error = errorMessage;
        }

        public string TestTitle
        {
            get { return testTitle; }
            set { testTitle = value; }
        }

        public string TestResult
        {
            get { return testResult; }
            set { testResult = value; }
        }

        public string Error
        {
            get { return error; }
            set { error = value; }
        }
    }

    public enum TestTypes
    {
        Renorex = 1,
        Selenium = 2,
        Manual = 3
    }

    class UpdateTestResultsToTestRail
    {
        static void Main(string[] args)
        {
            // This value comes from Vstest automation execution. This is test execution result path.
            string filePath = args[0];

            // When user clicks on 'Run Tests' button from test runs, we are getting that run id via UI scripts. 
            string testRailRunId = args[1];

            // From Selenium : This code is to parse the result xml file and get the testcase name, its outcome and error details if any
            List<TestResults> testResults = new List<TestResults>();
            testResults = GetTestAutomationExecutionResult(filePath);

            // From TestRail API :This method gets test details like test name and test case id using TestRail API. E.g Login_With_Valid_User -> 3185 (key value pair)
            IDictionary<string, string> testcaseDeatilsInTestRail = new Dictionary<string, string>();
            testcaseDeatilsInTestRail = GetTestsByTestRunId(testRailRunId);

            // bulk-add multiple test results to test rail
            ArrayList newresult = new ArrayList();
            foreach (var testResult in testResults)
            {
                foreach (var test in testcaseDeatilsInTestRail)
                {
                    // Test Title is mapping field between UI automation ans Test Rail. We are checking if Titles are matching, then update the result back to test rail.                    
                    if (testResult.TestTitle == test.Key)   // E.g if("Login_With_Valid_Use" == "Login_With_Valid_Use")
                    {
                        var tempData = new Dictionary<string, object>
                             {
                                { "case_id", test.Value },
                                { "status_id", GetStatusIdFromCode(testResult.TestResult) },  // Get UI test automation execution result and map to Test Rail Result. E.g. Pass-> 1, Fail->5 etc.                                
                                { "comment", testResult.Error}
                             };

                        newresult.Add(tempData);
                    }
                }
            }

            // Using TestRail's .NET binding to call the API's
            APIClient client = new APIClient("https://xxxxxx.testrail.io/");
            client.User = ConfigurationManager.AppSettings["username"];
            client.Password = ConfigurationManager.AppSettings["password"];

            var data = new Dictionary<string, object>
                             {
                                    { "results", newresult }
                             };


            client.SendPost("add_results_for_cases/" + testRailRunId, data);
        }

        // This code is to parse the result xml file and get the testcase name and its outcome, error if any
        private static List<TestResults> GetTestAutomationExecutionResult(string filePath)
        {
            List<TestResults> testResults = new List<TestResults>();

            // Locate and load the result file
            XElement xelement = XElement.Load(filePath);

            IEnumerable<XElement> results = xelement.Elements().Where(e => e.Name.LocalName == "Results");
            
            foreach (var result in results)
            {
                IEnumerable<XElement> unitTestResults = results.Elements().Where(e => e.Name.LocalName == "UnitTestResult");

                foreach (var unitTestResult in unitTestResults)
                {
                    if (unitTestResult.Attribute("outcome").Value == "Failed")
                    {
                        testResults.Add(new TestResults(unitTestResult.Attribute("testName").Value, unitTestResult.Attribute("outcome").Value, unitTestResult.Value));
                    }
                    else
                    {
                        testResults.Add(new TestResults(unitTestResult.Attribute("testName").Value, unitTestResult.Attribute("outcome").Value, ""));
                    }
                }
            }

            return testResults;
        }

        // This function gets test details of particular test run
        private static IDictionary<string, string> GetTestsByTestRunId(string testRunId)
        {
            IDictionary<string, string> testcaseDeatilsInTestRail = new Dictionary<string, string>();

            APIClient client = new APIClient("https://xxxxxx.testrail.io/");
            client.User = ConfigurationManager.AppSettings["username"];
            client.Password = ConfigurationManager.AppSettings["password"];
            JArray testDetails = (JArray)client.SendGet("get_tests/" + testRunId);

            foreach (var test in testDetails)

                // Filter the which are automated using selenium. Ignore other manual test cases.
                if ((int)test["custom_automation_type"] == (int)TestTypes.Selenium)
                {
                    testcaseDeatilsInTestRail.Add((string)test["title"], (string)test["case_id"]);
                }

            return testcaseDeatilsInTestRail;
        }

        // This function gets equivalent status Id from status code 
        private static string GetStatusIdFromCode(string statusCode)
        {
            string statusId = "";

            switch (statusCode)
            {
                case "Passed":
                    statusId = "1";
                    break;
                case "Failed":
                    statusId = "5";
                    break;
                case "Timeout":
                    statusId = "5";
                    break;
                case "Aborted":
                    statusId = "4";
                    break;
                default:
                    Console.WriteLine("Not Valid Status Code");
                    break;
            }

            return statusId;
        }
    }
}
