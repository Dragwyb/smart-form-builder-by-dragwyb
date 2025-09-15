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

/***/ "./assets/src/core/hooks/index.js":
/*!****************************************!*\
  !*** ./assets/src/core/hooks/index.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }\nfunction _nonIterableSpread() { throw new TypeError(\"Invalid attempt to spread non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\nfunction _iterableToArray(r) { if (\"undefined\" != typeof Symbol && null != r[Symbol.iterator] || null != r[\"@@iterator\"]) return Array.from(r); }\nfunction _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }\nfunction _createForOfIteratorHelper(r, e) { var t = \"undefined\" != typeof Symbol && r[Symbol.iterator] || r[\"@@iterator\"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && \"number\" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError(\"Invalid attempt to iterate non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t[\"return\"] || t[\"return\"](); } finally { if (u) throw o; } } }; }\nfunction _unsupportedIterableToArray(r, a) { if (r) { if (\"string\" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return \"Object\" === t && r.constructor && (t = r.constructor.name), \"Map\" === t || \"Set\" === t ? Array.from(r) : \"Arguments\" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }\nfunction _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }\nfunction _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, \"value\" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }\nfunction _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, \"prototype\", { writable: !1 }), e; }\nfunction _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError(\"Cannot call a class as a function\"); }\nfunction _classPrivateFieldInitSpec(e, t, a) { _checkPrivateRedeclaration(e, t), t.set(e, a); }\nfunction _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError(\"Cannot initialize the same private elements twice on an object\"); }\nfunction _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nfunction _classPrivateFieldGet(s, a) { return s.get(_assertClassBrand(s, a)); }\nfunction _assertClassBrand(e, t, n) { if (\"function\" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError(\"Private element is not present on this object\"); }\nvar _handleExists = /*#__PURE__*/new WeakMap();\nvar _addUserCallback = /*#__PURE__*/new WeakMap();\nvar _usercallBack = /*#__PURE__*/new WeakMap();\nvar Hooks = /*#__PURE__*/_createClass(function Hooks() {\n  var _this = this;\n  _classCallCheck(this, Hooks);\n  _defineProperty(this, \"addAction\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n    if (!handle) {\n      new Error('Do not call addAction without handle name');\n      return;\n    }\n    if (!callback) {\n      new Error('Do not call addAction without callback function');\n      return;\n    }\n    _classPrivateFieldGet(_addUserCallback, _this).call(_this, handle, callback, _this.Actions);\n  });\n  _defineProperty(this, \"addFilter\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};\n    if (!handle) {\n      new Error('Do not call addFilter without handle name');\n      return;\n    }\n    if (!callback) {\n      new Error('Do not call addFilter without callback function');\n      return;\n    }\n    _classPrivateFieldGet(_addUserCallback, _this).call(_this, handle, callback, _this.Filters);\n  });\n  _defineProperty(this, \"doAction\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {\n      args[_key - 1] = arguments[_key];\n    }\n    if (!handle) {\n      new Error('Do not call doAction without handle name');\n      return;\n    }\n    if (args.length < 0) {\n      args = false;\n    }\n    _classPrivateFieldGet(_usercallBack, _this).call(_this, handle, _this.Actions, args);\n  });\n  _defineProperty(this, \"applyFilter\", function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;\n    for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {\n      args[_key2 - 1] = arguments[_key2];\n    }\n    if (args.length < 0) {\n      new Error('Do not call doAction without arguments');\n    }\n    if (!handle) {\n      new Error('Do not call doAction without handle name');\n      return;\n    }\n    var data = _classPrivateFieldGet(_usercallBack, _this).call(_this, handle, _this.Filters, args);\n    if (!data.status) {\n      return args[0];\n    }\n    return data.found;\n  });\n  _defineProperty(this, \"hasAction\", function (handle) {\n    return _classPrivateFieldGet(_handleExists, _this).call(_this, handle, _this.Actions);\n  });\n  _defineProperty(this, \"hasFilter\", function (handle) {\n    return _classPrivateFieldGet(_handleExists, _this).call(_this, handle, _this.Filters);\n  });\n  _classPrivateFieldInitSpec(this, _handleExists, function (handle, object) {\n    var handleKeys = handle.split('/');\n    var lastKey = handleKeys[handleKeys.length - 1];\n    if (handleKeys[0] === 'Dragwyb') {\n      handleKeys.shift();\n    }\n    handleKeys.pop();\n    var currentObject = object;\n    var _iterator = _createForOfIteratorHelper(handleKeys),\n      _step;\n    try {\n      for (_iterator.s(); !(_step = _iterator.n()).done;) {\n        var _currentObject;\n        var key = _step.value;\n        if ((_currentObject = currentObject) !== null && _currentObject !== void 0 && _currentObject[key]) {\n          currentObject = currentObject[key];\n        } else {\n          currentObject = {};\n          break;\n        }\n      }\n    } catch (err) {\n      _iterator.e(err);\n    } finally {\n      _iterator.f();\n    }\n    return currentObject.hasOwnProperty(lastKey);\n  });\n  _defineProperty(this, \"removeAction\", function (handle) {\n    if (_this.Actions && _this.Actions[handle]) delete _this.Actions[handle];\n  });\n  _defineProperty(this, \"removeFilter\", function (handle) {\n    if (_this.Filters && _this.Filters[handle]) delete _this.Filters[handle];\n  });\n  _classPrivateFieldInitSpec(this, _addUserCallback, function () {\n    var handle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';\n    var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};\n    var object = arguments.length > 2 ? arguments[2] : undefined;\n    var handleKeys = handle.split('/');\n    if (handleKeys[0] === 'Dragwyb') {\n      handleKeys.shift();\n    }\n    var currentHookObj = object;\n    handleKeys.forEach(function (key, index) {\n      // If it's the last key, assign the callback\n      if (index === handleKeys.length - 1) {\n        currentHookObj[key] = [].concat(_toConsumableArray(currentHookObj[key] || []), [callback]);\n      } else {\n        // If key doesn't exist or is not an object, initialize as an object\n        if (_typeof(currentHookObj[key]) !== 'object' || currentHookObj[key] === null) {\n          currentHookObj[key] = {};\n        }\n\n        // Traverse deeper\n        currentHookObj = currentHookObj[key];\n      }\n    });\n  });\n  _classPrivateFieldInitSpec(this, _usercallBack, function (handle, object) {\n    var args = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;\n    var handleKeys = handle.split('/');\n    if (handleKeys[0] === 'Dragwyb') {\n      handleKeys.shift();\n    }\n    var callbacks = object;\n    var _iterator2 = _createForOfIteratorHelper(handleKeys),\n      _step2;\n    try {\n      for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {\n        var _callbacks;\n        var key = _step2.value;\n        if ((_callbacks = callbacks) !== null && _callbacks !== void 0 && _callbacks[key]) {\n          callbacks = callbacks[key];\n        } else {\n          callbacks = [];\n          break;\n        }\n      }\n    } catch (err) {\n      _iterator2.e(err);\n    } finally {\n      _iterator2.f();\n    }\n    var data = {\n      status: false\n    };\n    if (callbacks.length > 0) {\n      data.status = true;\n      data.found = true;\n      callbacks.forEach(function (callback) {\n        data.found = callback.apply(void 0, _toConsumableArray(args));\n      });\n    }\n    return data;\n  });\n  this.Actions = {};\n  this.Filters = {};\n});\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Hooks);\n\n//# sourceURL=webpack://dragwyb-form-builder/./assets/src/core/hooks/index.js?");

/***/ }),

/***/ "./assets/src/core/index.js":
/*!**********************************!*\
  !*** ./assets/src/core/index.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./hooks */ \"./assets/src/core/hooks/index.js\");\n\nvar DragwybCore = function DragwybCore() {\n  DragwybBuilder.Hooks = new _hooks__WEBPACK_IMPORTED_MODULE_0__[\"default\"]();\n  jQuery(document).trigger('Dragwyb:init');\n};\njQuery(window).on('load', function () {\n  DragwybCore();\n});\n\n//# sourceURL=webpack://dragwyb-form-builder/./assets/src/core/index.js?");

/***/ })

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