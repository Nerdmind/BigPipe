<?php
use BigPipe\BigPipe;
use BigPipe\Pagelet;

class Document {
	private $contentCallbacks = [];
	private $pagelets = [];

	public function addPagelet(Pagelet $Pagelet, callable $callback) {
		$this->pagelets[] = $Pagelet;

		$this->contentCallbacks[$Pagelet->getID()] = $callback;

		if(!BigPipe::enabled()) {
			$Pagelet->addHTML($callback($Pagelet));
		}
	}

	public function render($content_html, $sidebar_html) {
		require 'template/document.php';
		BigPipe::flushOutputBuffer();

		if(BigPipe::enabled()) {
			foreach($this->pagelets as $Pagelet) {
				$Pagelet->addHTML($this->contentCallbacks[$Pagelet->getID()]($Pagelet));
				$Pagelet->flush();
			}
		}

		BigPipe::completeResponse();
		echo "</body>\n</html>";
	}
}
?>