<?php
namespace Debugging\Resource;

class Javascript extends \BigPipe\Resource\Javascript {
	public function __construct($customID = NULL, $resourceURL) {
		parent::__construct(...func_get_args());

		foreach(['INIT', 'LOAD', 'DONE'] as $phase) {
			$code = 'console.log("PhaseDoneJS for Javascript %%c#%s%%c: %s", "color:#B24A4A", "color:inherit")';
			$this->addPhaseDoneJS(constant("self::PHASE_$phase"), sprintf($code, $this->getID(), $phase));
		}
	}
}
?>