//==============================================================================
// Revealing Module Pattern
//==============================================================================
var BigPipe = (function() {

	//==============================================================================
	// PhaseDoneJS object; responsible for Pagelet and Resource
	//==============================================================================
	var PhaseDoneJS = {
		//==============================================================================
		// PhaseDoneJS: Increases phase and executes callbacks
		//==============================================================================
		handler: function(context, phase) {
			for(var currentPhase = context.phase; currentPhase <= phase; ++currentPhase) {
				this.execute(context, currentPhase);
			}

			return context.phase = ++phase;
		},

		//==============================================================================
		// PhaseDoneJS: Executes the callbacks of the given phase
		//==============================================================================
		execute: function(context, phase) {
			context.phaseDoneJS[phase].forEach(function(code) {
				try {
					globalExecution(code);
				} catch(e) {
					console.error("PhaseDoneJS: " + e);
				}
			});
		}
	};

	//==============================================================================
	// Resource: Represents a single CSS or JS resource
	//==============================================================================
	function Resource(dataJSON, type, pageletID) {
		this.ID   = dataJSON.ID;
		this.HREF = dataJSON.HREF;
		this.pageletID = pageletID;
		this.callbacks = [];
		this.node = false;
		this.done = false;
		this.type = type;

		this.phaseDoneJS = dataJSON.PHASE;
		this.phase = 0;

		PhaseDoneJS.handler(this, Resource.PHASE_INIT);
	}

	//==============================================================================
	// Resource: Resource types
	//==============================================================================
	Resource.TYPE_STYLESHEET = 0;
	Resource.TYPE_JAVASCRIPT = 1;

	//==============================================================================
	// Resource: Phase numbers for PhaseDoneJS
	//==============================================================================
	Resource.PHASE_INIT = 0;
	Resource.PHASE_LOAD = 1;
	Resource.PHASE_DONE = 2;

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
		if(!this.done && (this.done = true)) {
			this.callbacks.forEach(function(callback) {
				callback();
			});
		}
	};

	//==============================================================================
	// Resource: Loading the resource
	//==============================================================================
	Resource.prototype.execute = function() {
		switch(this.type) {
			case Resource.TYPE_STYLESHEET:
				this.node = document.createElement("link");
				this.node.setAttribute("rel", "stylesheet");
				this.node.setAttribute("href", this.HREF);
				break;
			case Resource.TYPE_JAVASCRIPT:
				this.node = document.createElement("script");
				this.node.setAttribute("src", this.HREF);
				this.node.setAttribute("async", true);
				break;
			default:
				return false;
		}

		var callback = function() {
			PhaseDoneJS.handler(this, Resource.PHASE_DONE);
			this.executeCallbacks();
		}.bind(this);

		this.node.onload  = callback;
		this.node.onerror = callback;

		document.head.appendChild(this.node);

		PhaseDoneJS.handler(this, Resource.PHASE_LOAD);
	};

	//==============================================================================
	// Resource: Remove callbacks after abort of loading the resource
	//==============================================================================
	Resource.prototype.abortLoading = function() {
		if(this.node) {
			this.node.onload  = null;
			this.node.onerror = null;

			// Remove element from DOM
			var parentNode = this.node.parentNode;
			return parentNode.removeChild(this.node);
		}
	};

	//==============================================================================
	// Pagelet: Represents a single pagelet
	//==============================================================================
	function Pagelet(dataJSON, HTML) {
		this.ID   = dataJSON.ID;
		this.NEED = dataJSON.NEED;
		this.HTML = HTML;
		this.JSCode = dataJSON.CODE;
		this.phaseDoneJS = dataJSON.PHASE;
		this.stylesheets = dataJSON.RSRC[Resource.TYPE_STYLESHEET];
		this.javascripts = dataJSON.RSRC[Resource.TYPE_JAVASCRIPT];

		this.phase = 0;
		this.resources = [[], []];

		PhaseDoneJS.handler(this, Pagelet.PHASE_INIT);
	}

	//==============================================================================
	// Pagelet: Phase numbers for PhaseDoneJS
	//==============================================================================
	Pagelet.PHASE_INIT    = 0;
	Pagelet.PHASE_LOADCSS = 1;
	Pagelet.PHASE_HTML    = 2;
	Pagelet.PHASE_LOADJS  = 3;
	Pagelet.PHASE_DONE    = 4;

	//==============================================================================
	// Pagelet: Initialize the pagelet resources
	//==============================================================================
	Pagelet.prototype.initializeResources = function() {
		this.stylesheets.forEach(function(data) {
			this.attachResource(new Resource(data, Resource.TYPE_STYLESHEET, this.ID));
		}.bind(this));

		this.javascripts.forEach(function(data) {
			this.attachResource(new Resource(data, Resource.TYPE_JAVASCRIPT, this.ID));
		}.bind(this));
	};

	//==============================================================================
	// Pagelet: Executes all resources of the specific type
	//==============================================================================
	Pagelet.prototype.executeResources = function(type) {
		var somethingExecuted = false;

		this.resources[type].forEach(function(resource) {
			somethingExecuted = true;
			resource.execute();
		}.bind(this));

		return somethingExecuted;
	};

	//==============================================================================
	// Pagelet: Initialize and execute the CSS resources
	//==============================================================================
	Pagelet.prototype.execute = function() {
		this.initializeResources();

		if(!this.executeResources(Resource.TYPE_STYLESHEET)) {
			this.replaceHTML();
		}
	};

	//==============================================================================
	// Pagelet: Attach a new resource to the pagelet
	//==============================================================================
	Pagelet.prototype.attachResource = function(resource) {
		switch(resource.type) {
			case Resource.TYPE_STYLESHEET:
				resource.registerCallback(this.onloadCSS.bind(this));
				break;

			case Resource.TYPE_JAVASCRIPT:
				resource.registerCallback(this.onloadJS.bind(this));
				break;
		}

		return this.resources[resource.type].push(resource);
	}

	//==============================================================================
	// Pagelet: Executes the main JS code of the pagelet
	//==============================================================================
	Pagelet.prototype.executeJSCode = function() {
		this.JSCode.forEach(function(code) {
			try {
				globalExecution(code);
			} catch(e) {
				console.error(this.ID + ": " + e);
			}
		});
		PhaseDoneJS.handler(this, Pagelet.PHASE_DONE);
	};

	//==============================================================================
	// Pagelet: Get each time called if a single JS resource has been loaded
	//==============================================================================
	Pagelet.prototype.onloadJS = function() {
		if(this.phase === 3 && this.resources[Resource.TYPE_JAVASCRIPT].every(function(resource){
				return resource.done;
			})) {
			PhaseDoneJS.handler(this, Pagelet.PHASE_LOADJS);
			this.executeJSCode();
		}
	};

	//==============================================================================
	// Pagelet: Get each time called if a single CSS resource has been loaded
	//==============================================================================
	Pagelet.prototype.onloadCSS = function() {
		if(this.resources[Resource.TYPE_STYLESHEET].every(function(resource){
				return resource.done;
			})) {
			PhaseDoneJS.handler(this, Pagelet.PHASE_LOADCSS);
			this.replaceHTML();
		}
	};

	//==============================================================================
	// Pagelet: Replaces the placeholder node HTML
	//==============================================================================
	Pagelet.prototype.replaceHTML = function() {
		document.getElementById(this.ID).innerHTML = this.HTML;

		PhaseDoneJS.handler(this, Pagelet.PHASE_HTML);

		BigPipe.pageletHTMLreplaced(this.ID);
	};

	//==============================================================================
	// BigPipe
	//==============================================================================
	var BigPipe = {
		pagelets: [],
		phase: 0,
		done: [],
		wait: [],

		onPageletArrive: function(data, codeContainer) {
			var pageletHTML = codeContainer.innerHTML;
			pageletHTML = pageletHTML.substring(5, pageletHTML.length - 4);
			codeContainer.parentNode.removeChild(codeContainer);

			var pagelet = new Pagelet(data, pageletHTML);

			this.pagelets.push(pagelet);

			if(this.phase === 0) {
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
			if(BigPipe.phase === 2 && BigPipe.done.length === BigPipe.pagelets.length) {
				BigPipe.executeJSResources();
			}
		},

		executeJSResources: function() {
			this.phase = 3;

			this.pagelets.forEach(function(pagelet) {
				if(!pagelet.executeResources(Resource.TYPE_JAVASCRIPT)) {
					pagelet.onloadJS();
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
				pagelet.resources[Resource.TYPE_STYLESHEET].forEach(function(resource) {
					resource.abortLoading();
				});

				pagelet.resources[Resource.TYPE_JAVASCRIPT].forEach(function(resource) {
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