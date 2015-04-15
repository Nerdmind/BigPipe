<?php
#====================================================================================================
# Cache deaktivieren
#====================================================================================================
header('Cache-Control: no-cache, no-store, must-revalidate');

#====================================================================================================
# Klassen und Funktionen einbinden
#====================================================================================================
require_once 'include/classes/class.bigpipe.php';
require_once 'include/classes/class.pagelet.php';
require_once 'include/functions.php';

#====================================================================================================
# Pagelet mit rotem Hintergrund
#====================================================================================================
$PageletRed = new Pagelet();
$PageletRed->addHTML('<section id="red" class="text">I AM JUST A PAGELET WITH RED BACKGROUND</section>');
$PageletRed->addCSS('static/red.php');
$PageletRed->addJS('static/delayJS.php');
$PageletRed->addJSCode("document.getElementById('red').innerHTML += ' [JS executed]';document.getElementById('red').style.borderRadius = '30px';");

#====================================================================================================
# Pagelet mit blauem Hintergrund
#====================================================================================================
$PageletBlue = new Pagelet(60);
$PageletBlue->addHTML('<section id="blue" class="text">I AM JUST A PAGELET WITH BLUE BACKGROUND</section>');
$PageletBlue->addCSS('static/blue.php');
$PageletBlue->addJS('static/delayJS.php');
$PageletBlue->addJSCode("document.getElementById('blue').innerHTML += ' [JS executed]';document.getElementById('blue').style.borderRadius = '30px';");

#====================================================================================================
# Pagelet mit grünem Hintergrund
#====================================================================================================
$PageletGreen = new Pagelet();
$PageletGreen->addHTML('<section id="green" class="text">I AM JUST A PAGELET WITH GREEN BACKGROUND</section>');
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
	</style>
	<script>
		var globalExecution = function globalExecution(code) {
			window.execScript ? window.execScript(code) : window.eval.call(window, code);
		};
	</script>
	<script src="static/bigpipe.js"></script>
	<script src="static/bigpipe-callbacks.js"></script>
	<title>BigPipe Example</title>
</head>
<body>
<h1>BigPipe Example</h1>
<p>Auf dieser Beispielseite werden insgesamt 3 Pagelets gerendert von denen alle jeweils eine CSS- und eine JS-Ressource haben. Wobei jede der CSS-Ressourcen die Hintergrundfarbe des zugehörigen Pagelets ändert.
BigPipe wird hingehen und diese Pagelets der Reihe nach rendern. Dabei werden zuerst die zugehörigen CSS-Ressourcen geladen und dann der HTML-Code injiziert. Wenn dann von allen Pagelets die CSS-Ressourcen geladen
und der HTML-Code injiziert ist, dann wird BigPipe die JS-Ressourcen der Pagelets einbinden und den statischen Javascript-Code (falls vorhanden) ausführen. Damit man den Pipeline-Effekt auf dieser Beispielseite auch
sieht werden die CSS- und JS-Ressourcen über ein Delayscript geleitet. Debuginformationen findest du in der Javascript-Konsole.</p>

<p><b>Weitere Informationen:</b> <a href="https://blackphantom.de/artikel/bigpipe-website-pipelining-und-schnellerer-aufbau-durch-einzelne-pagelets/" target="_blank">https://blackphantom.de/artikel/bigpipe-website-pipelining-und-schnellerer-aufbau-durch-einzelne-pagelets/</a></p>

<?php
echo $PageletRed;
echo $PageletBlue;
echo $PageletGreen;
?>

<?php
BigPipe::render();
?>
</body>
</html>