<?php
#====================================================================================================
# Deactivate caching
#====================================================================================================
header('Cache-Control: no-cache, no-store, must-revalidate');

#====================================================================================================
# Include classes and functions
#====================================================================================================
require_once 'include/classes/BigPipe/BigPipe.php';
require_once 'include/classes/BigPipe/Pagelet.php';
require_once 'include/classes/BigPipe/DemoPagelet.php';
require_once 'include/functions.php';

#====================================================================================================
# Check if BigPipe should be disabled
#====================================================================================================
if(isset($_GET['bigpipe']) AND (int) $_GET['bigpipe'] === 0) {
	// You can also check for search spiders and disable the pipeline
	BigPipe\BigPipe::enablePipeline(FALSE);
}

#====================================================================================================
# Pagelet with red background color
#====================================================================================================
$PageletRed = new BigPipe\DemoPagelet();
$PageletRed->addHTML('<section id="red" class="text">I AM A PAGELET WITH RED BACKGROUND</section>');
$PageletRed->addCSS('static/red.php');
$PageletRed->addJS('static/delayJS.php');
$PageletRed->addJSCode("document.getElementById('red').innerHTML += ' [JS executed]';document.getElementById('red').style.borderRadius = '30px';");

#====================================================================================================
# Pagelet with blue background color
#====================================================================================================
$PageletBlue = new BigPipe\DemoPagelet('customPageletID', BigPipe\Pagelet::PRIORITY_HIGH);
$PageletBlue->addHTML('<section id="blue" class="text">I AM A PAGELET WITH BLUE BACKGROUND</section>');
$PageletBlue->addCSS('static/blue.php');
$PageletBlue->addJS('static/delayJS.php');
$PageletBlue->addJSCode("document.getElementById('blue').innerHTML += ' [JS executed]';document.getElementById('blue').style.borderRadius = '30px';");

#====================================================================================================
# Pagelet with green background color
#====================================================================================================
$PageletGreen = new BigPipe\DemoPagelet();
$PageletGreen->addHTML('<section id="green" class="text">I AM A PAGELET WITH GREEN BACKGROUND</section>');
$PageletGreen->addCSS('static/green.php');
$PageletGreen->addJS('static/delayJS.php');
$PageletGreen->addJSCode("document.getElementById('green').innerHTML += ' [JS executed]';document.getElementById('green').style.borderRadius = '30px';");
?>
<!DOCTYPE html>
<html lang="de">
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
</head>
<body>
<h1>BigPipe Demo</h1>
<p>You see on this page 3 pagelets are getting rendered. Each pagelet has his own CSS and JS resources. The CSS resources change the background color of the pagelet (so you can see the effect when the CSS is loaded). The next step is to load the JS resources and they change the border-radius of the pagelet. After the loading of CSS and JS resources the static JS callback get executed. Additionally, the PhaseDoneJS callbacks performed for each pagelet phase. See the javascript console for more debug informations.</p>
<p>PhaseDoneJS is a new feature of BigPipe which can execute JS callbacks for each pagelet phase. Each pagelet can have multiple PhaseDoneJS callbacks for each phase. The difference between a PhaseDoneJS callback and a static JS callback ("JS_CODE") is the following: The static JS callback always get executed (regardless of whether the pipeline is enabled or disabled) and can be a main part of the JS from the pagelet. But the PhaseDoneJS callbacks are only executed if the pipeline is enabled. They are suitable for application-specific stuff.</p>

<p><b>More information [at this time only in german; sorry]:</b><br /><a href="https://blackphantom.de/artikel/bigpipe-website-pipelining-und-schnellerer-aufbau-durch-einzelne-pagelets/" target="_blank">https://blackphantom.de/artikel/bigpipe-website-pipelining-und-schnellerer-aufbau-durch-einzelne-pagelets/</a></p>

<p><b>Check if output flushing works on your server:</b><br /><a href="output-flushing-test.php">output-flushing-test.php</a></p>

<?php
echo $PageletRed;
echo $PageletBlue;
echo $PageletGreen;
?>

<?php
BigPipe\BigPipe::render();
?>
</body>
</html>