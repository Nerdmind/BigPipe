<?php
namespace BigPipe\Resource;

class JS extends \BigPipe\Resource {

	#===============================================================================
	# Build resource
	#===============================================================================
	public function __construct($resourceURL) {
		parent::__construct(parent::TYPE_JAVASCRIPT, $resourceURL);
	}

	#===============================================================================
	# Render resource HTML
	#===============================================================================
	public function renderHTML() {
		return sprintf('<script data-id="%s" src="%s"></script>', $this->getID(), $this->getURL());
	}
}
?>