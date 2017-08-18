var ObservableArray = require("data/observable-array").ObservableArray;
var Observable = require("data/observable").Observable;
var page;
var frameModule = require("ui/frame");
var appSettings = require("application-settings");
var Directions = require("nativescript-directions").Directions;
var LocalNotifications = require("nativescript-local-notifications");

var utilityModule = require("utils/utils");

var pageData;
var postId;
var startdate;
var eventTitle;

var mapAddress;

exports.loaded = function(args) {
    page = args.object;
    var gotData = page.navigationContext;
    postId = gotData.post_id;

    var remcolor = 'Orange';
    var reminder = 'Set A Reminder';
    if ( appSettings.getNumber( "event" + postId ) == 1 ) {
        remcolor = "Green";
        reminder = 'Cancel Reminder';
    }

    pageData = new Observable({
        post_title: 'loading title',
        venue: 'loading venue',
        address: 'loading address',
        address2: '',
        startdate: 'loading event date',
        eventimage: '',
        remcolor: remcolor,
        reminder: reminder
    });
    page.bindingContext = pageData;

    loadArticle(gotData.post_id);
};

function loadArticle( post_id ) {

    fetch(global.giveurl + "/wp-json/wp/v2/tribe_events/" + post_id, {
        method: "GET",
        body: '',
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        }
    })
        .then(handleErrors)
        .then(function(data) {
            var post = JSON.parse(data._bodyInit);

            var venue = post['event_details']['venue'];
            var address = post['event_details']['address'];
            var address2 = post['event_details']['address2'];
            var image = post['event_details']['image'];
            var start = post['event_details']['start'];
            var end = post['event_details']['end'];
            startdate = post['startdate'];
            eventTitle = post['title']['rendered'];

            if ( start != '' ) {
                start = start.split(' ')[0];

                if ( end != '' ) {
                    start += ' - ' + end.split(' ')[0];
                }
            }

            if ( address != '' ) {
                mapAddress = address + ' ' + address2;
            }

            pageData.set( 'post_title', post['title']['rendered'] );
            pageData.set( 'venue', venue );
            pageData.set( 'address', address );
            pageData.set( 'address2', address2 );
            pageData.set( 'startdate', start );
            pageData.set( 'enddate', end );
            pageData.set( 'eventimage', image );
        });
};

exports.openMap = function() {
    var directions = new Directions();
    directions.navigate({
        to: { // either pass in a single object or an Array (see the TypeScript example below)
            address: mapAddress
        }
        // for iOS-specific options, see the TypeScript example below.
    }).then(
        function() {
            console.log("Maps app launched.");
        },
        function(error) {
            console.log(error);
        }
    );
};

exports.reminder = function() {
    if ( appSettings.getNumber( "event" + postId ) == 1 ) {
        pageData.set( 'remcolor', 'Orange' );
        pageData.set( 'reminder', 'Set A Reminder' );
        appSettings.setNumber("event"+postId, 0);
        LocalNotifications.cancel(postId).then(
            function(foundAndCanceled) {
                if (foundAndCanceled) {
                    console.log("OK, it's gone!");
                } else {
                    console.log("No ID 5 was scheduled");
                }
            }
        )
    } else {
        pageData.set( 'remcolor', 'Green' );
        pageData.set( 'reminder', 'Cancel Reminder' );
        appSettings.setNumber("event"+postId, 1);
        var setdate = new Date(startdate);
        setdate.setDate(setdate.getDate() - 1);

        LocalNotifications.schedule([{
            id: postId,
            title: 'Youth For Christ Event Tomorrow',
            body: eventTitle,
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