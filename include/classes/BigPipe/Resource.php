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
	private $url  = '';
	private $type = '';

	#===============================================================================
	# Render resource HTML for disabled pipeline
	#===============================================================================
	abstract public function renderHTML(): string;

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
	public function __construct(string $id, int $type, string $url) {
		$this->id = $id ?? spl_object_hash($this);
		$this->type = $type;
		$this->url = $url;

		$this->phaseDoneJS = array_pad($this->phaseDoneJS, 3, []);
	}

	#===============================================================================
	# Return the resource type
	#===============================================================================
	public function getType(): int {
		return $this->type;
	}

	#===============================================================================
	# Return the resource URL
	#===============================================================================
	public function getURL(): string {
		return $this->url;
	}

	#===============================================================================
	# Return the resource structure
	#===============================================================================
	public function getStructure(): array {
		return [
			'ID' => $this->getID(),
			'HREF' => $this->getURL(),
			'PHASE' => $this->getPhaseDoneJS()
		];
	}
}
