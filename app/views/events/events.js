var ObservableArray = require("data/observable-array").ObservableArray;
var Observable = require("data/observable").Observable;
var page;
var frameModule = require("ui/frame");
var appSettings = require("application-settings");
var appSet = require("application-settings");
var LocalNotifications = require("nativescript-local-notifications");

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

    fetch(global.giveurl + "/wp-json/wp/v2/tribe_events?filter[posts_per_page]=15", {
        method: "GET",
        body: '',
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        }
    })
        .then(handleErrors)
        .then(function(data) {
            var posts = JSON.parse(data._bodyInit);
            var sortPosts = new Array();
            posts.sort(function(a, b) {
                var keyA = a.datesort,
                    keyB = b.datesort;
                if(keyA < keyB) return -1;
                if(keyA > keyB) return 1;
                return 0;
            });
            posts.forEach(function(post) {
                var regImage = "~/imgs/register.jpg";
                if ( appSet.getNumber( "event" + post['id'] ) == 1 ) {
                    regImage = "~/imgs/unregister.jpg";
                }
                console.log(post['startdate'].toString());
                var thed = post['startdate'].split(' ')
                var theda = thed[0].split('-');
                var thedat = theda[1] + '/' + theda[2] + '/' + theda[0];
                var obArray = new Observable();
                postTitles.push({ postName: post['title']['rendered'],
                    theDate: thedat,
                    thelink: post['link'],
                    fulldate: post['startdate'],
                    eventID: post['id'],
                    regImg: regImage,
                    regImg2: regImage,
                    visreg: 'visibile',
                    visreg2: 'collapsed'});
            });
        });
}

exports.goLink = function(args) {
    var item = args.view.bindingContext;
    var navigationOptions={
        moduleName:"views/event/event",
        context:{
            post_id: item.eventID
        }
    };
    frameModule.topmost().navigate(navigationOptions);
};

exports.goDonate = function() {
    if(appSettings.hasKey("logged") && appSettings.hasKey("firstName")) {
        console.log( appSettings.getString("firstName") );
        if(appSettings.getBoolean("logged")) {
            utilityModule.openUrl(global.giveurl + "/give-app-api/donate/" +
                appSet.getString("userID") + "/" + appSet.getString("giveID") + "/" + appSet.getString("userPin") + "/" + global.defaultform + '/v2' );
        }
    } else {
        var navigationOptions = {
            clearHistory:true,
            moduleName: "main-page"
        };
        frameModule.topmost().navigate(navigationOptions);
    }
};

exports.goHistory = function() {
    if(appSettings.hasKey("logged") && appSettings.hasKey("firstName")) {
        console.log( appSettings.getString("firstName") );
        if(appSettings.getBoolean("logged")) {
            var topmost = frameModule.topmost();
            topmost.navigate("views/history/history");
        }
    } else {
        var navigationOptions = {
            clearHistory:true,
            moduleName: "main-page"
        };
        frameModule.topmost().navigate(navigationOptions);
    }
};

exports.regIt = function(args) {
    var item = args.view.bindingContext;
    if ( item.regImg == "~/imgs/unregister.jpg" ) {
        item.regImg = "~/imgs/register.jpg";
        appSet.setNumber("event"+item.eventID, 0);
        LocalNotifications.cancel(item.eventID).then(
            function(foundAndCanceled) {
                if (foundAndCanceled) {
                    console.log("OK, it's gone!");
                } else {
                    console.log("No ID 5 was scheduled");
                }
            }
        )
    } else {
        item.regImg = "~/imgs/unregister.jpg";
        appSet.setNumber("event"+item.eventID, 1);
        console.log("full date: " + item.fulldate);
        var setdate = new Date(item.fulldate);
        setdate.setDate(setdate.getDate() - 1);

        LocalNotifications.schedule([{
            id: item.eventID,
            title: 'Youth For Christ Event Tomorrow',
            body: item.postName,
            at: setdate // 60 seconds * 1000 milliseconds * 60 minutes * 24 hours * 4 days
        }]).then(
            function() {
                console.log("Notification Scheduled");
            },
            function(error) {
                console.log("Error scheduling: " + error);
            }
        );

    }
    page.getViewById("mainList").refresh();
};

function handleErrors(response) {
    if (!response.ok) {
        console.log(JSON.stringify(response));
        throw Error(response.statusText);
    }
    return response;
}