<?php
namespace Debugging\Resource;

class Stylesheet extends \BigPipe\Resource\Stylesheet {
	public function __construct($customID = NULL, $resourceURL) {
		parent::__construct(...func_get_args());

		foreach(['INIT', 'LOAD', 'DONE'] as $phase) {
			$code = 'console.log("PhaseDoneJS for Stylesheet %%c#%s%%c: %s", "color:#4169E1", "color:inherit")';
			$this->addPhaseDoneJS(constant("self::PHASE_$phase"), sprintf($code, $this->getID(), $phase));
		}
	}
}
?>