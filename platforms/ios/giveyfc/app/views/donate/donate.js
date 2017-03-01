var utilityModule = require("utils/utils");
// var webViewInterfaceModule = require('nativescript-webview-interface');
var oWebViewInterface;
var appSettings = require("application-settings");
var md5 = require('js-md5');

exports.loaded = function(args){
    page = args.object;
    page.bindingContext = { };
    setupWebViewInterface(page)
};

// Initializes plugin with a webView
function setupWebViewInterface(page){
    utilityModule.openUrl(global.giveurl + "/give-app-api/donate/" +
        appSettings.getString("userID") + "/" + appSettings.getString("giveID") + "/" + appSettings.getString("userPin"));
    /*
    console.log(appSettings.getString("giveID"));
    var webView = page.getViewById('webView');
    console.log(global.giveurl + "/give-app-api/donate/" + appSettings.getString("userID") + "/" + appSettings.getString("giveID"));
    oWebViewInterface = new webViewInterfaceModule.WebViewInterface(webView, global.giveurl + "/give-app-api/donate/" +
        appSettings.getString("userID") + "/" + appSettings.getString("giveID") + "/" + appSettings.getString("userPin"));
        */
};