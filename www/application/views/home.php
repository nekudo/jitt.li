<?php include Kohana::find_file('views', 'global/header'); ?>

<div id="jiCreateWall" class="text-center">
	<h2>Create a new wall</h2>
	<form action="/" method="post">
		<div class="input-prepend input-append">
			<span class="add-on">#</span>
			<input class="span3" name="wallname" type="text" placeholder="enter hashtag...">
			<button type="submit" class="btn">Go!</button>
		</div>
	</form>
</div>

<div id="jiAbout">
	<h3>What is jitt.li?</h3>
	<p>
		jitt.li is an websocket-powered realtime twitterwall. You will see all new tweets with the hashtag you selected instantly on the wall. There is no polling and therefore no delay!
	</p>
</div>

<div id="jiStats">
	<h3>Statistics</h3>
	<h5>Tweets delivered per hour (last 24h)</h5>
	<?php if(!empty($statsData['tweets'])): ?>
		<canvas id="jiTweetStats" width="650" height="250"></canvas>
		<script>
			var jiStatsTweets = {
				labels : [<?php echo $statsData['tweets']['labels']; ?>],
				datasets : [
					{
						fillColor : "rgba(151,187,205,0.5)",
						strokeColor : "rgba(151,187,205,1)",
						pointColor : "rgba(151,187,205,1)",
						pointStrokeColor : "#fff",
						data : [<?php echo $statsData['tweets']['values']; ?>]
					}
				]
			}
		</script>
	<?php endif; ?>
</div>
<?php include Kohana::find_file('views', 'global/footer'); ?>