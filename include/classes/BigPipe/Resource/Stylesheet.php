<?php
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
# Resource representation [stylesheet]       [Thomas Lange <code@nerdmind.de>] #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#                                                                              #
# [More information coming soon]                                               #
#                                                                              #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
namespace BigPipe\Resource;

class Stylesheet extends \BigPipe\Resource {

	#===============================================================================
	# Build resource
	#===============================================================================
	public function __construct(?string $id, string $url) {
		parent::__construct($id, parent::TYPE_STYLESHEET, $url);
	}

	#===============================================================================
	# Render resource HTML
	#===============================================================================
	public function renderHTML(): string {
		return sprintf('<link data-id="%s" href="%s" rel="stylesheet" />', $this->getID(), $this->getURL());
	}
}
