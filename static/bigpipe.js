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
		this.node = false;
		this.done = false;
		this.type = type;
	}

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
	// Resource: Loading the resource
	//==============================================================================
	Resource.prototype.execute = function() {
		if(this.type === 0) {
			this.node = document.createElement('link');
			this.node.setAttribute('rel', 'stylesheet');
			this.node.setAttribute('href', this.resourceURL);
		}

		else {
			this.node = document.createElement('script');
			this.node.setAttribute('src', this.resourceURL);
			this.node.async = true;
		}

		this.node.setAttribute('class', 'bigpipe');

		document.head.appendChild(this.node);

		this.node.onload = function() {
			this.executeCallbacks();
		}.bind(this);

		this.node.onerror = function() {
			this.executeCallbacks();
		}.bind(this);
	};

	//==============================================================================
	// Resource: Remove callbacks after abort of loading the resource
	//==============================================================================
	Resource.prototype.abortLoading = function() {
		if(this.node) {
			this.node.onload  = function(){};
			this.node.onerror = function(){};

			// Remove element from DOM
			var parentNode = this.node.parentNode;
			return parentNode.removeChild(this.node);
		}
	};

	//==============================================================================
	// Pagelet: Represents a single pagelet
	//==============================================================================
	function Pagelet(data, HTML) {
		this.pageletID = data.ID;
		this.HTML      = HTML;
		this.CSSFiles  = data.RESOURCES.CSS;
		this.JSFiles   = data.RESOURCES.JS;
		this.JSCode    = data.RESOURCES.JS_CODE;
		this.NEED      = data.NEED;

		this.phase = 0;
		this.CSSResources = [];
		this.JSResources  = [];

		this.phaseDoneJS = data.PHASES;

		this.phaseDoneHandler(0);
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
	// Pagelet: Initialize and execute the CSS resources
	//==============================================================================
	Pagelet.prototype.execute = function() {
		var isStarted = false;

		this.CSSFiles.forEach(function(resourceURL) {
			this.attachCSSResource(new Resource(resourceURL, 0));
		}.bind(this));

		this.JSFiles.forEach(function(resourceURL) {
			this.attachJSResource(new Resource(resourceURL, 1));
		}.bind(this));

		this.CSSResources.forEach(function(resource) {
			isStarted = true;
			resource.execute();
		}.bind(this));

		// If no CSS resource was started (= no external CSS resources exists), then begin to inject the HTML
		!isStarted && this.replaceHTML();
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
		this.JSCode.forEach(function(code) {
			try {
				globalExecution(code);
			} catch(e) {
				console.error(this.pageletID + ": " + e);
			}
		});
		this.phaseDoneHandler(4);
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
		}
	};

	//==============================================================================
	// Pagelet: Get each time called if a single CSS resource has been loaded
	//==============================================================================
	Pagelet.prototype.onloadCSS = function() {
		if(this.CSSResources.every(function(resource){
				return resource.done;
			})) {
			this.phaseDoneHandler(1);
			this.replaceHTML();
		}
	};

	//==============================================================================
	// Pagelet: Injects the HTML content into the DOM
	//==============================================================================
	Pagelet.prototype.replaceHTML = function() {
		document.getElementById(this.pageletID).innerHTML = this.HTML;

		this.phaseDoneHandler(2);

		BigPipe.pageletHTMLreplaced(this.pageletID);
	};

	//==============================================================================
	// BigPipe
	//==============================================================================
	var BigPipe = {
		pagelets:  [],
		phase: 0,
		done: [],
		wait: [],

		onPageletArrive: function(data, codeContainer) {
			var pageletHTML = codeContainer.innerHTML;
			pageletHTML = pageletHTML.substring(5, pageletHTML.length - 4);
			codeContainer.parentNode.removeChild(codeContainer);

			var pagelet = new Pagelet(data, pageletHTML);

			this.pagelets.push(pagelet);

			if(this.phase = 0) {
				this.phase = 1;
			}

			if(data.IS_LAST) {
				this.phase = 2;
			}

			if(pagelet.NEED.length === 0 || pagelet.NEED.every(function(needID) {
					return BigPipe.done.indexOf(needID) !== -1;
				})) {
				pagelet.execute();
			}

			else {
				this.wait.push(pagelet);
			}
		},

		pageletHTMLreplaced: function(pageletID) {
			BigPipe.done.push(pageletID);

			for(var i = 0; i < this.wait.length; ++i) {
				var pagelet = this.wait[i];

				// Check if all IDs from NEED exists within BigPipe.done
				// If this is true, then all required dependencies are satisfied.
				if(pagelet.NEED.every(function(needID){
						return BigPipe.done.indexOf(needID) !== -1;
					})) {
					BigPipe.wait.splice(i--, 1); // remove THIS pagelet from wait list
					pagelet.execute();
				}
			}

			// Check if this was the last pagelet and then execute loading of the external JS resources
			if(BigPipe.phase === 2 && BigPipe.done.length === BigPipe.pagelets.length ) {
				BigPipe.executeJSResources();
			}
		},

		executeJSResources: function() {
			this.phase = 3;

			this.pagelets.forEach(function(pagelet) {
				if(pagelet.JSResources.length === 0) {
					pagelet.onloadJS();
				}

				else {
					pagelet.JSResources.forEach(function(resource) {
						resource.execute();
					});
				}
			});
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
			BigPipe.pagelets.forEach(function(pagelet) {
				pagelet.CSSResources.concat(pagelet.JSResources).forEach(function(resource) {
					resource.abortLoading();
				});
			});

			window.stop() || document.execCommand("Stop");

			BigPipe.pagelets = [];
			BigPipe.phase = 0;
			BigPipe.wait = [];
			BigPipe.done = [];
		}
	};
})();