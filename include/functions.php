<?php
#===============================================================================
# Remove line breaks and tabs from a string
#===============================================================================
function removeLineBreaksAndTabs($string) {
	return str_replace(["\r", "\n", "\t"], NULL, $string);
}
?>