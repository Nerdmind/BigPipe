<?php
# >>> [Additional code for the async function]

#===============================================================================
# FUNCTION: Return TRUE if the request awaiting a async response
#===============================================================================
function isAsyncRequest() {
	return isset($_GET['response']) AND $_GET['response'] === 'async';
}

# <<<

#===============================================================================
# Deactivate caching
#===============================================================================
header('Cache-Control: no-cache, no-store, must-revalidate');

#===============================================================================
# Include classes and functions
#===============================================================================
require_once 'include/classes/BigPipe/BigPipe.php';
require_once 'include/classes/BigPipe/Pagelet.php';
require_once 'include/classes/BigPipe/DemoPagelet.php';
require_once 'include/functions.php';

#===============================================================================
# Check if BigPipe should be disabled
#===============================================================================
if(isset($_GET['bigpipe']) AND (int) $_GET['bigpipe'] === 0) {
	// You can also check for search spiders and disable the pipeline
	BigPipe\BigPipe::enablePipeline(FALSE);
}

// Outsourced to avoid duplicate code in index.php and async.php
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
	<script>
		var globalExecution = function globalExecution(code) {
			window.execScript ? window.execScript(code) : window.eval.call(window, code);
		};
	</script>
	<script src="static/bigpipe.js"></script>
	<title>BigPipe Demo</title>
	<!-- >>> [Additional code for the async function] -->
	<script>
		var Application = {
			placeholderHTML: function(HTML) {
				document.getElementById('placeholder_container').innerHTML = HTML;
			}
		};

		function fireAsyncRequest(href) {
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
	$BUFFER = removeLineBreaksAndTabs(ob_get_clean());
	echo '<script>["Application","BigPipe"].forEach(function(name){window[name] = parent[name];});</script>'."\n";
	echo '<script>Application.placeholderHTML('.json_encode($BUFFER).');</script>'."\n\n";
}

BigPipe\BigPipe::render();
?>
</body>
</html>