(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define("simple-print", [], factory);
	else if(typeof exports === 'object')
		exports["simple-print"] = factory();
	else
		root["simple-print"] = factory();
})(window, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/webpack/buildin/global.js":
/*!***********************************!*\
  !*** (webpack)/buildin/global.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var g;\n\n// This works in non-strict mode\ng = (function() {\n\treturn this;\n})();\n\ntry {\n\t// This works if eval is allowed (see CSP)\n\tg = g || new Function(\"return this\")();\n} catch (e) {\n\t// This works if the window reference is available\n\tif (typeof window === \"object\") g = window;\n}\n\n// g can still be undefined, but nothing to do about it...\n// We return undefined, instead of nothing here, so it's\n// easier to handle this case. if(!global) { ...}\n\nmodule.exports = g;\n\n\n//# sourceURL=webpack://simple-print/(webpack)/buildin/global.js?");

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("/* WEBPACK VAR INJECTION */(function(global) {function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nfunction _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }\n\nfunction _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }\n\nvar SimplePrint =\n/*#__PURE__*/\nfunction () {\n  function SimplePrint() {\n    var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},\n        _ref$name = _ref.name,\n        name = _ref$name === void 0 ? '_blank' : _ref$name,\n        _ref$specs = _ref.specs,\n        specs = _ref$specs === void 0 ? ['fullscreen=yes', 'titlebar=yes', 'scrollbars=yes'] : _ref$specs,\n        _ref$replace = _ref.replace,\n        replace = _ref$replace === void 0 ? true : _ref$replace,\n        _ref$styles = _ref.styles,\n        styles = _ref$styles === void 0 ? [] : _ref$styles;\n\n    _classCallCheck(this, SimplePrint);\n\n    this.name = name;\n    this.specs = specs;\n    this.replace = replace;\n    this.styles = styles;\n  }\n\n  _createClass(SimplePrint, [{\n    key: \"render\",\n    value: function render(element) {\n      var cb = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {\n        return true;\n      };\n      element = !!element.length ? element[0] : element;\n      if (!(element instanceof Element || element instanceof HTMLDocument)) return window.alert(\"Element to print can't be found!\");\n      if (Array.isArray(this.specs)) this.specs = !!this.specs.length ? this.specs.join(',') : '';\n      var win = window.open('', this.name, this.specs, this.replace);\n      win.document.write(\"<html><head><title>\".concat(document.title, \"</title></head><body>\").concat(element.innerHTML, \"</body></html>\"));\n      this.styles.forEach(function (style) {\n        var link = win.document.createElement('link');\n        link.setAttribute('rel', 'stylesheet');\n        link.setAttribute('type', 'text/css');\n        link.setAttribute('href', style);\n        win.document.getElementsByTagName('head')[0].appendChild(link);\n      });\n      setTimeout(function () {\n        win.document.close();\n        win.focus();\n        win.print();\n        win.close();\n        cb();\n      }, 1000);\n    }\n  }, {\n    key: \"options\",\n    set: function set() {\n      var _ref2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},\n          _ref2$name = _ref2.name,\n          name = _ref2$name === void 0 ? this.name : _ref2$name,\n          _ref2$specs = _ref2.specs,\n          specs = _ref2$specs === void 0 ? this.specs : _ref2$specs,\n          _ref2$replace = _ref2.replace,\n          replace = _ref2$replace === void 0 ? this.replace : _ref2$replace,\n          _ref2$styles = _ref2.styles,\n          styles = _ref2$styles === void 0 ? this.styles : _ref2$styles;\n\n      this.name = name;\n      this.specs = specs;\n      this.replace = replace;\n      this.styles = styles;\n    }\n  }]);\n\n  return SimplePrint;\n}();\n\nglobal.SimplePrint = SimplePrint;\nmodule.exports = SimplePrint;\n/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../node_modules/webpack/buildin/global.js */ \"./node_modules/webpack/buildin/global.js\")))\n\n//# sourceURL=webpack://simple-print/./src/index.js?");

/***/ }),

/***/ 0:
/*!****************************!*\
  !*** multi ./src/index.js ***!
  \****************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = __webpack_require__(/*! ./src/index.js */\"./src/index.js\");\n\n\n//# sourceURL=webpack://simple-print/multi_./src/index.js?");

/***/ })

/******/ });
});