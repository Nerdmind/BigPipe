<?php
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
# Pagelet representation class               [Thomas Lange <code@nerdmind.de>] #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#                                                                              #
# [More information coming soon]                                               #
#                                                                              #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
namespace BigPipe;

class Pagelet extends Item {
	private $HTML         = '';
	private $JSCode       = [];
	private $resources    = [];
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

	public function __construct($customID = NULL, $priority = self::PRIORITY_NORMAL) {
		$this->ID = $customID ?? 'P'.++self::$count;

		$this->resources   = array_pad($this->resources,   2, []);
		$this->phaseDoneJS = array_pad($this->phaseDoneJS, 5, []);

		BigPipe::addPagelet($this, $priority);
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
	public function getJSCode(): array {
		return $this->JSCode;
	}

	#===============================================================================
	# Return attached resources
	#===============================================================================
	public function getResources(): array {
		return $this->resources;
	}

	#===============================================================================
	# Return all display dependencies
	#===============================================================================
	public function getDependencies(): array {
		return array_unique($this->dependencies);
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
		return $this->resources[$Resource->getType()][] = $Resource;
	}

	#===============================================================================
	# Short: Add CSS resource by URL
	#===============================================================================
	public function addCSS($resourceURL): Resource {
		return $this->addResource(new Resource\CSS(NULL, $resourceURL));
	}

	#===============================================================================
	# Short: Add JS resource by URL
	#===============================================================================
	public function addJS($resourceURL): Resource {
		return $this->addResource(new Resource\JS(NULL, $resourceURL));
	}

	#===============================================================================
	# Attach a main JS code part
	#===============================================================================
	public function addJSCode($code) {
		return $this->JSCode[] = $code;
	}

	#===============================================================================
	# Attach a display dependency
	#===============================================================================
	public function addDependency($Pagelet) {
		if($Pagelet instanceof Pagelet) {
			return $this->dependencies[] = $Pagelet->getID();
		}

		return $this->dependencies[] = $Pagelet;
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
?>