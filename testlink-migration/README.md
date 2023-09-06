
# How to import test cases from TestLink into TestRail

## Background

[TestLink](https://testlink.org/) is an open source test management tool.

As TestLink exclusively supports exporting test cases in XML format. As part of this repository, you'll discover scripts designed to convert TestLink XML into a CSV file that is compatible with TestRail for import process.

## Contents

In this repo you'll find a set of examples on how to convert XML files exported from TestLink using a script present in the repository.
* Migration scripts are available for the  below configurations:
    * one_test_case
        * One XML file that is the result of exporting one test case from TestLink
        * One CSV file that is the result of executing the script (TestLink2TestRail.py)  against the XML file
    * one_test_project
        * One XML file that is the result of exporting one test project (with 2 test suites and several test cases) from TestLink
        * One CSV file that is the result of executing the script (TestLink2TestRail.py) against the XML file
    * one_test_suite
        * One XML file that is the result of exporting one test suite (with several test cases) from TestLink
        * One CSV file that is the result of executing the script against the XML file
    * TestLink2TestRail.py - Scripts that will convert XML files to CSV files which are compatible with TestRail.  

### Script usage

```Python
python3 TestLink2TestRail.py -i one_test_case/LoginValidation.testcase.xml -o one_test_case/LoginValidation.testcase.csv
```

```Python
python3 TestLink2TestRail.py -i one_test_project/Comic-EStore.testproject.xml -o one_test_project/Comic-EStore.testproject.csv
```

```Python
python3 TestLink2TestRail.py -i one_test_suite/RegressionTestSuite.xml -o one_test_suite/RegressionTestSuite.csv
```

### Source-code

- [Python](./python/)
