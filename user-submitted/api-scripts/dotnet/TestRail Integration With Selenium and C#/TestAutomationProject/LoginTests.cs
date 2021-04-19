using Microsoft.VisualStudio.TestTools.UnitTesting;
using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Support.UI;
using System;
using System.Configuration;
using System.Net;

namespace TestAutomationProject
{
    [TestClass]
    public class LoginTests
    {
        IWebDriver webDriver;        

        [TestInitialize]
        public void TestInit()
        {
            ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
            ChromeOptions chromeOptions = new ChromeOptions();
            chromeOptions.AddArgument("disable-infobars");
            webDriver = new ChromeDriver(chromeOptions);
            webDriver.Navigate().GoToUrl("https://xxxxxx.testrail.io/");    // change this
            webDriver.Manage().Window.Maximize();
            WebDriverWait waitForControl = new WebDriverWait(webDriver, TimeSpan.FromSeconds(10));
        }

        [TestCategory("BVT")]
        [TestMethod]           
        public void Login_With_Valid_User()
        {
            // Credentials of website
            string username = ConfigurationManager.AppSettings["username"];
            string password = ConfigurationManager.AppSettings["password"];

            // Find the username field and enter value
            var input = webDriver.FindElement(By.Id("name"));
            input.SendKeys(username);

            // Find the password field and enter value
            input = webDriver.FindElement(By.Id("password"));
            input.SendKeys(password);

            // Click on the login button
            var loginButtonElement = webDriver.FindElement(By.Id("button_primary"));
            loginButtonElement.Click();

            // Assert - Check whether Login is sucessful and navigated to Dashboard view.
            var dashboardElement = webDriver.FindElement(By.Id("navigation-dashboard"));
            Assert.AreEqual("DASHBOARD", dashboardElement.Text, "Login failed. Dashboard is not available");
        }

        [TestCategory("BVT")]
        [TestMethod]             
        public void Login_With_Invalid_User()
        {
            // Credentials of website
            string username = ConfigurationManager.AppSettings["username"];
            string password = "Test123";

            // Find the username field and enter value
            var input = webDriver.FindElement(By.Id("name"));
            input.SendKeys(username);

            // Find the password field and enter value
            input = webDriver.FindElement(By.Id("password"));
            input.SendKeys(password);

            // Click on the login button
            var loginButtonElement = webDriver.FindElement(By.Id("button_primary"));
            loginButtonElement.Click();

            // Assert - Check whether Login is sucessful and navigated to Dashboard view.
            var errorMessage = webDriver.FindElement(By.XPath("/html/body/div[1]/div[2]/div/div/div[2]"));
            Assert.AreEqual("Email/Login or Password is incorrect. Please try again.", errorMessage.Text, "Login failed message is not available");
        }

        [TestCategory("BVT")]
        [TestMethod]
        public void Demo_Failed_Test_Case()
        {
            // Credentials of website
            string username = ConfigurationManager.AppSettings["username"];
            string password = ConfigurationManager.AppSettings["password"];

            // Find the username field and enter value
            var input = webDriver.FindElement(By.Id("name"));
            input.SendKeys(username);

            // Find the password field and enter value
            input = webDriver.FindElement(By.Id("password"));
            input.SendKeys(password);

            // Click on the login button
            var loginButtonElement = webDriver.FindElement(By.Id("button_primary"));
            loginButtonElement.Click();

            // Assert - Check whether Login is sucessful and navigated to Dashboard view.
            var dashboardElement = webDriver.FindElement(By.Id("navigation-dashboard"));
            Assert.AreNotEqual("DASHBOARD", dashboardElement.Text, "Login failed. Dashboard is not available");
        }

        [TestCleanup]
        public void TestCleanup()
        {
            webDriver.Dispose();       
        }
    }
}
