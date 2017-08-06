<?php
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
# Resource representation [javascript]       [Thomas Lange <code@nerdmind.de>] #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#                                                                              #
# [More information coming soon]                                               #
#                                                                              #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
namespace BigPipe\Resource;

class Javascript extends \BigPipe\Resource {

	#===============================================================================
	# Build resource
	#===============================================================================
	public function __construct($customID = NULL, $resourceURL) {
		parent::__construct($customID, parent::TYPE_JAVASCRIPT, $resourceURL);
	}

	#===============================================================================
	# Render resource HTML
	#===============================================================================
	public function renderHTML() {
		return sprintf('<script data-id="%s" src="%s"></script>', $this->getID(), $this->getURL());
	}
}
?>