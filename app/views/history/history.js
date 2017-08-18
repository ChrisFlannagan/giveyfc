var ObservableArray = require("data/observable-array").ObservableArray;
var Observable = require("data/observable").Observable;
var page;
var appSettings = require("application-settings");
var frameModule = require("ui/frame");
var utilityModule = require("utils/utils");
var postTitles;

exports.loaded = function(args) {
    page = args.object;
    loadLatest();
};

function loadLatest() {
    postTitles = new ObservableArray([].map(function(postTitle) {
        return new Observable({
            postName: postTitle
        });
    }));
    page.bindingContext = {
        postList: postTitles
    };

    console.log("Fetching history");
    fetch(global.giveurl + "/give-app-history/" + appSettings.getString("userID") + "/" + appSettings.getString("userPin"), {
        method: "GET",
        body: '',
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        }
    })
        .then(handleErrors)
        .then(function(data) {
            var posts = JSON.parse(data._bodyInit);
            posts.forEach(function(post) {
                console.log(post['id']);
                postTitles.push({ postName: "#" + post['id'] + ": " + post['amt'],
                    theDate: post['data'] });
            });

            if(posts.length == 0) {
                postTitles.push({postName: "You have not made any donations" });
            }
        });
}
exports.goDonate = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("views/donate/donate");
};

function handleErrors(response) {
    if (!response.ok) {
        console.log(JSON.stringify(response));
        throw Error(response.statusText);
    }
    return response;
}