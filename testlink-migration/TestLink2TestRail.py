import sys, getopt
import re
import xml.etree.ElementTree as ET
import pandas as pd

column = ["Issue ID","Test Type","Test Summary", "Test Priority", "Action","Data","Result"]
row = []

CLEANR = re.compile('<.*?>|&([a-z0-9]+|#[0-9]{1,6}|#x[0-9a-f]{1,6});')
EMPTYSPACES = re.compile('\n|\r')
QUOTES = re.compile('\&quot\;')

def cleanTags(txt):
    if txt:
        cleanTxt = re.sub(QUOTES, '"', txt)
        cleanTxt = re.sub(CLEANR, '', cleanTxt)
        cleanTxt = re.sub(EMPTYSPACES, ' ', cleanTxt)
    else:
        cleanTxt = ''

    return cleanTxt

def appendRows(issueID='', issueKey='', testType='', testSummary='', testPriority='', action='', data='', result=''):
    row.append({"Issue ID": issueID,
                "Test Type": 'Manual' if testType=='1' else 'Automated',
                "Test Summary": cleanTags(testSummary),
                "Test Priority": testPriority,
                "Action": cleanTags(action),
                "Data": '',
                "Result": cleanTags(result)
                })

def handleTestSuites(root, issueID, outputfile):
    # When exporting from a Test Suite Testlink adds one level
    testsuites = root.findall('testsuite')
    for testsuite in testsuites:
        issueID = handleTestSuites(testsuite, issueID, outputfile)
    # Parse Testcases
    return handleTestCases(root, issueID, outputfile=outputfile)

def handleTestCases(root, issueID, outputfile):

    # When exporting from a Test Suite Testlink adds one level
    if root.tag != 'testcase':
        root = root.findall('testcase')

    for testcase in root:
        # Parse Testcases
        name = testcase.attrib['name']
        summary = testcase.find('summary').text
        test_Type = testcase.find('execution_type').text
        test_priority = testcase.find('importance').text
        first_step = True
        hasSteps = False
        
        # Parse Steps  
        for step in testcase.findall('steps/step'):
            hasSteps = True
            action = step.find('actions').text
            expectedResult = step.find('expectedresults').text
            if first_step:
                appendRows(issueID=issueID,testType=test_Type,testSummary=summary,testPriority=test_priority,action=action,result=expectedResult)
                first_step = False
            else:
                appendRows(issueID=issueID,testType=test_Type, action=action,result=expectedResult)
        
        if not hasSteps:
            appendRows(issueID=issueID,testType=test_Type,testSummary=summary,testPriority=test_priority)
        issueID = issueID+1  
        
        df = pd.DataFrame(row, columns= column)
        df.set_index("Issue ID", inplace=True)
        df.to_csv(outputfile)
    return issueID


def parseTestlink2TestRailData(inputfile, outputfile):
    # Parsing XML file
    xmlParse = ET.parse(inputfile)
    root = xmlParse.getroot()
    issueID = 1

    if root.tag != 'testcase' and root.tag != 'testcases':
        handleTestSuites(root=root, issueID=issueID, outputfile=outputfile)
    else:
        handleTestCases(root=root, issueID=issueID, outputfile=outputfile)

    
def main(argv):
   inputfile = ''
   outputfile = ''

   try:
        opts, args = getopt.getopt(argv,"hi:o:",["ifile=","ofile="])
        for opt, arg in opts:
            if opt == '-h':
                print ('TestLink2TestRail.py -i <XML_inputfile> -o <CSV_outputfile>')
                sys.exit()
            elif opt in ("-i", "--ifile"):
                inputfile = arg
            elif opt in ("-o", "--ofile"):
                outputfile = arg
   except Exception as err:
       print ("An exception occurred:", err)

   #inputfile='RegressionTestSuite.xml'
   #outputfile='RegressionTestSuite2.csv'
   if not inputfile or not outputfile:
    print ('One of the input parameters is missing, please use: TestLink2TestRail.py -i <XML_inputfile> -o <CSV_outputfile>')
    sys.exit()

   parseTestlink2TestRailData(inputfile, outputfile)

if __name__ == "__main__":
   main(sys.argv[1:])
