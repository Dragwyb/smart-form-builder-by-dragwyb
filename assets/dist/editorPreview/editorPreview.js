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

/***/ "./assets/src/editor/components/IconsManager/index.js"
/*!************************************************************!*\
  !*** ./assets/src/editor/components/IconsManager/index.js ***!
  \************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\nvar _excluded = [\"icon\", \"className\"],\n  _excluded2 = [\"icon\"];\nfunction _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _objectWithoutProperties(e, t) { if (null == e) return {}; var o, r, i = _objectWithoutPropertiesLoose(e, t); if (Object.getOwnPropertySymbols) { var n = Object.getOwnPropertySymbols(e); for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]); } return i; }\nfunction _objectWithoutPropertiesLoose(r, e) { if (null == r) return {}; var t = {}; for (var n in r) if ({}.hasOwnProperty.call(r, n)) { if (-1 !== e.indexOf(n)) continue; t[n] = r[n]; } return t; }\n\n\n/**\r\n * IconsManager React component.\r\n *\r\n * Renders an SVG icon from the localized list if available,\r\n * otherwise falls back to a standard FontAwesome CSS-class icon.\r\n *\r\n * @param {Object} props\r\n * @param {Object|string} props.icon The icon object {icon: string, type: string} or CSS class string.\r\n * @param {string} [props.className=''] Extra CSS classes.\r\n */\nvar IconsManager = function IconsManager(_ref) {\n  var _window$DragwybEditor;\n  var icon = _ref.icon,\n    _ref$className = _ref.className,\n    className = _ref$className === void 0 ? '' : _ref$className,\n    props = _objectWithoutProperties(_ref, _excluded);\n  if (!icon) {\n    return null;\n  }\n  var iconName = '';\n  var iconType = 'solid';\n  if (_typeof(icon) === 'object') {\n    iconName = icon.icon || '';\n    iconType = icon.type || 'solid';\n  } else if (typeof icon === 'string') {\n    // Handle FontAwesome class strings like \"fas fa-user\" or \"fa-user\"\n    var parts = icon.trim().split(/\\s+/);\n\n    // Find a part starting with 'fa-'\n    var faPart = parts.find(function (p) {\n      return p.startsWith('fa-') && p !== 'fa';\n    });\n    if (faPart) {\n      iconName = faPart.substring(3);\n    } else {\n      // If it doesn't start with fa-, check if any non-prefix part is the icon name\n      var nonPrefixParts = parts.filter(function (p) {\n        return !['fas', 'far', 'fab', 'fa', 'dragwyb-icon'].includes(p);\n      });\n      iconName = nonPrefixParts[0] || '';\n    }\n\n    // Map prefixes to types\n    if (parts.includes('fab')) {\n      iconType = 'brands';\n    } else if (parts.includes('far')) {\n      iconType = 'regular';\n    } else if (parts.includes('dragwyb-icon')) {\n      iconType = 'custom';\n    } else {\n      iconType = 'solid';\n    }\n  }\n  if (!iconName) {\n    return null;\n  }\n\n  // Check if we have SVG data localized for this icon\n  var faIconsList = ((_window$DragwybEditor = window.DragwybEditor) === null || _window$DragwybEditor === void 0 ? void 0 : _window$DragwybEditor.faIconsList) || {};\n  var iconGroup = faIconsList[iconType];\n  var iconData = iconGroup ? iconGroup[iconName] : null;\n  if (iconData && Array.isArray(iconData)) {\n    var width = iconData[0];\n    var height = iconData[1];\n    var path = iconData[4];\n\n    // Combine default styles / classes to ensure it behaves nicely\n    var svgClasses = \"dragwyb-svg-icon dragwyb-svg-icon--\".concat(iconType, \" dragwyb-svg-icon--\").concat(iconName, \" \").concat(className).trim();\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"svg\", _extends({\n      xmlns: \"http://www.w3.org/2000/svg\",\n      viewBox: \"0 0 \".concat(width, \" \").concat(height),\n      fill: \"currentColor\",\n      className: svgClasses,\n      \"aria-hidden\": \"true\"\n    }, props), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"path\", {\n      d: path\n    }));\n  }\n  return null;\n};\n\n/**\r\n * Static method to get the valid icon groups.\r\n *\r\n * @return {string[]} Array of icon groups.\r\n */\nIconsManager.iconsGroups = function () {\n  var _window$DragwybEditor2;\n  return Object.keys(((_window$DragwybEditor2 = window.DragwybEditor) === null || _window$DragwybEditor2 === void 0 ? void 0 : _window$DragwybEditor2.faIconsList) || {\n    'solid': '',\n    'brands': '',\n    'regular': '',\n    'custom': ''\n  });\n};\n\n/**\r\n * Static method to retrieve all icon names belonging to a specific group/library.\r\n *\r\n * @param {string} group The icon group (solid, brands, regular, custom).\r\n * @return {string[]} Array of icon names.\r\n */\nIconsManager.getIconsByGroup = function (group) {\n  var _window$DragwybEditor3;\n  var faIconsList = ((_window$DragwybEditor3 = window.DragwybEditor) === null || _window$DragwybEditor3 === void 0 ? void 0 : _window$DragwybEditor3.faIconsList) || {};\n  var groupData = faIconsList[group];\n  if (!groupData) {\n    return [];\n  }\n  if (Array.isArray(groupData)) {\n    return groupData;\n  }\n  if (_typeof(groupData) === 'object') {\n    return Object.keys(groupData);\n  }\n  return [];\n};\n\n/**\r\n * Static method for rendering icons, similar to PHP IconsManager::render_icon.\r\n *\r\n * @param {Object|string} icon The icon object or class string.\r\n * @param {Object} [attributes={}] HTML attributes to pass to the element.\r\n * @param {string} [tag='i'] The HTML tag to render.\r\n * @return {JSX.Element} React element.\r\n */\nIconsManager.Render = function (_ref2) {\n  var icon = _ref2.icon,\n    attributes = _objectWithoutProperties(_ref2, _excluded2);\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(IconsManager, _extends({\n    icon: icon\n  }, attributes));\n};\n\n/**\r\n * Alias static method for Render/render_icon.\r\n */\nIconsManager.renderIcons = IconsManager.Render;\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (IconsManager);\n\n//# sourceURL=webpack://smart-form-builder-by-dragwyb/./assets/src/editor/components/IconsManager/index.js?\n}");

/***/ },

/***/ "./assets/src/editor/fieldBase/index.js"
/*!**********************************************!*\
  !*** ./assets/src/editor/fieldBase/index.js ***!
  \**********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError(\"Cannot call a class as a function\"); }\nfunction _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, \"value\" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }\nfunction _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, \"prototype\", { writable: !1 }), e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nfunction _classPrivateMethodInitSpec(e, a) { _checkPrivateRedeclaration(e, a), a.add(e); }\nfunction _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError(\"Cannot initialize the same private elements twice on an object\"); }\nfunction _assertClassBrand(e, t, n) { if (\"function\" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError(\"Private element is not present on this object\"); }\nvar _DragwybFieldBase_brand = /*#__PURE__*/new WeakSet();\nvar DragwybFieldBase = /*#__PURE__*/function () {\n  function DragwybFieldBase(_args) {\n    _classCallCheck(this, DragwybFieldBase);\n    _classPrivateMethodInitSpec(this, _DragwybFieldBase_brand);\n    this.fieldName = this.fieldName();\n    return _assertClassBrand(_DragwybFieldBase_brand, this, _renderContent).call(this, _args);\n  }\n  return _createClass(DragwybFieldBase, [{\n    key: \"renderComponent\",\n    value: function renderComponent(args) {\n      _assertClassBrand(_DragwybFieldBase_brand, this, _setDisplaySetting).call(this, args);\n      return this.bind();\n    }\n  }, {\n    key: \"RenderLabel\",\n    value: function RenderLabel(_ref) {\n      var id = _ref.id,\n        label = _ref.label,\n        required = _ref.required,\n        settings = _ref.settings;\n      if (!label || '' === label) {\n        return;\n      }\n      var field_icon = settings.label_icon;\n      var icon_to_render = field_icon && '' !== field_icon['icon'] ? field_icon : false;\n      var field_label_class = 'dragwyb-field-label';\n      if (icon_to_render && '' !== icon_to_render['icon']) {\n        field_label_class += ' dragwyb-field-label-icon';\n      }\n      return /*#__PURE__*/React.createElement(\"label\", {\n        htmlFor: id,\n        className: field_label_class\n      }, icon_to_render && '' !== icon_to_render['icon'] ? /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(DragwybEditor.editor.IconsManager.Render, {\n        icon: {\n          type: icon_to_render['type'],\n          icon: icon_to_render['icon']\n        },\n        className: \"dragwyb-label-icon\"\n      }), /*#__PURE__*/React.createElement(\"span\", {\n        className: \"dragwyb-field-label-text\"\n      }, label, required === 'yes' && /*#__PURE__*/React.createElement(\"span\", {\n        className: \"dragwyb-required\"\n      }, \"*\"))) : /*#__PURE__*/React.createElement(React.Fragment, null, label, required === 'yes' && /*#__PURE__*/React.createElement(\"span\", {\n        className: \"dragwyb-required\"\n      }, \"*\")));\n    }\n\n    /**\r\n     * ✅ Shared method: Check if this control should render based on settings.type\r\n     */\n  }, {\n    key: \"shouldRender\",\n    value: function shouldRender() {\n      var _this$field;\n      return ((_this$field = this.field) === null || _this$field === void 0 ? void 0 : _this$field.type) === this.fieldName;\n    }\n  }]);\n}();\nfunction _renderContent(args) {\n  if (!this.fieldName) {\n    return;\n  }\n  return this.renderComponent(args);\n}\nfunction _setDisplaySetting(args) {\n  this.html = args[0];\n  this.children = args[1];\n  this.type = args[2];\n  this.id = args[3];\n  this.value = args[4];\n  this.field = args[5];\n  this.Utils = args[6];\n  this.childrenIds = args[7];\n  this.attributes = this.field.attributes;\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (DragwybFieldBase);\n\n//# sourceURL=webpack://smart-form-builder-by-dragwyb/./assets/src/editor/fieldBase/index.js?\n}");

/***/ },

/***/ "./assets/src/editorPreview/index.js"
/*!*******************************************!*\
  !*** ./assets/src/editorPreview/index.js ***!
  \*******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _editor_fieldBase__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../editor/fieldBase */ \"./assets/src/editor/fieldBase/index.js\");\n/* harmony import */ var _editor_components_IconsManager__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../editor/components/IconsManager */ \"./assets/src/editor/components/IconsManager/index.js\");\n\n\nwindow.DragwybEditor = {};\nwindow.DragwybEditor.editor = {};\nwindow.DragwybEditor.editor[\"extends\"] = {};\nwindow.DragwybEditor.editor.IconsManager = _editor_components_IconsManager__WEBPACK_IMPORTED_MODULE_1__[\"default\"];\nwindow.DragwybEditor.editor[\"extends\"].FieldBase = _editor_fieldBase__WEBPACK_IMPORTED_MODULE_0__[\"default\"];\njQuery(document).on('Dragwyb:init', function () {\n  jQuery(document).trigger('Dragwyb:editorAppLoaded');\n});\n\n//# sourceURL=webpack://smart-form-builder-by-dragwyb/./assets/src/editorPreview/index.js?\n}");

/***/ },

/***/ "react"
/*!************************!*\
  !*** external "React" ***!
  \************************/
(module) {

module.exports = window["React"];

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
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/src/editorPreview/index.js");
/******/ 	
/******/ })()
;