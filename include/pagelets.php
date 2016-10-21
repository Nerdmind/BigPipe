<?php
#===============================================================================
# Enable debugging mode
#===============================================================================
BigPipe\BigPipe::debugging(TRUE);

#===============================================================================
# Pagelet with red background color
#===============================================================================
$PageletRed = new BigPipe\Pagelet('redPL');
$PageletRed->addHTML('<section id="red" class="text">I AM A PAGELET WITH RED BACKGROUND</section>');
$PageletRed->addCSS('static/red.php');
$PageletRed->addCSS('static/red.php');
$PageletRed->addJS('static/delayJS.php');
$PageletRed->addJSCode("document.getElementById('red').innerHTML += ' [JS executed]';document.getElementById('red').style.borderRadius = '30px';");

#===============================================================================
# Pagelet with blue background color
#===============================================================================
$PageletBlue = new BigPipe\Pagelet('bluePL', BigPipe\Pagelet::PRIORITY_HIGH);
$PageletBlue->addHTML('<section id="blue" class="text">I AM A PAGELET WITH BLUE BACKGROUND</section>');
$PageletBlue->addCSS('static/blue.php');
$PageletRed->addCSS('static/red.php');
$PageletBlue->addJS('static/delayJS.php');
$PageletBlue->addJSCode("document.getElementById('blue').innerHTML += ' [JS executed]';document.getElementById('blue').style.borderRadius = '30px';");

#===============================================================================
# Pagelet with green background color
#===============================================================================
$PageletGreen = new BigPipe\Pagelet('greenPL');

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

	$InnerPagelet = new BigPipe\Pagelet('innerPL', BigPipe\Pagelet::PRIORITY_HIGHEST);

	// NOTICE: You can also use the Pagelet ID (as string) as argument. May be helpful
	// if a dependency Pagelet object is not accessible within the current scope.
	$InnerPagelet->addDependency($PageletGreen);

	$InnerPagelet->addHTML('<section sytle="background:#FFF;padding:5px;">Inner Pagelet \(o_o)/</section>');
}

$PageletGreen->addHTML('<section id="green" class="text">I AM A PAGELET WITH GREEN BACKGROUND'.$InnerPagelet.'</section>');
$PageletGreen->addCSS('static/green.php');
$PageletGreen->addJS('static/delayJS.php');
$PageletGreen->addJSCode("document.getElementById('green').innerHTML += ' [JS executed]';document.getElementById('green').style.borderRadius = '30px';");
?>