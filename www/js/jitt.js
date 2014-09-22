$(document).ready(function() {
	
	$.log = function(msg) {
		return $('#tweetcontainer').prepend("" + msg + "<br />");
    }
	
	$.newTweet = function(data) {
		var tweet = document.createElement("div");
		$(tweet).addClass("tweet");		
		$(tweet).html('<div class="tweet row"><div class="wrapperLeft span1"><div class="tweet_userpic"><img src="' + data.user_pic + '"></div></div><div class="wrapperRight span11"><div class="tweet_text">' + data.text + '</div><div class="tweet_meta"><span class="tweet_date">' + data.date + '</span> by <span class="tweet_nick"><a href="https://twitter.com/@' + data.user_nick + '" target="_blank">@' + data.user_nick + '</a></span> <span class="tweet_name">(' + data.user_name + ')</span></div></div><div class="clear"></div></div>');
		$(tweet).prependTo("#tweetcontainer").hide().fadeIn();
		while ($('.tweet').length > 20) {
			$('#tweetcontainer div:last').remove();
		}
	};
	
	$.updateServerinfo = function(data) {		
		$('#jiConnectedClients').text(data.connectedClients);
		$('#jiActiveWalls').text(data.activeWalls);
		$('#jiTweetRate').text(data.tweetRate + '/s');
		
	};

	$.initWsConn = function(wallname) {
		var conn = new ab.Session('ws://jitt.li:8080',
			function() {				
				$('#jiServerStatus').text('connected');
				conn.subscribe('wall_' + wallname, function(topic, data) {
					$.newTweet(data.tweet);
				});
				conn.subscribe('serverEvents', function(topic, data) {
					switch(data.action) {
						case 'updateServerinfo':
							$.updateServerinfo(data.actionData);
						break;
					}
				});
			},
			function(reason) {
				switch (reason) {
					case ab.CONNECTION_CLOSED:
						$.log("Connection was closed properly - done.");
						$('#jiServerStatus').text('disconnected');
					break;
					case ab.CONNECTION_UNREACHABLE:
						$.log("Connection could not be established.");
						$('#jiServerStatus').text('disconnected');
					break;
					case ab.CONNECTION_UNSUPPORTED:
						$.log("Browser does not support WebSocket.");
						$('#jiServerStatus').text('disconnected');
					break;
					case ab.CONNECTION_LOST:
						$.log("Connection lost.");
						$('#jiServerStatus').text('disconnected');
					break;
				}

			}, {				
				'skipSubprotocolCheck': true
			}
		);
	}
	
	$.fn.tabToggle = function(options) {
		var tabOffset = options.tabOffset;
		var elementHeight = this.height();
		this.toggleClass('jiOut');
		this.css('top', (elementHeight - tabOffset) * -1);
		this.click(function(){
			if($(this).hasClass('jiOut')) {
				$(this).animate({top : "0px"});
			} else {
				$(this).animate({top : (elementHeight - tabOffset) * -1 + "px"});
			}
			$(this).toggleClass('jiOut');
		});
	}
	
	if(typeof wallname != 'undefined') {		
		$.initWsConn(wallname);
		$('#tabStats').tabToggle({'tabOffset' : 30});
	}
	
	// stats:
	if(typeof jiStatsTweets != 'undefined') {
		var ctx = $("#jiTweetStats").get(0).getContext("2d");
		var options = {
			scaleGridLineColor : "rgba(0,0,0,.08)"
		}
		new Chart(ctx).Line(jiStatsTweets, options);
	}
});