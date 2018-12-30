<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="referrer" content="origin-when-crossorigin" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<style>
		html,body{margin:0;padding:0;}
		html{font-size:1.25rem;color:#333;background:#CCC;-webkit-hyphens:auto;hyphens:auto;}
		body{font-family:Ruda,sans-serif;font-size:0.7rem;line-height:1.0rem;}
		blockquote,pre,code{font-family:"PT Mono",monospace;}
		a{color:#0060A0;}a:focus{background:#CCC;text-decoration:underline;}
		#container{background:#FFF;max-width:50rem;margin:0 auto;border:0.05rem solid #AAA;}
		#main_header{background:#5E819F;}
	</style>
	<script src="static/bigpipe.js"></script>
	<title>Example</title>
</head>
<body>
<section id="container">
	<header id="main_header">
		<h1>BigPipe</h1>
		<p>This is an example page to demonstrate how <em>BigPipe</em> works.</p>
		<nav id="main_navi">
			<ul>
				<li><a href="#">Home</a></li>
				<li><a href="#">Profile</a></li>
				<li><a href="#">Settings</a></li>
			</ul>
		</nav>
	</header>
	<div id="main_container">
		<main id="main_content">
			<?="{$content_html}\n"?>
		</main>
		<aside id="main_sidebar">
			<?="{$sidebar_html}\n"?>
		</aside>
	</div>
	<footer id="main_footer">
		Imprint | Footer
	</footer>
</section>
<!-- We skip closing the <body> and <html> tags because the pagelets are getting flushed at this position. -->
