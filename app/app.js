var application = require("application");
// MUST CONTAIN HTTPS
global.giveurl = 'http://donate.dev';
global.hash = '5kdj8ls';
global.phone = '(334) 555-1111';
global.defaultform = '1786';
// MUST CONTAIN HTTPS
application.start({ moduleName: "main-page" });