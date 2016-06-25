<?php
namespace BigPipe;

class Pagelet {
	private $ID           = '';
	private $HTML         = '';
	private $JSCode       = [];
	private $JSResources  = [];
	private $CSSResources = [];
	private $phaseDoneJS  = [];
	private $dependencies = [];
	private $tagname      = 'div';
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
	# Phase numbers for PhaseDoneJS
	#===============================================================================
	const PHASE_INIT    = 0; # After the pagelet object was initialized
	const PHASE_LOADCSS = 1; # After all the CSS resources have been loaded
	const PHASE_HTML    = 2; # After the placeholder HTML was replaced
	const PHASE_LOADJS  = 3; # After all the JS resources have been loaded
	const PHASE_DONE    = 4; # After the static JS code has been executed

	public function __construct($customID = NULL, $priority = self::PRIORITY_NORMAL, array $dependencies = []) {
		$this->phaseDoneJS = array_pad($this->phaseDoneJS, 5, []);
		$this->dependencies = $dependencies;
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
	# Add resource
	#===============================================================================
	public function addResource(Resource $Resource): Resource {
		switch($Resource->getType()) {
			case Resource::TYPE_STYLESHEET:
				return $this->CSSResources[] = $Resource;
				break;

			case Resource::TYPE_JAVASCRIPT:
				return $this->JSResources[] = $Resource;
				break;

			default:
				return $Resource;
		}
	}

	#===============================================================================
	# Short: Add CSS resource by URL
	#===============================================================================
	public function addCSS($resourceURL): Resource {
		return $this->addResource(new Resource\CSS($resourceURL));
	}

	#===============================================================================
	# Short: Add JS resource by URL
	#===============================================================================
	public function addJS($resourceURL): Resource {
		return $this->addResource(new Resource\JS($resourceURL));
	}

	#===============================================================================
	# Attach a main JS code part
	#===============================================================================
	public function addJSCode($code) {
		return $this->JSCode[] = $code;
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
	public function getPhaseDoneJS(): array {
		return $this->phaseDoneJS;
	}

	#===============================================================================
	# Return the attached CSS resources
	#===============================================================================
	public function getCSSResources(): array {
		return $this->CSSResources;
	}

	#===============================================================================
	# Return the attached JS resources
	#===============================================================================
	public function getJSResources(): array {
		return $this->JSResources;
	}

	#===============================================================================
	# Return all display dependencies
	#===============================================================================
	public function getDependencies(): array {
		return $this->dependencies;
	}

	#===============================================================================
	# Set custom placeholder tagname
	#===============================================================================
	public function setTagname($tagname) {
		return $this->tagname = $tagname;
	}

	#===============================================================================
	# Magic method: __toString()
	#===============================================================================
	public function __toString() {
		$pageletHTML  = "<{$this->tagname} id=\"{$this->getID()}\">";
		$pageletHTML .= !BigPipe::enabled() ? $this->getHTML() : NULL;
		$pageletHTML .= "</{$this->tagname}>";

		return $pageletHTML;
	}
}