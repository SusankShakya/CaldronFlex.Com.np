var PusherPushNotifications = (function (exports) {
  'use strict';

  function _arrayLikeToArray$1(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }

  function _arrayWithoutHoles(r) {
    if (Array.isArray(r)) return _arrayLikeToArray$1(r);
  }

  function _iterableToArray(r) {
    if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r);
  }

  function _unsupportedIterableToArray$1(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray$1(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray$1(r, a) : void 0;
    }
  }

  function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }

  function _toConsumableArray(r) {
    return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray$1(r) || _nonIterableSpread();
  }

  function _typeof$1(o) {
    "@babel/helpers - typeof";

    return _typeof$1 = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
      return typeof o;
    } : function (o) {
      return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
    }, _typeof$1(o);
  }

  function toPrimitive(t, r) {
    if ("object" != _typeof$1(t) || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != _typeof$1(i)) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return (String )(t);
  }

  function toPropertyKey(t) {
    var i = toPrimitive(t, "string");
    return "symbol" == _typeof$1(i) ? i : i + "";
  }

  function _defineProperty(e, r, t) {
    return (r = toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: !0,
      configurable: !0,
      writable: !0
    }) : e[r] = t, e;
  }

  function asyncGeneratorStep(n, t, e, r, o, a, c) {
    try {
      var i = n[a](c),
        u = i.value;
    } catch (n) {
      return void e(n);
    }
    i.done ? t(u) : Promise.resolve(u).then(r, o);
  }
  function _asyncToGenerator(n) {
    return function () {
      var t = this,
        e = arguments;
      return new Promise(function (r, o) {
        var a = n.apply(t, e);
        function _next(n) {
          asyncGeneratorStep(a, r, o, _next, _throw, "next", n);
        }
        function _throw(n) {
          asyncGeneratorStep(a, r, o, _next, _throw, "throw", n);
        }
        _next(void 0);
      });
    };
  }

  function _classCallCheck(a, n) {
    if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function");
  }

  function _defineProperties(e, r) {
    for (var t = 0; t < r.length; t++) {
      var o = r[t];
      o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, toPropertyKey(o.key), o);
    }
  }
  function _createClass(e, r, t) {
    return r && _defineProperties(e.prototype, r), Object.defineProperty(e, "prototype", {
      writable: !1
    }), e;
  }

  function getDefaultExportFromCjs (x) {
  	return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, 'default') ? x['default'] : x;
  }

  var regeneratorRuntime$1 = {exports: {}};

  var _typeof = {exports: {}};

  var hasRequired_typeof;

  function require_typeof () {
  	if (hasRequired_typeof) return _typeof.exports;
  	hasRequired_typeof = 1;
  	(function (module) {
  		function _typeof(o) {
  		  "@babel/helpers - typeof";

  		  return module.exports = _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
  		    return typeof o;
  		  } : function (o) {
  		    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  		  }, module.exports.__esModule = true, module.exports["default"] = module.exports, _typeof(o);
  		}
  		module.exports = _typeof, module.exports.__esModule = true, module.exports["default"] = module.exports; 
  	} (_typeof));
  	return _typeof.exports;
  }

  var hasRequiredRegeneratorRuntime;

  function requireRegeneratorRuntime () {
  	if (hasRequiredRegeneratorRuntime) return regeneratorRuntime$1.exports;
  	hasRequiredRegeneratorRuntime = 1;
  	(function (module) {
  		var _typeof = require_typeof()["default"];
  		function _regeneratorRuntime() {
  		  module.exports = _regeneratorRuntime = function _regeneratorRuntime() {
  		    return e;
  		  }, module.exports.__esModule = true, module.exports["default"] = module.exports;
  		  var t,
  		    e = {},
  		    r = Object.prototype,
  		    n = r.hasOwnProperty,
  		    o = Object.defineProperty || function (t, e, r) {
  		      t[e] = r.value;
  		    },
  		    i = "function" == typeof Symbol ? Symbol : {},
  		    a = i.iterator || "@@iterator",
  		    c = i.asyncIterator || "@@asyncIterator",
  		    u = i.toStringTag || "@@toStringTag";
  		  function define(t, e, r) {
  		    return Object.defineProperty(t, e, {
  		      value: r,
  		      enumerable: !0,
  		      configurable: !0,
  		      writable: !0
  		    }), t[e];
  		  }
  		  try {
  		    define({}, "");
  		  } catch (t) {
  		    define = function define(t, e, r) {
  		      return t[e] = r;
  		    };
  		  }
  		  function wrap(t, e, r, n) {
  		    var i = e && e.prototype instanceof Generator ? e : Generator,
  		      a = Object.create(i.prototype),
  		      c = new Context(n || []);
  		    return o(a, "_invoke", {
  		      value: makeInvokeMethod(t, r, c)
  		    }), a;
  		  }
  		  function tryCatch(t, e, r) {
  		    try {
  		      return {
  		        type: "normal",
  		        arg: t.call(e, r)
  		      };
  		    } catch (t) {
  		      return {
  		        type: "throw",
  		        arg: t
  		      };
  		    }
  		  }
  		  e.wrap = wrap;
  		  var h = "suspendedStart",
  		    l = "suspendedYield",
  		    f = "executing",
  		    s = "completed",
  		    y = {};
  		  function Generator() {}
  		  function GeneratorFunction() {}
  		  function GeneratorFunctionPrototype() {}
  		  var p = {};
  		  define(p, a, function () {
  		    return this;
  		  });
  		  var d = Object.getPrototypeOf,
  		    v = d && d(d(values([])));
  		  v && v !== r && n.call(v, a) && (p = v);
  		  var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p);
  		  function defineIteratorMethods(t) {
  		    ["next", "throw", "return"].forEach(function (e) {
  		      define(t, e, function (t) {
  		        return this._invoke(e, t);
  		      });
  		    });
  		  }
  		  function AsyncIterator(t, e) {
  		    function invoke(r, o, i, a) {
  		      var c = tryCatch(t[r], t, o);
  		      if ("throw" !== c.type) {
  		        var u = c.arg,
  		          h = u.value;
  		        return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) {
  		          invoke("next", t, i, a);
  		        }, function (t) {
  		          invoke("throw", t, i, a);
  		        }) : e.resolve(h).then(function (t) {
  		          u.value = t, i(u);
  		        }, function (t) {
  		          return invoke("throw", t, i, a);
  		        });
  		      }
  		      a(c.arg);
  		    }
  		    var r;
  		    o(this, "_invoke", {
  		      value: function value(t, n) {
  		        function callInvokeWithMethodAndArg() {
  		          return new e(function (e, r) {
  		            invoke(t, n, e, r);
  		          });
  		        }
  		        return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg();
  		      }
  		    });
  		  }
  		  function makeInvokeMethod(e, r, n) {
  		    var o = h;
  		    return function (i, a) {
  		      if (o === f) throw Error("Generator is already running");
  		      if (o === s) {
  		        if ("throw" === i) throw a;
  		        return {
  		          value: t,
  		          done: !0
  		        };
  		      }
  		      for (n.method = i, n.arg = a;;) {
  		        var c = n.delegate;
  		        if (c) {
  		          var u = maybeInvokeDelegate(c, n);
  		          if (u) {
  		            if (u === y) continue;
  		            return u;
  		          }
  		        }
  		        if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) {
  		          if (o === h) throw o = s, n.arg;
  		          n.dispatchException(n.arg);
  		        } else "return" === n.method && n.abrupt("return", n.arg);
  		        o = f;
  		        var p = tryCatch(e, r, n);
  		        if ("normal" === p.type) {
  		          if (o = n.done ? s : l, p.arg === y) continue;
  		          return {
  		            value: p.arg,
  		            done: n.done
  		          };
  		        }
  		        "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg);
  		      }
  		    };
  		  }
  		  function maybeInvokeDelegate(e, r) {
  		    var n = r.method,
  		      o = e.iterator[n];
  		    if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y;
  		    var i = tryCatch(o, e.iterator, r.arg);
  		    if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y;
  		    var a = i.arg;
  		    return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y);
  		  }
  		  function pushTryEntry(t) {
  		    var e = {
  		      tryLoc: t[0]
  		    };
  		    1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e);
  		  }
  		  function resetTryEntry(t) {
  		    var e = t.completion || {};
  		    e.type = "normal", delete e.arg, t.completion = e;
  		  }
  		  function Context(t) {
  		    this.tryEntries = [{
  		      tryLoc: "root"
  		    }], t.forEach(pushTryEntry, this), this.reset(!0);
  		  }
  		  function values(e) {
  		    if (e || "" === e) {
  		      var r = e[a];
  		      if (r) return r.call(e);
  		      if ("function" == typeof e.next) return e;
  		      if (!isNaN(e.length)) {
  		        var o = -1,
  		          i = function next() {
  		            for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next;
  		            return next.value = t, next.done = !0, next;
  		          };
  		        return i.next = i;
  		      }
  		    }
  		    throw new TypeError(_typeof(e) + " is not iterable");
  		  }
  		  return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", {
  		    value: GeneratorFunctionPrototype,
  		    configurable: !0
  		  }), o(GeneratorFunctionPrototype, "constructor", {
  		    value: GeneratorFunction,
  		    configurable: !0
  		  }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) {
  		    var e = "function" == typeof t && t.constructor;
  		    return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name));
  		  }, e.mark = function (t) {
  		    return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t;
  		  }, e.awrap = function (t) {
  		    return {
  		      __await: t
  		    };
  		  }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () {
  		    return this;
  		  }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) {
  		    void 0 === i && (i = Promise);
  		    var a = new AsyncIterator(wrap(t, r, n, o), i);
  		    return e.isGeneratorFunction(r) ? a : a.next().then(function (t) {
  		      return t.done ? t.value : a.next();
  		    });
  		  }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () {
  		    return this;
  		  }), define(g, "toString", function () {
  		    return "[object Generator]";
  		  }), e.keys = function (t) {
  		    var e = Object(t),
  		      r = [];
  		    for (var n in e) r.push(n);
  		    return r.reverse(), function next() {
  		      for (; r.length;) {
  		        var t = r.pop();
  		        if (t in e) return next.value = t, next.done = !1, next;
  		      }
  		      return next.done = !0, next;
  		    };
  		  }, e.values = values, Context.prototype = {
  		    constructor: Context,
  		    reset: function reset(e) {
  		      if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t);
  		    },
  		    stop: function stop() {
  		      this.done = !0;
  		      var t = this.tryEntries[0].completion;
  		      if ("throw" === t.type) throw t.arg;
  		      return this.rval;
  		    },
  		    dispatchException: function dispatchException(e) {
  		      if (this.done) throw e;
  		      var r = this;
  		      function handle(n, o) {
  		        return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o;
  		      }
  		      for (var o = this.tryEntries.length - 1; o >= 0; --o) {
  		        var i = this.tryEntries[o],
  		          a = i.completion;
  		        if ("root" === i.tryLoc) return handle("end");
  		        if (i.tryLoc <= this.prev) {
  		          var c = n.call(i, "catchLoc"),
  		            u = n.call(i, "finallyLoc");
  		          if (c && u) {
  		            if (this.prev < i.catchLoc) return handle(i.catchLoc, !0);
  		            if (this.prev < i.finallyLoc) return handle(i.finallyLoc);
  		          } else if (c) {
  		            if (this.prev < i.catchLoc) return handle(i.catchLoc, !0);
  		          } else {
  		            if (!u) throw Error("try statement without catch or finally");
  		            if (this.prev < i.finallyLoc) return handle(i.finallyLoc);
  		          }
  		        }
  		      }
  		    },
  		    abrupt: function abrupt(t, e) {
  		      for (var r = this.tryEntries.length - 1; r >= 0; --r) {
  		        var o = this.tryEntries[r];
  		        if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) {
  		          var i = o;
  		          break;
  		        }
  		      }
  		      i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null);
  		      var a = i ? i.completion : {};
  		      return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a);
  		    },
  		    complete: function complete(t, e) {
  		      if ("throw" === t.type) throw t.arg;
  		      return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y;
  		    },
  		    finish: function finish(t) {
  		      for (var e = this.tryEntries.length - 1; e >= 0; --e) {
  		        var r = this.tryEntries[e];
  		        if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y;
  		      }
  		    },
  		    "catch": function _catch(t) {
  		      for (var e = this.tryEntries.length - 1; e >= 0; --e) {
  		        var r = this.tryEntries[e];
  		        if (r.tryLoc === t) {
  		          var n = r.completion;
  		          if ("throw" === n.type) {
  		            var o = n.arg;
  		            resetTryEntry(r);
  		          }
  		          return o;
  		        }
  		      }
  		      throw Error("illegal catch attempt");
  		    },
  		    delegateYield: function delegateYield(e, r, n) {
  		      return this.delegate = {
  		        iterator: values(e),
  		        resultName: r,
  		        nextLoc: n
  		      }, "next" === this.method && (this.arg = t), y;
  		    }
  		  }, e;
  		}
  		module.exports = _regeneratorRuntime, module.exports.__esModule = true, module.exports["default"] = module.exports; 
  	} (regeneratorRuntime$1));
  	return regeneratorRuntime$1.exports;
  }

  var regenerator;
  var hasRequiredRegenerator;

  function requireRegenerator () {
  	if (hasRequiredRegenerator) return regenerator;
  	hasRequiredRegenerator = 1;
  	// TODO(Babel 8): Remove this file.

  	var runtime = requireRegeneratorRuntime()();
  	regenerator = runtime;

  	// Copied from https://github.com/facebook/regenerator/blob/main/packages/runtime/runtime.js#L736=
  	try {
  	  regeneratorRuntime = runtime;
  	} catch (accidentalStrictMode) {
  	  if (typeof globalThis === "object") {
  	    globalThis.regeneratorRuntime = runtime;
  	  } else {
  	    Function("r", "regeneratorRuntime = r")(runtime);
  	  }
  	}
  	return regenerator;
  }

  var regeneratorExports = requireRegenerator();
  var _regeneratorRuntime = /*@__PURE__*/getDefaultExportFromCjs(regeneratorExports);

  function ownKeys$2(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
  function _objectSpread$2(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys$2(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys$2(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
  function doRequest(_ref) {
    var method = _ref.method,
      path = _ref.path,
      _ref$params = _ref.params,
      params = _ref$params === void 0 ? {} : _ref$params,
      _ref$body = _ref.body,
      body = _ref$body === void 0 ? null : _ref$body,
      _ref$headers = _ref.headers,
      headers = _ref$headers === void 0 ? {} : _ref$headers,
      _ref$credentials = _ref.credentials,
      credentials = _ref$credentials === void 0 ? 'same-origin' : _ref$credentials;
    var options = {
      method: method,
      headers: headers,
      credentials: credentials
    };
    if (!emptyParams(params)) {
      // check for empty params obj
      path += '?';
      path += Object.entries(params).filter(function (x) {
        return x[1];
      }).map(function (pair) {
        return pair.map(function (x) {
          return encodeURIComponent(x);
        }).join('=');
      }).join('&');
    }
    if (body !== null) {
      options.body = JSON.stringify(body);
      options.headers = _objectSpread$2({
        'Content-Type': 'application/json'
      }, headers);
    }
    return fetch(path, options).then(/*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee(response) {
        return _regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              if (response.ok) {
                _context.next = 3;
                break;
              }
              _context.next = 3;
              return handleError(response);
            case 3:
              _context.prev = 3;
              _context.next = 6;
              return response.json();
            case 6:
              return _context.abrupt("return", _context.sent);
            case 9:
              _context.prev = 9;
              _context.t0 = _context["catch"](3);
              return _context.abrupt("return", null);
            case 12:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[3, 9]]);
      }));
      return function (_x) {
        return _ref2.apply(this, arguments);
      };
    }());
  }
  function emptyParams(params) {
    for (var i in params) return false;
    return true;
  }
  function handleError(_x2) {
    return _handleError.apply(this, arguments);
  }
  function _handleError() {
    _handleError = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee2(response) {
      var errorMessage, _yield$response$json, _yield$response$json$, error, _yield$response$json$2, description;
      return _regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            _context2.prev = 0;
            _context2.next = 3;
            return response.json();
          case 3:
            _yield$response$json = _context2.sent;
            _yield$response$json$ = _yield$response$json.error;
            error = _yield$response$json$ === void 0 ? 'Unknown error' : _yield$response$json$;
            _yield$response$json$2 = _yield$response$json.description;
            description = _yield$response$json$2 === void 0 ? 'No description' : _yield$response$json$2;
            errorMessage = "Unexpected status code ".concat(response.status, ": ").concat(error, ", ").concat(description);
            _context2.next = 14;
            break;
          case 11:
            _context2.prev = 11;
            _context2.t0 = _context2["catch"](0);
            errorMessage = "Unexpected status code ".concat(response.status, ": Cannot parse error response");
          case 14:
            throw new Error(errorMessage);
          case 15:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[0, 11]]);
    }));
    return _handleError.apply(this, arguments);
  }

  function ownKeys$1(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
  function _objectSpread$1(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys$1(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys$1(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
  var TokenProvider = /*#__PURE__*/function () {
    function TokenProvider() {
      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
        url = _ref.url,
        queryParams = _ref.queryParams,
        headers = _ref.headers,
        credentials = _ref.credentials;
      _classCallCheck(this, TokenProvider);
      this.url = url;
      this.queryParams = queryParams;
      this.headers = headers;
      this.credentials = credentials;
    }
    return _createClass(TokenProvider, [{
      key: "fetchToken",
      value: function () {
        var _fetchToken = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee(userId) {
          var queryParams, encodedParams, options, response;
          return _regeneratorRuntime.wrap(function _callee$(_context) {
            while (1) switch (_context.prev = _context.next) {
              case 0:
                queryParams = _objectSpread$1({
                  user_id: userId
                }, this.queryParams);
                encodedParams = Object.entries(queryParams).map(function (kv) {
                  return kv.map(encodeURIComponent).join('=');
                }).join('&');
                options = {
                  method: 'GET',
                  path: "".concat(this.url, "?").concat(encodedParams),
                  headers: this.headers,
                  credentials: this.credentials
                };
                _context.next = 5;
                return doRequest(options);
              case 5:
                response = _context.sent;
                return _context.abrupt("return", response);
              case 7:
              case "end":
                return _context.stop();
            }
          }, _callee, this);
        }));
        function fetchToken(_x) {
          return _fetchToken.apply(this, arguments);
        }
        return fetchToken;
      }()
    }]);
  }();

  var DeviceStateStore = /*#__PURE__*/function () {
    function DeviceStateStore(instanceId) {
      _classCallCheck(this, DeviceStateStore);
      this._instanceId = instanceId;
      this._dbConn = null;
    }
    return _createClass(DeviceStateStore, [{
      key: "_dbName",
      get: function get() {
        return "beams-".concat(this._instanceId);
      }
    }, {
      key: "isConnected",
      get: function get() {
        return this._dbConn !== null;
      }
    }, {
      key: "connect",
      value: function connect() {
        var _this = this;
        return new Promise(function (resolve, reject) {
          var request = indexedDB.open(_this._dbName);
          request.onsuccess = function (event) {
            var db = event.target.result;
            _this._dbConn = db;
            _this._readState().then(function (state) {
              return state === null ? _this.clear() : Promise.resolve();
            }).then(resolve);
          };
          request.onupgradeneeded = function (event) {
            var db = event.target.result;
            db.createObjectStore('beams', {
              keyPath: 'instance_id'
            });
          };
          request.onerror = function (event) {
            var error = new Error("Database error: ".concat(event.target.error));
            reject(error);
          };
        });
      }
    }, {
      key: "clear",
      value: function clear() {
        return this._writeState({
          instance_id: this._instanceId,
          device_id: null,
          token: null,
          user_id: null
        });
      }
    }, {
      key: "_readState",
      value: function _readState() {
        var _this2 = this;
        if (!this.isConnected) {
          throw new Error('Cannot read value: DeviceStateStore not connected to IndexedDB');
        }
        return new Promise(function (resolve, reject) {
          var request = _this2._dbConn.transaction('beams').objectStore('beams').get(_this2._instanceId);
          request.onsuccess = function (event) {
            var state = event.target.result;
            if (!state) {
              resolve(null);
            }
            resolve(state);
          };
          request.onerror = function (event) {
            reject(event.target.error);
          };
        });
      }
    }, {
      key: "_readProperty",
      value: function () {
        var _readProperty2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee(name) {
          var state;
          return _regeneratorRuntime.wrap(function _callee$(_context) {
            while (1) switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return this._readState();
              case 2:
                state = _context.sent;
                if (!(state === null)) {
                  _context.next = 5;
                  break;
                }
                return _context.abrupt("return", null);
              case 5:
                return _context.abrupt("return", state[name] || null);
              case 6:
              case "end":
                return _context.stop();
            }
          }, _callee, this);
        }));
        function _readProperty(_x) {
          return _readProperty2.apply(this, arguments);
        }
        return _readProperty;
      }()
    }, {
      key: "_writeState",
      value: function _writeState(state) {
        var _this3 = this;
        if (!this.isConnected) {
          throw new Error('Cannot write value: DeviceStateStore not connected to IndexedDB');
        }
        return new Promise(function (resolve, reject) {
          var request = _this3._dbConn.transaction('beams', 'readwrite').objectStore('beams').put(state);
          request.onsuccess = function (_) {
            resolve();
          };
          request.onerror = function (event) {
            reject(event.target.error);
          };
        });
      }
    }, {
      key: "_writeProperty",
      value: function () {
        var _writeProperty2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee2(name, value) {
          var state;
          return _regeneratorRuntime.wrap(function _callee2$(_context2) {
            while (1) switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return this._readState();
              case 2:
                state = _context2.sent;
                state[name] = value;
                _context2.next = 6;
                return this._writeState(state);
              case 6:
              case "end":
                return _context2.stop();
            }
          }, _callee2, this);
        }));
        function _writeProperty(_x2, _x3) {
          return _writeProperty2.apply(this, arguments);
        }
        return _writeProperty;
      }()
    }, {
      key: "getToken",
      value: function getToken() {
        return this._readProperty('token');
      }
    }, {
      key: "setToken",
      value: function setToken(token) {
        return this._writeProperty('token', token);
      }
    }, {
      key: "getDeviceId",
      value: function getDeviceId() {
        return this._readProperty('device_id');
      }
    }, {
      key: "setDeviceId",
      value: function setDeviceId(deviceId) {
        return this._writeProperty('device_id', deviceId);
      }
    }, {
      key: "getUserId",
      value: function getUserId() {
        return this._readProperty('user_id');
      }
    }, {
      key: "setUserId",
      value: function setUserId(userId) {
        return this._writeProperty('user_id', userId);
      }
    }, {
      key: "getLastSeenSdkVersion",
      value: function getLastSeenSdkVersion() {
        return this._readProperty('last_seen_sdk_version');
      }
    }, {
      key: "setLastSeenSdkVersion",
      value: function setLastSeenSdkVersion(sdkVersion) {
        return this._writeProperty('last_seen_sdk_version', sdkVersion);
      }
    }, {
      key: "getLastSeenUserAgent",
      value: function getLastSeenUserAgent() {
        return this._readProperty('last_seen_user_agent');
      }
    }, {
      key: "setLastSeenUserAgent",
      value: function setLastSeenUserAgent(userAgent) {
        return this._writeProperty('last_seen_user_agent', userAgent);
      }
    }]);
  }();

  var version = "2.1.0";

  function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
  function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
  function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
  function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
  function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
  var INTERESTS_REGEX = new RegExp('^(_|\\-|=|@|,|\\.|;|[A-Z]|[a-z]|[0-9])*$');
  var MAX_INTEREST_LENGTH = 164;
  var MAX_INTERESTS_NUM = 5000;
  var SERVICE_WORKER_URL = "/?pusherBeamsWebSDKVersion=".concat(version);
  var RegistrationState = Object.freeze({
    PERMISSION_PROMPT_REQUIRED: 'PERMISSION_PROMPT_REQUIRED',
    PERMISSION_GRANTED_NOT_REGISTERED_WITH_BEAMS: 'PERMISSION_GRANTED_NOT_REGISTERED_WITH_BEAMS',
    PERMISSION_GRANTED_REGISTERED_WITH_BEAMS: 'PERMISSION_GRANTED_REGISTERED_WITH_BEAMS',
    PERMISSION_DENIED: 'PERMISSION_DENIED'
  });
  var Client = /*#__PURE__*/function () {
    function Client(config) {
      _classCallCheck(this, Client);
      if (!config) {
        throw new Error('Config object required');
      }
      var instanceId = config.instanceId,
        _config$endpointOverr = config.endpointOverride,
        endpointOverride = _config$endpointOverr === void 0 ? null : _config$endpointOverr,
        _config$serviceWorker = config.serviceWorkerRegistration,
        serviceWorkerRegistration = _config$serviceWorker === void 0 ? null : _config$serviceWorker;
      if (instanceId === undefined) {
        throw new Error('Instance ID is required');
      }
      if (typeof instanceId !== 'string') {
        throw new Error('Instance ID must be a string');
      }
      if (instanceId.length === 0) {
        throw new Error('Instance ID cannot be empty');
      }
      if (!('indexedDB' in window)) {
        throw new Error('Pusher Beams does not support this browser version (IndexedDB not supported)');
      }
      if (!window.isSecureContext) {
        throw new Error('Pusher Beams relies on Service Workers, which only work in secure contexts. Check that your page is being served from localhost/over HTTPS');
      }
      if (!('serviceWorker' in navigator)) {
        throw new Error('Pusher Beams does not support this browser version (Service Workers not supported)');
      }
      if (!('PushManager' in window)) {
        throw new Error('Pusher Beams does not support this browser version (Web Push not supported)');
      }
      if (serviceWorkerRegistration) {
        var serviceWorkerScope = serviceWorkerRegistration.scope;
        var currentURL = window.location.href;
        var scopeMatchesCurrentPage = currentURL.startsWith(serviceWorkerScope);
        if (!scopeMatchesCurrentPage) {
          throw new Error("Could not initialize Pusher web push: current page not in serviceWorkerRegistration scope (".concat(serviceWorkerScope, ")"));
        }
      }
      this.instanceId = instanceId;
      this._deviceId = null;
      this._token = null;
      this._userId = null;
      this._serviceWorkerRegistration = serviceWorkerRegistration;
      this._deviceStateStore = new DeviceStateStore(instanceId);
      this._endpoint = endpointOverride; // Internal only

      this._ready = this._init();
    }
    return _createClass(Client, [{
      key: "_init",
      value: function () {
        var _init2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee() {
          return _regeneratorRuntime.wrap(function _callee$(_context) {
            while (1) switch (_context.prev = _context.next) {
              case 0:
                if (!(this._deviceId !== null)) {
                  _context.next = 2;
                  break;
                }
                return _context.abrupt("return");
              case 2:
                _context.next = 4;
                return this._deviceStateStore.connect();
              case 4:
                if (!this._serviceWorkerRegistration) {
                  _context.next = 9;
                  break;
                }
                _context.next = 7;
                return window.navigator.serviceWorker.ready;
              case 7:
                _context.next = 12;
                break;
              case 9:
                _context.next = 11;
                return getServiceWorkerRegistration();
              case 11:
                this._serviceWorkerRegistration = _context.sent;
              case 12:
                _context.next = 14;
                return this._detectSubscriptionChange();
              case 14:
                _context.next = 16;
                return this._deviceStateStore.getDeviceId();
              case 16:
                this._deviceId = _context.sent;
                _context.next = 19;
                return this._deviceStateStore.getToken();
              case 19:
                this._token = _context.sent;
                _context.next = 22;
                return this._deviceStateStore.getUserId();
              case 22:
                this._userId = _context.sent;
              case 23:
              case "end":
                return _context.stop();
            }
          }, _callee, this);
        }));
        function _init() {
          return _init2.apply(this, arguments);
        }
        return _init;
      }() // Ensure SDK is loaded and is consistent
    }, {
      key: "_resolveSDKState",
      value: function () {
        var _resolveSDKState2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee2() {
          return _regeneratorRuntime.wrap(function _callee2$(_context2) {
            while (1) switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return this._ready;
              case 2:
                _context2.next = 4;
                return this._detectSubscriptionChange();
              case 4:
              case "end":
                return _context2.stop();
            }
          }, _callee2, this);
        }));
        function _resolveSDKState() {
          return _resolveSDKState2.apply(this, arguments);
        }
        return _resolveSDKState;
      }()
    }, {
      key: "_detectSubscriptionChange",
      value: function () {
        var _detectSubscriptionChange2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee3() {
          var storedToken, actualToken, pushTokenHasChanged;
          return _regeneratorRuntime.wrap(function _callee3$(_context3) {
            while (1) switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return this._deviceStateStore.getToken();
              case 2:
                storedToken = _context3.sent;
                _context3.next = 5;
                return getWebPushToken(this._serviceWorkerRegistration);
              case 5:
                actualToken = _context3.sent;
                pushTokenHasChanged = storedToken !== actualToken;
                if (!pushTokenHasChanged) {
                  _context3.next = 13;
                  break;
                }
                _context3.next = 10;
                return this._deviceStateStore.clear();
              case 10:
                this._deviceId = null;
                this._token = null;
                this._userId = null;
              case 13:
              case "end":
                return _context3.stop();
            }
          }, _callee3, this);
        }));
        function _detectSubscriptionChange() {
          return _detectSubscriptionChange2.apply(this, arguments);
        }
        return _detectSubscriptionChange;
      }()
    }, {
      key: "getDeviceId",
      value: function () {
        var _getDeviceId = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee4() {
          var _this = this;
          return _regeneratorRuntime.wrap(function _callee4$(_context4) {
            while (1) switch (_context4.prev = _context4.next) {
              case 0:
                _context4.next = 2;
                return this._resolveSDKState();
              case 2:
                return _context4.abrupt("return", this._ready.then(function () {
                  return _this._deviceId;
                }));
              case 3:
              case "end":
                return _context4.stop();
            }
          }, _callee4, this);
        }));
        function getDeviceId() {
          return _getDeviceId.apply(this, arguments);
        }
        return getDeviceId;
      }()
    }, {
      key: "getToken",
      value: function () {
        var _getToken = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee5() {
          var _this2 = this;
          return _regeneratorRuntime.wrap(function _callee5$(_context5) {
            while (1) switch (_context5.prev = _context5.next) {
              case 0:
                _context5.next = 2;
                return this._resolveSDKState();
              case 2:
                return _context5.abrupt("return", this._ready.then(function () {
                  return _this2._token;
                }));
              case 3:
              case "end":
                return _context5.stop();
            }
          }, _callee5, this);
        }));
        function getToken() {
          return _getToken.apply(this, arguments);
        }
        return getToken;
      }()
    }, {
      key: "getUserId",
      value: function () {
        var _getUserId = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee6() {
          var _this3 = this;
          return _regeneratorRuntime.wrap(function _callee6$(_context6) {
            while (1) switch (_context6.prev = _context6.next) {
              case 0:
                _context6.next = 2;
                return this._resolveSDKState();
              case 2:
                return _context6.abrupt("return", this._ready.then(function () {
                  return _this3._userId;
                }));
              case 3:
              case "end":
                return _context6.stop();
            }
          }, _callee6, this);
        }));
        function getUserId() {
          return _getUserId.apply(this, arguments);
        }
        return getUserId;
      }()
    }, {
      key: "_baseURL",
      get: function get() {
        if (this._endpoint !== null) {
          return this._endpoint;
        }
        return "https://".concat(this.instanceId, ".pushnotifications.pusher.com");
      }
    }, {
      key: "_throwIfNotStarted",
      value: function _throwIfNotStarted(message) {
        if (!this._deviceId) {
          throw new Error("".concat(message, ". SDK not registered with Beams. Did you call .start?"));
        }
      }
    }, {
      key: "start",
      value: function () {
        var _start = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee7() {
          var _yield$this$_getPubli, publicKey, token, deviceId;
          return _regeneratorRuntime.wrap(function _callee7$(_context7) {
            while (1) switch (_context7.prev = _context7.next) {
              case 0:
                _context7.next = 2;
                return this._resolveSDKState();
              case 2:
                if (isWebPushSupported()) {
                  _context7.next = 4;
                  break;
                }
                return _context7.abrupt("return", this);
              case 4:
                if (!(this._deviceId !== null)) {
                  _context7.next = 6;
                  break;
                }
                return _context7.abrupt("return", this);
              case 6:
                _context7.next = 8;
                return this._getPublicKey();
              case 8:
                _yield$this$_getPubli = _context7.sent;
                publicKey = _yield$this$_getPubli.vapidPublicKey;
                _context7.next = 12;
                return this._getPushToken(publicKey);
              case 12:
                token = _context7.sent;
                _context7.next = 15;
                return this._registerDevice(token);
              case 15:
                deviceId = _context7.sent;
                _context7.next = 18;
                return this._deviceStateStore.setToken(token);
              case 18:
                _context7.next = 20;
                return this._deviceStateStore.setDeviceId(deviceId);
              case 20:
                _context7.next = 22;
                return this._deviceStateStore.setLastSeenSdkVersion(version);
              case 22:
                _context7.next = 24;
                return this._deviceStateStore.setLastSeenUserAgent(window.navigator.userAgent);
              case 24:
                this._token = token;
                this._deviceId = deviceId;
                return _context7.abrupt("return", this);
              case 27:
              case "end":
                return _context7.stop();
            }
          }, _callee7, this);
        }));
        function start() {
          return _start.apply(this, arguments);
        }
        return start;
      }()
    }, {
      key: "getRegistrationState",
      value: function () {
        var _getRegistrationState = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee8() {
          return _regeneratorRuntime.wrap(function _callee8$(_context8) {
            while (1) switch (_context8.prev = _context8.next) {
              case 0:
                _context8.next = 2;
                return this._resolveSDKState();
              case 2:
                if (!(Notification.permission === 'denied')) {
                  _context8.next = 4;
                  break;
                }
                return _context8.abrupt("return", RegistrationState.PERMISSION_DENIED);
              case 4:
                if (!(Notification.permission === 'granted' && this._deviceId !== null)) {
                  _context8.next = 6;
                  break;
                }
                return _context8.abrupt("return", RegistrationState.PERMISSION_GRANTED_REGISTERED_WITH_BEAMS);
              case 6:
                if (!(Notification.permission === 'granted' && this._deviceId === null)) {
                  _context8.next = 8;
                  break;
                }
                return _context8.abrupt("return", RegistrationState.PERMISSION_GRANTED_NOT_REGISTERED_WITH_BEAMS);
              case 8:
                return _context8.abrupt("return", RegistrationState.PERMISSION_PROMPT_REQUIRED);
              case 9:
              case "end":
                return _context8.stop();
            }
          }, _callee8, this);
        }));
        function getRegistrationState() {
          return _getRegistrationState.apply(this, arguments);
        }
        return getRegistrationState;
      }()
    }, {
      key: "addDeviceInterest",
      value: function () {
        var _addDeviceInterest = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee9(interest) {
          var path, options;
          return _regeneratorRuntime.wrap(function _callee9$(_context9) {
            while (1) switch (_context9.prev = _context9.next) {
              case 0:
                _context9.next = 2;
                return this._resolveSDKState();
              case 2:
                this._throwIfNotStarted('Could not add Device Interest');
                validateInterestName(interest);
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web/").concat(this._deviceId, "/interests/").concat(encodeURIComponent(interest));
                options = {
                  method: 'POST',
                  path: path
                };
                _context9.next = 8;
                return doRequest(options);
              case 8:
              case "end":
                return _context9.stop();
            }
          }, _callee9, this);
        }));
        function addDeviceInterest(_x) {
          return _addDeviceInterest.apply(this, arguments);
        }
        return addDeviceInterest;
      }()
    }, {
      key: "removeDeviceInterest",
      value: function () {
        var _removeDeviceInterest = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee10(interest) {
          var path, options;
          return _regeneratorRuntime.wrap(function _callee10$(_context10) {
            while (1) switch (_context10.prev = _context10.next) {
              case 0:
                _context10.next = 2;
                return this._resolveSDKState();
              case 2:
                this._throwIfNotStarted('Could not remove Device Interest');
                validateInterestName(interest);
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web/").concat(this._deviceId, "/interests/").concat(encodeURIComponent(interest));
                options = {
                  method: 'DELETE',
                  path: path
                };
                _context10.next = 8;
                return doRequest(options);
              case 8:
              case "end":
                return _context10.stop();
            }
          }, _callee10, this);
        }));
        function removeDeviceInterest(_x2) {
          return _removeDeviceInterest.apply(this, arguments);
        }
        return removeDeviceInterest;
      }()
    }, {
      key: "getDeviceInterests",
      value: function () {
        var _getDeviceInterests = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee11() {
          var limit,
            cursor,
            path,
            options,
            res,
            _args11 = arguments;
          return _regeneratorRuntime.wrap(function _callee11$(_context11) {
            while (1) switch (_context11.prev = _context11.next) {
              case 0:
                limit = _args11.length > 0 && _args11[0] !== undefined ? _args11[0] : 100;
                cursor = _args11.length > 1 && _args11[1] !== undefined ? _args11[1] : null;
                _context11.next = 4;
                return this._resolveSDKState();
              case 4:
                this._throwIfNotStarted('Could not get Device Interests');
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web/").concat(this._deviceId, "/interests");
                options = {
                  method: 'GET',
                  path: path,
                  params: {
                    limit: limit,
                    cursor: cursor
                  }
                };
                _context11.next = 9;
                return doRequest(options);
              case 9:
                res = _context11.sent;
                res = _objectSpread({
                  interests: res && res['interests'] || []
                }, res && res.responseMetadata || {});
                return _context11.abrupt("return", res);
              case 12:
              case "end":
                return _context11.stop();
            }
          }, _callee11, this);
        }));
        function getDeviceInterests() {
          return _getDeviceInterests.apply(this, arguments);
        }
        return getDeviceInterests;
      }()
    }, {
      key: "setDeviceInterests",
      value: function () {
        var _setDeviceInterests = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee12(interests) {
          var _iterator, _step, interest, uniqueInterests, path, options;
          return _regeneratorRuntime.wrap(function _callee12$(_context12) {
            while (1) switch (_context12.prev = _context12.next) {
              case 0:
                _context12.next = 2;
                return this._resolveSDKState();
              case 2:
                this._throwIfNotStarted('Could not set Device Interests');
                if (!(interests === undefined || interests === null)) {
                  _context12.next = 5;
                  break;
                }
                throw new Error('interests argument is required');
              case 5:
                if (Array.isArray(interests)) {
                  _context12.next = 7;
                  break;
                }
                throw new Error('interests argument must be an array');
              case 7:
                if (!(interests.length > MAX_INTERESTS_NUM)) {
                  _context12.next = 9;
                  break;
                }
                throw new Error("Number of interests (".concat(interests.length, ") exceeds maximum of ").concat(MAX_INTERESTS_NUM));
              case 9:
                _iterator = _createForOfIteratorHelper(interests);
                try {
                  for (_iterator.s(); !(_step = _iterator.n()).done;) {
                    interest = _step.value;
                    validateInterestName(interest);
                  }
                } catch (err) {
                  _iterator.e(err);
                } finally {
                  _iterator.f();
                }
                uniqueInterests = Array.from(new Set(interests));
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web/").concat(this._deviceId, "/interests");
                options = {
                  method: 'PUT',
                  path: path,
                  body: {
                    interests: uniqueInterests
                  }
                };
                _context12.next = 16;
                return doRequest(options);
              case 16:
              case "end":
                return _context12.stop();
            }
          }, _callee12, this);
        }));
        function setDeviceInterests(_x3) {
          return _setDeviceInterests.apply(this, arguments);
        }
        return setDeviceInterests;
      }()
    }, {
      key: "clearDeviceInterests",
      value: function () {
        var _clearDeviceInterests = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee13() {
          return _regeneratorRuntime.wrap(function _callee13$(_context13) {
            while (1) switch (_context13.prev = _context13.next) {
              case 0:
                _context13.next = 2;
                return this._resolveSDKState();
              case 2:
                this._throwIfNotStarted('Could not clear Device Interests');
                _context13.next = 5;
                return this.setDeviceInterests([]);
              case 5:
              case "end":
                return _context13.stop();
            }
          }, _callee13, this);
        }));
        function clearDeviceInterests() {
          return _clearDeviceInterests.apply(this, arguments);
        }
        return clearDeviceInterests;
      }()
    }, {
      key: "setUserId",
      value: function () {
        var _setUserId = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee14(userId, tokenProvider) {
          var error, path, _yield$tokenProvider$, beamsAuthToken, options;
          return _regeneratorRuntime.wrap(function _callee14$(_context14) {
            while (1) switch (_context14.prev = _context14.next) {
              case 0:
                _context14.next = 2;
                return this._resolveSDKState();
              case 2:
                if (isWebPushSupported()) {
                  _context14.next = 4;
                  break;
                }
                return _context14.abrupt("return");
              case 4:
                if (!(this._deviceId === null)) {
                  _context14.next = 7;
                  break;
                }
                error = new Error('.start must be called before .setUserId');
                return _context14.abrupt("return", Promise.reject(error));
              case 7:
                if (!(typeof userId !== 'string')) {
                  _context14.next = 9;
                  break;
                }
                throw new Error("User ID must be a string (was ".concat(userId, ")"));
              case 9:
                if (!(userId === '')) {
                  _context14.next = 11;
                  break;
                }
                throw new Error('User ID cannot be the empty string');
              case 11:
                if (!(this._userId !== null && this._userId !== userId)) {
                  _context14.next = 13;
                  break;
                }
                throw new Error('Changing the `userId` is not allowed.');
              case 13:
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web/").concat(this._deviceId, "/user");
                _context14.next = 16;
                return tokenProvider.fetchToken(userId);
              case 16:
                _yield$tokenProvider$ = _context14.sent;
                beamsAuthToken = _yield$tokenProvider$.token;
                options = {
                  method: 'PUT',
                  path: path,
                  headers: {
                    Authorization: "Bearer ".concat(beamsAuthToken)
                  }
                };
                _context14.next = 21;
                return doRequest(options);
              case 21:
                this._userId = userId;
                return _context14.abrupt("return", this._deviceStateStore.setUserId(userId));
              case 23:
              case "end":
                return _context14.stop();
            }
          }, _callee14, this);
        }));
        function setUserId(_x4, _x5) {
          return _setUserId.apply(this, arguments);
        }
        return setUserId;
      }()
    }, {
      key: "stop",
      value: function () {
        var _stop = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee15() {
          return _regeneratorRuntime.wrap(function _callee15$(_context15) {
            while (1) switch (_context15.prev = _context15.next) {
              case 0:
                _context15.next = 2;
                return this._resolveSDKState();
              case 2:
                if (isWebPushSupported()) {
                  _context15.next = 4;
                  break;
                }
                return _context15.abrupt("return");
              case 4:
                if (!(this._deviceId === null)) {
                  _context15.next = 6;
                  break;
                }
                return _context15.abrupt("return");
              case 6:
                _context15.next = 8;
                return this._deleteDevice();
              case 8:
                _context15.next = 10;
                return this._deviceStateStore.clear();
              case 10:
                this._clearPushToken()["catch"](function () {}); // Not awaiting this, best effort.

                this._deviceId = null;
                this._token = null;
                this._userId = null;
              case 14:
              case "end":
                return _context15.stop();
            }
          }, _callee15, this);
        }));
        function stop() {
          return _stop.apply(this, arguments);
        }
        return stop;
      }()
    }, {
      key: "clearAllState",
      value: function () {
        var _clearAllState = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee16() {
          return _regeneratorRuntime.wrap(function _callee16$(_context16) {
            while (1) switch (_context16.prev = _context16.next) {
              case 0:
                if (isWebPushSupported()) {
                  _context16.next = 2;
                  break;
                }
                return _context16.abrupt("return");
              case 2:
                _context16.next = 4;
                return this.stop();
              case 4:
                _context16.next = 6;
                return this.start();
              case 6:
              case "end":
                return _context16.stop();
            }
          }, _callee16, this);
        }));
        function clearAllState() {
          return _clearAllState.apply(this, arguments);
        }
        return clearAllState;
      }()
    }, {
      key: "_getPublicKey",
      value: function () {
        var _getPublicKey2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee17() {
          var path, options;
          return _regeneratorRuntime.wrap(function _callee17$(_context17) {
            while (1) switch (_context17.prev = _context17.next) {
              case 0:
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/web-vapid-public-key");
                options = {
                  method: 'GET',
                  path: path
                };
                return _context17.abrupt("return", doRequest(options));
              case 3:
              case "end":
                return _context17.stop();
            }
          }, _callee17, this);
        }));
        function _getPublicKey() {
          return _getPublicKey2.apply(this, arguments);
        }
        return _getPublicKey;
      }()
    }, {
      key: "_getPushToken",
      value: function () {
        var _getPushToken2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee18(publicKey) {
          var sub;
          return _regeneratorRuntime.wrap(function _callee18$(_context18) {
            while (1) switch (_context18.prev = _context18.next) {
              case 0:
                _context18.prev = 0;
                _context18.next = 3;
                return this._clearPushToken();
              case 3:
                _context18.next = 5;
                return this._serviceWorkerRegistration.pushManager.subscribe({
                  userVisibleOnly: true,
                  applicationServerKey: urlBase64ToUInt8Array(publicKey)
                });
              case 5:
                sub = _context18.sent;
                return _context18.abrupt("return", btoa(JSON.stringify(sub)));
              case 9:
                _context18.prev = 9;
                _context18.t0 = _context18["catch"](0);
                return _context18.abrupt("return", Promise.reject(_context18.t0));
              case 12:
              case "end":
                return _context18.stop();
            }
          }, _callee18, this, [[0, 9]]);
        }));
        function _getPushToken(_x6) {
          return _getPushToken2.apply(this, arguments);
        }
        return _getPushToken;
      }()
    }, {
      key: "_clearPushToken",
      value: function () {
        var _clearPushToken2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee19() {
          return _regeneratorRuntime.wrap(function _callee19$(_context19) {
            while (1) switch (_context19.prev = _context19.next) {
              case 0:
                return _context19.abrupt("return", navigator.serviceWorker.ready.then(function (reg) {
                  return reg.pushManager.getSubscription();
                }).then(function (sub) {
                  if (sub) sub.unsubscribe();
                }));
              case 1:
              case "end":
                return _context19.stop();
            }
          }, _callee19);
        }));
        function _clearPushToken() {
          return _clearPushToken2.apply(this, arguments);
        }
        return _clearPushToken;
      }()
    }, {
      key: "_registerDevice",
      value: function () {
        var _registerDevice2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee20(token) {
          var path, device, options, response;
          return _regeneratorRuntime.wrap(function _callee20$(_context20) {
            while (1) switch (_context20.prev = _context20.next) {
              case 0:
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web");
                device = {
                  token: token,
                  metadata: {
                    sdkVersion: version
                  }
                };
                options = {
                  method: 'POST',
                  path: path,
                  body: device
                };
                _context20.next = 5;
                return doRequest(options);
              case 5:
                response = _context20.sent;
                return _context20.abrupt("return", response.id);
              case 7:
              case "end":
                return _context20.stop();
            }
          }, _callee20, this);
        }));
        function _registerDevice(_x7) {
          return _registerDevice2.apply(this, arguments);
        }
        return _registerDevice;
      }()
    }, {
      key: "_deleteDevice",
      value: function () {
        var _deleteDevice2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee21() {
          var path, options;
          return _regeneratorRuntime.wrap(function _callee21$(_context21) {
            while (1) switch (_context21.prev = _context21.next) {
              case 0:
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web/").concat(encodeURIComponent(this._deviceId));
                options = {
                  method: 'DELETE',
                  path: path
                };
                _context21.next = 4;
                return doRequest(options);
              case 4:
              case "end":
                return _context21.stop();
            }
          }, _callee21, this);
        }));
        function _deleteDevice() {
          return _deleteDevice2.apply(this, arguments);
        }
        return _deleteDevice;
      }()
      /**
       * Submit SDK version and browser details (via the user agent) to Pusher Beams.
       */
    }, {
      key: "_updateDeviceMetadata",
      value: (function () {
        var _updateDeviceMetadata2 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee22() {
          var userAgent, storedUserAgent, storedSdkVersion, path, metadata, options;
          return _regeneratorRuntime.wrap(function _callee22$(_context22) {
            while (1) switch (_context22.prev = _context22.next) {
              case 0:
                userAgent = window.navigator.userAgent;
                _context22.next = 3;
                return this._deviceStateStore.getLastSeenUserAgent();
              case 3:
                storedUserAgent = _context22.sent;
                _context22.next = 6;
                return this._deviceStateStore.getLastSeenSdkVersion();
              case 6:
                storedSdkVersion = _context22.sent;
                if (!(userAgent === storedUserAgent && version === storedSdkVersion)) {
                  _context22.next = 9;
                  break;
                }
                return _context22.abrupt("return");
              case 9:
                path = "".concat(this._baseURL, "/device_api/v1/instances/").concat(encodeURIComponent(this.instanceId), "/devices/web/").concat(this._deviceId, "/metadata");
                metadata = {
                  sdkVersion: version
                };
                options = {
                  method: 'PUT',
                  path: path,
                  body: metadata
                };
                _context22.next = 14;
                return doRequest(options);
              case 14:
                _context22.next = 16;
                return this._deviceStateStore.setLastSeenSdkVersion(version);
              case 16:
                _context22.next = 18;
                return this._deviceStateStore.setLastSeenUserAgent(userAgent);
              case 18:
              case "end":
                return _context22.stop();
            }
          }, _callee22, this);
        }));
        function _updateDeviceMetadata() {
          return _updateDeviceMetadata2.apply(this, arguments);
        }
        return _updateDeviceMetadata;
      }())
    }]);
  }();
  var validateInterestName = function validateInterestName(interest) {
    if (interest === undefined || interest === null) {
      throw new Error('Interest name is required');
    }
    if (typeof interest !== 'string') {
      throw new Error("Interest ".concat(interest, " is not a string"));
    }
    if (!INTERESTS_REGEX.test(interest)) {
      throw new Error("interest \"".concat(interest, "\" contains a forbidden character. ") + 'Allowed characters are: ASCII upper/lower-case letters, ' + 'numbers or one of _-=@,.;');
    }
    if (interest.length > MAX_INTEREST_LENGTH) {
      throw new Error("Interest is longer than the maximum of ".concat(MAX_INTEREST_LENGTH, " chars"));
    }
  };
  function getServiceWorkerRegistration() {
    return _getServiceWorkerRegistration.apply(this, arguments);
  }
  function _getServiceWorkerRegistration() {
    _getServiceWorkerRegistration = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime.mark(function _callee23() {
      var _yield$fetch, swStatusCode;
      return _regeneratorRuntime.wrap(function _callee23$(_context23) {
        while (1) switch (_context23.prev = _context23.next) {
          case 0:
            _context23.next = 2;
            return fetch(SERVICE_WORKER_URL);
          case 2:
            _yield$fetch = _context23.sent;
            swStatusCode = _yield$fetch.status;
            if (!(swStatusCode !== 200)) {
              _context23.next = 6;
              break;
            }
            throw new Error('Cannot start SDK, service worker missing: No file found at /service-worker.js');
          case 6:
            window.navigator.serviceWorker.register(SERVICE_WORKER_URL, {
              // explicitly opting out of `importScripts` caching just in case our
              // customers decides to host and serve the imported scripts and
              // accidentally set `Cache-Control` to something other than `max-age=0`
              updateViaCache: 'none'
            });
            return _context23.abrupt("return", window.navigator.serviceWorker.ready);
          case 8:
          case "end":
            return _context23.stop();
        }
      }, _callee23);
    }));
    return _getServiceWorkerRegistration.apply(this, arguments);
  }
  function getWebPushToken(swReg) {
    return swReg.pushManager.getSubscription().then(function (sub) {
      return !sub ? null : encodeSubscription(sub);
    });
  }
  function encodeSubscription(sub) {
    return btoa(JSON.stringify(sub));
  }
  function urlBase64ToUInt8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = window.atob(base64);
    return Uint8Array.from(_toConsumableArray(rawData).map(function (_char) {
      return _char.charCodeAt(0);
    }));
  }
  function isWebPushSupported() {
    var hasNotification = 'Notification' in window;
    var hasPushManager = 'PushManager' in window;
    var hasServiceWorker = 'serviceWorker' in navigator;
    if (!hasNotification || !hasPushManager || !hasServiceWorker) {
      console.warn('Missing required Web Push APIs. Please upgrade your browser');
      return false;
    }
    return true;
  }

  exports.Client = Client;
  exports.RegistrationState = RegistrationState;
  exports.TokenProvider = TokenProvider;

  return exports;

})({});
