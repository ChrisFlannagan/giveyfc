var page;
var appSettings = require("application-settings");
var observable = require("data/observable");
var observableArray = require("data/observable-array").ObservableArray;
var pageData = new observable.Observable();
var postTitles;
var numCC;

exports.loaded = function(args) {
    postTitles = new observableArray([].map(function(postTitle) {
        return new Observable({
            postName: postTitle
        });
    }));

    page = args.object;
    pageData.set("showCard", false);
    pageData.set("postList", postTitles);
    page.bindingContext = pageData;

    listCards();
};

exports.addCard = function() {
    pageData.set("showCard", !pageData.get("showCard"));
};

exports.saveCard = function() {
    numCC++;
    appSettings.setNumber("numCC", numCC);
    appSettings.setNumber("ccNum" + numCC, Number(pageData.get("ccNum")));
    listCards();
}

exports.removeCard = function(args) {
    var item = args.object.bindingContext;
    var index = postTitles.indexOf(item);
    var relayNums = new Array();
    postTitles.splice(index, 1);

    for (var i = 1; i < numCC+1; i++) {
        if(i-1 != index) {
            relayNums.push(appSettings.getNumber("ccNum" + i));
        }
        appSettings.remove("ccNum" + i);
    }

    numCC--;
    appSettings.setNumber("numCC", numCC);
    relayNums.forEach(function(item, i) {
        appSettings.setNumber("ccNum" + (i+1), Number(item));
    });
};

exports.clearAll = function() {
    if (appSettings.hasKey("numCC")) {
        numCC = appSettings.getNumber("numCC");
        for (var i = 1; i < numCC + 1; i++) {
            if(appSettings.hasKey("ccNum" + i)) {
                appSettings.remove("ccNum" + i);
            }
        }
        numCC = 0;
        appSettings.remove("numCC");
    }
}

function listCards() {
    numCC = 0;
    if (appSettings.hasKey("numCC")) {
        numCC = appSettings.getNumber("numCC");
        postTitles.length = 0;

        for (var i = 1; i < numCC+1; i++) {
            if(appSettings.hasKey("ccNum" + i)) {
                var curnum = appSettings.getNumber("ccNum" + i).toString();
                postTitles.push({postName: "****-" + curnum.substring(curnum.length - 4, curnum.length)});
            } else {
                console.log("No card: " + i);
            }
        }
    }
}