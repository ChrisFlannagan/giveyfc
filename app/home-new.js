var viewModule = require("ui/core/view");
var ObservableArray = require("data/observable-array").ObservableArray;
var Observable = require("data/observable").Observable;
var page;
var frameModule = require("ui/frame");
var gradients = require("~/grad.js");
var utilityModule = require("utils/utils");
var appSettings = require("application-settings");

exports.loaded = function(args) {
    page = args.object;

    if(appSettings.hasKey("logged") && appSettings.hasKey("firstName")) {
        console.log( appSettings.getString("firstName") );
        if(appSettings.getBoolean("logged")) {
            home();
        }
    }

    loadLatest();
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


exports.goPaymentMethods = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("views/payment-methods/payment-methods");
};

exports.goDonate = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("main-page");
};

function loadLatest() {
    var postTitles = new ObservableArray([].map(function(postTitle) {
        return new Observable({
            postName: postTitle,
            labelID: labelID,
            hasRead: hasRead,
            viewArticle: viewArticle
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
                var t = post['title']['rendered'].replace("&#8217;", "'").replace("&#8221;", "\"").replace("&#8311;", "-").replace("&#038;", "&");
                var hr = '#FFF';
                var va = 'View Article';
                if ( appSettings.hasKey("read" + post['id']) ) {
                    hr = '#ccc';
                    va = 'View Article (Read)';
                }
                postTitles.push({ postName: t, post_id: post['id'], hasRead: hr, viewArticle: va });
            });
        });

}

exports.goEvent = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("views/events/events");
};

exports.goLink = function(args) {
    var item = args.view.bindingContext;
    var navigationOptions={
        moduleName:"views/article/article",
        context:{
            post_id: item.post_id,
            came_from: 'homenew'
        }
    };
    frameModule.topmost().navigate(navigationOptions);
};

exports.goRegister = function() {
    var navigationOptions={
        moduleName:"main-page"
    };
    frameModule.topmost().navigate(navigationOptions);
};

function handleErrors(response) {
    if (!response.ok) {
        console.log(JSON.stringify(response));
        throw Error(response.statusText);
    }
    return response;
}