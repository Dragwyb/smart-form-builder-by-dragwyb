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

/***/ "./assets/src/rangeSlider/index.js"
/*!*****************************************!*\
  !*** ./assets/src/rangeSlider/index.js ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _range_slider__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./range-slider */ \"./assets/src/rangeSlider/range-slider.js\");\n\njQuery(document).on('Dragwyb:frontendInit', function () {\n  DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', function (container, formId) {\n    new _range_slider__WEBPACK_IMPORTED_MODULE_0__[\"default\"](container);\n  });\n});\n\n//# sourceURL=webpack://smart-form-builder-by-dragwyb/./assets/src/rangeSlider/index.js?\n}");

/***/ },

/***/ "./assets/src/rangeSlider/range-slider.js"
/*!************************************************!*\
  !*** ./assets/src/rangeSlider/range-slider.js ***!
  \************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError(\"Cannot call a class as a function\"); }\nfunction _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, \"value\" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }\nfunction _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, \"prototype\", { writable: !1 }), e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nfunction _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }\nfunction _possibleConstructorReturn(t, e) { if (e && (\"object\" == _typeof(e) || \"function\" == typeof e)) return e; if (void 0 !== e) throw new TypeError(\"Derived constructors may only return object or undefined\"); return _assertThisInitialized(t); }\nfunction _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError(\"this hasn't been initialised - super() hasn't been called\"); return e; }\nfunction _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }\nfunction _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }\nfunction _inherits(t, e) { if (\"function\" != typeof e && null !== e) throw new TypeError(\"Super expression must either be null or a function\"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, \"prototype\", { writable: !1 }), e && _setPrototypeOf(t, e); }\nfunction _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }\n/**\n * Individual Form Handler Class\n */\nvar DragwybRangeSlider = /*#__PURE__*/function (_DragwybBuilder$Dragw) {\n  function DragwybRangeSlider() {\n    _classCallCheck(this, DragwybRangeSlider);\n    return _callSuper(this, DragwybRangeSlider, arguments);\n  }\n  _inherits(DragwybRangeSlider, _DragwybBuilder$Dragw);\n  return _createClass(DragwybRangeSlider, [{\n    key: \"bindElements\",\n    value: function bindElements() {\n      this.elements.$form = this.$container.is('form') ? this.$container : this.$container.find('form.dragwyb-form');\n    }\n  }, {\n    key: \"init\",\n    value: function init() {\n      var _this = this;\n      var $sliders = this.$container.find('.dragwyb-custom-range-container');\n      $sliders.each(function (_, el) {\n        _this.initSlider(jQuery(el));\n      });\n    }\n  }, {\n    key: \"initSlider\",\n    value: function initSlider($slider) {\n      var $input = $slider.find('input[type=\"range\"]');\n      var $progress = $slider.find('.dragwyb-range-progress');\n      var $thumb = $slider.find('.dragwyb-range-thumb');\n      if (!$input.length) return;\n      var updateVisuals = function updateVisuals() {\n        var min = parseFloat($input.attr('min')) || 0;\n        var max = parseFloat($input.attr('max')) || 100;\n        var val = parseFloat($input.val()) || 0;\n        var percentage = (val - min) / (max - min) * 100;\n        if (percentage < 0) percentage = 0;\n        if (percentage > 100) percentage = 100;\n        $progress.css('width', \"\".concat(percentage, \"%\"));\n        $thumb.css('left', \"\".concat(percentage, \"%\"));\n      };\n\n      // Initial update\n      updateVisuals();\n\n      // Bind input event for real-time dragging updates\n      $input.on('input', function () {\n        updateVisuals();\n      });\n\n      // Ensure thumb gets focus styling when input is focused\n      $input.on('focus', function () {\n        $slider.addClass('dragwyb-range-focused');\n      }).on('blur', function () {\n        $slider.removeClass('dragwyb-range-focused');\n      });\n    }\n  }]);\n}(DragwybBuilder.DragwybFormFrontendBase);\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (DragwybRangeSlider);\n\n//# sourceURL=webpack://smart-form-builder-by-dragwyb/./assets/src/rangeSlider/range-slider.js?\n}");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/src/rangeSlider/index.js");
/******/ 	
/******/ })()
;