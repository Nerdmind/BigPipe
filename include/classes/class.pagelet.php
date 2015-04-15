<?php
class Pagelet {
	private $ID           = NULL;
	private $HTML         = NULL;
	private $JSCode       = "";
	private $CSSFiles     = [];
	private $JSFiles      = [];
	private static $count = 0;

	public function __construct($priority = 50) {
		$this->ID = 'P'.++self::$count;
		BigPipe::addPagelet($this, $priority);
	}

	#====================================================================================================
	# ID zurückgeben
	#====================================================================================================
	public function getID() {
		return $this->ID;
	}

	#====================================================================================================
	# HTML-Code zurückgeben
	#====================================================================================================
	public function getHTML() {
		return $this->HTML;
	}

	#====================================================================================================
	# CSS-Ressourcen zurückgeben
	#====================================================================================================
	public function getCSSFiles() {
		return $this->CSSFiles;
	}

	#====================================================================================================
	# JS-Ressourcen zurückgeben
	#====================================================================================================
	public function getJSFiles() {
		return $this->JSFiles;
	}

	#====================================================================================================
	# JS-Code zurückgeben
	#====================================================================================================
	public function getJSCode() {
		return $this->JSCode;
	}

	#====================================================================================================
	# HTML-Code hinzufügen
	#====================================================================================================
	public function addHTML($HTML) {
		$this->HTML .= $HTML;
	}

	#====================================================================================================
	# CSS-Ressource hinzufügen
	#====================================================================================================
	public function addCSS($file) {
		$this->CSSFiles[] = $file;
	}

	#====================================================================================================
	# JS-Ressource hinzufügen
	#====================================================================================================
	public function addJS($file) {
		$this->JSFiles[] = $file;
	}

	#====================================================================================================
	# JS-Code hinzufügen
	#====================================================================================================
	public function addJSCode($code) {
		$this->JSCode .= $code;
	}

	#====================================================================================================
	# Magische Methode: __toString()
	#====================================================================================================
	public function __toString() {
		return '<div id="'.$this->getID().'">'.((!BigPipe::isEnabled()) ? $this->getHTML() : NULL).'</div>';
	}
}