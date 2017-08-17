var ObservableArray = require("data/observable-array").ObservableArray;
var Observable = require("data/observable").Observable;
var page;
var frameModule = require("ui/frame");
var appSettings = require("application-settings");

var pageData;
var postId;

exports.loaded = function(args) {
    page = args.object;
    var gotData = page.navigationContext;

    pageData = new Observable({
        post_title: 'loading title',
        post_content: 'loading content'
    });
    page.bindingContext = pageData;

    postId = gotData.post_id;

    loadArticle(gotData.post_id);
};

function loadArticle( post_id ) {

    fetch(global.giveurl + "/wp-json/wp/v2/posts/" + post_id, {
        method: "GET",
        body: '',
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        }
    })
        .then(handleErrors)
        .then(function(data) {
            var post = JSON.parse(data._bodyInit);
            console.log(post['title']['rendered']);
            var display = post['content']['rendered'];
            if ( display.indexOf('<a href="http://www.giveyfc.com/give/">' ) > 0 ) {
                var content = post['content']['rendered'].split('<a href="http://www.giveyfc.com/give/">');
                display = content[0];
            }
            pageData.set( 'post_title', post['title']['rendered'] );
            pageData.set( 'post_content', display );
        });
};

exports.markRead = function() {
    appSettings.setBoolean("read" + postId, true);
    var topmost = frameModule.topmost();
    topmost.navigate("home-new");
};

exports.goBack = function() {
    var topmost = frameModule.topmost();
    topmost.navigate("home-new");
};


function handleErrors(response) {
    if (!response.ok) {
        console.log(JSON.stringify(response));
        throw Error(response.statusText);
    }
    return response;
};