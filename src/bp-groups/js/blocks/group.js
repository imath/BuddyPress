// modules are defined as an array
// [ module function, map of requires ]
//
// map of requires is short require name -> numeric require
//
// anything defined in a previous bundle is accessed via the
// orig method which is the require for previous bundles
parcelRequire = (function (modules, cache, entry, globalName) {
  // Save the require from previous bundle to this closure if any
  var previousRequire = typeof parcelRequire === 'function' && parcelRequire;
  var nodeRequire = typeof require === 'function' && require;

  function newRequire(name, jumped) {
    if (!cache[name]) {
      if (!modules[name]) {
        // if we cannot find the module within our internal map or
        // cache jump to the current global require ie. the last bundle
        // that was added to the page.
        var currentRequire = typeof parcelRequire === 'function' && parcelRequire;
        if (!jumped && currentRequire) {
          return currentRequire(name, true);
        }

        // If there are other bundles on this page the require from the
        // previous one is saved to 'previousRequire'. Repeat this as
        // many times as there are bundles until the module is found or
        // we exhaust the require chain.
        if (previousRequire) {
          return previousRequire(name, true);
        }

        // Try the node require function if it exists.
        if (nodeRequire && typeof name === 'string') {
          return nodeRequire(name);
        }

        var err = new Error('Cannot find module \'' + name + '\'');
        err.code = 'MODULE_NOT_FOUND';
        throw err;
      }

      localRequire.resolve = resolve;
      localRequire.cache = {};

      var module = cache[name] = new newRequire.Module(name);

      modules[name][0].call(module.exports, localRequire, module, module.exports, this);
    }

    return cache[name].exports;

    function localRequire(x){
      return newRequire(localRequire.resolve(x));
    }

    function resolve(x){
      return modules[name][1][x] || x;
    }
  }

  function Module(moduleName) {
    this.id = moduleName;
    this.bundle = newRequire;
    this.exports = {};
  }

  newRequire.isParcelRequire = true;
  newRequire.Module = Module;
  newRequire.modules = modules;
  newRequire.cache = cache;
  newRequire.parent = previousRequire;
  newRequire.register = function (id, exports) {
    modules[id] = [function (require, module) {
      module.exports = exports;
    }, {}];
  };

  var error;
  for (var i = 0; i < entry.length; i++) {
    try {
      newRequire(entry[i]);
    } catch (e) {
      // Save first error but execute all entries
      if (!error) {
        error = e;
      }
    }
  }

  if (entry.length) {
    // Expose entry point to Node, AMD or browser globals
    // Based on https://github.com/ForbesLindesay/umd/blob/master/template.js
    var mainExports = newRequire(entry[entry.length - 1]);

    // CommonJS
    if (typeof exports === "object" && typeof module !== "undefined") {
      module.exports = mainExports;

    // RequireJS
    } else if (typeof define === "function" && define.amd) {
     define(function () {
       return mainExports;
     });

    // <script>
    } else if (globalName) {
      this[globalName] = mainExports;
    }
  }

  // Override the current require with this new one
  parcelRequire = newRequire;

  if (error) {
    // throw error from earlier, _after updating parcelRequire_
    throw error;
  }

  return newRequire;
})({"kUj2":[function(require,module,exports) {
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

module.exports = _classCallCheck;
},{}],"dMjH":[function(require,module,exports) {
function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

module.exports = _createClass;
},{}],"FlpK":[function(require,module,exports) {
function _typeof(obj) {
  "@babel/helpers - typeof";

  if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
    module.exports = _typeof = function _typeof(obj) {
      return typeof obj;
    };
  } else {
    module.exports = _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };
  }

  return _typeof(obj);
}

module.exports = _typeof;
},{}],"oXBW":[function(require,module,exports) {
function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

module.exports = _assertThisInitialized;
},{}],"cbGp":[function(require,module,exports) {
var _typeof = require("../helpers/typeof");

var assertThisInitialized = require("./assertThisInitialized");

function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  }

  return assertThisInitialized(self);
}

