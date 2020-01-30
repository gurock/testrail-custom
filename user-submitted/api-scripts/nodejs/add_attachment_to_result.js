// Adds an attachment to a test result
// The test result must exist first
// Author: Gurock Software

var request = require('request');
var fs = require('fs');

//user and pwd need to be defined first
var auth = Buffer.from(user + ":" + pwd).toString('base64');

const options = {
    method: "POST",
    //{your server} and {result_id} should be replaced with actual values or variables
    url: "https://{your server}/index.php?/api/v2/add_attachment_to_result/{result_id}",
    port: 443,
    headers: {
        "Authorization": "Basic " + auth,
        "Content-Type": "multipart/form-data"
    },
    formData : {
        "attachment" : fs.createReadStream('./image.jpg')
    }
};

request(options, function (err, res, body) {
    if(err) console.log(err);
    console.log(body);
});
