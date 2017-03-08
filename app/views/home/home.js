var viewModule = require("ui/core/view");
var ObservableArray = require("data/observable-array").ObservableArray;
var Observable = require("data/observable").Observable;
var page;
var frameModule = require("ui/frame");
var gradients = require("~/grad.js");
var utilityModule = require("utils/utils");

exports.loaded = function(args) {
    page = args.object;
    loadLatest();
};

exports.goPaymentMethods = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("views/payment-methods/payment-methods");
};

exports.goDonate = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("views/donate/donate");
};

exports.goHistory = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("views/history/history");
};

exports.goEvent = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("views/events/events");
};

function loadLatest() {
    var postTitles = new ObservableArray([].map(function(postTitle) {
        return new Observable({
            postName: postTitle,
            labelID: labelID
        });
    }));
    page.bindingContext = {
        postList: postTitles
    };

    fetch(global.giveurl + "/wp-json/wp/v2/posts?filter[posts_per_page]=15", {
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
                console.log(post['title']['rendered']);
                postTitles.push({ postName: post['title']['rendered'], thelink: post['link'] });
            });
        });

}

exports.goLink = function(args) {
    var item = args.view.bindingContext;
    console.log(item.thelink);
    utilityModule.openUrl(item.thelink);
};

function handleErrors(response) {
    if (!response.ok) {
        console.log(JSON.stringify(response));
        throw Error(response.statusText);
    }
    return response;
}