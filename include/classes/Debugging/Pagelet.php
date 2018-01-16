<?php
namespace Debugging;

class Pagelet extends \BigPipe\Pagelet {
	public function __construct($customID = NULL, $priority = self::PRIORITY_NORMAL) {
		parent::__construct(...func_get_args());

		foreach(['INIT', 'LOADCSS', 'HTML', 'LOADJS', 'DONE'] as $phase) {
			$code = 'console.log("PhaseDoneJS for Pagelet %%c#%s%%c: %s", "color:#008B45", "color:inherit")';
			$this->addPhaseDoneJS(constant("self::PHASE_$phase"), sprintf($code, $this->getID(), $phase));
		}
	}
}
?>