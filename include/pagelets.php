<?php
#===============================================================================
# Autoload register for classes
#===============================================================================
spl_autoload_register(function($classname) {
	$classname = str_replace('\\', '/', $classname);
	require "classes/{$classname}.php";
});

#===============================================================================
# Enable debugging mode
#===============================================================================
Application::$debugging = TRUE;

#===============================================================================
# Check if BigPipe should be disabled
#===============================================================================
if(isset($_GET['bigpipe']) AND $_GET['bigpipe'] === '0') {
	# You can use this method to disable the pipeline for Googlebot or something
	# else. If BigPipe is "disabled", then all pagelets will be rendered without
	# being pipelined through the javascript library. The content of the pagelet
	# will be present at the original position within the HTML response (and all
	# external stylesheets and javascripts will be displayed as simple <link> or
	# <script> elements within the HTML document).
	BigPipe\BigPipe::enabled(FALSE);
}

#===============================================================================
# Pagelet with red background color
#===============================================================================
$PageletRed = Application::createPagelet('redPL');
$PageletRed->addHTML('<section id="red" class="text">I AM A PAGELET WITH RED BACKGROUND</section>');
$PageletRed->addResource(Application::createStylesheet('red-stylesheet', 'static/red.php'));
$PageletRed->addResource(Application::createJavascript('delayed-javascript', 'static/delayJS.php'));
$PageletRed->addJSCode("document.getElementById('red').innerHTML += ' [JS executed]';document.getElementById('red').style.borderRadius = '30px';");

#===============================================================================
# Pagelet with blue background color
#===============================================================================
$PageletBlue = Application::createPagelet('bluePL', BigPipe\Pagelet::PRIORITY_HIGH);
$PageletBlue->addHTML('<section id="blue" class="text">I AM A PAGELET WITH BLUE BACKGROUND</section>');
$PageletBlue->addResource(Application::createStylesheet('blue-stylesheet', 'static/blue.php'));
$PageletBlue->addResource(Application::createJavascript('delayed-javascript', 'static/delayJS.php'));
$PageletBlue->addJSCode("document.getElementById('blue').innerHTML += ' [JS executed]';document.getElementById('blue').style.borderRadius = '30px';");

#===============================================================================
# Pagelet with green background color
#===============================================================================
$PageletGreen = Application::createPagelet('greenPL');

{
	#===============================================================================
	# Pagelet within $PageletGreen
	#===============================================================================
	// The addDependency call is required to ensure that $InnerPagelet will only be
	// executed if the HTML from the $PageletGreen has ALREADY DISPLAYED. Otherwise,
	// $InnerPagelet would not find his placeholder tag which is defined WITHIN the
	// HTML on $PageletGreen. Of course, you can still add other pagelets as
	// dependency. Then will $InnerPagelet only displayed if all dependencies are
	// already displayed!
	//
	// NOTE: PRIORITY_HIGHEST is only set so that you can see, that this pagelet is
	// the first which arrives, but it will first be displayed if his dependency
	// pagelets are already displayed.

	$InnerPagelet = Application::createPagelet('innerPL', BigPipe\Pagelet::PRIORITY_HIGHEST);
	$InnerPagelet->addDependency($PageletGreen);

	$InnerPagelet->addHTML('<section sytle="background:#FFF;padding:5px;">Inner Pagelet \(o_o)/</section>');
}

$PageletGreen->addHTML('<section id="green" class="text">I AM A PAGELET WITH GREEN BACKGROUND'.$InnerPagelet.'</section>');
$PageletGreen->addResource(Application::createStylesheet('green-stylesheet', 'static/green.php'));
$PageletGreen->addResource(Application::createJavascript('delayed-javascript', 'static/delayJS.php'));
$PageletGreen->addJSCode("document.getElementById('green').innerHTML += ' [JS executed]';document.getElementById('green').style.borderRadius = '30px';");
