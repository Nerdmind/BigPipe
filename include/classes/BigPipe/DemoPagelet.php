<?php
namespace BigPipe;

class DemoPagelet extends Pagelet {

	public function __construct($customID = NULL, $priority = Pagelet::PRIORITY_NORMAL, array $dependencies = []) {
		parent::__construct($customID, $priority, $dependencies);

		$message = '%s: PhaseDoneJS for phase %s';

		$this->addPhaseDoneJS(self::PHASE_ARRIVE,  'console.log("'.sprintf($message, $this->getID(), 'ARRIVE').'")');
		$this->addPhaseDoneJS(self::PHASE_LOADCSS, 'console.log("'.sprintf($message, $this->getID(), 'LOADCSS').'")');
		$this->addPhaseDoneJS(self::PHASE_PUTHTML, 'console.log("'.sprintf($message, $this->getID(), 'PUTHTML').'")');
		$this->addPhaseDoneJS(self::PHASE_LOADJS,  'console.log("'.sprintf($message, $this->getID(), 'LOADJS').'")');
		$this->addPhaseDoneJS(self::PHASE_EXECJS,  'console.log("'.sprintf($message, $this->getID(), 'EXECJS').'")');
	}
}