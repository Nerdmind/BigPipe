<?php
class Application {
	public static $debugging = FALSE;

	#===============================================================================
	# Create Pagelet instance
	#===============================================================================
	public static function createPagelet(string $ID = NULL): BigPipe\Pagelet {
		$namespace = self::$debugging ? 'Debugging' : 'BigPipe';
		$classname = "{$namespace}\Pagelet";

		$Pagelet = new $classname(...func_get_args());
		return $Pagelet;
	}

	#===============================================================================
	# Create Stylesheet instance
	#===============================================================================
	public static function createStylesheet(string $ID, string $href): BigPipe\Resource\Stylesheet {
		$namespace = self::$debugging ? 'Debugging' : 'BigPipe';
		$classname = "{$namespace}\Resource\Stylesheet";

		$Stylesheet = new $classname(...func_get_args());
		return $Stylesheet;
	}

	#===============================================================================
	# Create Javascript instance
	#===============================================================================
	public static function createJavascript(string $ID, string $href): BigPipe\Resource\Javascript {
		$namespace = self::$debugging ? 'Debugging' : 'BigPipe';
		$classname = "{$namespace}\Resource\Javascript";

		$Javascript = new $classname(...func_get_args());
		return $Javascript;
	}
}