module.exports = _possibleConstructorReturn;
},{"../helpers/typeof":"FlpK","./assertThisInitialized":"oXBW"}],"XApn":[function(require,module,exports) {
function _getPrototypeOf(o) {
  module.exports = _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

module.exports = _getPrototypeOf;
},{}],"Omxx":[function(require,module,exports) {
function _setPrototypeOf(o, p) {
  module.exports = _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

module.exports = _setPrototypeOf;
},{}],"PhTw":[function(require,module,exports) {
var setPrototypeOf = require("./setPrototypeOf");

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) setPrototypeOf(subClass, superClass);
}

module.exports = _inherits;
},{"./setPrototypeOf":"Omxx"}],"xHsb":[function(require,module,exports) {
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _assertThisInitialized2 = _interopRequireDefault(require("@babel/runtime/helpers/assertThisInitialized"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * WordPress dependencies.
 */
var _wp$element = wp.element,
    Component = _wp$element.Component,
    Fragment = _wp$element.Fragment,
    createElement = _wp$element.createElement;
var Popover = wp.components.Popover;
var _wp = wp,
    apiFetch = _wp.apiFetch;
var __ = wp.i18n.__;

var BPAutocompleter = /*#__PURE__*/function (_Component) {
  (0, _inherits2.default)(BPAutocompleter, _Component);

  function BPAutocompleter() {
    var _this;

    (0, _classCallCheck2.default)(this, BPAutocompleter);
    _this = (0, _possibleConstructorReturn2.default)(this, (0, _getPrototypeOf2.default)(BPAutocompleter).apply(this, arguments));
    _this.state = {
      search: '',
      items: [],
      error: ''
    };
    _this.searchItemName = _this.searchItemName.bind((0, _assertThisInitialized2.default)(_this));
    _this.selectItemName = _this.selectItemName.bind((0, _assertThisInitialized2.default)(_this));
    return _this;
  }

  (0, _createClass2.default)(BPAutocompleter, [{
    key: "searchItemName",
    value: function searchItemName(value) {
      var _this2 = this;

      var search = this.state.search;
      var _this$props = this.props,
          component = _this$props.component,
          objectStatus = _this$props.objectStatus;
      this.setState({
        search: value
      });

      if (value.length < search.length) {
        this.setState({
          items: []
        });
      }

      var path = '/buddypress/v1/' + component;

      if (value) {
        path += '?search=' + encodeURIComponent(value);
      }

      if (objectStatus) {
        path += '&status=' + objectStatus;
      }

      apiFetch({
        path: path
      }).then(function (items) {
        _this2.setState({
          items: items
        });
      }, function (error) {
        _this2.setState({
          error: error.message
        });
      });
    }
  }, {
    key: "selectItemName",
    value: function selectItemName(event, itemID) {
      var onSelectItem = this.props.onSelectItem;
      event.preventDefault();
      this.setState({
        search: '',
        items: [],
        error: ''
      });
      return onSelectItem({
        itemID: itemID
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this3 = this;

      var _this$state = this.state,
          search = _this$state.search,
          items = _this$state.items;
      var _this$props2 = this.props,
          ariaLabel = _this$props2.ariaLabel,
          placeholder = _this$props2.placeholder,
          useAvatar = _this$props2.useAvatar;
      var itemsList;

      if (!ariaLabel) {
        ariaLabel = __('Item\'s name', 'buddypress');
      }

      if (!placeholder) {
        placeholder = __('Enter Item\'s name here…', 'buddypress');
      }

      if (items.length) {
        itemsList = items.map(function (item) {
          return createElement("button", {
            type: "button",
            key: 'editor-autocompleters__item-item-' + item.id,
            role: "option",
            "aria-selected": "true",
            className: "components-button components-autocomplete__result editor-autocompleters__user",
            onClick: function onClick(event) {
              return _this3.selectItemName(event, item.id);
            }
          }, useAvatar && createElement("img", {
            key: "avatar",
            className: "editor-autocompleters__user-avatar",
            alt: "",
            src: item.avatar_urls.thumb
          }), createElement("span", {
            key: "name",
            className: "editor-autocompleters__user-name"
          }, item.name), item.mention_name && createElement("span", {
            key: "slug",
            className: "editor-autocompleters__user-slug"
          }, item.mention_name));
        });
      }

      return createElement(Fragment, null, createElement("input", {
        type: "text",
        value: search,
        className: "components-placeholder__input",
        "aria-label": ariaLabel,
        placeholder: placeholder,
        onChange: function onChange(event) {
          return _this3.searchItemName(event.target.value);
        }
      }), 0 !== items.length && createElement(Popover, {
        className: "components-autocomplete__popover",
        focusOnMount: false,
        position: "bottom left"
      }, createElement("div", {
        className: "components-autocomplete__results"
      }, itemsList)));
    }
  }]);
  return BPAutocompleter;
}(Component);

var _default = BPAutocompleter;
exports.default = _default;
},{"@babel/runtime/helpers/classCallCheck":"kUj2","@babel/runtime/helpers/createClass":"dMjH","@babel/runtime/helpers/possibleConstructorReturn":"cbGp","@babel/runtime/helpers/getPrototypeOf":"XApn","@babel/runtime/helpers/assertThisInitialized":"oXBW","@babel/runtime/helpers/inherits":"PhTw"}],"pvse":[function(require,module,exports) {
"use strict";

var _bpAutocompleter = _interopRequireDefault(require("../../../bp-core/js/blocks/bp-autocompleter"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * WordPress dependencies.
 */
var registerBlockType = wp.blocks.registerBlockType;
var _wp$element = wp.element,
    createElement = _wp$element.createElement,
    Fragment = _wp$element.Fragment;
var _wp$components = wp.components,
    Placeholder = _wp$components.Placeholder,
    Disabled = _wp$components.Disabled,
    PanelBody = _wp$components.PanelBody,
    SelectControl = _wp$components.SelectControl,
    ToggleControl = _wp$components.ToggleControl,
    Toolbar = _wp$components.Toolbar,
    ToolbarButton = _wp$components.ToolbarButton;
var _wp$blockEditor = wp.blockEditor,
    InspectorControls = _wp$blockEditor.InspectorControls,
    BlockControls = _wp$blockEditor.BlockControls;
var withSelect = wp.data.withSelect;
var compose = wp.compose.compose;
var ServerSideRender = wp.editor.ServerSideRender;
var __ = wp.i18n.__;
/**
 * Internal dependencies.
 */

var AVATAR_SIZES = [{
  label: __('None', 'buddypress'),
  value: 'none'
}, {
  label: __('Thumb', 'buddypress'),
  value: 'thumb'
}, {
  label: __('Full', 'buddypress'),
  value: 'full'
}];

var editGroup = function editGroup(_ref) {
  var attributes = _ref.attributes,
      setAttributes = _ref.setAttributes,
      bpSettings = _ref.bpSettings;
  var isAvatarEnabled = bpSettings.isAvatarEnabled,
      isCoverImageEnabled = bpSettings.isCoverImageEnabled;
  var avatarSize = attributes.avatarSize,
      displayDescription = attributes.displayDescription,
      displayActionButton = attributes.displayActionButton,
      displayCoverImage = attributes.displayCoverImage;

  if (!attributes.itemID) {
    return createElement(Placeholder, {
      icon: "buddicons-groups",
      label: __('BuddyPress Group', 'buddypress'),
      instructions: __('Start typing the name of the group you want to feature into this post.', 'buddypress')
    }, createElement(_bpAutocompleter.default, {
      component: "groups",
      objectStatus: "public",
      ariaLabel: __('Group\'s name', 'buddypress'),
      placeholder: __('Enter Group\'s name here…', 'buddypress'),
      onSelectItem: setAttributes,
      useAvatar: isAvatarEnabled
    }));
  }

  return createElement(Fragment, null, createElement(BlockControls, null, createElement(Toolbar, null, createElement(ToolbarButton, {
    icon: "edit",
    title: __('Select another group', 'buddypress'),
    onClick: function onClick() {
      setAttributes({
        itemID: 0
      });
    }
  }))), createElement(InspectorControls, null, createElement(PanelBody, {
    title: __('Group\'s home button settings', 'buddypress'),
    initialOpen: true
  }, createElement(ToggleControl, {
    label: __('Display Group\'s home button', 'buddypress'),
    checked: !!displayActionButton,
    onChange: function onChange() {
      setAttributes({
        displayActionButton: !displayActionButton
      });
    },
    help: displayActionButton ? __('Include a link to the group\'s home page under their name.', 'buddypress') : __('Toggle to display a link to the group\'s home page under their name.', 'buddypress')
  })), createElement(PanelBody, {
    title: __('Description settings', 'buddypress'),
    initialOpen: false
  }, createElement(ToggleControl, {
    label: __('Display group\'s description', 'buddypress'),
    checked: !!displayDescription,
    onChange: function onChange() {
      setAttributes({
        displayDescription: !displayDescription
      });
    },
    help: displayDescription ? __('Include the group\'s description under their name.', 'buddypress') : __('Toggle to display the group\'s description under their name.', 'buddypress')
  })), isAvatarEnabled && createElement(PanelBody, {
    title: __('Avatar settings', 'buddypress'),
    initialOpen: false
  }, createElement(SelectControl, {
    label: __('Size', 'buddypress'),
    value: avatarSize,
    options: AVATAR_SIZES,
    onChange: function onChange(option) {
      setAttributes({
        avatarSize: option
      });
    }
  })), isCoverImageEnabled && createElement(PanelBody, {
    title: __('Cover image settings', 'buddypress'),
    initialOpen: false
  }, createElement(ToggleControl, {
    label: __('Display Cover Image', 'buddypress'),
    checked: !!displayCoverImage,
    onChange: function onChange() {
      setAttributes({
        displayCoverImage: !displayCoverImage
      });
    },
    help: displayCoverImage ? __('Include the group\'s cover image over their name.', 'buddypress') : __('Toggle to display the group\'s cover image over their name.', 'buddypress')
  }))), createElement(Disabled, null, createElement(ServerSideRender, {
    block: "bp/group",
    attributes: attributes
  })));
};

var editGroupBlock = compose([withSelect(function (select) {
  var editorSettings = select('core/editor').getEditorSettings();
  return {
    bpSettings: editorSettings.bp.groups || {}
  };
})])(editGroup);
registerBlockType('bp/group', {
  title: __('Group', 'buddypress'),
  description: __('BuddyPress Group.', 'buddypress'),
  icon: 'buddicons-groups',
  category: 'buddypress',
  attributes: {
    itemID: {
      type: 'integer',
      default: 0
    },
    avatarSize: {
      type: 'string',
      default: 'full'
    },
    displayDescription: {
      type: 'boolean',
      default: true
    },
    displayActionButton: {
      type: 'boolean',
      default: true
    },
    displayCoverImage: {
      type: 'boolean',
      default: true
    }
  },
  edit: editGroupBlock
});
},{"../../../bp-core/js/blocks/bp-autocompleter":"xHsb"}]},{},["pvse"], null)