<?php
namespace BigPipe;

class BigPipe {
	private static $enabled  = TRUE;
	private static $debug    = TRUE;
	private static $pagelets = [];
	private static $count    = 0;

	#===============================================================================
	# Return TRUE if the pipeline is enabled
	#===============================================================================
	public static function isEnabled() {
		return self::$enabled;
	}

	#===============================================================================
	# Enable or disable the pipeline mode
	#===============================================================================
	public static function enablePipeline($enabled = TRUE) {
		return self::$enabled = (bool) $enabled;
	}

	#===============================================================================
	# Add a new pagelet to pipeline
	#===============================================================================
	public static function addPagelet(Pagelet $Pagelet, $priority) {
		self::$pagelets[$priority][] = $Pagelet;
		return ++self::$count;
	}

	#===============================================================================
	# Prints a single pagelet response
	#===============================================================================
	private static function singleResponse(Pagelet $Pagelet, $last = FALSE) {
		$pageletJSON = [
			'ID' => $Pagelet->getID(), 'NEED' => $Pagelet->getDependencies(),
			'RESOURCES' => ['CSS' => $Pagelet->getCSSFiles(), 'JS' => $Pagelet->getJSFiles(), 'JS_CODE' => removeLineBreaksAndTabs($Pagelet->getJSCode())],
			'PHASES' => (object) $Pagelet->getPhaseDoneJS()
		];

		if($last) {
			$pageletJSON['IS_LAST'] = true;
		}

		$pageletHTML = removeLineBreaksAndTabs($Pagelet->getHTML());
		$pageletHTML = str_replace('--', '&#45;&#45;', $pageletHTML);

		$pageletJSON = json_encode($pageletJSON, (self::$debug ? JSON_PRETTY_PRINT : NULL));

		echo "<code class=\"hidden\" id=\"_{$Pagelet->getID()}\"><!-- {$pageletHTML} --></code>\n";
		echo "<script>BigPipe.onPageletArrive({$pageletJSON}, document.getElementById(\"_{$Pagelet->getID()}\"));</script>\n\n";
	}

	#===============================================================================
	# Sends output buffer so far as possible towards user
	#===============================================================================
	public static function flushOutputBuffer() {
		ob_flush(); flush();
	}

	#===============================================================================
	# Render the pagelets
	#===============================================================================
	public static function render() {
		self::flushOutputBuffer();

		$i = 0;

		ksort(self::$pagelets);

		foreach(array_reverse(self::$pagelets) as $priority => $pagelets) {
			foreach($pagelets as $Pagelet) {
				if(!self::isEnabled()) {
					foreach($Pagelet->getCSSFiles() as $CSSFile) {
						echo "<link href=\"{$CSSFile}\" rel=\"stylesheet\" />\n";
					}

					foreach($Pagelet->getJSFiles() as $JSFile) {
						echo "<script src=\"{$JSFile}\"></script>\n";
					}

					foreach($Pagelet->getJSCode() as $JSCode) {
						echo "<script>{$JSCode}</script>\n";
					}
				}

				else {
					self::singleResponse($Pagelet, (self::$count === ++$i));
					self::flushOutputBuffer();

					self::$debug AND usleep((rand(250, 1000) * 1000));
				}
			}
		}
	}
}