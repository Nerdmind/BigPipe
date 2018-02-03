//==============================================================================
// BigPipe Module
//==============================================================================
BigPipe = (function() {
	"use strict";

	//==============================================================================
	// PhaseDoneJS: Responsible for Pagelet and Resource
	//==============================================================================
	const PhaseDoneJS = {
		//==============================================================================
		// Increase phase and execute callbacks
		//==============================================================================
		handler(context, phase) {
			for(let currentPhase = context.phase; currentPhase <= phase; ++currentPhase) {
				this.execute(context, currentPhase);
			}

			return context.phase = ++phase;
		},

		//==============================================================================
		// Execute callbacks of the given phase
		//==============================================================================
		execute(context, phase) {
			context.phaseDoneJS[phase].forEach(function(code) {
				try {
					window.eval.call(window, code);
				} catch(e) {
					console.error("PhaseDoneJS: " + e);
				}
			});
		}
	};

	//==============================================================================
	// Resource: Represents a resource
	//==============================================================================
	class Resource {
		constructor(data, type) {
			this.ID   = data.ID;
			this.HREF = data.HREF;
			this.callbacks = [];
			this.node = false;
			this.done = false;
			this.type = type;

			this.phaseDoneJS = data.PHASE;
			this.phase = 0;

			PhaseDoneJS.handler(this, Resource.PHASE_INIT);
		}

		//==============================================================================
		// Resource types
		//==============================================================================
		static get TYPE_STYLESHEET() { return 0; }
		static get TYPE_JAVASCRIPT() { return 1; }

		//==============================================================================
		// Phase numbers for PhaseDoneJS
		//==============================================================================
		static get PHASE_INIT() { return 0; }
		static get PHASE_LOAD() { return 1; }
		static get PHASE_DONE() { return 2; }

		//==============================================================================
		// Loading the resource
		//==============================================================================
		execute() {
			switch(this.type) {
				case Resource.TYPE_STYLESHEET:
					this.node = document.createElement("link");
					this.node.setAttribute("rel", "stylesheet");
					this.node.setAttribute("href", this.HREF);
					break;
				case Resource.TYPE_JAVASCRIPT:
					this.node = document.createElement("script");
					this.node.setAttribute("src", this.HREF);
					this.node.setAttribute("async", "async");
					break;
				default:
					return false;
			}

			const callback = () => {
				PhaseDoneJS.handler(this, Resource.PHASE_DONE);
				this.executeCallbacks();
			};

			this.node.onload  = callback;
			this.node.onerror = callback;

			document.head.appendChild(this.node);

			PhaseDoneJS.handler(this, Resource.PHASE_LOAD);
		}

		//==============================================================================
		// Register a new callback
		//==============================================================================
		registerCallback(callback) {
			return this.callbacks.push(callback);
		}

		//==============================================================================
		// Executes all registered callbacks
		//==============================================================================
		executeCallbacks() {
			if(!this.done && (this.done = true)) {
				this.callbacks.forEach(function(callback) {
					callback();
				});
			}
		}

		//==============================================================================
		// Remove callbacks after abort of loading the resource
		//==============================================================================
		abortLoading() {
			if(this.node) {
				this.node.onload  = null;
				this.node.onerror = null;

				// Remove element from DOM
				let parentNode = this.node.parentNode;
				return parentNode.removeChild(this.node);
			}
		}
	}

	//==============================================================================
	// Pagelet: Represents a pagelet
	//==============================================================================
	class Pagelet {
		constructor(data, HTML) {
			this.ID   = data.ID;
			this.NEED = data.NEED;
			this.HTML = HTML;
			this.JSCode = data.CODE;
			this.phaseDoneJS = data.PHASE;
			this.stylesheets = data.RSRC[Resource.TYPE_STYLESHEET];
			this.javascripts = data.RSRC[Resource.TYPE_JAVASCRIPT];

			this.phase = 0;
			this.resources = [[], []];

			PhaseDoneJS.handler(this, Pagelet.PHASE_INIT);
		}

		//==============================================================================
		// Phase numbers for PhaseDoneJS
		//==============================================================================
		static get PHASE_INIT()    { return 0; }
		static get PHASE_LOADCSS() { return 1; }
		static get PHASE_HTML()    { return 2; }
		static get PHASE_LOADJS()  { return 3; }
		static get PHASE_DONE()    { return 4; }

		//==============================================================================
		// Initialize and execute the CSS resources
		//==============================================================================
		execute() {
			this.initializeResources();

			if(!this.executeResources(Resource.TYPE_STYLESHEET)) {
				this.replaceHTML();
			}
		}

		//==============================================================================
		// Initialize the pagelet resources
		//==============================================================================
		initializeResources() {
			this.stylesheets.forEach(data => {
				this.attachResource(new Resource(data, Resource.TYPE_STYLESHEET));
			});

			this.javascripts.forEach(data => {
				this.attachResource(new Resource(data, Resource.TYPE_JAVASCRIPT));
			});
		}

		//==============================================================================
		// Executes all resources of the specific type
		//==============================================================================
		executeResources(type) {
			let somethingExecuted = false;

			this.resources[type].forEach(function(resource) {
				somethingExecuted = true;
				resource.execute();
			});

			return somethingExecuted;
		}

		//==============================================================================
		// Attach a new resource to the pagelet
		//==============================================================================
		attachResource(resource) {
			switch(resource.type) {
				case Resource.TYPE_STYLESHEET:
					resource.registerCallback(() => this.onStylesheetLoaded());
					break;

				case Resource.TYPE_JAVASCRIPT:
					resource.registerCallback(() => this.onJavascriptLoaded());
					break;
			}

			return this.resources[resource.type].push(resource);
		}

		//==============================================================================
		// Replaces the placeholder node HTML
		//==============================================================================
		replaceHTML() {
			document.getElementById(this.ID).innerHTML = this.HTML;

			PhaseDoneJS.handler(this, Pagelet.PHASE_HTML);

			BigPipe.onPageletHTMLreplaced(this.ID);
		}

		//==============================================================================
		// Executes the inline javascript code of the pagelet
		//==============================================================================
		executeInlineJavascript() {
			this.JSCode.forEach(code => {
				try {
					window.eval.call(window, code);
				} catch(e) {
					console.error(this.ID + ": " + e);
				}
			});
			PhaseDoneJS.handler(this, Pagelet.PHASE_DONE);
		}

		//==============================================================================
		// Executed each time when a stylesheet resource has been loaded
		//==============================================================================
		onStylesheetLoaded() {
			if(this.resources[Resource.TYPE_STYLESHEET].every(function(resource){
					return resource.done;
				})) {
				PhaseDoneJS.handler(this, Pagelet.PHASE_LOADCSS);
				this.replaceHTML();
			}
		}

		//==============================================================================
		// Executed each time when a javascript resource has been loaded
		//==============================================================================
		onJavascriptLoaded() {
			if(this.resources[Resource.TYPE_JAVASCRIPT].every(function(resource){
					return resource.done;
				})) {
				PhaseDoneJS.handler(this, Pagelet.PHASE_LOADJS);
				this.executeInlineJavascript();
			}
		}
	}

	//==============================================================================
	// BigPipe
	//==============================================================================
	const BigPipe = {
		pagelets: [],
		phase: 0,
		done: [],
		wait: [],
		interval: null,

		onPageletArrive(data, codeContainer) {
			let pageletHTML = codeContainer.innerHTML;
			pageletHTML = pageletHTML.substring(5, pageletHTML.length - 4);
			codeContainer.parentNode.removeChild(codeContainer);

			let pagelet = new Pagelet(data, pageletHTML);

			this.pagelets.push(pagelet);

			if(this.phase === 0) {
				this.phase = 1;
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

		onLastPageletArrived() {
			this.phase = 2;

			this.interval = setInterval(() => {
				if(this.done.length === this.pagelets.length) {
					clearInterval(this.interval);
					this.executeJavascriptResources();
				}
			}, 50);
		},

		onPageletHTMLreplaced(pageletID) {
			BigPipe.done.push(pageletID);

			for(let i = 0; i < this.wait.length; ++i) {
				let pagelet = this.wait[i];

				// Check if all IDs from NEED exists within BigPipe.done
				// If this is true, then all required dependencies are satisfied.
				if(pagelet.NEED.every(function(needID){
						return BigPipe.done.indexOf(needID) !== -1;
					})) {
					BigPipe.wait.splice(i--, 1); // remove THIS pagelet from wait list
					pagelet.execute();
				}
			}
		},

		executeJavascriptResources() {
			this.phase = 3;

			this.pagelets.forEach(function(pagelet) {
				if(!pagelet.executeResources(Resource.TYPE_JAVASCRIPT)) {
					pagelet.onJavascriptLoaded();
				}
			});
		}
	};

	//==============================================================================
	// Public-Access
	//==============================================================================
	return {
		onPageletArrive(data, codeContainer) {
			BigPipe.onPageletArrive(data, codeContainer);
		},

		onLastPageletArrived() {
			BigPipe.onLastPageletArrived();
		},

		reset() {
			BigPipe.pagelets.forEach(function(pagelet) {
				pagelet.resources[Resource.TYPE_STYLESHEET].forEach(function(resource) {
					resource.abortLoading();
				});

				pagelet.resources[Resource.TYPE_JAVASCRIPT].forEach(function(resource) {
					resource.abortLoading();
				});
			});

			try {
				window.stop();
			} catch(e) {
				document.execCommand('Stop');
			}

			clearInterval(BigPipe.interval);

			BigPipe.pagelets = [];
			BigPipe.phase = 0;
			BigPipe.wait = [];
			BigPipe.done = [];
		}
	};
})();