/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/core/hooks/index.js"
/*!****************************************!*\
  !*** ./assets/src/core/hooks/index.js ***!
  \****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }\nfunction _nonIterableSpread() { throw new TypeError(\"Invalid attempt to spread non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\nfunction _unsupportedIterableToArray(r, a) { if (r) { if (\"string\" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return \"Object\" === t && r.constructor && (t = r.constructor.name), \"Map\" === t || \"Set\" === t ? Array.from(r) : \"Arguments\" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }\nfunction _iterableToArray(r) { if (\"undefined\" != typeof Symbol && null != r[Symbol.iterator] || null != r[\"@@iterator\"]) return Array.from(r); }\nfunction _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }\nfunction _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }\nfunction _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, \"value\" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }\nfunction _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, \"prototype\", { writable: !1 }), e; }\nfunction _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError(\"Cannot call a class as a function\"); }\nfunction _classPrivateFieldInitSpec(e, t, a) { _checkPrivateRedeclaration(e, t), t.set(e, a); }\nfunction _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError(\"Cannot initialize the same private elements twice on an object\"); }\nfunction _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nfunction _classPrivateFieldGet(s, a) { return s.get(_assertClassBrand(s, a)); }\nfunction _assertClassBrand(e, t, n) { if (\"function\" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError(\"Private element is not present on this object\"); }\nvar _getHandle = /*#__PURE__*/new WeakMap();\nvar _handleExists = /*#__PURE__*/new WeakMap();\nvar _removeHandle = /*#__PURE__*/new WeakMap();\nvar _addUserCallback = /*#__PURE__*/new WeakMap();\nvar _usercallBack = /*#__PURE__*/new WeakMap();\nvar Hooks = /*#__PURE__*/_createClass(function Hooks() {\n  var _this = this;\n  _classCallCheck(this, Hooks);\n  _defineProperty(this, \"addAction\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n    var priority = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 10;\n    if (!handle || !callback) {\n      console.error('Error: addAction requires a handle and a callback.');\n      return;\n    }\n    _classPrivateFieldGet(_addUserCallback, _this).call(_this, handle, callback, priority, _this.Actions);\n  });\n  _defineProperty(this, \"addFilter\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};\n    var priority = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 10;\n    if (!handle || !callback) {\n      console.error('Error: addFilter requires a handle and a callback.');\n      return;\n    }\n    _classPrivateFieldGet(_addUserCallback, _this).call(_this, handle, callback, priority, _this.Filters);\n  });\n  _defineProperty(this, \"doAction\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    if (!handle) return;\n    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {\n      args[_key - 1] = arguments[_key];\n    }\n    _classPrivateFieldGet(_usercallBack, _this).call(_this, handle, _this.Actions, args, false);\n  });\n  _defineProperty(this, \"applyFilter\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {\n      args[_key2 - 1] = arguments[_key2];\n    }\n    if (!handle) return args[0];\n\n    // Pass true to indicate this is a filter chain\n    var result = _classPrivateFieldGet(_usercallBack, _this).call(_this, handle, _this.Filters, args, true);\n    return result.value;\n  });\n  _defineProperty(this, \"hasAction\", function (handle) {\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n    return _classPrivateFieldGet(_handleExists, _this).call(_this, handle, _this.Actions, callback);\n  });\n  _defineProperty(this, \"hasFilter\", function (handle) {\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n    return _classPrivateFieldGet(_handleExists, _this).call(_this, handle, _this.Filters, callback);\n  });\n  _defineProperty(this, \"removeAction\", function (handle) {\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n    return _classPrivateFieldGet(_removeHandle, _this).call(_this, handle, _this.Actions, callback);\n  });\n  _defineProperty(this, \"removeFilter\", function (handle) {\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n    return _classPrivateFieldGet(_removeHandle, _this).call(_this, handle, _this.Filters, callback);\n  });\n  /**\r\n   * ---------------------------------------------------------\r\n   * PRIVATE METHODS\r\n   * ---------------------------------------------------------\r\n   */\n\n  _classPrivateFieldInitSpec(this, _getHandle, function (handle) {\n    var normalized = handle;\n    // Keep the namespace logic but simplify it for a flat object\n    if (normalized.startsWith('Dragwyb/')) {\n      normalized = normalized.substring(8);\n    }\n    return normalized;\n  });\n  _classPrivateFieldInitSpec(this, _handleExists, function (handle, object) {\n    var callbackToCheck = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;\n    var normalized = _classPrivateFieldGet(_getHandle, _this).call(_this, handle);\n    if (!object[normalized]) return false;\n    if (!callbackToCheck) return true;\n    return object[normalized].some(function (item) {\n      return item.callback === callbackToCheck;\n    });\n  });\n  _classPrivateFieldInitSpec(this, _removeHandle, function (handle, object) {\n    var callbackToRemove = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;\n    var normalized = _classPrivateFieldGet(_getHandle, _this).call(_this, handle);\n    if (!object[normalized]) return false;\n    if (callbackToRemove) {\n      var _object$normalized;\n      var initialLength = object[normalized].length;\n      object[normalized] = object[normalized].filter(function (item) {\n        return item.callback !== callbackToRemove;\n      });\n      if (object[normalized].length === 0) {\n        delete object[normalized];\n      }\n      return ((_object$normalized = object[normalized]) === null || _object$normalized === void 0 ? void 0 : _object$normalized.length) < initialLength;\n    } else {\n      delete object[normalized];\n      return true;\n    }\n  });\n  _classPrivateFieldInitSpec(this, _addUserCallback, function (handle, callback, priority, object) {\n    var normalized = _classPrivateFieldGet(_getHandle, _this).call(_this, handle);\n    if (!object[normalized]) {\n      object[normalized] = [];\n    }\n    object[normalized].push({\n      callback: callback,\n      priority: priority\n    });\n    // Sort by priority (ascending)\n    object[normalized].sort(function (a, b) {\n      return a.priority - b.priority;\n    });\n  });\n  _classPrivateFieldInitSpec(this, _usercallBack, function (handle, object) {\n    var args = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];\n    var isFilter = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;\n    var normalized = _classPrivateFieldGet(_getHandle, _this).call(_this, handle);\n    var value = args[0];\n    if (!object[normalized]) {\n      return {\n        status: false,\n        value: value\n      };\n    }\n    object[normalized].forEach(function (item) {\n      var callback = item.callback;\n      if (typeof callback === 'function') {\n        if (isFilter) {\n          value = callback.apply(void 0, [value].concat(_toConsumableArray(args.slice(1))));\n        } else {\n          callback.apply(void 0, _toConsumableArray(args));\n        }\n      } else if (typeof callback === 'string' && typeof window[callback] === 'function') {\n        if (isFilter) {\n          var _window;\n          value = (_window = window)[callback].apply(_window, [value].concat(_toConsumableArray(args.slice(1))));\n        } else {\n          var _window2;\n          (_window2 = window)[callback].apply(_window2, _toConsumableArray(args));\n        }\n      }\n    });\n    return {\n      status: true,\n      value: value\n    };\n  });\n  this.Actions = {};\n  this.Filters = {};\n});\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Hooks);\n\n//# sourceURL=webpack://smart-form-builder-by-dragwyb/./assets/src/core/hooks/index.js?\n}");

/***/ },

/***/ "./assets/src/core/index.js"
/*!**********************************!*\
  !*** ./assets/src/core/index.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./hooks */ \"./assets/src/core/hooks/index.js\");\n\nvar DragwybCore = function DragwybCore() {\n  DragwybBuilder.Hooks = new _hooks__WEBPACK_IMPORTED_MODULE_0__[\"default\"]();\n  jQuery(document).trigger('Dragwyb:init');\n};\njQuery(window).on('load', function () {\n  DragwybCore();\n});\n\n//# sourceURL=webpack://smart-form-builder-by-dragwyb/./assets/src/core/index.js?\n}");

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/src/core/index.js");
/******/ 	
/******/ })()
;