var page;
var appSettings = require("application-settings");
var observable = require("data/observable");
var frameModule = require("ui/frame");
var pageData = new observable.Observable();
var gestures = require("ui/gestures");

var md5 = require('js-md5');

var firstName = '';
var lastName = '';
var userEmail = '';
var userPin = '';

exports.loaded = function(args) {
    if(appSettings.hasKey("logged") && appSettings.hasKey("firstName")) {
        console.log( appSettings.getString("firstName") );
        if(appSettings.getBoolean("logged")) {
            home();
        }
    }

    firstName = '';
    lastName = '';
    userEmail = '';
    userPin = '';
    if(appSettings.hasKey("firstName")) {
        firstName = appSettings.getString("firstName");
    }
    if(appSettings.hasKey("lastName")) {
        lastName = appSettings.getString("lastName");
    }
    if(appSettings.hasKey("userEmail")) {
        userEmail = appSettings.getString("userEmail");
    }
    if(appSettings.hasKey("userPin")) {
        userPin = appSettings.getString("userPin");
    }

    page = args.object;
    pageData.set("firstName", firstName);
    pageData.set("lastName", lastName);
    pageData.set("userEmail", userEmail);
    pageData.set("userPin", userPin);
    page.bindingContext = pageData;
};

exports.closeKeybd = function() {
    page.getViewById("user-name").dismissSoftInput();
    page.getViewById("user-lname").dismissSoftInput();
    page.getViewById("user-email").dismissSoftInput();
    page.getViewById("user-pin").dismissSoftInput();
};

exports.saveUser = function() {
    if(pageData.get("firstName") != '' && pageData.get("lastName") != "" && pageData.get("userEmail") != "" && pageData.userPin.length == 4) {
    console.log("Sending: " + global.giveurl + "/give-app-api/register/" + pageData.get("firstName").replace(" ", "%20") + "%20" + pageData.get("lastName").replace(" ", "%20") + "/" + pageData.get("userEmail") + "/" + md5(pageData.userPin+global.hash) );
        fetch(global.giveurl + "/give-app-api/register/" + pageData.get("firstName").replace(" ", "%20") + "%20" + pageData.get("lastName").replace(" ", "%20") + "/" + pageData.get("userEmail") + "/" + pageData.get("userPin") , {
            method: "GET",
            body: '',
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
            }
        })
            .then(handleErrors)
            .then(function(data) {
                console.log(data);
                console.dump(data);
                if(data._bodyInit != "success0") {
                    console.log("New ID: " + data._bodyInit.replace("success", ""));
                    appSettings.setString("firstName", pageData.get("firstName"));
                    appSettings.setString("lastName", pageData.get("lastName"));
                    appSettings.setString("userEmail", pageData.get("userEmail"));
                    appSettings.setString("userPin", pageData.get("userPin"));
                    var getIDs = data._bodyInit.replace("success", "").split("|SP|");
                    appSettings.setString("giveID", getIDs[0]);
                    appSettings.setString("userID", getIDs[1]);
                    appSettings.setString("userPass", getIDs[2]);
                    appSettings.setBoolean("logged", true);
                    home();
                } else {
                    console.log(data)
                }
            });
    }
};

function home() {
    var navigationOptions={
        moduleName:"views/home/home",
        clearHistory:true,
        context:{
            logged: true
        }
    };
    frameModule.topmost().navigate(navigationOptions);
}

function handleErrors(response) {
    if (!response.ok) {
        console.log(JSON.stringify(response));
        throw Error(response.statusText);
    }
    return response;
}