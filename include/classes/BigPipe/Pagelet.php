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
	private $priority     = NULL;
	private $resources    = [];
	private $dependencies = [];
	private $tagName      = 'div';
	private $tagHTML      = '';

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

	public function __construct(?string $id = NULL, int $priority = self::PRIORITY_NORMAL) {
		$this->id = $id ?? spl_object_hash($this);

		$this->priority    = $priority;
		$this->resources   = array_pad($this->resources,   2, []);
		$this->phaseDoneJS = array_pad($this->phaseDoneJS, 5, []);

		BigPipe::enqueue($this);
	}

	#===============================================================================
	# Return the priority
	#===============================================================================
	public function getPriority(): int {
		return $this->priority;
	}

	#===============================================================================
	# Return the HTML content
	#===============================================================================
	public function getHTML(): string {
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
	public function addHTML(string $HTML): void {
		$this->HTML .= $HTML;
	}

	#===============================================================================
	# Add resource
	#===============================================================================
	public function addResource(Resource $Resource): void {
		$this->resources[$Resource->getType()][] = $Resource;
	}

	#===============================================================================
	# Attach a main JS code part
	#===============================================================================
	public function addJSCode(string $code): void {
		$this->JSCode[] = $code;
	}

	#===============================================================================
	# Attach a display dependency
	#===============================================================================
	public function addDependency(Pagelet $Pagelet): void {
		$this->dependencies[] = $Pagelet->getID();
	}

	#===============================================================================
	# Set custom placeholder tag name
	#===============================================================================
	public function setTagName(string $tagName): void {
		$this->tagName = $tagName;
	}

	#===============================================================================
	# Set custom placeholder HTML
	#===============================================================================
	public function setPlaceholderHTML(string $HTML): void {
		$this->tagHTML = $HTML;
	}

	#===============================================================================
	# Return the pagelet structure
	#===============================================================================
	public function getStructure(): array {
		foreach($this->getResources()[Resource::TYPE_STYLESHEET] as $Resource) {
			$stylesheets[] = $Resource->getStructure();
		}

		foreach($this->getResources()[Resource::TYPE_JAVASCRIPT] as $Resource) {
			$javascripts[] = $Resource->getStructure();
		}

		return [
			'ID' => $this->getID(),
			'NEED' => $this->getDependencies(),
			'RSRC' => [
				Resource::TYPE_STYLESHEET => $stylesheets ?? [],
				Resource::TYPE_JAVASCRIPT => $javascripts ?? []
			],
			'CODE' => $this->getJSCode(),
			'PHASE' => $this->getPhaseDoneJS()
		];
	}

	#===============================================================================
	# Flush pagelet immediately
	#===============================================================================
	public function flush(): void {
		if(BigPipe::isEnabled()) {
			$pageletHTML = str_replace(["\r", "\n", "\t"], '', $this->getHTML());
			$pageletHTML = str_replace('--', '&#45;&#45;', $pageletHTML);

			$pageletJSON = json_encode($this->getStructure());

			echo "<code hidden id=\"_{$this->getID()}\"><!-- {$pageletHTML} --></code>\n";
			echo "<script>BigPipe.onPageletArrive({$pageletJSON}, document.getElementById(\"_{$this->getID()}\"));</script>\n\n";

			BigPipe::dequeue($this);
			BigPipe::flushOutputBuffer();
		}
	}

	#===============================================================================
	# Magic method: __toString()
	#===============================================================================
	public function __toString(): string {
		$pageletHTML  = "<{$this->tagName} id=\"{$this->getID()}\">";
		$pageletHTML .= !BigPipe::isEnabled() ? $this->getHTML() : $this->tagHTML;
		$pageletHTML .= "</{$this->tagName}>";

		return $pageletHTML;
	}
}
