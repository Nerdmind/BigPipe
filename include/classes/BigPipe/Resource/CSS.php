<?php
namespace BigPipe\Resource;

class CSS extends \BigPipe\Resource {

	#===============================================================================
	# Build resource
	#===============================================================================
	public function __construct($resourceURL) {
		parent::__construct(parent::TYPE_STYLESHEET, $resourceURL);
	}

	#===============================================================================
	# Render resource HTML
	#===============================================================================
	public function renderHTML() {
		return sprintf('<link data-id="%s" href="%s" rel="stylesheet" />', $this->getID(), $this->getURL());
	}
}
?>