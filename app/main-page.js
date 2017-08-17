var page;
var appSettings = require("application-settings");
var observable = require("data/observable");
var frameModule = require("ui/frame");
var pageData = new observable.Observable();
var gestures = require("ui/gestures");
var dialogs = require("ui/dialogs");
var md5 = require('js-md5');

var firstName = '';
var lastName = '';
var userEmail = '';
var userPin = '';

/** Core App Code for Main Page **/

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
    pageData.set("phoneNum", global.phone);



    /** Push Notifications setup **/

    var pushPlugin = require("nativescript-push-notifications");
    var self = pageData;
    var iosSettings = {
        badge: true,
        sound: true,
        alert: true,
        interactiveSettings: {
            actions: [{
                identifier: 'READ_IDENTIFIER',
                title: 'Read',
                activationMode: "foreground",
                destructive: false,
                authenticationRequired: true
            }, {
                identifier: 'CANCEL_IDENTIFIER',
                title: 'Cancel',
                activationMode: "foreground",
                destructive: true,
                authenticationRequired: true
            }],
            categories: [{
                identifier: 'READ_CATEGORY',
                actionsForDefaultContext: ['READ_IDENTIFIER', 'CANCEL_IDENTIFIER'],
                actionsForMinimalContext: ['READ_IDENTIFIER', 'CANCEL_IDENTIFIER']
            }]
        },
        notificationCallbackIOS: function (data) {
            self.set("message", "" + JSON.stringify(data));
        }
    };

    pushPlugin.register(iosSettings, function (data) {
        self.set("message", "" + JSON.stringify(data));

        // Register the interactive settings
        if(iosSettings.interactiveSettings) {
            pushPlugin.registerUserNotificationSettings(function(success) {
                console.dump(success);
                alert('Successfully registered for interactive push.');
            }, function(err) {
                alert('Error registering for interactive push: ' + JSON.stringify(err));
            });
        }
    }, function() { });

    page.bindingContext = pageData;
};

exports.closeKeybd = function() {
    page.getViewById("user-name").dismissSoftInput();
    page.getViewById("user-lname").dismissSoftInput();
    page.getViewById("user-email").dismissSoftInput();
    page.getViewById("user-pin").dismissSoftInput();
};

exports.saveUser = function() {
    if(page.getViewById("signInBtn").text != "Please wait ... ") {
        page.getViewById("signInBtn").text = "Please wait ... ";
        if (pageData.get("firstName") != '' && pageData.get("lastName") != "" && pageData.get("userEmail") != "" && pageData.userPin.length == 4) {
            var _url = global.giveurl + "/give-app-api/register/" + pageData.get("firstName").replace(" ", "%20") + "%20" + pageData.get("lastName").replace(" ", "%20") + "/" + pageData.get("userEmail") + "/" + pageData.userPin + "/v2";
            console.log("Sending: " + _url);

            fetch(_url)
            .then(response => { return response.json(); })
            .then(function (data) {
                console.log(data);
                if (data.success == '1') {

                    appSettings.setString("firstName", pageData.get("firstName"));
                    appSettings.setString("lastName", pageData.get("lastName"));
                    appSettings.setString("userEmail", pageData.get("userEmail"));
                    appSettings.setString("userPin", pageData.get("userPin"));
                    appSettings.setString("userPass", pageData.get("userPin")); // backwards compat

                    appSettings.setString("giveID", data.giveid);
                    appSettings.setString("userID", data.userid);
                    appSettings.setBoolean("logged", true);

                    home();

                } else {
                    dialogs.alert(data.msg).then(function () {
                        console.log("Dialog closed!");
                    });
                }
                page.getViewById("signInBtn").text = "Sign In";
            });
        } else {
            dialogs.alert("All fields are required");
            page.getViewById("signInBtn").text = "Sign In";
        }
    }
};

exports.resetPin = function() {
    if(page.getViewById("emailNewPin").text != "Please wait...") {
        page.getViewById("emailNewPin").text = "Please wait...";
        if(pageData.get("userEmail") != "") {
            var _url = global.giveurl + "/give-app-resetpin/" + pageData.get("userEmail");
            console.log("Sending: " + _url);
            fetch(_url)
            .then(response => { return response.json(); })
            .then(function (data) {
                console.log(data);
                if (data.success == '1') {
                    dialogs.alert("Check your email for new Pin Number");
                    page.getViewById("emailNewPin").text = "Reset &amp; Email New Pin";
                } else {
                    dialogs.alert(data.msg).then(function () {
                        console.log("Dialog closed!");
                    });
                }
                page.getViewById("signInBtn").text = "Sign In";
            });
        } else {
            dialogs.alert("Please enter an email address");
            page.getViewById("emailNewPin").text = "Reset &amp; Email New Pin";
        }
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