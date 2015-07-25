<?php
namespace BigPipe;

class BigPipe {
	public  static $enabled  = TRUE;
	private static $pagelets = [];
	private static $count    = 0;

	#====================================================================================================
	# Gibt TRUE zurück wenn BigPipe eingeschaltet ist
	#====================================================================================================
	public static function isEnabled() {
		return self::$enabled ? TRUE : FALSE;
	}

	#====================================================================================================
	# Neues Pagelet zur Pipeline hinzufügen
	#====================================================================================================
	public static function addPagelet(Pagelet $Pagelet, $priority) {
		self::$pagelets[$priority][] = $Pagelet;
		self::$count++;
	}

	#====================================================================================================
	# Gibt einen einzelnen Pagelet-Response aus
	#====================================================================================================
	private static function pageletResponse(Pagelet $Pagelet, $async = FALSE, $last = FALSE) {
		$data = [
			'ID' => $Pagelet->getID(),
			'RESOURCES' => ['CSS' => $Pagelet->getCSSFiles(), 'JS' => $Pagelet->getJSFiles(), 'JS_CODE' => removeLineBreaksAndTabs($Pagelet->getJSCode())]
		];

		if($last) {
			$data['IS_LAST'] = true;
		}

		echo '<code class="hidden" id="_'.$data['ID'].'"><!-- '.str_replace('--', '&#45;&#45;', removeLineBreaksAndTabs($Pagelet->getHTML())).' --></code>'."\n";
		echo '<script>BigPipe.onPageletArrive('.json_encode($data).($async ? ', document.getElementById("_'.$Pagelet->getID().'").innerHTML' : NULL).');</script>'."\n\n";
	}

	#====================================================================================================
	# Sendet den Output-Buffer so weit wie möglich in Richtung User
	#====================================================================================================
	public static function flushOutputBuffer() {
		ob_flush(); flush();
	}

	#====================================================================================================
	# Alle Pagelets an Client schicken
	#====================================================================================================
	public static function render($async = FALSE) {
		self::flushOutputBuffer();

		$i = 0;

		ksort(self::$pagelets);

		foreach(array_reverse(self::$pagelets) as $priority => $pagelets) {
			foreach($pagelets as $Pagelet) {
				if(!self::isEnabled()) {
					if($Pagelet->getJSCode()) {
						echo '<script>'.$Pagelet->getJSCode().'</script>'."\n";
					}

					foreach($Pagelet->getCSSFiles() as $CSSFile) {
						echo '<link href="'.$CSSFile.'" rel="stylesheet" />'."\n";
					}

					foreach($Pagelet->getJSFiles() as $JSFile) {
						echo '<script src="'.$JSFile.'"></script>'."\n";
					}
				}

				else {
					self::pageletResponse($Pagelet, $async, (self::$count === ++$i));
					self::flushOutputBuffer();
				}
			}
		}
	}
}