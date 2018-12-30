<?php
# >>> pagelets.php
#===============================================================================
# Autoload register for classes
#===============================================================================
spl_autoload_register(function($classname) {
	$classname = str_replace('\\', '/', $classname);
	require "include/classes/{$classname}.php";
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
# Initialize pagelet instances
#===============================================================================
$DemoPagelet0 = Application::createPagelet('main_pagelet_0');
$DemoPagelet1 = Application::createPagelet('main_pagelet_1');
$DemoPagelet2 = Application::createPagelet('main_pagelet_2');
$DemoPagelet3 = Application::createPagelet('side_pagelet_3');

$Document = new Document();

$Document->addPagelet($DemoPagelet0, function() {
	return 'I am the first demo pagelet.';
});

$Document->addPagelet($DemoPagelet1, function() {
	sleep(1); # simulate long execution of code (database queries, network communication, etc)
	return 'I am the second demo pagelet and take very long to generate at server.';
});

$Document->addPagelet($DemoPagelet2, function() {
	return 'I am the third demo pagelet.';
});

$Document->addPagelet($DemoPagelet3, function() {
	sleep(1); # simulate long execution of code (database queries, network communication, etc)
	return 'I am the fourth demo pagelet and I also take very long to generate at server.';
});

$content_html = "<ul>
		<li>DemoPagelet0: {$DemoPagelet0}</li>
		<li>DemoPagelet1: {$DemoPagelet1}</li>
		<li>DemoPagelet2: {$DemoPagelet2}</li>
	</ul>";

$sidebar_html = "{$DemoPagelet3}";

$Document->render($content_html, $sidebar_html);
?>