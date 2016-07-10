<?php
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
# Resource representation class [CSS]        [Thomas Lange <code@nerdmind.de>] #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#                                                                              #
# [More information coming soon]                                               #
#                                                                              #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
namespace BigPipe\Resource;

class CSS extends \BigPipe\Resource {

	#===============================================================================
	# Build resource
	#===============================================================================
	public function __construct($customID = NULL, $resourceURL) {
		parent::__construct($customID, parent::TYPE_STYLESHEET, $resourceURL);
	}

	#===============================================================================
	# Render resource HTML
	#===============================================================================
	public function renderHTML() {
		return sprintf('<link data-id="%s" href="%s" rel="stylesheet" />', $this->getID(), $this->getURL());
	}
}
?>