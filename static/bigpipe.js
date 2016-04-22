//==============================================================================
// Revealing Module Pattern
//==============================================================================
var BigPipe = (function() {
	//==============================================================================
	// Resource: Represents a single CSS or JS resource
	//==============================================================================
	function Resource(resourceURL, type) {
		this.resourceURL = resourceURL;
		this.callbacks = [];
		this.done = false;
		this.type = type;
	}

	//==============================================================================
	// Resource: Loading the resource
	//==============================================================================
	Resource.prototype.start = function() {
		if(this.type === 0) {
			var element = document.createElement('link');
			element.setAttribute('rel', 'stylesheet');
			element.setAttribute('href', this.resourceURL);
		}

		else {
			var element = document.createElement('script');
			element.setAttribute('src', this.resourceURL);
			element.async = true;
		}

		document.head.appendChild(element);

		element.onload = function() {
			this.executeCallbacks();
		}.bind(this);

		element.onerror = function() {
			this.executeCallbacks();
		}.bind(this);
	};

	//==============================================================================
	// Resource: Register a new callback
	//==============================================================================
	Resource.prototype.registerCallback = function(callback) {
		return this.callbacks.push(callback);
	};

	//==============================================================================
	// Resource: Executes all registered callbacks
	//==============================================================================
	Resource.prototype.executeCallbacks = function() {
		if(!this.done) {
			this.done = true;

			this.callbacks.forEach(function(callback) {
				callback();
			});
		}
	};

	//==============================================================================
	// Pagelet: Represents a single pagelet
	//==============================================================================
	function Pagelet(data, HTML) {
		this.pageletID = data.ID;
		this.HTML      = HTML || "";
		this.CSSFiles  = data.RESOURCES.CSS;
		this.JSFiles   = data.RESOURCES.JS;
		this.JSCode    = data.RESOURCES.JS_CODE;

		this.phase = 0;
		this.CSSResources = [];
		this.JSResources  = [];

		this.phaseDoneJS = data.PHASES;
	}

	//==============================================================================
	// Pagelet: Increases phase and executes PhaseDoneJS
	//==============================================================================
	Pagelet.prototype.phaseDoneHandler = function(phase) {
		for(var currentPhase = this.phase; currentPhase <= phase; ++currentPhase) {
			this.executePhaseDoneJS(currentPhase);
		}

		return (this.phase = ++phase);
	};

	//==============================================================================
	// Pagelet: Executes the callbacks of the specific phase
	//==============================================================================
	Pagelet.prototype.executePhaseDoneJS = function(phase) {
		this.phaseDoneJS[phase].forEach(function(code) {
			try {
				globalExecution(code);
			} catch(e) {
				console.error("PhaseDoneJS: " + e);
			}
		});
	};

	//==============================================================================
	// Pagelet: Initialize and start the CSS resources
	//==============================================================================
	Pagelet.prototype.start = function() {
		var isStarted = false;

		this.CSSFiles.forEach(function(resourceURL) {
			this.attachCSSResource(new Resource(resourceURL, 0));
		}.bind(this));

		this.JSFiles.forEach(function(resourceURL) {
			this.attachJSResource(new Resource(resourceURL, 1));
		}.bind(this));

		this.CSSResources.forEach(function(resource) {
			isStarted = true;
			resource.start();
		}.bind(this));

		// If no CSS resource was started (= no external CSS resources exists), then begin to inject the HTML
		!isStarted && this.injectHTML();
	};

	//==============================================================================
	// Pagelet: Attach a new CSS resource to the pagelet
	//==============================================================================
	Pagelet.prototype.attachCSSResource = function(resource) {
		resource.registerCallback(this.onloadCSS.bind(this));
		return this.CSSResources.push(resource);
	};

	//==============================================================================
	// Pagelet: Attach a new JS resource to the pagelet
	//==============================================================================
	Pagelet.prototype.attachJSResource = function(resource) {
		resource.registerCallback(this.onloadJS.bind(this));
		return this.JSResources.push(resource);
	};

	//==============================================================================
	// Pagelet: Executes the main JS code of the pagelet
	//==============================================================================
	Pagelet.prototype.executeJSCode = function() {
		try {
			globalExecution(this.JSCode);
		} catch(e) {
			console.error(this.pageletID + ": " + e);
		}
	};

	//==============================================================================
	// Pagelet: Get each time called if a single JS resource has been loaded
	//==============================================================================
	Pagelet.prototype.onloadJS = function() {
		if(this.phase === 3 && this.JSResources.every(function(resource){
			return resource.done;
		})) {
			this.phaseDoneHandler(3);
			this.executeJSCode();
			this.phaseDoneHandler(4);
		}
	};

	//==============================================================================
	// Pagelet: Get each time called if a single CSS resource has been loaded
	//==============================================================================
	Pagelet.prototype.onloadCSS = function() {
		if(this.CSSResources.every(function(resource){
			return resource.done;
		})) {
			this.injectHTML();
		}
	};

	//==============================================================================
	// Pagelet: Injects the HTML content into the DOM
	//==============================================================================
	Pagelet.prototype.injectHTML = function() {
		this.phaseDoneHandler(1);

		document.getElementById(this.pageletID).innerHTML = this.HTML;

		this.phaseDoneHandler(2);

		BigPipe.executeNextPagelet();

		// Check if this was the last pagelet and then start loading of the external JS resources
		if(BigPipe.phase === 2 && BigPipe.pagelets[BigPipe.pagelets.length - 1].pageletID === this.pageletID) {
			BigPipe.loadJSResources();
		}
	};

	//==============================================================================
	// BigPipe
	//==============================================================================
	var BigPipe = {
		pagelets:  [],
		phase: 0,
		offset: 0,

		executeNextPagelet: function() {
			if(this.pagelets[this.offset]) {
				this.pagelets[this.offset++].start();
			}

			else if(this.phase < 2) {
				setTimeout(this.executeNextPagelet.bind(this), 20);
			}
		},

		onPageletArrive: function(data, codeContainer) {
			var pageletHTML = codeContainer.innerHTML;
			pageletHTML = pageletHTML.substring(5, pageletHTML.length - 4);
			codeContainer.parentNode.removeChild(codeContainer);

			var pagelet = new Pagelet(data, pageletHTML);
			pagelet.phaseDoneHandler(0);

			if(this.pagelets.push(pagelet) && this.phase === 0 && !data.IS_LAST) {
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

	//==============================================================================
	// Public-Access
	//==============================================================================
	return {
		onPageletArrive: function(data, codeContainer) {
			BigPipe.onPageletArrive(data, codeContainer);
		},

		reset: function() {
			BigPipe.pagelets = [];
			BigPipe.offset = 0;
			BigPipe.phase = 0;
		}
	};
})();