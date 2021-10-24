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
	public function __construct(string $id, string $url) {
		parent::__construct($id, parent::TYPE_JAVASCRIPT, $url);
	}

	#===============================================================================
	# Render resource HTML
	#===============================================================================
	public function renderHTML(): string {
		return sprintf('<script data-id="%s" src="%s"></script>', $this->getID(), $this->getURL());
	}
}
