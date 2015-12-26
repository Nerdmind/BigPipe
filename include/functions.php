<?php
#====================================================================================================
# FUNCTION: Removes all line breaks and tabs from a string or an array with strings
#====================================================================================================
function removeLineBreaksAndTabs($mixed, $replace = NULL) {
	if(is_array($mixed)) {
		return array_map(__FUNCTION__, $mixed);
	}

	return is_string($mixed) ? str_replace(["\r\n", "\r", "\n", "\t"], $replace, $mixed) : $mixed;
}
?>