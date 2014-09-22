<?php include Kohana::find_file('views', 'global/header'); ?>

<div id="jiWall">
	<h3>#<?php echo $wallname; ?></h3>
</div>

<div class="alert alert-block">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Please note!</h4>
  Due to Tiwtter API restrictions it may take up to 1 minute until tweets appear on newly created walls.
</div>

<div id="tweetcontainer"></div>
<script type="text/javascript">var wallname="<?php echo $wallname; ?>";</script>

<div id="tabStats">		
	<div class="tabContent">
		<dl class="dl-horizontal">
			<dt>Status:</dt>
			<dd id="jiServerStatus">disconnected</dd>
			<dt>Connected clients:<dt>
			<dd id="jiConnectedClients">-</dd>
			<dt>Active walls:<dt>
			<dd id="jiActiveWalls">-</dd>
			<dt>Tweet rate:<dt>
			<dd id="jiTweetRate">-</dd>
		</dl>
	</div>
	<div class="tabHeadline">Serverinfo</div>
</div>

<?php include Kohana::find_file('views', 'global/footer'); ?>