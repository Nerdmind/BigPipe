<?php
namespace BigPipe;

class Pagelet {
	private $ID           = NULL;
	private $HTML         = '';
	private $JSCode       = '';
	private $JSFiles      = [];
	private $CSSFiles     = [];
	private $phaseDoneJS  = [];
	private static $count = 0;

	#===============================================================================
	# Priorities for pagelet sorting
	#===============================================================================
	const PRIORITY_HIGHEST = 100;
	const PRIORITY_HIGH    = 75;
	const PRIORITY_NORMAL  = 50;
	const PRIORITY_LOW     = 25;
	const PRIORITY_LOWEST  = 0;

	#===============================================================================
	# Callback phase numbers for PhaseDoneJS
	#===============================================================================
	const PHASE_ARRIVE  = 0; # After the pagelet reached BigPipe
	const PHASE_LOADCSS = 1; # After all the CSS resources have been loaded
	const PHASE_PUTHTML = 2; # After the HTML content has been injected into the placeholders
	const PHASE_LOADJS  = 3; # After all the JS resources have been loaded
	const PHASE_EXECJS  = 4; # After the static JS code has been executed

	public function __construct($customID = NULL, $priority = self::PRIORITY_NORMAL) {
		$this->phaseDoneJS = array_pad([], 5, []);
		$this->ID = is_string($customID) ? $customID : 'P'.++self::$count;

		BigPipe::addPagelet($this, $priority);
	}

	#===============================================================================
	# Return the unique ID
	#===============================================================================
	public function getID() {
		return $this->ID;
	}

	#===============================================================================
	# Return the HTML content
	#===============================================================================
	public function getHTML() {
		return $this->HTML;
	}

	#===============================================================================
	# Return the CSS resources
	#===============================================================================
	public function getCSSFiles() {
		return $this->CSSFiles;
	}

	#===============================================================================
	# Return the JS resources
	#===============================================================================
	public function getJSFiles() {
		return $this->JSFiles;
	}

	#===============================================================================
	# Return the main JS code
	#===============================================================================
	public function getJSCode() {
		return $this->JSCode;
	}

	#===============================================================================
	# Add HTML or attach more
	#===============================================================================
	public function addHTML($HTML) {
		return $this->HTML .= $HTML;
	}

	#===============================================================================
	# Attach a CSS resource
	#===============================================================================
	public function addCSS($file) {
		return $this->CSSFiles[] = $file;
	}

	#===============================================================================
	# Attach a JS resource
	#===============================================================================
	public function addJS($file) {
		return $this->JSFiles[] = $file;
	}

	#===============================================================================
	# Add JS code or attach more
	#===============================================================================
	public function addJSCode($code) {
		return $this->JSCode .= $code;
	}

	#===============================================================================
	# Attach a PhaseDoneJS callback
	#===============================================================================
	public function addPhaseDoneJS($phase, $callback) {
		return $this->phaseDoneJS[$phase][] = removeLineBreaksAndTabs($callback);
	}

	#===============================================================================
	# Return all registered PhaseDoneJS callbacks
	#===============================================================================
	public function getPhaseDoneJS() {
		return $this->phaseDoneJS;
	}

	#===============================================================================
	# Magic method: __toString()
	#===============================================================================
	public function __toString() {
		return '<div id="'.$this->getID().'">'.((!BigPipe::isEnabled()) ? $this->getHTML() : NULL).'</div>';
	}
}