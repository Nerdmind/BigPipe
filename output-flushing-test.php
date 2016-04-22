<?php
header('Content-Type: text/plain; charset=UTF-8');

echo "[INFO: If you see the single blocks successively, then the output flushing on your server works. If not, and you see it all at once, your server need to be configured to use output flushing.]\n\n";

for($i = 1; $i <= 8; $i++) {
	echo '[BLOCK: '.$i.']'."\n".str_repeat('[0]', 1500)."\n\n\n";
	flush(); usleep(750000);
}

echo '[ALL BLOCKS LOADED]';
?>