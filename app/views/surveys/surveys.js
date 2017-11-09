var observable = require("data/observable");
var viewModule = require("ui/core/view");
var sliderModule = require("ui/slider");
var appSettings = require("application-settings");
var pageData = new observable.Observable();
var page;
var slider1;
var slider2;
var slider3;
var dialogs = require("ui/dialogs");


exports.loaded = function(args) {
    page = args.object;
    page.bindingContext = pageData;

    slider1 = page.getViewById("qs1");
    if (appSettings.hasKey("slider1")) {
        page.getViewById("saveBtn").text = "Save New Answers";
        slider1.value = parseInt(appSettings.getString("slider1"));
    } else {
        slider1.value = 50;
    }
    slider2 = page.getViewById("qs2");
    if (appSettings.hasKey("slider2")) {
        slider2.value = parseInt(appSettings.getString("slider2"));
    } else {
        slider2.value = 50;
    }
    slider3 = page.getViewById("qs3");
    if (appSettings.hasKey("slider3")) {
        slider3.value = parseInt(appSettings.getString("slider3"));
    } else {
        slider3.value = 50;
    }

    slider1.on('propertyChange', function (args) {
        updateDisplay(args, '1');
    });

    slider2.on('propertyChange', function (args) {
        updateDisplay(args, '2');
    });

    slider3.on('propertyChange', function (args) {
        updateDisplay(args, '3');
    });

    var timer = setTimeout(function() {updateDisplay();},200);
};

exports.saveAnswers = function() {
    if(page.getViewById("saveBtn").text != "Please wait...") {
        page.getViewById("saveBtn").text = "Please wait...";
        var user = 'random';
        if(appSettings.hasKey("logged") && appSettings.hasKey("userEmail")) {
            user = appSettings.getString("userEmail");
        }

        appSettings.setString("slider1", slider1.value.toString());
        appSettings.setString("slider2", slider2.value.toString());
        appSettings.setString("slider3", slider3.value.toString());

        var _url = global.giveurl + "/give-app-savesurvey/" + user + "/" + slider1.value + "/" + slider2.value + "/" + slider3.value;
            fetch(_url)
                .then(response => { return response.json(); })
                .then(function (data) {
                if (data.success == '1') {
                    dialogs.alert("Results saved!");
                    page.getViewById("saveBtn").text = "Save New Answers";
                } else {
                    dialogs.alert(data.msg).then(function () {
                        console.log("Dialog closed!");
                    });
                    page.getViewById("saveBtn").text = "Save New Answers";
                }
            });
    }
};

function updateDisplay() {
    for(var n=1; n < 4; n++) {
        var val = parseInt(page.getViewById("qs" + n).value);
        if (val > 70) {
            page.getViewById("a" + n + "3").color = "Blue";
            page.getViewById("a" + n + "1").color = "Black";
            page.getViewById("a" + n + "2").color = "Black";
            page.getViewById("a" + n + "1").fontSize = 10;
            page.getViewById("a" + n + "2").fontSize = 10;
            page.getViewById("a" + n + "3").fontSize = 15;
        }
        if (val > 30 && val < 69) {
            page.getViewById("a" + n + "2").color = "Blue";
            page.getViewById("a" + n + "1").color = "Black";
            page.getViewById("a" + n + "3").color = "Black";
            page.getViewById("a" + n + "1").fontSize = 10;
            page.getViewById("a" + n + "2").fontSize = 15;
            page.getViewById("a" + n + "3").fontSize = 10;
        }
        if (val < 29) {
            page.getViewById("a" + n + "1").color = "Blue";
            page.getViewById("a" + n + "2").color = "Black";
            page.getViewById("a" + n + "3").color = "Black";
            page.getViewById("a" + n + "1").fontSize = 15;
            page.getViewById("a" + n + "2").fontSize = 10;
            page.getViewById("a" + n + "3").fontSize = 10;
        }
    }
    var timer = setTimeout(function() {updateDisplay();},200);
}