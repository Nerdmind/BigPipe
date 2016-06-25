<?php
namespace BigPipe;

abstract class Resource {
	private $ID           = '';
	private $type         = '';
	private $resourceURL  = '';
	private $phaseDoneJS  = [];
	private static $count = 0;

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
	public function __construct($type, $resourceURL) {
		$this->phaseDoneJS = array_pad($this->phaseDoneJS, 3, []);
		$this->ID = 'R'.++self::$count;
		$this->type = $type;
		$this->resourceURL = $resourceURL;
	}

	#===============================================================================
	# Return the unique ID
	#===============================================================================
	public function getID() {
		return $this->ID;
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
}
?>