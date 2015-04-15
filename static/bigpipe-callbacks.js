// Folgende Phasen stehen zur Auswahl: PAGELET_STARTED, RESOURCE_DONE, PAGELET_HTML_RENDERED, PAGELET_JS_EXECUTED und BIGPIPE_PAGELETS_RENDERED

var debugLine = "------------------------------------------------------------------------------------------------------------------------";

BigPipe.registerPhaseDoneCallback('PAGELET_STARTED', function(Pagelet) {
	console.log(debugLine);
	console.log(Pagelet.pageletID + ":\t" + "Die Ausführung des Pagelets wurde gestartet.");
});

BigPipe.registerPhaseDoneCallback('RESOURCE_DONE', function(Resource) {
	console.log(Resource.pageletID + ":\t" + 'Die Ressource ' + Resource.file + ' wurde geladen.');
});

BigPipe.registerPhaseDoneCallback('PAGELET_HTML_RENDERED', function(Pagelet) {
	console.log(Pagelet.pageletID + ":\t" + 'Die Platzhalter wurden mit HTML-Code befüllt.');
});

BigPipe.registerPhaseDoneCallback('BIGPIPE_PAGELETS_RENDERED', function() {
	console.log(debugLine);
	console.log('BP' + ":\t" + 'Die Platzhalter von allen Pagelets wurden mit ihrem HTML-Code befüllt.');
	console.log(debugLine);
});

BigPipe.registerPhaseDoneCallback('PAGELET_JS_EXECUTED', function(Pagelet) {
	console.log(Pagelet.pageletID + ":\t" + 'Der zugehörige Javascript-Code des Pagelets wurde ausgeführt.');
	console.log(debugLine);
});