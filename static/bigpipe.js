//===================================================================================================
// REVEALING MODLUE PATTERN: BigPipe
//===================================================================================================
var BigPipe = (function() {
	//===================================================================================================
	// PROTOTYPE: PageletResource-Konstruktor
	//===================================================================================================
	function PageletResource(file, type, pageletID) {
		this.pageletID = pageletID;
		this.callbacks = [];
		this.done = false;
		this.file = file;
		this.type = type;
	}

	//===================================================================================================
	// PROTOTYPE: Startet den Ladevorgang der Ressource
	//===================================================================================================
	PageletResource.prototype.start = function() {
		if(this.type === 0) {
			var element = document.createElement('link');
			element.setAttribute('rel', 'stylesheet');
			element.setAttribute('href', this.file);
		}

		else {
			var element = document.createElement('script');
			element.setAttribute('src', this.file);
			element.async = true;
		}

		document.head.appendChild(element);

		element.onload = function() {
			BigPipe.executePhaseDoneCallbacks('RESOURCE_DONE', this);
			this.executeCallbacks();
		}.bind(this);

		element.onerror = function() {
			BigPipe.executePhaseDoneCallbacks('RESOURCE_DONE', this);
			this.executeCallbacks();
		}.bind(this);
	};

	//===================================================================================================
	// PROTOTYPE: Registriert eine Callback-Funktion
	//===================================================================================================
	PageletResource.prototype.registerCallback = function(callback) {
		return this.callbacks.push(callback);
	};

	//===================================================================================================
	// PROTOTYPE: Führt alle registrierten Callback-Funktionen aus
	//===================================================================================================
	PageletResource.prototype.executeCallbacks = function() {
		if(!this.done) {
			this.done = true;

			this.callbacks.forEach(function(callback) {
				callback();
			});
		}
	};

	//===================================================================================================
	// PROTOTYPE: Pagelet-Konstruktor
	//===================================================================================================
	function Pagelet(data) {
		this.pageletID = data.ID;
		this.HTML      = data.HTML || "";
		this.CSSFiles  = data.RESOURCES.CSS;
		this.JSFiles   = data.RESOURCES.JS;
		this.JSCode    = data.RESOURCES.JS_CODE;

		this.phase = 0; // 1 => Laden von CSS-Ressourcen, 2 => CSS-Ressourcen geladen, 3 => HTML wurde injiziert, 4 => JS-Ressourcen geladen und JS-Code ausgeführt
		this.CSSResources = [];
		this.JSResources  = [];
	}

	//===================================================================================================
	// PROTOTYPE: Startet die Initialisierung des Pagelets und startet die Pagelet-Ressourcen
	//===================================================================================================
	Pagelet.prototype.start = function() {
		BigPipe.executePhaseDoneCallbacks('PAGELET_STARTED', this);
		this.CSSFiles.forEach(function(file) {
			this.attachCSSResource(new PageletResource(file, 0, this.pageletID));
		}.bind(this));

		this.JSFiles.forEach(function(file) {
			this.attachJSResource(new PageletResource(file, 1, this.pageletID));
		}.bind(this));

		this.CSSResources.forEach(function(resource) {
			this.phase = 1;
			resource.start();
		}.bind(this));

		if(this.phase === 0) {
			this.injectHTML();
		}
	};

	//===================================================================================================
	// PROTOTYPE: Fügt eine CSS-Ressource hinzu
	//===================================================================================================
	Pagelet.prototype.attachCSSResource = function(resource) {
		resource.registerCallback(this.onloadCSS.bind(this));
		return this.CSSResources.push(resource);
	};

	//===================================================================================================
	// PROTOTYPE: Fügt eine JS-Ressource hinzu
	//===================================================================================================
	Pagelet.prototype.attachJSResource = function(resource) {
		resource.registerCallback(this.onloadJS.bind(this));
		return this.JSResources.push(resource);
	};

	//===================================================================================================
	// PROTOTYPE: Führt den statischen JS-Code des Pagelets aus
	//===================================================================================================
	Pagelet.prototype.executeJSCode = function() {
		try {
			globalExecution(this.JSCode);
			BigPipe.executePhaseDoneCallbacks('PAGELET_JS_EXECUTED', this);
		} catch(e) {
			console.error(this.pageletID + ":\t" + e);
		}
	};

	//===================================================================================================
	// PROTOTYPE: Pagelet-Methode
	//===================================================================================================
	Pagelet.prototype.onloadJS = function() {
		if(this.phase === 3 && this.JSResources.every(function(resource){
			return resource.done;
		})) {
			this.executeJSCode();
			this.phase = 4;
		}
	};

	//===================================================================================================
	// PROTOTYPE: Pagelet-Methode
	//===================================================================================================
	Pagelet.prototype.onloadCSS = function() {
		if(this.CSSResources.every(function(resource){
			return resource.done;
		})) {
			this.injectHTML();
		}
	};

	//===================================================================================================
	// PROTOTYPE: Injiziert den HTML-Code des Pagelets in den DOM
	//===================================================================================================
	Pagelet.prototype.injectHTML = function() {
		this.phase = 2;
		if(placeholder = document.getElementById(this.pageletID)) {
			if(this.HTML) {
				placeholder.innerHTML = this.HTML;
			}

			else {
				var content = document.getElementById('_' + this.pageletID);
				placeholder.innerHTML = content.innerHTML.substring(5, content.innerHTML.length - 4);
				document.body.removeChild(content);
			}
		}

		this.phase = 3;

		BigPipe.executePhaseDoneCallbacks('PAGELET_HTML_RENDERED', this);
		BigPipe.executeNextPagelet();

		if(BigPipe.phase === 2 && BigPipe.pagelets[BigPipe.pagelets.length - 1].pageletID === this.pageletID) {
			BigPipe.executePhaseDoneCallbacks('BIGPIPE_PAGELETS_RENDERED');
			BigPipe.loadJSResources();
		}
	};

	//===================================================================================================
	// BigPipe-Hauptobjekt
	//===================================================================================================
	var BigPipe = {
		pagelets:  [],
		phase: 0, // 1 => Erstes Pagelet gestartet, 2 => Alle Pagelets angekommen, 3 => JS-Ressourcen geladen + JS-Code ausgeführt
		offset: 0,
		phaseDoneCallbacks: {},

		executeNextPagelet: function() {
			if(this.pagelets[this.offset]) {
				this.pagelets[this.offset++].start();
			}

			else if(this.phase < 2) {
				setTimeout(this.executeNextPagelet.bind(this), 30);
			}
		},

		registerPhaseDoneCallback: function(phase, callback) {
			if(!this.phaseDoneCallbacks[phase]) {
				this.phaseDoneCallbacks[phase] = [];
			}
			return this.phaseDoneCallbacks[phase].push(callback);
		},

		executePhaseDoneCallbacks: function(phase, param) {
			if(this.phaseDoneCallbacks[phase]) {
				this.phaseDoneCallbacks[phase].forEach(function(callback) {
					callback(param);
				});
			}
		},

		onPageletArrive: function(data) {
			if(this.pagelets.push(new Pagelet(data)) && this.phase === 0 && !data.IS_LAST) {
				this.phase = 1;
				this.executeNextPagelet();
			}

			else if(data.IS_LAST) {
				this.phase = 2;
				if(this.pagelets.length === 1) {
					this.executeNextPagelet();
				}
			}
		},

		loadJSResources: function() {
			this.phase = 3;
			var isLoading = false;

			this.pagelets.forEach(function(Pagelet) {
				if(Pagelet.JSResources.length === 0) {
					Pagelet.onloadJS();
				}
			});

			this.pagelets.forEach(function(Pagelet) {
				Pagelet.JSResources.forEach(function(Resource) {
					Resource.start();
					isLoading = true;
				});
			});

			if(!isLoading) {
				this.pagelets.forEach(function(Pagelet) {
					Pagelet.onloadJS();
				});
			}
		}
	};

	//===================================================================================================
	// Public-Access
	//===================================================================================================
	return {
		onPageletArrive: function(data) {
			BigPipe.onPageletArrive(data);
		},

		registerPhaseDoneCallback: function(phase, callback) {
			BigPipe.registerPhaseDoneCallback(phase, callback);
		},

		reset: function() {
			BigPipe.phaseDoneCallbacks = {};
			BigPipe.pagelets = [];
			BigPipe.offset = 0;
			BigPipe.phase = 0;
		}
	};
})();