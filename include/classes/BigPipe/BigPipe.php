<?php
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
# BigPipe main class                         [Thomas Lange <code@nerdmind.de>] #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#                                                                              #
# The BigPipe main class is responsible for sorting and rendering the pagelets #
# and their associated resources. This class also provides methods to turn off #
# the pipeline mode or turn on the debugging mode.                             #
#                                                                              #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
namespace BigPipe;

class BigPipe {
	private static $enabled  = TRUE;
	private static $pagelets = [];

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
	# Insert pagelet into queue
	#===============================================================================
	public static function enqueue(Pagelet $Pagelet) {
		self::$pagelets[spl_object_hash($Pagelet)] = $Pagelet;
	}

	#===============================================================================
	# Remove pagelet from queue
	#===============================================================================
	public static function dequeue(Pagelet $Pagelet) {
		unset(self::$pagelets[spl_object_hash($Pagelet)]);
	}

	#===============================================================================
	# Prints a single pagelet response
	#===============================================================================
	private static function singleResponse(Pagelet $Pagelet) {
		$pageletJSON = $Pagelet->getStructure();

		$pageletHTML = removeLineBreaksAndTabs($Pagelet->getHTML());
		$pageletHTML = str_replace('--', '&#45;&#45;', $pageletHTML);

		$pageletJSON = json_encode($pageletJSON);

		echo "<code hidden id=\"_{$Pagelet->getID()}\"><!-- {$pageletHTML} --></code>\n";
		echo "<script>BigPipe.onPageletArrive({$pageletJSON}, document.getElementById(\"_{$Pagelet->getID()}\"));</script>\n\n";
	}

	#===============================================================================
	# Sends output buffer so far as possible towards user
	#===============================================================================
	public static function flushOutputBuffer() {
		ob_flush(); flush();
	}

	#===============================================================================
	# Renders all remaining pagelets from the queue in the appropriate order
	#===============================================================================
	public static function completeResponse() {
		self::flushOutputBuffer();

		$pagelets_ordered = [];

		foreach(self::$pagelets as $Pagelet) {
			$pagelets_ordered[$Pagelet->getPriority()][] = $Pagelet;
		}

		krsort($pagelets_ordered);

		if(!empty($pagelets_ordered)) {
			$pagelets = call_user_func_array('array_merge', $pagelets_ordered);

			if(self::enabled()) {
				foreach($pagelets as $Pagelet) {
					self::singleResponse($Pagelet);
					self::flushOutputBuffer();
				}
			}

			# NOTE: If BigPipe is disabled, Pagelet::flush() will NOT call BigPipe::dequeue().
			# This means that (if the pipeline is disabled) $pagelets_ordered contains ALL
			# Pagelets regardless of whether if Pagelet::flush() was already called. And then
			# we can iterate over them and echo all requiered CSS and JS resources.
			else {
				foreach($pagelets as $Pagelet) {
					foreach($Pagelet->getResources()[Resource::TYPE_STYLESHEET] as $Resource) {
						echo "{$Resource->renderHTML()}\n";
					}

					foreach($Pagelet->getResources()[Resource::TYPE_JAVASCRIPT] as $Resource) {
						echo "{$Resource->renderHTML()}\n";
					}

					foreach($Pagelet->getJSCode() as $JSCode) {
						echo "<script>{$JSCode}</script>\n";
					}
				}
			}
		}

		if(self::enabled()) {
			echo "<script>BigPipe.onLastPageletArrived();</script>\n";
		}
	}
}
?>