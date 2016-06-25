<?php
namespace BigPipe;

class BigPipe {
	private static $debugging = FALSE;
	private static $enabled   = TRUE;
	private static $pagelets  = [];
	private static $count     = 0;

	#===============================================================================
	# Enable or disable the pipeline mode
	#===============================================================================
	public static function enabled($change = NULL) {
		if($change !== NULL) {
			self::$enabled = (bool) $change;
		}

		return self::$enabled;
	}

	#===============================================================================
	# Return if debugging is enabled or change
	#===============================================================================
	public static function debugging($change = NULL): bool {
		if($change !== NULL) {
			self::$debugging = (bool) $change;
		}

		return self::$debugging;
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
		if(self::debugging()) {
			self::addDebugPhaseDoneJS($Pagelet);

			array_map('self::addDebugPhaseDoneJS', $Pagelet->getCSSResources());
			array_map('self::addDebugPhaseDoneJS', $Pagelet->getJSResources());

			usleep(rand(125, 175) * 2000);
		}

		$stylesheets = [];
		$javascripts = [];

		foreach($Pagelet->getCSSResources() as $Resource) {
			$stylesheets[$Resource->getURL()] = $Resource->getPhaseDoneJS();
		}

		foreach($Pagelet->getJSResources() as $Resource) {
			$javascripts[$Resource->getURL()] = $Resource->getPhaseDoneJS();
		}

		$pageletJSON = [
			'ID' => $Pagelet->getID(),
			'NEED' => $Pagelet->getDependencies(),
			'RSRC' => (object) [
				Resource::TYPE_STYLESHEET => (object) $stylesheets,
				Resource::TYPE_JAVASCRIPT => (object) $javascripts,
			],
			'CODE' => removeLineBreaksAndTabs($Pagelet->getJSCode()),
			'PHASE' => $Pagelet->getPhaseDoneJS()
		];

		if($last) {
			$pageletJSON['IS_LAST'] = TRUE;
		}

		$pageletHTML = removeLineBreaksAndTabs($Pagelet->getHTML());
		$pageletHTML = str_replace('--', '&#45;&#45;', $pageletHTML);

		$pageletJSON = json_encode($pageletJSON, (self::debugging() ? JSON_PRETTY_PRINT : NULL));

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
				if(!self::enabled()) {
					foreach($Pagelet->getCSSResources() as $Resource) {
						echo $Resource->renderHTML()."\n";
					}

					foreach($Pagelet->getJSResources() as $Resource) {
						echo $Resource->renderHTML()."\n";
					}

					foreach($Pagelet->getJSCode() as $JSCode) {
						echo "<script>{$JSCode}</script>\n";
					}
				}

				else {
					self::singleResponse($Pagelet, (self::$count === ++$i));
					self::flushOutputBuffer();
				}
			}
		}
	}

	#===============================================================================
	# Add PhaseDoneJS for debugging Pagelet and Resource
	#===============================================================================
	private static function addDebugPhaseDoneJS($Instance) {
		$objpath = str_replace('\\', '|', get_class($Instance));

		if($Instance instanceof Pagelet) {
			$message = "console.log(\"%%c[{$objpath}]%%c#(%%c%s%%c): PhaseDoneJS for phase: %s\", \"font-weight:bold\", \"color:#666\", \"color:#008B45\", \"color:#666\")";

			$Instance->addPhaseDoneJS($Instance::PHASE_INIT,    sprintf($message, $Instance->getID(), 'INIT'));
			$Instance->addPhaseDoneJS($Instance::PHASE_LOADCSS, sprintf($message, $Instance->getID(), 'LOADCSS'));
			$Instance->addPhaseDoneJS($Instance::PHASE_HTML,    sprintf($message, $Instance->getID(), 'HTML'));
			$Instance->addPhaseDoneJS($Instance::PHASE_LOADJS,  sprintf($message, $Instance->getID(), 'LOADJS'));
			$Instance->addPhaseDoneJS($Instance::PHASE_DONE,    sprintf($message, $Instance->getID(), 'DONE'));

			return $Instance;
		}

		if($Instance instanceof Resource) {
			$message = "console.log(\"[{$objpath}]%%c#(%%c%s%%c): PhaseDoneJS for phase: %s\", \"color:#666\", \"color:#008B45\", \"color:#666\")";

			$Instance->addPhaseDoneJS($Instance::PHASE_INIT, sprintf($message, $Instance->getID(), 'INIT'));
			$Instance->addPhaseDoneJS($Instance::PHASE_LOAD, sprintf($message, $Instance->getID(), 'LOAD'));
			$Instance->addPhaseDoneJS($Instance::PHASE_DONE, sprintf($message, $Instance->getID(), 'DONE'));

			return $Instance;
		}
	}
}
?>