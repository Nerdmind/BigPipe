<?php
# >>> [Additional code for the async function]

#===============================================================================
# FUNCTION: Return TRUE if the request awaiting a async response
#===============================================================================
function isAsyncRequest() {
	return isset($_GET['response']) AND $_GET['response'] === 'async';
}

# <<<

require_once 'include/pagelets.php';
?>
<!DOCTYPE html>
<html lang="de">
<?php if(!isAsyncRequest()): ?>
<head>
	<meta charset="UTF-8" />
	<meta name="robots" content="noindex, nofollow" />
	<style>
		html{margin:0;padding:0;background:#B9C3D2;font-family:Calibri,Sans-Serif;}
		body{max-width:1200px;margin:0 auto;}
		.text{color:white;margin-bottom:30px;padding:40px;border-radius:4px;font-weight:600;text-align:center;border:4px solid black;}
		.hidden{display:none;}
	</style>
	<script src="static/bigpipe.js"></script>
	<title>BigPipe Demo</title>
	<!-- >>> [Additional code for the async function] -->
	<script>
		var Application = {
			bigPipeEnabled: <?=json_encode(BigPipe\BigPipe::isEnabled())?>,

			placeholderHTML: function(HTML) {
				document.getElementById('placeholder_container').innerHTML = HTML;
			}
		};

		function fireAsyncRequest(href) {
			if(Application.bigPipeEnabled === false) {
				alert("Note: Pipelining is disabled and page will be loaded quite normal.");
				return;
			}

			console.info('ASYNC REQUEST FIRED!');

			Application.placeholderHTML("");
			BigPipe.reset();
			var transport_frame;

			if(transport_frame = document.getElementById('transport_frame')) {
				document.body.removeChild(transport_frame);
			}

			var iframe = document.createElement('iframe');
			iframe.setAttribute('id', 'transport_frame');
			iframe.setAttribute('class', 'hidden');
			iframe.setAttribute('src', href + '?response=async');

			document.body.appendChild(iframe);

			iframe.onload = function() {
				document.body.removeChild(iframe);
			}.bind(this);

			return false;
		}
	</script>
	<!-- <<< -->
</head>
<?php endif; ?>
<body>
<?php if(!isAsyncRequest()): ?>
<h1>BigPipe Async Demo</h1>

<p><a href="async.php" onclick="return fireAsyncRequest(this)">LOAD CONTENT VIA TRANSPORT FRAME</a> [Current Time: <?=time();?> – So you can see, that the page does not get completely reloaded]</p>
<p><em>Look at the developer console of your browser to see the debug messages and how the async response from server looks.</em></p>

<section id="placeholder_container">
	<?php else: ob_start(); endif; ?>
	<?php
	echo $PageletRed;
	echo $PageletBlue;
	echo $PageletGreen;
	?>
	<?php if(!isAsyncRequest()):?>
</section>
<footer><strong>The footer of the page.</strong></footer>

<?php endif;
if(isAsyncRequest()) {
	$BUFFER = ob_get_clean();
	echo '<script>["Application","BigPipe"].forEach(function(name){window[name] = parent[name];});</script>'."\n";
	echo '<script>Application.placeholderHTML('.json_encode($BUFFER).');</script>'."\n\n";
}

BigPipe\BigPipe::completeResponse();
?>
</body>
</html>
