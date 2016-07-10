<?php
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
# Abstract item class                        [Thomas Lange <code@nerdmind.de>] #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#                                                                              #
# The item class abstracts the properties and methods that are required by the #
# Pagelet and Resource class both. Each one can have PhaseDoneJS callbacks for #
# several phases numbers which are defined as constants of the specific class. #
#                                                                              #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
namespace BigPipe;

abstract class Item {
	protected $ID = '';
	protected $phaseDoneJS = [];

	#===============================================================================
	# Return the unique ID
	#===============================================================================
	public function getID() {
		return $this->ID;
	}

	#===============================================================================
	# Return all registered PhaseDoneJS callbacks
	#===============================================================================
	public function getPhaseDoneJS(): array {
		return $this->phaseDoneJS;
	}

	#===============================================================================
	# Attach a PhaseDoneJS callback
	#===============================================================================
	public function addPhaseDoneJS($phase, $callback) {
		return $this->phaseDoneJS[$phase][] = removeLineBreaksAndTabs($callback);
	}
}
?>