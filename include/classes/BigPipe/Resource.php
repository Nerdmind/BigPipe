<?php
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
# Abstract Resource representation class     [Thomas Lange <code@nerdmind.de>] #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#                                                                              #
# [More information coming soon]                                               #
#                                                                              #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
namespace BigPipe;

abstract class Resource extends Item {
	private $type         = '';
	private $resourceURL  = '';

	#===============================================================================
	# Render resource HTML for disabled pipeline
	#===============================================================================
	abstract public function renderHTML();

	#===============================================================================
	# Resource types
	#===============================================================================
	const TYPE_STYLESHEET = 0;
	const TYPE_JAVASCRIPT = 1;

	#===============================================================================
	# Phase numbers for PhaseDoneJS
	#===============================================================================
	const PHASE_INIT = 0; # Resource object has been initialized
	const PHASE_LOAD = 1; # Loading of resource has been started
	const PHASE_DONE = 2; # Loading of resource is done.

	#===============================================================================
	# Build resource
	#===============================================================================
	public function __construct($customID = NULL, $type, $resourceURL) {
		$this->ID = $customID ?? spl_object_hash($this);
		$this->type = $type;
		$this->resourceURL = $resourceURL;

		$this->phaseDoneJS = array_pad($this->phaseDoneJS, 3, []);
	}

	#===============================================================================
	# Return the resource type
	#===============================================================================
	public function getType() {
		return $this->type;
	}

	#===============================================================================
	# Return the resource URL
	#===============================================================================
	public function getURL() {
		return $this->resourceURL;
	}

	#===============================================================================
	# Return the resource structure
	#===============================================================================
	public function getStructure(): array {
		return ['ID' => $this->getID(), 'HREF' => $this->getURL(), 'PHASE' => $this->getPhaseDoneJS()];
	}
}
?>