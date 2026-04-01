/*!
 * dosomething
 * Fiercely quick and opinionated front-ends
 * https://HosseinKarami.github.io/fastshell
 * @author Hossein Karami
 * @version 1.0.5
 * Copyright 2022. MIT licensed.
 */
// Loading vendors
/*!
 * Glide.js v3.2.2
 * (c) 2013-2018 Jędrzej Chałubek <jedrzej.chalubek@gmail.com> (http://jedrzejchalubek.com/)
 * Released under the MIT License.
 */

(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global.Glide = factory());
}(this, (function () { 'use strict';

  var defaults = {
    /**
     * Type of the movement.
     *
     * Available types:
     * `slider` - Rewinds slider to the start/end when it reaches the first or last slide.
     * `carousel` - Changes slides without starting over when it reaches the first or last slide.
     *
     * @type {String}
     */
    type: 'slider',

    /**
     * Start at specific slide number defined with zero-based index.
     *
     * @type {Number}
     */
    startAt: 0,

    /**
     * A number of slides visible on the single viewport.
     *
     * @type {Number}
     */
    perView: 1,

    /**
     * Focus currently active slide at a specified position in the track.
     *
     * Available inputs:
     * `center` - Current slide will be always focused at the center of a track.
     * `0,1,2,3...` - Current slide will be focused on the specified zero-based index.
     *
     * @type {String|Number}
     */
    focusAt: 0,

    /**
     * A size of the gap added between slides.
     *
     * @type {Number}
     */
    gap: 10,

    /**
     * Change slides after a specified interval. Use `false` for turning off autoplay.
     *
     * @type {Number|Boolean}
     */
    autoplay: false,

    /**
     * Stop autoplay on mouseover event.
     *
     * @type {Boolean}
     */
    hoverpause: true,

    /**
     * Allow for changing slides with left and right keyboard arrows.
     *
     * @type {Boolean}
     */
    keyboard: true,

    /**
     * Stop running `perView` number of slides from the end. Use this
     * option if you don't want to have an empty space after
     * a slider. Works only with `slider` type and a
     * non-centered `focusAt` setting.
     *
     * @type {Boolean}
     */
    bound: false,

    /**
     * Minimal swipe distance needed to change the slide. Use `false` for turning off a swiping.
     *
     * @type {Number|Boolean}
     */
    swipeThreshold: 80,

    /**
     * Minimal mouse drag distance needed to change the slide. Use `false` for turning off a dragging.
     *
     * @type {Number|Boolean}
     */
    dragThreshold: 120,

    /**
     * A maximum number of slides to which movement will be made on swiping or dragging. Use `false` for unlimited.
     *
     * @type {Number|Boolean}
     */
    perTouch: false,

    /**
     * Moving distance ratio of the slides on a swiping and dragging.
     *
     * @type {Number}
     */
    touchRatio: 0.5,

    /**
     * Angle required to activate slides moving on swiping or dragging.
     *
     * @type {Number}
     */
    touchAngle: 45,

    /**
     * Duration of the animation in milliseconds.
     *
     * @type {Number}
     */
    animationDuration: 400,

    /**
     * Allows looping the `slider` type. Slider will rewind to the first/last slide when it's at the start/end.
     *
     * @type {Boolean}
     */
    rewind: true,

    /**
     * Duration of the rewinding animation of the `slider` type in milliseconds.
     *
     * @type {Number}
     */
    rewindDuration: 800,

    /**
     * Easing function for the animation.
     *
     * @type {String}
     */
    animationTimingFunc: 'cubic-bezier(0.165, 0.840, 0.440, 1.000)',

    /**
     * Throttle costly events at most once per every wait milliseconds.
     *
     * @type {Number}
     */
    throttle: 10,

    /**
     * Moving direction mode.
     *
     * Available inputs:
     * - 'ltr' - left to right movement,
     * - 'rtl' - right to left movement.
     *
     * @type {String}
     */
    direction: 'ltr',

    /**
     * The distance value of the next and previous viewports which
     * have to peek in the current view. Accepts number and
     * pixels as a string. Left and right peeking can be
     * set up separately with a directions object.
     *
     * For example:
     * `100` - Peek 100px on the both sides.
     * { before: 100, after: 50 }` - Peek 100px on the left side and 50px on the right side.
     *
     * @type {Number|String|Object}
     */
    peek: 0,

    /**
     * Collection of options applied at specified media breakpoints.
     * For example: display two slides per view under 800px.
     * `{
     *   '800px': {
     *     perView: 2
     *   }
     * }`
     */
    breakpoints: {},

    /**
     * Collection of internally used HTML classes.
     *
     * @todo Refactor `slider` and `carousel` properties to single `type: { slider: '', carousel: '' }` object
     * @type {Object}
     */
    classes: {
      direction: {
        ltr: 'glide--ltr',
        rtl: 'glide--rtl'
      },
      slider: 'glide--slider',
      carousel: 'glide--carousel',
      swipeable: 'glide--swipeable',
      dragging: 'glide--dragging',
      cloneSlide: 'glide__slide--clone',
      activeNav: 'glide__bullet--active',
      activeSlide: 'glide__slide--active',
      disabledArrow: 'glide__arrow--disabled'
    }
  };

  /**
   * Outputs warning message to the bowser console.
   *
   * @param  {String} msg
   * @return {Void}
   */
  function warn(msg) {
    console.error("[Glide warn]: " + msg);
  }

  var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
    return typeof obj;
  } : function (obj) {
    return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
  };

  var classCallCheck = function (instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  };

  var createClass = function () {
    function defineProperties(target, props) {
      for (var i = 0; i < props.length; i++) {
        var descriptor = props[i];
        descriptor.enumerable = descriptor.enumerable || false;
        descriptor.configurable = true;
        if ("value" in descriptor) descriptor.writable = true;
        Object.defineProperty(target, descriptor.key, descriptor);
      }
    }

    return function (Constructor, protoProps, staticProps) {
      if (protoProps) defineProperties(Constructor.prototype, protoProps);
      if (staticProps) defineProperties(Constructor, staticProps);
      return Constructor;
    };
  }();

  var _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  };

  var get = function get(object, property, receiver) {
    if (object === null) object = Function.prototype;
    var desc = Object.getOwnPropertyDescriptor(object, property);

    if (desc === undefined) {
      var parent = Object.getPrototypeOf(object);

      if (parent === null) {
        return undefined;
      } else {
        return get(parent, property, receiver);
      }
    } else if ("value" in desc) {
      return desc.value;
    } else {
      var getter = desc.get;

      if (getter === undefined) {
        return undefined;
      }

      return getter.call(receiver);
    }
  };

  var inherits = function (subClass, superClass) {
    if (typeof superClass !== "function" && superClass !== null) {
      throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
    }

    subClass.prototype = Object.create(superClass && superClass.prototype, {
      constructor: {
        value: subClass,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
  };

  var possibleConstructorReturn = function (self, call) {
    if (!self) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return call && (typeof call === "object" || typeof call === "function") ? call : self;
  };

  /**
   * Converts value entered as number
   * or string to integer value.
   *
   * @param {String} value
   * @returns {Number}
   */
  function toInt(value) {
    return parseInt(value);
  }

  /**
   * Converts value entered as number
   * or string to flat value.
   *
   * @param {String} value
   * @returns {Number}
   */
  function toFloat(value) {
    return parseFloat(value);
  }

  /**
   * Indicates whether the specified value is a string.
   *
   * @param  {*}   value
   * @return {Boolean}
   */
  function isString(value) {
    return typeof value === 'string';
  }

  /**
   * Indicates whether the specified value is an object.
   *
   * @param  {*} value
   * @return {Boolean}
   *
   * @see https://github.com/jashkenas/underscore
   */
  function isObject(value) {
    var type = typeof value === 'undefined' ? 'undefined' : _typeof(value);

    return type === 'function' || type === 'object' && !!value; // eslint-disable-line no-mixed-operators
  }

  /**
   * Indicates whether the specified value is a number.
   *
   * @param  {*} value
   * @return {Boolean}
   */
  function isNumber(value) {
    return typeof value === 'number';
  }

  /**
   * Indicates whether the specified value is a function.
   *
   * @param  {*} value
   * @return {Boolean}
   */
  function isFunction(value) {
    return typeof value === 'function';
  }

  /**
   * Indicates whether the specified value is undefined.
   *
   * @param  {*} value
   * @return {Boolean}
   */
  function isUndefined(value) {
    return typeof value === 'undefined';
  }

  /**
   * Indicates whether the specified value is an array.
   *
   * @param  {*} value
   * @return {Boolean}
   */
  function isArray(value) {
    return value.constructor === Array;
  }

  /**
   * Creates and initializes specified collection of extensions.
   * Each extension receives access to instance of glide and rest of components.
   *
   * @param {Object} glide
   * @param {Object} extensions
   *
   * @returns {Object}
   */
  function mount(glide, extensions, events) {
    var components = {};

    for (var name in extensions) {
      if (isFunction(extensions[name])) {
        components[name] = extensions[name](glide, components, events);
      } else {
        warn('Extension must be a function');
      }
    }

    for (var _name in components) {
      if (isFunction(components[_name].mount)) {
        components[_name].mount();
      }
    }

    return components;
  }

  /**
   * Defines getter and setter property on the specified object.
   *
   * @param  {Object} obj         Object where property has to be defined.
   * @param  {String} prop        Name of the defined property.
   * @param  {Object} definition  Get and set definitions for the property.
   * @return {Void}
   */
  function define(obj, prop, definition) {
    Object.defineProperty(obj, prop, definition);
  }

  /**
   * Sorts aphabetically object keys.
   *
   * @param  {Object} obj
   * @return {Object}
   */
  function sortKeys(obj) {
    return Object.keys(obj).sort().reduce(function (r, k) {
      r[k] = obj[k];

      return r[k], r;
    }, {});
  }

  /**
   * Merges passed settings object with default options.
   *
   * @param  {Object} defaults
   * @param  {Object} settings
   * @return {Object}
   */
  function mergeOptions(defaults, settings) {
    var options = _extends({}, defaults, settings);

    // `Object.assign` do not deeply merge objects, so we
    // have to do it manually for every nested object
    // in options. Although it does not look smart,
    // it's smaller and faster than some fancy
    // merging deep-merge algorithm script.
    if (settings.hasOwnProperty('classes')) {
      options.classes = _extends({}, defaults.classes, settings.classes);

      if (settings.classes.hasOwnProperty('direction')) {
        options.classes.direction = _extends({}, defaults.classes.direction, settings.classes.direction);
      }
    }

    if (settings.hasOwnProperty('breakpoints')) {
      options.breakpoints = _extends({}, defaults.breakpoints, settings.breakpoints);
    }

    return options;
  }

  var EventsBus = function () {
    /**
     * Construct a EventBus instance.
     *
     * @param {Object} events
     */
    function EventsBus() {
      var events = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      classCallCheck(this, EventsBus);

      this.events = events;
      this.hop = events.hasOwnProperty;
    }

    /**
     * Adds listener to the specifed event.
     *
     * @param {String|Array} event
     * @param {Function} handler
     */


    createClass(EventsBus, [{
      key: 'on',
      value: function on(event, handler) {
        if (isArray(event)) {
          for (var i = 0; i < event.length; i++) {
            this.on(event[i], handler);
          }
        }

        // Create the event's object if not yet created
        if (!this.hop.call(this.events, event)) {
          this.events[event] = [];
        }

        // Add the handler to queue
        var index = this.events[event].push(handler) - 1;

        // Provide handle back for removal of event
        return {
          remove: function remove() {
            delete this.events[event][index];
          }
        };
      }

      /**
       * Runs registered handlers for specified event.
       *
       * @param {String|Array} event
       * @param {Object=} context
       */

    }, {
      key: 'emit',
      value: function emit(event, context) {
        if (isArray(event)) {
          for (var i = 0; i < event.length; i++) {
            this.emit(event[i], context);
          }
        }

        // If the event doesn't exist, or there's no handlers in queue, just leave
        if (!this.hop.call(this.events, event)) {
          return;
        }

        // Cycle through events queue, fire!
        this.events[event].forEach(function (item) {
          item(context || {});
        });
      }
    }]);
    return EventsBus;
  }();

  var Glide = function () {
    /**
     * Construct glide.
     *
     * @param  {String} selector
     * @param  {Object} options
     */
    function Glide(selector) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      classCallCheck(this, Glide);

      this._c = {};
      this._t = [];
      this._e = new EventsBus();

      this.disabled = false;
      this.selector = selector;
      this.settings = mergeOptions(defaults, options);
      this.index = this.settings.startAt;
    }

    /**
     * Initializes glide.
     *
     * @param {Object} extensions Collection of extensions to initialize.
     * @return {Glide}
     */


    createClass(Glide, [{
      key: 'mount',
      value: function mount$$1() {
        var extensions = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

        this._e.emit('mount.before');

        if (isObject(extensions)) {
          this._c = mount(this, extensions, this._e);
        } else {
          warn('You need to provide a object on `mount()`');
        }

        this._e.emit('mount.after');

        return this;
      }

      /**
       * Collects an instance `translate` transformers.
       *
       * @param  {Array} transformers Collection of transformers.
       * @return {Void}
       */

    }, {
      key: 'mutate',
      value: function mutate() {
        var transformers = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];

        if (isArray(transformers)) {
          this._t = transformers;
        } else {
          warn('You need to provide a array on `mutate()`');
        }

        return this;
      }

      /**
       * Updates glide with specified settings.
       *
       * @param {Object} settings
       * @return {Glide}
       */

    }, {
      key: 'update',
      value: function update() {
        var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

        this.settings = mergeOptions(this.settings, settings);

        if (settings.hasOwnProperty('startAt')) {
          this.index = settings.startAt;
        }

        this._e.emit('update');

        return this;
      }

      /**
       * Change slide with specified pattern. A pattern must be in the special format:
       * `>` - Move one forward
       * `<` - Move one backward
       * `={i}` - Go to {i} zero-based slide (eq. '=1', will go to second slide)
       * `>>` - Rewinds to end (last slide)
       * `<<` - Rewinds to start (first slide)
       *
       * @param {String} pattern
       * @return {Glide}
       */

    }, {
      key: 'go',
      value: function go(pattern) {
        this._c.Run.make(pattern);

        return this;
      }

      /**
       * Move track by specified distance.
       *
       * @param {String} distance
       * @return {Glide}
       */

    }, {
      key: 'move',
      value: function move(distance) {
        this._c.Transition.disable();
        this._c.Move.make(distance);

        return this;
      }

      /**
       * Destroy instance and revert all changes done by this._c.
       *
       * @return {Glide}
       */

    }, {
      key: 'destroy',
      value: function destroy() {
        this._e.emit('destroy');

        return this;
      }

      /**
       * Start instance autoplaying.
       *
       * @param {Boolean|Number} interval Run autoplaying with passed interval regardless of `autoplay` settings
       * @return {Glide}
       */

    }, {
      key: 'play',
      value: function play() {
        var interval = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

        if (interval) {
          this.settings.autoplay = interval;
        }

        this._e.emit('play');

        return this;
      }

      /**
       * Stop instance autoplaying.
       *
       * @return {Glide}
       */

    }, {
      key: 'pause',
      value: function pause() {
        this._e.emit('pause');

        return this;
      }

      /**
       * Sets glide into a idle status.
       *
       * @return {Glide}
       */

    }, {
      key: 'disable',
      value: function disable() {
        this.disabled = true;

        return this;
      }

      /**
       * Sets glide into a active status.
       *
       * @return {Glide}
       */

    }, {
      key: 'enable',
      value: function enable() {
        this.disabled = false;

        return this;
      }

      /**
       * Adds cuutom event listener with handler.
       *
       * @param  {String|Array} event
       * @param  {Function} handler
       * @return {Glide}
       */

    }, {
      key: 'on',
      value: function on(event, handler) {
        this._e.on(event, handler);

        return this;
      }

      /**
       * Checks if glide is a precised type.
       *
       * @param  {String} name
       * @return {Boolean}
       */

    }, {
      key: 'isType',
      value: function isType(name) {
        return this.settings.type === name;
      }

      /**
       * Gets value of the core options.
       *
       * @return {Object}
       */

    }, {
      key: 'settings',
      get: function get$$1() {
        return this._o;
      }

      /**
       * Sets value of the core options.
       *
       * @param  {Object} o
       * @return {Void}
       */
      ,
      set: function set$$1(o) {
        if (isObject(o)) {
          this._o = o;
        } else {
          warn('Options must be an `object` instance.');
        }
      }

      /**
       * Gets current index of the slider.
       *
       * @return {Object}
       */

    }, {
      key: 'index',
      get: function get$$1() {
        return this._i;
      }

      /**
       * Sets current index a slider.
       *
       * @return {Object}
       */
      ,
      set: function set$$1(i) {
        this._i = toInt(i);
      }

      /**
       * Gets type name of the slider.
       *
       * @return {String}
       */

    }, {
      key: 'type',
      get: function get$$1() {
        return this.settings.type;
      }

      /**
       * Gets value of the idle status.
       *
       * @return {Boolean}
       */

    }, {
      key: 'disabled',
      get: function get$$1() {
        return this._d;
      }

      /**
       * Sets value of the idle status.
       *
       * @return {Boolean}
       */
      ,
      set: function set$$1(status) {
        this._d = !!status;
      }
    }]);
    return Glide;
  }();

  function Run (Glide, Components, Events) {
    var Run = {
      /**
       * Initializes autorunning of the glide.
       *
       * @return {Void}
       */
      mount: function mount() {
        this._o = false;
      },


      /**
       * Makes glides running based on the passed moving schema.
       *
       * @param {String} move
       */
      make: function make(move) {
        var _this = this;

        if (!Glide.disabled) {
          Glide.disable();

          this.move = move;

          Events.emit('run.before', this.move);

          this.calculate();

          Events.emit('run', this.move);

          Components.Transition.after(function () {
            if (_this.isOffset('<') || _this.isOffset('>')) {
              _this._o = false;

              Events.emit('run.offset', _this.move);
            }

            Events.emit('run.after', _this.move);

            Glide.enable();
          });
        }
      },


      /**
       * Calculates current index based on defined move.
       *
       * @return {Void}
       */
      calculate: function calculate() {
        var move = this.move,
            length = this.length;
        var steps = move.steps,
            direction = move.direction;


        var countableSteps = isNumber(toInt(steps)) && toInt(steps) !== 0;

        switch (direction) {
          case '>':
            if (steps === '>') {
              Glide.index = length;
            } else if (this.isEnd()) {
              if (!(Glide.isType('slider') && !Glide.settings.rewind)) {
                this._o = true;

                Glide.index = 0;
              }

              Events.emit('run.end', move);
            } else if (countableSteps) {
              Glide.index += Math.min(length - Glide.index, -toInt(steps));
            } else {
              Glide.index++;
            }
            break;

          case '<':
            if (steps === '<') {
              Glide.index = 0;
            } else if (this.isStart()) {
              if (!(Glide.isType('slider') && !Glide.settings.rewind)) {
                this._o = true;

                Glide.index = length;
              }

              Events.emit('run.start', move);
            } else if (countableSteps) {
              Glide.index -= Math.min(Glide.index, toInt(steps));
            } else {
              Glide.index--;
            }
            break;

          case '=':
            Glide.index = steps;
            break;
        }
      },


      /**
       * Checks if we are on the first slide.
       *
       * @return {Boolean}
       */
      isStart: function isStart() {
        return Glide.index === 0;
      },


      /**
       * Checks if we are on the last slide.
       *
       * @return {Boolean}
       */
      isEnd: function isEnd() {
        return Glide.index === this.length;
      },


      /**
       * Checks if we are making a offset run.
       *
       * @param {String} direction
       * @return {Boolean}
       */
      isOffset: function isOffset(direction) {
        return this._o && this.move.direction === direction;
      }
    };

    define(Run, 'move', {
      /**
       * Gets value of the move schema.
       *
       * @returns {Object}
       */
      get: function get() {
        return this._m;
      },


      /**
       * Sets value of the move schema.
       *
       * @returns {Object}
       */
      set: function set(value) {
        this._m = {
          direction: value.substr(0, 1),
          steps: value.substr(1) ? value.substr(1) : 0
        };
      }
    });

    define(Run, 'length', {
      /**
       * Gets value of the running distance based
       * on zero-indexing number of slides.
       *
       * @return {Number}
       */
      get: function get() {
        var settings = Glide.settings;
        var length = Components.Html.slides.length;

        // While number of slides inside instance is smaller
        // that `perView` settings we should't run at all.
        // Running distance has to be zero.

        if (settings.perView > length) {
          return 0;
        }

        // If the `bound` option is acitve, a maximum running distance should be
        // reduced by `perView` and `focusAt` settings. Running distance
        // should end before creating an empty space after instance.
        if (Glide.isType('slider') && settings.focusAt !== 'center' && settings.bound) {
          return length - 1 - (toInt(settings.perView) - 1) + toInt(settings.focusAt);
        }

        return length - 1;
      }
    });

    define(Run, 'offset', {
      /**
       * Gets status of the offsetting flag.
       *
       * @return {Boolean}
       */
      get: function get() {
        return this._o;
      }
    });

    return Run;
  }

  /**
   * Returns a current time.
   *
   * @return {Number}
   */
  function now() {
    return new Date().getTime();
  }

  /**
   * Returns a function, that, when invoked, will only be triggered
   * at most once during a given window of time.
   *
   * @param {Function} func
   * @param {Number} wait
   * @param {Object=} options
   * @return {Function}
   *
   * @see https://github.com/jashkenas/underscore
   */
  function throttle(func, wait, options) {
    var timeout = void 0,
        context = void 0,
        args = void 0,
        result = void 0;
    var previous = 0;
    if (!options) options = {};

    var later = function later() {
      previous = options.leading === false ? 0 : now();
      timeout = null;
      result = func.apply(context, args);
      if (!timeout) context = args = null;
    };

    var throttled = function throttled() {
      var at = now();
      if (!previous && options.leading === false) previous = at;
      var remaining = wait - (at - previous);
      context = this;
      args = arguments;
      if (remaining <= 0 || remaining > wait) {
        if (timeout) {
          clearTimeout(timeout);
          timeout = null;
        }
        previous = at;
        result = func.apply(context, args);
        if (!timeout) context = args = null;
      } else if (!timeout && options.trailing !== false) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };

    throttled.cancel = function () {
      clearTimeout(timeout);
      previous = 0;
      timeout = context = args = null;
    };

    return throttled;
  }

  var MARGIN_TYPE = {
    ltr: ['marginLeft', 'marginRight'],
    rtl: ['marginRight', 'marginLeft']
  };

  function Gaps (Glide, Components, Events) {
    var Gaps = {
      /**
       * Setups gap value based on settings.
       *
       * @return {Void}
       */
      mount: function mount() {
        this.value = Glide.settings.gap;
      },


      /**
       * Applies gaps between slides. First and last
       * slides do not receive it's edge margins.
       *
       * @param {HTMLCollection} slides
       * @return {Void}
       */
      apply: function apply(slides) {
        for (var i = 0, len = slides.length; i < len; i++) {
          var style = slides[i].style;
          var direction = Components.Direction.value;

          if (i !== 0) {
            style[MARGIN_TYPE[direction][0]] = this.value / 2 + 'px';
          } else {
            style[MARGIN_TYPE[direction][0]] = '';
          }

          if (i !== slides.length - 1) {
            style[MARGIN_TYPE[direction][1]] = this.value / 2 + 'px';
          } else {
            style[MARGIN_TYPE[direction][1]] = '';
          }
        }
      },


      /**
       * Removes gaps from the slides.
       *
       * @param {HTMLCollection} slides
       * @returns {Void}
      */
      remove: function remove(slides) {
        for (var i = 0, len = slides.length; i < len; i++) {
          var style = slides[i].style;

          style.marginLeft = '';
          style.marginRight = '';
        }
      }
    };

    define(Gaps, 'value', {
      /**
       * Gets value of the gap.
       *
       * @returns {Number}
       */
      get: function get() {
        return Gaps._v;
      },


      /**
       * Sets value of the gap.
       *
       * @param {String} value
       * @return {Void}
       */
      set: function set(value) {
        Gaps._v = toInt(value);
      }
    });

    define(Gaps, 'grow', {
      /**
       * Gets additional dimentions value caused by gaps.
       * Used to increase width of the slides wrapper.
       *
       * @returns {Number}
       */
      get: function get() {
        return Gaps.value * (Components.Sizes.length - 1);
      }
    });

    define(Gaps, 'reductor', {
      /**
       * Gets reduction value caused by gaps.
       * Used to subtract width of the slides.
       *
       * @returns {Number}
       */
      get: function get() {
        var perView = Glide.settings.perView;

        return Gaps.value * (perView - 1) / perView;
      }
    });

    /**
     * Remount component:
     * - on updating via API, to update gap value
     */
    Events.on('update', function () {
      Gaps.mount();
    });

    /**
     * Apply calculated gaps:
     * - after building, so slides (including clones) will receive proper margins
     * - on updating via API, to recalculate gaps with new options
     */
    Events.on(['build.after', 'update'], throttle(function () {
      Gaps.apply(Components.Html.wrapper.children);
    }, 30));

    /**
     * Remove gaps:
     * - on destroying to bring markup to its inital state
     */
    Events.on('destroy', function () {
      Gaps.remove(Components.Html.wrapper.children);
    });

    return Gaps;
  }

  /**
   * Finds siblings nodes of the passed node.
   *
   * @param  {Element} node
   * @return {Array}
   */
  function siblings(node) {
    if (node && node.parentNode) {
      var n = node.parentNode.firstChild;
      var matched = [];

      for (; n; n = n.nextSibling) {
        if (n.nodeType === 1 && n !== node) {
          matched.push(n);
        }
      }

      return matched;
    }

    return [];
  }

  /**
   * Checks if passed node exist and is a valid element.
   *
   * @param  {Element} node
   * @return {Boolean}
   */
  function exist(node) {
    if (node && node instanceof window.HTMLElement) {
      return true;
    }

    return false;
  }

  var TRACK_SELECTOR = '[data-glide-el="track"]';

  function Html (Glide, Components) {
    var Html = {
      /**
       * Setup slider HTML nodes.
       *
       * @param {Glide} glide
       */
      mount: function mount() {
        this.root = Glide.selector;
        this.track = this.root.querySelector(TRACK_SELECTOR);
        this.slides = Array.prototype.slice.call(this.wrapper.children).filter(function (slide) {
          return !slide.classList.contains(Glide.settings.classes.cloneSlide);
        });
      }
    };

    define(Html, 'root', {
      /**
       * Gets node of the glide main element.
       *
       * @return {Object}
       */
      get: function get() {
        return Html._r;
      },


      /**
       * Sets node of the glide main element.
       *
       * @return {Object}
       */
      set: function set(r) {
        if (isString(r)) {
          r = document.querySelector(r);
        }

        if (exist(r)) {
          Html._r = r;
        } else {
          warn('Root element must be a existing Html node');
        }
      }
    });

    define(Html, 'track', {
      /**
       * Gets node of the glide track with slides.
       *
       * @return {Object}
       */
      get: function get() {
        return Html._t;
      },


      /**
       * Sets node of the glide track with slides.
       *
       * @return {Object}
       */
      set: function set(t) {
        if (exist(t)) {
          Html._t = t;
        } else {
          warn('Could not find track element. Please use ' + TRACK_SELECTOR + ' attribute.');
        }
      }
    });

    define(Html, 'wrapper', {
      /**
       * Gets node of the slides wrapper.
       *
       * @return {Object}
       */
      get: function get() {
        return Html.track.children[0];
      }
    });

    return Html;
  }

  function Peek (Glide, Components, Events) {
    var Peek = {
      /**
       * Setups how much to peek based on settings.
       *
       * @return {Void}
       */
      mount: function mount() {
        this.value = Glide.settings.peek;
      }
    };

    define(Peek, 'value', {
      /**
       * Gets value of the peek.
       *
       * @returns {Number|Object}
       */
      get: function get() {
        return Peek._v;
      },


      /**
       * Sets value of the peek.
       *
       * @param {Number|Object} value
       * @return {Void}
       */
      set: function set(value) {
        if (isObject(value)) {
          value.before = toInt(value.before);
          value.after = toInt(value.after);
        } else {
          value = toInt(value);
        }

        Peek._v = value;
      }
    });

    define(Peek, 'reductor', {
      /**
       * Gets reduction value caused by peek.
       *
       * @returns {Number}
       */
      get: function get() {
        var value = Peek.value;
        var perView = Glide.settings.perView;

        if (isObject(value)) {
          return value.before / perView + value.after / perView;
        }

        return value * 2 / perView;
      }
    });

    /**
     * Recalculate peeking sizes on:
     * - when resizing window to update to proper percents
     */
    Events.on(['resize', 'update'], function () {
      Peek.mount();
    });

    return Peek;
  }

  function Move (Glide, Components, Events) {
    var Move = {
      /**
       * Constructs move component.
       *
       * @returns {Void}
       */
      mount: function mount() {
        this._o = 0;
      },


      /**
       * Calculates a movement value based on passed offset and currently active index.
       *
       * @param  {Number} offset
       * @return {Void}
       */
      make: function make() {
        var _this = this;

        var offset = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;

        this.offset = offset;

        Events.emit('move', {
          movement: this.value
        });

        Components.Transition.after(function () {
          Events.emit('move.after', {
            movement: _this.value
          });
        });
      }
    };

    define(Move, 'offset', {
      /**
       * Gets an offset value used to modify current translate.
       *
       * @return {Object}
       */
      get: function get() {
        return Move._o;
      },


      /**
       * Sets an offset value used to modify current translate.
       *
       * @return {Object}
       */
      set: function set(value) {
        Move._o = !isUndefined(value) ? toInt(value) : 0;
      }
    });

    define(Move, 'translate', {
      /**
       * Gets a raw movement value.
       *
       * @return {Number}
       */
      get: function get() {
        return Components.Sizes.slideWidth * Glide.index;
      }
    });

    define(Move, 'value', {
      /**
       * Gets an actual movement value corrected by offset.
       *
       * @return {Number}
       */
      get: function get() {
        var offset = this.offset;
        var translate = this.translate;

        if (Components.Direction.is('rtl')) {
          return translate + offset;
        }

        return translate - offset;
      }
    });

    /**
     * Make movement to proper slide on:
     * - before build, so glide will start at `startAt` index
     * - on each standard run to move to newly calculated index
     */
    Events.on(['build.before', 'run'], function () {
      Move.make();
    });

    return Move;
  }

  function Sizes (Glide, Components, Events) {
    var Sizes = {
      /**
       * Setups dimentions of slides.
       *
       * @return {Void}
       */
      setupSlides: function setupSlides() {
        var slides = Components.Html.slides;

        for (var i = 0; i < slides.length; i++) {
          slides[i].style.width = this.slideWidth + 'px';
        }
      },


      /**
       * Setups dimentions of slides wrapper.
       *
       * @return {Void}
       */
      setupWrapper: function setupWrapper(dimention) {
        Components.Html.wrapper.style.width = this.wrapperSize + 'px';
      },


      /**
       * Removes applied styles from HTML elements.
       *
       * @returns {Void}
       */
      remove: function remove() {
        var slides = Components.Html.slides;

        for (var i = 0; i < slides.length; i++) {
          slides[i].style.width = '';
        }

        Components.Html.wrapper.style.width = '';
      }
    };

    define(Sizes, 'length', {
      /**
       * Gets count number of the slides.
       *
       * @return {Number}
       */
      get: function get() {
        return Components.Html.slides.length;
      }
    });

    define(Sizes, 'width', {
      /**
       * Gets width value of the glide.
       *
       * @return {Number}
       */
      get: function get() {
        return Components.Html.root.offsetWidth;
      }
    });

    define(Sizes, 'wrapperSize', {
      /**
       * Gets size of the slides wrapper.
       *
       * @return {Number}
       */
      get: function get() {
        return Sizes.slideWidth * Sizes.length + Components.Gaps.grow + Components.Clones.grow;
      }
    });

    define(Sizes, 'slideWidth', {
      /**
       * Gets width value of the single slide.
       *
       * @return {Number}
       */
      get: function get() {
        return Sizes.width / Glide.settings.perView - Components.Peek.reductor - Components.Gaps.reductor;
      }
    });

    /**
     * Apply calculated glide's dimensions:
     * - before building, so other dimentions (e.g. translate) will be calculated propertly
     * - when resizing window to recalculate sildes dimensions
     * - on updating via API, to calculate dimensions based on new options
     */
    Events.on(['build.before', 'resize', 'update'], function () {
      Sizes.setupSlides();
      Sizes.setupWrapper();
    });

    /**
     * Remove calculated glide's dimensions:
     * - on destoting to bring markup to its inital state
     */
    Events.on('destroy', function () {
      Sizes.remove();
    });

    return Sizes;
  }

  function Build (Glide, Components, Events) {
    var Build = {
      /**
       * Init glide building. Adds classes, sets
       * dimensions and setups initial state.
       *
       * @return {Void}
       */
      mount: function mount() {
        Events.emit('build.before');

        this.typeClass();
        this.activeClass();

        Events.emit('build.after');
      },


      /**
       * Adds `type` class to the glide element.
       *
       * @return {Void}
       */
      typeClass: function typeClass() {
        Components.Html.root.classList.add(Glide.settings.classes[Glide.settings.type]);
      },


      /**
       * Sets active class to current slide.
       *
       * @return {Void}
       */
      activeClass: function activeClass() {
        var classes = Glide.settings.classes;
        var slide = Components.Html.slides[Glide.index];

        if (slide) {
          slide.classList.add(classes.activeSlide);

          siblings(slide).forEach(function (sibling) {
            sibling.classList.remove(classes.activeSlide);
          });
        }
      },


      /**
       * Removes HTML classes applied at building.
       *
       * @return {Void}
       */
      removeClasses: function removeClasses() {
        var classes = Glide.settings.classes;

        Components.Html.root.classList.remove(classes[Glide.settings.type]);

        Components.Html.slides.forEach(function (sibling) {
          sibling.classList.remove(classes.activeSlide);
        });
      }
    };

    /**
     * Clear building classes:
     * - on destroying to bring HTML to its initial state
     * - on updating to remove classes before remounting component
     */
    Events.on(['destroy', 'update'], function () {
      Build.removeClasses();
    });

    /**
     * Remount component:
     * - on resizing of the window to calculate new dimentions
     * - on updating settings via API
     */
    Events.on(['resize', 'update'], function () {
      Build.mount();
    });

    /**
     * Swap active class of current slide:
     * - after each move to the new index
     */
    Events.on('move.after', function () {
      Build.activeClass();
    });

    return Build;
  }

  function Clones (Glide, Components, Events) {
    var Clones = {
      /**
       * Create pattern map and collect slides to be cloned.
       */
      mount: function mount() {
        this.items = [];

        if (Glide.isType('carousel')) {
          this.items = this.collect();
        }
      },


      /**
       * Collect clones with pattern.
       *
       * @return {Void}
       */
      collect: function collect() {
        var items = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
        var slides = Components.Html.slides;
        var _Glide$settings = Glide.settings,
            perView = _Glide$settings.perView,
            classes = _Glide$settings.classes;


        var start = slides.slice(0, perView);
        var end = slides.slice(-perView);

        for (var r = 0; r < Math.max(1, Math.floor(perView / slides.length)); r++) {
          for (var i = 0; i < start.length; i++) {
            var clone = start[i].cloneNode(true);

            clone.classList.add(classes.cloneSlide);

            items.push(clone);
          }

          for (var _i = 0; _i < end.length; _i++) {
            var _clone = end[_i].cloneNode(true);

            _clone.classList.add(classes.cloneSlide);

            items.unshift(_clone);
          }
        }

        return items;
      },


      /**
       * Append cloned slides with generated pattern.
       *
       * @return {Void}
       */
      append: function append() {
        var items = this.items;
        var _Components$Html = Components.Html,
            wrapper = _Components$Html.wrapper,
            slides = _Components$Html.slides;


        var half = Math.floor(items.length / 2);
        var prepend = items.slice(0, half).reverse();
        var append = items.slice(half, items.length);

        for (var i = 0; i < append.length; i++) {
          wrapper.appendChild(append[i]);
        }

        for (var _i2 = 0; _i2 < prepend.length; _i2++) {
          wrapper.insertBefore(prepend[_i2], slides[0]);
        }

        for (var _i3 = 0; _i3 < items.length; _i3++) {
          items[_i3].style.width = Components.Sizes.slideWidth + 'px';
        }
      },


      /**
       * Remove all cloned slides.
       *
       * @return {Void}
       */
      remove: function remove() {
        var items = this.items;


        for (var i = 0; i < items.length; i++) {
          Components.Html.wrapper.removeChild(items[i]);
        }
      }
    };

    define(Clones, 'grow', {
      /**
       * Gets additional dimentions value caused by clones.
       *
       * @return {Number}
       */
      get: function get() {
        return (Components.Sizes.slideWidth + Components.Gaps.value) * Clones.items.length;
      }
    });

    /**
     * Append additional slide's clones:
     * - while glide's type is `carousel`
     */
    Events.on('update', function () {
      Clones.remove();
      Clones.mount();
      Clones.append();
    });

    /**
     * Append additional slide's clones:
     * - while glide's type is `carousel`
     */
    Events.on('build.before', function () {
      if (Glide.isType('carousel')) {
        Clones.append();
      }
    });

    /**
     * Remove clones HTMLElements:
     * - on destroying, to bring HTML to its initial state
     */
    Events.on('destroy', function () {
      Clones.remove();
    });

    return Clones;
  }

  var EventsBinder = function () {
    /**
     * Construct a EventsBinder instance.
     */
    function EventsBinder() {
      var listeners = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      classCallCheck(this, EventsBinder);

      this.listeners = listeners;
    }

    /**
     * Adds events listeners to arrows HTML elements.
     *
     * @param  {String|Array} events
     * @param  {Element|Window|Document} el
     * @param  {Function} closure
     * @return {Void}
     */


    createClass(EventsBinder, [{
      key: 'on',
      value: function on(events, el, closure) {
        var capture = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

        if (isString(events)) {
          events = [events];
        }

        for (var i = 0; i < events.length; i++) {
          this.listeners[events[i]] = closure;

          el.addEventListener(events[i], this.listeners[events[i]], capture);
        }
      }

      /**
       * Removes event listeners from arrows HTML elements.
       *
       * @param  {String|Array} events
       * @param  {Element|Window|Document} el
       * @return {Void}
       */

    }, {
      key: 'off',
      value: function off(events, el) {
        if (isString(events)) {
          events = [events];
        }

        for (var i = 0; i < events.length; i++) {
          el.removeEventListener(events[i], this.listeners[events[i]], false);
        }
      }

      /**
       * Destroy collected listeners.
       *
       * @returns {Void}
       */

    }, {
      key: 'destroy',
      value: function destroy() {
        delete this.listeners;
      }
    }]);
    return EventsBinder;
  }();

  function Resize (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    var Resize = {
      /**
       * Initializes window bindings.
       */
      mount: function mount() {
        this.bind();
      },


      /**
       * Binds `rezsize` listener to the window.
       * It's a costly event, so we are debouncing it.
       *
       * @return {Void}
       */
      bind: function bind() {
        Binder.on('resize', window, throttle(function () {
          Events.emit('resize');
        }, Glide.settings.throttle));
      },


      /**
       * Unbinds listeners from the window.
       *
       * @return {Void}
       */
      unbind: function unbind() {
        Binder.off('resize', window);
      }
    };

    /**
     * Remove bindings from window:
     * - on destroying, to remove added EventListener
     */
    Events.on('destroy', function () {
      Resize.unbind();
      Binder.destroy();
    });

    return Resize;
  }

  var VALID_DIRECTIONS = ['ltr', 'rtl'];
  var FLIPED_MOVEMENTS = {
    '>': '<',
    '<': '>',
    '=': '='
  };

  function Direction (Glide, Components, Events) {
    var Direction = {
      /**
       * Setups gap value based on settings.
       *
       * @return {Void}
       */
      mount: function mount() {
        this.value = Glide.settings.direction;
      },


      /**
       * Resolves pattern based on direction value
       *
       * @param {String} pattern
       * @returns {String}
       */
      resolve: function resolve(pattern) {
        var token = pattern.slice(0, 1);

        if (this.is('rtl')) {
          return pattern.split(token).join(FLIPED_MOVEMENTS[token]);
        }

        return pattern;
      },


      /**
       * Checks value of direction mode.
       *
       * @param {String} direction
       * @returns {Boolean}
       */
      is: function is(direction) {
        return this.value === direction;
      },


      /**
       * Applies direction class to the root HTML element.
       *
       * @return {Void}
       */
      addClass: function addClass() {
        Components.Html.root.classList.add(Glide.settings.classes.direction[this.value]);
      },


      /**
       * Removes direction class from the root HTML element.
       *
       * @return {Void}
       */
      removeClass: function removeClass() {
        Components.Html.root.classList.remove(Glide.settings.classes.direction[this.value]);
      }
    };

    define(Direction, 'value', {
      /**
       * Gets value of the direction.
       *
       * @returns {Number}
       */
      get: function get() {
        return Direction._v;
      },


      /**
       * Sets value of the direction.
       *
       * @param {String} value
       * @return {Void}
       */
      set: function set(value) {
        if (VALID_DIRECTIONS.indexOf(value) > -1) {
          Direction._v = value;
        } else {
          warn('Direction value must be `ltr` or `rtl`');
        }
      }
    });

    /**
     * Clear direction class:
     * - on destroy to bring HTML to its initial state
     * - on update to remove class before reappling bellow
     */
    Events.on(['destroy', 'update'], function () {
      Direction.removeClass();
    });

    /**
     * Remount component:
     * - on update to reflect changes in direction value
     */
    Events.on('update', function () {
      Direction.mount();
    });

    /**
     * Apply direction class:
     * - before building to apply class for the first time
     * - on updating to reapply direction class that may changed
     */
    Events.on(['build.before', 'update'], function () {
      Direction.addClass();
    });

    return Direction;
  }

  /**
   * Reflects value of glide movement.
   *
   * @param  {Object} Glide
   * @param  {Object} Components
   * @return {Object}
   */
  function Rtl (Glide, Components) {
    return {
      /**
       * Negates the passed translate if glide is in RTL option.
       *
       * @param  {Number} translate
       * @return {Number}
       */
      modify: function modify(translate) {
        if (Components.Direction.is('rtl')) {
          return -translate;
        }

        return translate;
      }
    };
  }

  /**
   * Updates glide movement with a `gap` settings.
   *
   * @param  {Object} Glide
   * @param  {Object} Components
   * @return {Object}
   */
  function Gap (Glide, Components) {
    return {
      /**
       * Modifies passed translate value with number in the `gap` settings.
       *
       * @param  {Number} translate
       * @return {Number}
       */
      modify: function modify(translate) {
        return translate + Components.Gaps.value * Glide.index;
      }
    };
  }

  /**
   * Updates glide movement with width of additional clones width.
   *
   * @param  {Object} Glide
   * @param  {Object} Components
   * @return {Object}
   */
  function Grow (Glide, Components) {
    return {
      /**
       * Adds to the passed translate width of the half of clones.
       *
       * @param  {Number} translate
       * @return {Number}
       */
      modify: function modify(translate) {
        return translate + Components.Clones.grow / 2;
      }
    };
  }

  /**
   * Updates glide movement with a `peek` settings.
   *
   * @param  {Object} Glide
   * @param  {Object} Components
   * @return {Object}
   */
  function Peeking (Glide, Components) {
    return {
      /**
       * Modifies passed translate value with a `peek` setting.
       *
       * @param  {Number} translate
       * @return {Number}
       */
      modify: function modify(translate) {
        if (Glide.settings.focusAt >= 0) {
          var peek = Components.Peek.value;

          if (isObject(peek)) {
            return translate - peek.before;
          }

          return translate - peek;
        }

        return translate;
      }
    };
  }

  /**
   * Updates glide movement with a `focusAt` settings.
   *
   * @param  {Object} Glide
   * @param  {Object} Components
   * @return {Object}
   */
  function Focusing (Glide, Components) {
    return {
      /**
       * Modifies passed translate value with index in the `focusAt` setting.
       *
       * @param  {Number} translate
       * @return {Number}
       */
      modify: function modify(translate) {
        var gap = Components.Gaps.value;
        var width = Components.Sizes.width;
        var focusAt = Glide.settings.focusAt;
        var slideWidth = Components.Sizes.slideWidth;

        if (focusAt === 'center') {
          return translate - (width / 2 - slideWidth / 2);
        }

        return translate - slideWidth * focusAt - gap * focusAt;
      }
    };
  }

  /**
   * Applies diffrent transformers on translate value.
   *
   * @param  {Object} Glide
   * @param  {Object} Components
   * @return {Object}
   */
  function mutator (Glide, Components, Events) {
    /**
     * Merge instance transformers with collection of default transformers.
     * It's important that the Rtl component be last on the list,
     * so it reflects all previous transformations.
     *
     * @type {Array}
     */
    var TRANSFORMERS = [Gap, Grow, Peeking, Focusing].concat(Glide._t, [Rtl]);

    return {
      /**
       * Piplines translate value with registered transformers.
       *
       * @param  {Number} translate
       * @return {Number}
       */
      mutate: function mutate(translate) {
        for (var i = 0; i < TRANSFORMERS.length; i++) {
          var transformer = TRANSFORMERS[i];

          if (isFunction(transformer) && isFunction(transformer().modify)) {
            translate = transformer(Glide, Components, Events).modify(translate);
          } else {
            warn('Transformer should be a function that returns an object with `modify()` method');
          }
        }

        return translate;
      }
    };
  }

  function Translate (Glide, Components, Events) {
    var Translate = {
      /**
       * Sets value of translate on HTML element.
       *
       * @param {Number} value
       * @return {Void}
       */
      set: function set(value) {
        var transform = mutator(Glide, Components).mutate(value);

        Components.Html.wrapper.style.transform = 'translate3d(' + -1 * transform + 'px, 0px, 0px)';
      },


      /**
       * Removes value of translate from HTML element.
       *
       * @return {Void}
       */
      remove: function remove() {
        Components.Html.wrapper.style.transform = '';
      }
    };

    /**
     * Set new translate value:
     * - on move to reflect index change
     * - on updating via API to reflect possible changes in options
     */
    Events.on('move', function (context) {
      var gap = Components.Gaps.value;
      var length = Components.Sizes.length;
      var width = Components.Sizes.slideWidth;

      if (Glide.isType('carousel') && Components.Run.isOffset('<')) {
        Components.Transition.after(function () {
          Events.emit('translate.jump');

          Translate.set(width * (length - 1));
        });

        return Translate.set(-width - gap * length);
      }

      if (Glide.isType('carousel') && Components.Run.isOffset('>')) {
        Components.Transition.after(function () {
          Events.emit('translate.jump');

          Translate.set(0);
        });

        return Translate.set(width * length + gap * length);
      }

      return Translate.set(context.movement);
    });

    /**
     * Remove translate:
     * - on destroying to bring markup to its inital state
     */
    Events.on('destroy', function () {
      Translate.remove();
    });

    return Translate;
  }

  function Transition (Glide, Components, Events) {
    /**
     * Holds inactivity status of transition.
     * When true transition is not applied.
     *
     * @type {Boolean}
     */
    var disabled = false;

    var Transition = {
      /**
       * Composes string of the CSS transition.
       *
       * @param {String} property
       * @return {String}
       */
      compose: function compose(property) {
        var settings = Glide.settings;

        if (!disabled) {
          return property + ' ' + this.duration + 'ms ' + settings.animationTimingFunc;
        }

        return property + ' 0ms ' + settings.animationTimingFunc;
      },


      /**
       * Sets value of transition on HTML element.
       *
       * @param {String=} property
       * @return {Void}
       */
      set: function set() {
        var property = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'transform';

        Components.Html.wrapper.style.transition = this.compose(property);
      },


      /**
       * Removes value of transition from HTML element.
       *
       * @return {Void}
       */
      remove: function remove() {
        Components.Html.wrapper.style.transition = '';
      },


      /**
       * Runs callback after animation.
       *
       * @param  {Function} callback
       * @return {Void}
       */
      after: function after(callback) {
        setTimeout(function () {
          callback();
        }, this.duration);
      },


      /**
       * Enable transition.
       *
       * @return {Void}
       */
      enable: function enable() {
        disabled = false;

        this.set();
      },


      /**
       * Disable transition.
       *
       * @return {Void}
       */
      disable: function disable() {
        disabled = true;

        this.set();
      }
    };

    define(Transition, 'duration', {
      /**
       * Gets duration of the transition based
       * on currently running animation type.
       *
       * @return {Number}
       */
      get: function get() {
        var settings = Glide.settings;

        if (Glide.isType('slider') && Components.Run.offset) {
          return settings.rewindDuration;
        }

        return settings.animationDuration;
      }
    });

    /**
     * Set transition `style` value:
     * - on each moving, because it may be cleared by offset move
     */
    Events.on('move', function () {
      Transition.set();
    });

    /**
     * Disable transition:
     * - before initial build to avoid transitioning from `0` to `startAt` index
     * - while resizing window and recalculating dimentions
     * - on jumping from offset transition at start and end edges in `carousel` type
     */
    Events.on(['build.before', 'resize', 'translate.jump'], function () {
      Transition.disable();
    });

    /**
     * Enable transition:
     * - on each running, because it may be disabled by offset move
     */
    Events.on('run', function () {
      Transition.enable();
    });

    /**
     * Remove transition:
     * - on destroying to bring markup to its inital state
     */
    Events.on('destroy', function () {
      Transition.remove();
    });

    return Transition;
  }

  var START_EVENTS = ['touchstart', 'mousedown'];
  var MOVE_EVENTS = ['touchmove', 'mousemove'];
  var END_EVENTS = ['touchend', 'touchcancel', 'mouseup', 'mouseleave'];
  var MOUSE_EVENTS = ['mousedown', 'mousemove', 'mouseup', 'mouseleave'];

  function Swipe (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    var swipeSin = 0;
    var swipeStartX = 0;
    var swipeStartY = 0;
    var disabled = false;

    var Swipe = {
      /**
       * Initializes swipe bindings.
       *
       * @return {Void}
       */
      mount: function mount() {
        this.bindSwipeStart();
      },


      /**
       * Handler for `swipestart` event. Calculates entry points of the user's tap.
       *
       * @param {Object} event
       * @return {Void}
       */
      start: function start(event) {
        if (!disabled && !Glide.disabled) {
          this.disable();

          var swipe = this.touches(event);

          swipeSin = null;
          swipeStartX = toInt(swipe.pageX);
          swipeStartY = toInt(swipe.pageY);

          this.bindSwipeMove();
          this.bindSwipeEnd();

          Events.emit('swipe.start');
        }
      },


      /**
       * Handler for `swipemove` event. Calculates user's tap angle and distance.
       *
       * @param {Object} event
       */
      move: function move(event) {
        if (!Glide.disabled) {
          var _Glide$settings = Glide.settings,
              touchAngle = _Glide$settings.touchAngle,
              touchRatio = _Glide$settings.touchRatio,
              classes = _Glide$settings.classes;


          var swipe = this.touches(event);

          var subExSx = toInt(swipe.pageX) - swipeStartX;
          var subEySy = toInt(swipe.pageY) - swipeStartY;
          var powEX = Math.abs(subExSx << 2);
          var powEY = Math.abs(subEySy << 2);
          var swipeHypotenuse = (powEX + powEY) * (powEX + powEY);
          var swipeCathetus = powEY * powEY;

          swipeSin = Math.asin(swipeCathetus / swipeHypotenuse);

          Components.Move.make(subExSx * toFloat(touchRatio));

          if (swipeSin * 180 / Math.PI < touchAngle) {
            event.stopPropagation();

            Components.Html.root.classList.add(classes.dragging);

            Events.emit('swipe.move');
          } else {
            return false;
          }
        }
      },


      /**
       * Handler for `swipeend` event. Finitializes user's tap and decides about glide move.
       *
       * @param {Object} event
       * @return {Void}
       */
      end: function end(event) {
        if (!Glide.disabled) {
          var settings = Glide.settings;

          var swipe = this.touches(event);
          var threshold = this.threshold(event);

          var swipeDistance = swipe.pageX - swipeStartX;
          var swipeDeg = swipeSin * 180 / Math.PI;
          var steps = Math.round(swipeDistance / Components.Sizes.slideWidth);

          this.enable();

          if (swipeDistance > threshold && swipeDeg < settings.touchAngle) {
            // While swipe is positive and greater than threshold move backward.
            if (settings.perTouch) {
              steps = Math.min(steps, toInt(settings.perTouch));
            }

            if (Components.Direction.is('rtl')) {
              steps = -steps;
            }

            Components.Run.make(Components.Direction.resolve('<' + steps));
          } else if (swipeDistance < -threshold && swipeDeg < settings.touchAngle) {
            // While swipe is negative and lower than negative threshold move forward.
            if (settings.perTouch) {
              steps = Math.max(steps, -toInt(settings.perTouch));
            }

            if (Components.Direction.is('rtl')) {
              steps = -steps;
            }

            Components.Run.make(Components.Direction.resolve('>' + steps));
          } else {
            // While swipe don't reach distance apply previous transform.
            Components.Move.make();
          }

          Components.Html.root.classList.remove(settings.classes.dragging);

          this.unbindSwipeMove();
          this.unbindSwipeEnd();

          Events.emit('swipe.end');
        }
      },


      /**
       * Binds swipe's starting event.
       *
       * @return {Void}
       */
      bindSwipeStart: function bindSwipeStart() {
        var _this = this;

        var settings = Glide.settings;

        if (settings.swipeThreshold) {
          Binder.on(START_EVENTS[0], Components.Html.wrapper, function (event) {
            _this.start(event);
          }, { passive: true });
        }

        if (settings.dragThreshold) {
          Binder.on(START_EVENTS[1], Components.Html.wrapper, function (event) {
            _this.start(event);
          });
        }
      },


      /**
       * Unbinds swipe's starting event.
       *
       * @return {Void}
       */
      unbindSwipeStart: function unbindSwipeStart() {
        Binder.off(START_EVENTS[0], Components.Html.wrapper);
        Binder.off(START_EVENTS[1], Components.Html.wrapper);
      },


      /**
       * Binds swipe's moving event.
       *
       * @return {Void}
       */
      bindSwipeMove: function bindSwipeMove() {
        var _this2 = this;

        Binder.on(MOVE_EVENTS, Components.Html.wrapper, throttle(function (event) {
          _this2.move(event);
        }, Glide.settings.throttle), { passive: true });
      },


      /**
       * Unbinds swipe's moving event.
       *
       * @return {Void}
       */
      unbindSwipeMove: function unbindSwipeMove() {
        Binder.off(MOVE_EVENTS, Components.Html.wrapper);
      },


      /**
       * Binds swipe's ending event.
       *
       * @return {Void}
       */
      bindSwipeEnd: function bindSwipeEnd() {
        var _this3 = this;

        Binder.on(END_EVENTS, Components.Html.wrapper, function (event) {
          _this3.end(event);
        });
      },


      /**
       * Unbinds swipe's ending event.
       *
       * @return {Void}
       */
      unbindSwipeEnd: function unbindSwipeEnd() {
        Binder.off(END_EVENTS, Components.Html.wrapper);
      },


      /**
       * Normalizes event touches points accorting to different types.
       *
       * @param {Object} event
       */
      touches: function touches(event) {
        if (MOUSE_EVENTS.indexOf(event.type) > -1) {
          return event;
        }

        return event.touches[0] || event.changedTouches[0];
      },


      /**
       * Gets value of minimum swipe distance settings based on event type.
       *
       * @return {Number}
       */
      threshold: function threshold(event) {
        var settings = Glide.settings;

        if (MOUSE_EVENTS.indexOf(event.type) > -1) {
          return settings.dragThreshold;
        }

        return settings.swipeThreshold;
      },


      /**
       * Enables swipe event.
       *
       * @return {self}
       */
      enable: function enable() {
        disabled = false;

        Components.Transition.enable();

        return this;
      },


      /**
       * Disables swipe event.
       *
       * @return {self}
       */
      disable: function disable() {
        disabled = true;

        Components.Transition.disable();

        return this;
      }
    };

    /**
     * Add component class:
     * - after initial building
     */
    Events.on('build.after', function () {
      Components.Html.root.classList.add(Glide.settings.classes.swipeable);
    });

    /**
     * Remove swiping bindings:
     * - on destroying, to remove added EventListeners
     */
    Events.on('destroy', function () {
      Swipe.unbindSwipeStart();
      Swipe.unbindSwipeMove();
      Swipe.unbindSwipeEnd();
      Binder.destroy();
    });

    return Swipe;
  }

  function Images (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    var Images = {
      /**
       * Binds listener to glide wrapper.
       *
       * @return {Void}
       */
      mount: function mount() {
        this.bind();
      },


      /**
       * Binds `dragstart` event on wrapper to prevent dragging images.
       *
       * @return {Void}
       */
      bind: function bind() {
        Binder.on('dragstart', Components.Html.wrapper, this.dragstart);
      },


      /**
       * Unbinds `dragstart` event on wrapper.
       *
       * @return {Void}
       */
      unbind: function unbind() {
        Binder.off('dragstart', Components.Html.wrapper);
      },


      /**
       * Event handler. Prevents dragging.
       *
       * @return {Void}
       */
      dragstart: function dragstart(event) {
        event.preventDefault();
      }
    };

    /**
     * Remove bindings from images:
     * - on destroying, to remove added EventListeners
     */
    Events.on('destroy', function () {
      Images.unbind();
      Binder.destroy();
    });

    return Images;
  }

  function Anchors (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    /**
     * Holds detaching status of anchors.
     * Prevents detaching of already detached anchors.
     *
     * @private
     * @type {Boolean}
     */
    var detached = false;

    /**
     * Holds preventing status of anchors.
     * If `true` redirection after click will be disabled.
     *
     * @private
     * @type {Boolean}
     */
    var prevented = false;

    var Anchors = {
      /**
       * Setups a initial state of anchors component.
       *
       * @returns {Void}
       */
      mount: function mount() {
        /**
         * Holds collection of anchors elements.
         *
         * @private
         * @type {HTMLCollection}
         */
        this._a = Components.Html.wrapper.querySelectorAll('a');

        this.bind();
      },


      /**
       * Binds events to anchors inside a track.
       *
       * @return {Void}
       */
      bind: function bind() {
        Binder.on('click', Components.Html.wrapper, this.click);
      },


      /**
       * Unbinds events attached to anchors inside a track.
       *
       * @return {Void}
       */
      unbind: function unbind() {
        Binder.off('click', Components.Html.wrapper);
      },


      /**
       * Handler for click event. Prevents clicks when glide is in `prevent` status.
       *
       * @param  {Object} event
       * @return {Void}
       */
      click: function click(event) {
        event.stopPropagation();

        if (prevented) {
          event.preventDefault();
        }
      },


      /**
       * Detaches anchors click event inside glide.
       *
       * @return {self}
       */
      detach: function detach() {
        prevented = true;

        if (!detached) {
          for (var i = 0; i < this.items.length; i++) {
            this.items[i].draggable = false;

            this.items[i].setAttribute('data-href', this.items[i].getAttribute('href'));

            this.items[i].removeAttribute('href');
          }

          detached = true;
        }

        return this;
      },


      /**
       * Attaches anchors click events inside glide.
       *
       * @return {self}
       */
      attach: function attach() {
        prevented = false;

        if (detached) {
          for (var i = 0; i < this.items.length; i++) {
            this.items[i].draggable = true;

            this.items[i].setAttribute('href', this.items[i].getAttribute('data-href'));
          }

          detached = false;
        }

        return this;
      }
    };

    define(Anchors, 'items', {
      /**
       * Gets collection of the arrows HTML elements.
       *
       * @return {HTMLElement[]}
       */
      get: function get() {
        return Anchors._a;
      }
    });

    /**
     * Detach anchors inside slides:
     * - on swiping, so they won't redirect to its `href` attributes
     */
    Events.on('swipe.move', function () {
      Anchors.detach();
    });

    /**
     * Attach anchors inside slides:
     * - after swiping and transitions ends, so they can redirect after click again
     */
    Events.on('swipe.end', function () {
      Components.Transition.after(function () {
        Anchors.attach();
      });
    });

    /**
     * Unbind anchors inside slides:
     * - on destroying, to bring anchors to its initial state
     */
    Events.on('destroy', function () {
      Anchors.attach();
      Anchors.unbind();
      Binder.destroy();
    });

    return Anchors;
  }

  var NAV_SELECTOR = '[data-glide-el="controls[nav]"]';
  var CONTROLS_SELECTOR = '[data-glide-el^="controls"]';

  function Controls (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    var Controls = {
      /**
       * Inits arrows. Binds events listeners
       * to the arrows HTML elements.
       *
       * @return {Void}
       */
      mount: function mount() {
        /**
         * Collection of navigation HTML elements.
         *
         * @private
         * @type {HTMLCollection}
         */
        this._n = Components.Html.root.querySelectorAll(NAV_SELECTOR);

        /**
         * Collection of controls HTML elements.
         *
         * @private
         * @type {HTMLCollection}
         */
        this._c = Components.Html.root.querySelectorAll(CONTROLS_SELECTOR);

        this.addBindings();
      },


      /**
       * Sets active class to current slide.
       *
       * @return {Void}
       */
      setActive: function setActive() {
        for (var i = 0; i < this._n.length; i++) {
          this.addClass(this._n[i].children);
        }
      },


      /**
       * Removes active class to current slide.
       *
       * @return {Void}
       */
      removeActive: function removeActive() {
        for (var i = 0; i < this._n.length; i++) {
          this.removeClass(this._n[i].children);
        }
      },


      /**
       * Toggles active class on items inside navigation.
       *
       * @param  {HTMLElement} controls
       * @return {Void}
       */
      addClass: function addClass(controls) {
        var settings = Glide.settings;
        var item = controls[Glide.index];

        item.classList.add(settings.classes.activeNav);

        siblings(item).forEach(function (sibling) {
          sibling.classList.remove(settings.classes.activeNav);
        });
      },


      /**
       * Removes active class from active control.
       *
       * @param  {HTMLElement} controls
       * @return {Void}
       */
      removeClass: function removeClass(controls) {
        controls[Glide.index].classList.remove(Glide.settings.classes.activeNav);
      },


      /**
       * Adds handles to the each group of controls.
       *
       * @return {Void}
       */
      addBindings: function addBindings() {
        for (var i = 0; i < this._c.length; i++) {
          this.bind(this._c[i].children);
        }
      },


      /**
       * Removes handles from the each group of controls.
       *
       * @return {Void}
       */
      removeBindings: function removeBindings() {
        for (var i = 0; i < this._c.length; i++) {
          this.unbind(this._c[i].children);
        }
      },


      /**
       * Binds events to arrows HTML elements.
       *
       * @param {HTMLCollection} elements
       * @return {Void}
       */
      bind: function bind(elements) {
        for (var i = 0; i < elements.length; i++) {
          Binder.on(['click', 'touchstart'], elements[i], this.click);
        }
      },


      /**
       * Unbinds events binded to the arrows HTML elements.
       *
       * @param {HTMLCollection} elements
       * @return {Void}
       */
      unbind: function unbind(elements) {
        for (var i = 0; i < elements.length; i++) {
          Binder.off(['click', 'touchstart'], elements[i]);
        }
      },


      /**
       * Handles `click` event on the arrows HTML elements.
       * Moves slider in driection precised in
       * `data-glide-dir` attribute.
       *
       * @param {Object} event
       * @return {Void}
       */
      click: function click(event) {
        event.preventDefault();

        Components.Run.make(Components.Direction.resolve(event.currentTarget.getAttribute('data-glide-dir')));
      }
    };

    define(Controls, 'items', {
      /**
       * Gets collection of the controls HTML elements.
       *
       * @return {HTMLElement[]}
       */
      get: function get() {
        return Controls._c;
      }
    });

    /**
     * Swap active class of current navigation item:
     * - after mounting to set it to initial index
     * - after each move to the new index
     */
    Events.on(['mount.after', 'move.after'], function () {
      Controls.setActive();
    });

    /**
     * Remove bindings and HTML Classes:
     * - on destroying, to bring markup to its initial state
     */
    Events.on('destroy', function () {
      Controls.removeBindings();
      Controls.removeActive();
      Binder.destroy();
    });

    return Controls;
  }

  function Keyboard (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    var Keyboard = {
      /**
       * Binds keyboard events on component mount.
       *
       * @return {Void}
       */
      mount: function mount() {
        if (Glide.settings.keyboard) {
          this.bind();
        }
      },


      /**
       * Adds keyboard press events.
       *
       * @return {Void}
       */
      bind: function bind() {
        Binder.on('keyup', document, this.press);
      },


      /**
       * Removes keyboard press events.
       *
       * @return {Void}
       */
      unbind: function unbind() {
        Binder.off('keyup', document);
      },


      /**
       * Handles keyboard's arrows press and moving glide foward and backward.
       *
       * @param  {Object} event
       * @return {Void}
       */
      press: function press(event) {
        if (event.keyCode === 39) {
          Components.Run.make(Components.Direction.resolve('>'));
        }

        if (event.keyCode === 37) {
          Components.Run.make(Components.Direction.resolve('<'));
        }
      }
    };

    /**
     * Remove bindings from keyboard:
     * - on destroying to remove added events
     * - on updating to remove events before remounting
     */
    Events.on(['destroy', 'update'], function () {
      Keyboard.unbind();
    });

    /**
     * Remount component
     * - on updating to reflect potential changes in settings
     */
    Events.on('update', function () {
      Keyboard.mount();
    });

    /**
     * Destroy binder:
     * - on destroying to remove listeners
     */
    Events.on('destroy', function () {
      Binder.destroy();
    });

    return Keyboard;
  }

  function Autoplay (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    var Autoplay = {
      /**
       * Initializes autoplaying and events.
       *
       * @return {Void}
       */
      mount: function mount() {
        this.start();

        if (Glide.settings.hoverpause) {
          this.bind();
        }
      },


      /**
       * Starts autoplaying in configured interval.
       *
       * @param {Boolean|Number} force Run autoplaying with passed interval regardless of `autoplay` settings
       * @return {Void}
       */
      start: function start() {
        var _this = this;

        if (Glide.settings.autoplay) {
          if (isUndefined(this._i)) {
            this._i = setInterval(function () {
              _this.stop();

              Components.Run.make('>');

              _this.start();
            }, this.time);
          }
        }
      },


      /**
       * Stops autorunning of the glide.
       *
       * @return {Void}
       */
      stop: function stop() {
        this._i = clearInterval(this._i);
      },


      /**
       * Stops autoplaying while mouse is over glide's area.
       *
       * @return {Void}
       */
      bind: function bind() {
        var _this2 = this;

        Binder.on('mouseover', Components.Html.root, function () {
          _this2.stop();
        });

        Binder.on('mouseout', Components.Html.root, function () {
          _this2.start();
        });
      },


      /**
       * Unbind mouseover events.
       *
       * @returns {Void}
       */
      unbind: function unbind() {
        Binder.off(['mouseover', 'mouseout'], Components.Html.root);
      }
    };

    define(Autoplay, 'time', {
      /**
       * Gets time period value for the autoplay interval. Prioritizes
       * times in `data-glide-autoplay` attrubutes over options.
       *
       * @return {Number}
       */
      get: function get() {
        var autoplay = Components.Html.slides[Glide.index].getAttribute('data-glide-autoplay');

        if (autoplay) {
          return toInt(autoplay);
        }

        return toInt(Glide.settings.autoplay);
      }
    });

    /**
     * Stop autoplaying and unbind events:
     * - on destroying, to clear defined interval
     * - on updating via API to reset interval that may changed
     */
    Events.on(['destroy', 'update'], function () {
      Autoplay.unbind();
    });

    /**
     * Stop autoplaying:
     * - before each run, to restart autoplaying
     * - on pausing via API
     * - on destroying, to clear defined interval
     * - while starting a swipe
     * - on updating via API to reset interval that may changed
     */
    Events.on(['run.before', 'pause', 'destroy', 'swipe.start', 'update'], function () {
      Autoplay.stop();
    });

    /**
     * Start autoplaying:
     * - after each run, to restart autoplaying
     * - on playing via API
     * - while ending a swipe
     */
    Events.on(['run.after', 'play', 'swipe.end'], function () {
      Autoplay.start();
    });

    /**
     * Remount autoplaying:
     * - on updating via API to reset interval that may changed
     */
    Events.on('update', function () {
      Autoplay.mount();
    });

    /**
     * Destroy a binder:
     * - on destroying glide instance to clearup listeners
     */
    Events.on('destroy', function () {
      Binder.destroy();
    });

    return Autoplay;
  }

  /**
   * Sorts keys of breakpoint object so they will be ordered from lower to bigger.
   *
   * @param {Object} points
   * @returns {Object}
   */
  function sortBreakpoints(points) {
    if (isObject(points)) {
      return sortKeys(points);
    } else {
      warn('Breakpoints option must be an object');
    }

    return {};
  }

  function Breakpoints (Glide, Components, Events) {
    /**
     * Instance of the binder for DOM Events.
     *
     * @type {EventsBinder}
     */
    var Binder = new EventsBinder();

    /**
     * Holds reference to settings.
     *
     * @type {Object}
     */
    var settings = Glide.settings;

    /**
     * Holds reference to breakpoints object in settings. Sorts breakpoints
     * from smaller to larger. It is required in order to proper
     * matching currently active breakpoint settings.
     *
     * @type {Object}
     */
    var points = sortBreakpoints(settings.breakpoints);

    /**
     * Cache initial settings before overwritting.
     *
     * @type {Object}
     */
    var defaults = _extends({}, settings);

    var Breakpoints = {
      /**
       * Matches settings for currectly matching media breakpoint.
       *
       * @param {Object} points
       * @returns {Object}
       */
      match: function match(points) {
        if (typeof window.matchMedia !== 'undefined') {
          for (var point in points) {
            if (points.hasOwnProperty(point)) {
              if (window.matchMedia('(max-width: ' + point + 'px)').matches) {
                return points[point];
              }
            }
          }
        }

        return defaults;
      }
    };

    /**
     * Overwrite instance settings with currently matching breakpoint settings.
     * This happens right after component initialization.
     */
    _extends(settings, Breakpoints.match(points));

    /**
     * Update glide with settings of matched brekpoint:
     * - window resize to update slider
     */
    Binder.on('resize', window, throttle(function () {
      Glide.settings = mergeOptions(settings, Breakpoints.match(points));
    }, Glide.settings.throttle));

    /**
     * Resort and update default settings:
     * - on reinit via API, so breakpoint matching will be performed with options
     */
    Events.on('update', function () {
      points = sortBreakpoints(points);

      defaults = _extends({}, settings);
    });

    /**
     * Unbind resize listener:
     * - on destroying, to bring markup to its initial state
     */
    Events.on('destroy', function () {
      Binder.off('resize', window);
    });

    return Breakpoints;
  }

  var COMPONENTS = {
    // Required
    Html: Html,
    Translate: Translate,
    Transition: Transition,
    Direction: Direction,
    Peek: Peek,
    Sizes: Sizes,
    Gaps: Gaps,
    Move: Move,
    Clones: Clones,
    Resize: Resize,
    Build: Build,
    Run: Run,

    // Optional
    Swipe: Swipe,
    Images: Images,
    Anchors: Anchors,
    Controls: Controls,
    Keyboard: Keyboard,
    Autoplay: Autoplay,
    Breakpoints: Breakpoints
  };

  var Glide$1 = function (_Core) {
    inherits(Glide$$1, _Core);

    function Glide$$1() {
      classCallCheck(this, Glide$$1);
      return possibleConstructorReturn(this, (Glide$$1.__proto__ || Object.getPrototypeOf(Glide$$1)).apply(this, arguments));
    }

    createClass(Glide$$1, [{
      key: 'mount',
      value: function mount() {
        var extensions = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

        return get(Glide$$1.prototype.__proto__ || Object.getPrototypeOf(Glide$$1.prototype), 'mount', this).call(this, _extends({}, COMPONENTS, extensions));
      }
    }]);
    return Glide$$1;
  }(Glide);

  return Glide$1;

})));

!function(i){"use strict";"function"==typeof define&&define.amd?define(["jquery"],i):"undefined"!=typeof exports?module.exports=i(require("jquery")):i(jQuery)}(function(d){"use strict";var s,l=window.Slick||{};s=0,(l=function(i,e){var t,o=this;o.defaults={adaptiveHeight:!1,appendArrows:d(i),appendDots:d(i),arrows:!0,arrowsPlacement:null,asNavFor:null,prevArrow:'<button class="slick-prev" type="button"><span class="slick-prev-icon" aria-hidden="true"></span><span class="slick-sr-only">Previous</span></button>',nextArrow:'<button class="slick-next" type="button"><span class="slick-next-icon" aria-hidden="true"></span><span class="slick-sr-only">Next</span></button>',autoplay:!1,autoplaySpeed:3e3,centerMode:!1,centerPadding:"50px",cssEase:"ease",customPaging:function(i,e){return d('<button type="button"><span class="slick-dot-icon" aria-hidden="true"></span><span class="slick-sr-only">Go to slide '+(e+1)+"</span></button>")},dots:!1,dotsClass:"slick-dots",draggable:!0,easing:"linear",edgeFriction:.35,fade:!1,infinite:!0,initialSlide:0,instructionsText:null,lazyLoad:"ondemand",mobileFirst:!1,playIcon:'<span class="slick-play-icon" aria-hidden="true"></span>',pauseIcon:'<span class="slick-pause-icon" aria-hidden="true"></span>',pauseOnHover:!0,pauseOnFocus:!0,pauseOnDotsHover:!1,regionLabel:"carousel",respondTo:"window",responsive:null,rows:1,rtl:!1,slide:"",slidesPerRow:1,slidesToShow:1,slidesToScroll:1,speed:500,swipe:!0,swipeToSlide:!1,touchMove:!0,touchThreshold:5,useAutoplayToggleButton:!0,useCSS:!0,useGroupRole:!0,useTransform:!0,variableWidth:!1,vertical:!1,verticalSwiping:!1,waitForAnimate:!0,zIndex:1e3},o.initials={animating:!1,dragging:!1,autoPlayTimer:null,currentDirection:0,currentLeft:null,currentSlide:0,direction:1,$dots:null,$instructionsText:null,listWidth:null,listHeight:null,loadIndex:0,$nextArrow:null,$pauseButton:null,$pauseIcon:null,$playIcon:null,$prevArrow:null,scrolling:!1,slideCount:null,slideWidth:null,$slideTrack:null,$slides:null,sliding:!1,slideOffset:0,swipeLeft:null,swiping:!1,$list:null,touchObject:{},transformsEnabled:!1,unslicked:!1},d.extend(o,o.initials),o.activeBreakpoint=null,o.animType=null,o.animProp=null,o.breakpoints=[],o.breakpointSettings=[],o.cssTransitions=!1,o.focussed=!1,o.interrupted=!1,o.hidden="hidden",o.paused=!0,o.positionProp=null,o.respondTo=null,o.rowCount=1,o.shouldClick=!0,o.$slider=d(i),o.$slidesCache=null,o.transformType=null,o.transitionType=null,o.visibilityChange="visibilitychange",o.windowWidth=0,o.windowTimer=null,t=d(i).data("slick")||{},o.options=d.extend({},o.defaults,e,t),o.currentSlide=o.options.initialSlide,o.originalSettings=o.options,void 0!==document.mozHidden?(o.hidden="mozHidden",o.visibilityChange="mozvisibilitychange"):void 0!==document.webkitHidden&&(o.hidden="webkitHidden",o.visibilityChange="webkitvisibilitychange"),o.autoPlay=d.proxy(o.autoPlay,o),o.autoPlayClear=d.proxy(o.autoPlayClear,o),o.autoPlayIterator=d.proxy(o.autoPlayIterator,o),o.autoPlayToggleHandler=d.proxy(o.autoPlayToggleHandler,o),o.changeSlide=d.proxy(o.changeSlide,o),o.clickHandler=d.proxy(o.clickHandler,o),o.selectHandler=d.proxy(o.selectHandler,o),o.setPosition=d.proxy(o.setPosition,o),o.swipeHandler=d.proxy(o.swipeHandler,o),o.dragHandler=d.proxy(o.dragHandler,o),o.instanceUid=s++,o.htmlExpr=/^(?:\s*(<[\w\W]+>)[^>]*)$/,o.registerBreakpoints(),o.init(!0)}).prototype.addSlide=l.prototype.slickAdd=function(i,e,t){var o=this;if("boolean"==typeof e)t=e,e=null;else if(e<0||e>=o.slideCount)return!1;o.unload(),"number"==typeof e?0===e&&0===o.$slides.length?d(i).appendTo(o.$slideTrack):t?d(i).insertBefore(o.$slides.eq(e)):d(i).insertAfter(o.$slides.eq(e)):!0===t?d(i).prependTo(o.$slideTrack):d(i).appendTo(o.$slideTrack),o.$slides=o.$slideTrack.children(this.options.slide),o.$slideTrack.children(this.options.slide).detach(),o.$slideTrack.append(o.$slides),o.$slides.each(function(i,e){d(e).attr("data-slick-index",i),d(e).attr("role","group"),d(e).attr("aria-label","slide "+i)}),o.$slidesCache=o.$slides,o.reinit()},l.prototype.animateHeight=function(){var i,e=this;1===e.options.slidesToShow&&!0===e.options.adaptiveHeight&&!1===e.options.vertical&&(i=e.$slides.eq(e.currentSlide).outerHeight(!0),e.$list.animate({height:i},e.options.speed))},l.prototype.animateSlide=function(i,e){var t={},o=this;o.animateHeight(),!0===o.options.rtl&&!1===o.options.vertical&&(i=-i),!1===o.transformsEnabled?!1===o.options.vertical?o.$slideTrack.animate({left:i},o.options.speed,o.options.easing,e):o.$slideTrack.animate({top:i},o.options.speed,o.options.easing,e):!1===o.cssTransitions?(!0===o.options.rtl&&(o.currentLeft=-o.currentLeft),d({animStart:o.currentLeft}).animate({animStart:i},{duration:o.options.speed,easing:o.options.easing,step:function(i){i=Math.ceil(i),!1===o.options.vertical?t[o.animType]="translate("+i+"px, 0px)":t[o.animType]="translate(0px,"+i+"px)",o.$slideTrack.css(t)},complete:function(){e&&e.call()}})):(o.applyTransition(),i=Math.ceil(i),!1===o.options.vertical?t[o.animType]="translate3d("+i+"px, 0px, 0px)":t[o.animType]="translate3d(0px,"+i+"px, 0px)",o.$slideTrack.css(t),e&&setTimeout(function(){o.disableTransition(),e.call()},o.options.speed))},l.prototype.getNavTarget=function(){var i=this.options.asNavFor;return i&&null!==i&&(i=d(i).not(this.$slider)),i},l.prototype.asNavFor=function(e){var i=this.getNavTarget();null!==i&&"object"==typeof i&&i.each(function(){var i=d(this).slick("getSlick");i.unslicked||i.slideHandler(e,!0)})},l.prototype.applyTransition=function(i){var e=this,t={};!1===e.options.fade?t[e.transitionType]=e.transformType+" "+e.options.speed+"ms "+e.options.cssEase:t[e.transitionType]="opacity "+e.options.speed+"ms "+e.options.cssEase,!1===e.options.fade?e.$slideTrack.css(t):e.$slides.eq(i).css(t)},l.prototype.autoPlay=function(){var i=this;i.autoPlayClear(),i.slideCount>i.options.slidesToShow&&(i.autoPlayTimer=setInterval(i.autoPlayIterator,i.options.autoplaySpeed))},l.prototype.autoPlayClear=function(){this.autoPlayTimer&&clearInterval(this.autoPlayTimer)},l.prototype.autoPlayIterator=function(){var i=this,e=i.currentSlide+i.options.slidesToScroll;i.paused||i.interrupted||i.focussed||(!1===i.options.infinite&&(1===i.direction&&i.currentSlide+1===i.slideCount-1?i.direction=0:0===i.direction&&(e=i.currentSlide-i.options.slidesToScroll,i.currentSlide-1==0&&(i.direction=1))),i.slideHandler(e))},l.prototype.autoPlayToggleHandler=function(){var i=this;i.paused?(i.$playIcon.css("display","none"),i.$pauseIcon.css("display","inline"),i.$pauseButton.find(".slick-play-text").attr("style","display: none"),i.$pauseButton.find(".slick-pause-text").removeAttr("style"),i.slickPlay()):(i.$playIcon.css("display","inline"),i.$pauseIcon.css("display","none"),i.$pauseButton.find(".slick-play-text").removeAttr("style"),i.$pauseButton.find(".slick-pause-text").attr("style","display: none"),i.slickPause())},l.prototype.buildArrows=function(){var i=this;if(!0===i.options.arrows)if(i.$prevArrow=d(i.options.prevArrow).addClass("slick-arrow"),i.$nextArrow=d(i.options.nextArrow).addClass("slick-arrow"),i.slideCount>i.options.slidesToShow){if(i.htmlExpr.test(i.options.prevArrow))if(null!=i.options.arrowsPlacement)switch(i.options.arrowsPlacement){case"beforeSlides":case"split":console.log("test"),i.$prevArrow.prependTo(i.options.appendArrows);break;case"afterSlides":i.$prevArrow.appendTo(i.options.appendArrows)}else i.$prevArrow.prependTo(i.options.appendArrows);if(i.htmlExpr.test(i.options.nextArrow))if(null!=i.options.arrowsPlacement)switch(i.options.arrowsPlacement){case"beforeSlides":console.log("test2"),i.$prevArrow.after(i.$nextArrow);break;case"afterSlides":case"split":i.$nextArrow.appendTo(i.options.appendArrows)}else i.$nextArrow.appendTo(i.options.appendArrows);!0!==i.options.infinite&&i.$prevArrow.addClass("slick-disabled").prop("disabled",!0)}else i.$prevArrow.add(i.$nextArrow).addClass("slick-hidden").prop("disabled",!0)},l.prototype.buildDots=function(){var i,e,t=this;if(!0===t.options.dots&&t.slideCount>t.options.slidesToShow){for(t.$slider.addClass("slick-dotted"),e=d("<ul />").addClass(t.options.dotsClass),i=0;i<=t.getDotCount();i+=1)e.append(d("<li />").append(t.options.customPaging.call(this,t,i)));t.$dots=e.appendTo(t.options.appendDots),t.$dots.find("li").first().addClass("slick-active")}},l.prototype.buildOut=function(){var t=this;t.$slides=t.$slider.children(t.options.slide+":not(.slick-cloned)").addClass("slick-slide"),t.slideCount=t.$slides.length,t.$slides.each(function(i,e){d(e).attr("data-slick-index",i).data("originalStyling",d(e).attr("style")||""),t.options.useGroupRole&&d(e).attr("role","group").attr("aria-label","slide "+(i+1))}),t.$slider.addClass("slick-slider"),t.$slider.attr("role","region"),t.$slider.attr("aria-label",t.options.regionLabel),t.$slideTrack=0===t.slideCount?d('<div class="slick-track"/>').appendTo(t.$slider):t.$slides.wrapAll('<div class="slick-track"/>').parent(),t.$list=t.$slideTrack.wrap('<div class="slick-list"/>').parent(),t.$slideTrack.css("opacity",0),!0!==t.options.centerMode&&!0!==t.options.swipeToSlide||(t.options.slidesToScroll=1),d("img[data-lazy]",t.$slider).not("[src]").addClass("slick-loading"),t.setupInfinite(),t.buildArrows(),t.buildDots(),t.updateDots(),t.setSlideClasses("number"==typeof t.currentSlide?t.currentSlide:0),!0===t.options.draggable&&t.$list.addClass("draggable"),t.options.autoplay&&t.options.useAutoplayToggleButton&&(t.$pauseIcon=d(t.options.pauseIcon).attr("aria-hidden",!0),t.$playIcon=d(t.options.playIcon).attr("aria-hidden",!0),t.$pauseButton=d('<button type="button" class="slick-autoplay-toggle-button">'),t.$pauseButton.append(t.$pauseIcon),t.$pauseButton.append(t.$playIcon.css("display","none")),t.$pauseButton.append(d('<span class="slick-pause-text slick-sr-only">Pause</span>')),t.$pauseButton.append(d('<span class="slick-play-text slick-sr-only" style="display: none">Play</span>')),t.$pauseButton.prependTo(t.$slider)),null!=t.options.instructionsText&&""!=t.options.instructionsText&&(t.$instructionsText=d('<p class="slick-instructions slick-sr-only">'+t.options.instructionsText+"</p>"),t.$instructionsText.prependTo(t.$slider))},l.prototype.buildRows=function(){var i,e,t,o=this,s=document.createDocumentFragment(),n=o.$slider.children();if(0<o.options.rows){for(t=o.options.slidesPerRow*o.options.rows,e=Math.ceil(n.length/t),i=0;i<e;i++){for(var l=document.createElement("div"),r=0;r<o.options.rows;r++){for(var a=document.createElement("div"),d=0;d<o.options.slidesPerRow;d++){var p=i*t+(r*o.options.slidesPerRow+d);n.get(p)&&a.appendChild(n.get(p))}l.appendChild(a)}s.appendChild(l)}o.$slider.empty().append(s),o.$slider.children().children().children().css({width:100/o.options.slidesPerRow+"%",display:"inline-block"})}},l.prototype.checkResponsive=function(i,e){var t,o,s,n=this,l=!1,r=n.$slider.width(),a=window.innerWidth||d(window).width();if("window"===n.respondTo?s=a:"slider"===n.respondTo?s=r:"min"===n.respondTo&&(s=Math.min(a,r)),n.options.responsive&&n.options.responsive.length&&null!==n.options.responsive){for(t in o=null,n.breakpoints)n.breakpoints.hasOwnProperty(t)&&(!1===n.originalSettings.mobileFirst?s<n.breakpoints[t]&&(o=n.breakpoints[t]):s>n.breakpoints[t]&&(o=n.breakpoints[t]));null!==o?null!==n.activeBreakpoint&&o===n.activeBreakpoint&&!e||(n.activeBreakpoint=o,"unslick"===n.breakpointSettings[o]?n.unslick(o):(n.options=d.extend({},n.originalSettings,n.breakpointSettings[o]),!0===i&&(n.currentSlide=n.options.initialSlide),n.refresh(i)),l=o):null!==n.activeBreakpoint&&(n.activeBreakpoint=null,n.options=n.originalSettings,!0===i&&(n.currentSlide=n.options.initialSlide),n.refresh(i),l=o),i||!1===l||n.$slider.trigger("breakpoint",[n,l])}},l.prototype.changeSlide=function(i,e){var t,o,s=this,n=d(i.currentTarget);switch(n.is("a")&&i.preventDefault(),n.is("li")||(n=n.closest("li")),t=s.slideCount%s.options.slidesToScroll!=0?0:(s.slideCount-s.currentSlide)%s.options.slidesToScroll,i.data.message){case"previous":o=0==t?s.options.slidesToScroll:s.options.slidesToShow-t,s.slideCount>s.options.slidesToShow&&s.slideHandler(s.currentSlide-o,!1,e);break;case"next":o=0==t?s.options.slidesToScroll:t,s.slideCount>s.options.slidesToShow&&s.slideHandler(s.currentSlide+o,!1,e);break;case"index":var l=0===i.data.index?0:i.data.index||n.index()*s.options.slidesToScroll;s.slideHandler(s.checkNavigable(l),!1,e),n.children().trigger("focus");break;default:return}},l.prototype.checkNavigable=function(i){var e=this.getNavigableIndexes(),t=0;if(i>e[e.length-1])i=e[e.length-1];else for(var o in e){if(i<e[o]){i=t;break}t=e[o]}return i},l.prototype.cleanUpEvents=function(){var i=this;i.options.autoplay&&i.options.useAutoplayToggleButton&&i.$pauseButton.off("click.slick",i.autoPlayToggleHandler),i.options.dots&&null!==i.$dots&&d("li",i.$dots).off("click.slick",i.changeSlide).off("mouseenter.slick",d.proxy(i.interrupt,i,!0)).off("mouseleave.slick",d.proxy(i.interrupt,i,!1)),i.$slider.off("focus.slick blur.slick"),!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&(i.$prevArrow&&i.$prevArrow.off("click.slick",i.changeSlide),i.$nextArrow&&i.$nextArrow.off("click.slick",i.changeSlide)),i.$list.off("touchstart.slick mousedown.slick",i.swipeHandler),i.$list.off("touchmove.slick mousemove.slick",i.swipeHandler),i.$list.off("touchend.slick mouseup.slick",i.swipeHandler),i.$list.off("touchcancel.slick mouseleave.slick",i.swipeHandler),i.$list.off("click.slick",i.clickHandler),d(document).off(i.visibilityChange,i.visibility),i.cleanUpSlideEvents(),d(window).off("orientationchange.slick.slick-"+i.instanceUid,i.orientationChange),d(window).off("resize.slick.slick-"+i.instanceUid,i.resize),d("[draggable!=true]",i.$slideTrack).off("dragstart",i.preventDefault),d(window).off("load.slick.slick-"+i.instanceUid,i.setPosition)},l.prototype.cleanUpSlideEvents=function(){var i=this;i.$list.off("mouseenter.slick",d.proxy(i.interrupt,i,!0)),i.$list.off("mouseleave.slick",d.proxy(i.interrupt,i,!1))},l.prototype.cleanUpRows=function(){var i;0<this.options.rows&&((i=this.$slides.children().children()).removeAttr("style"),this.$slider.empty().append(i))},l.prototype.clickHandler=function(i){!1===this.shouldClick&&(i.stopImmediatePropagation(),i.stopPropagation(),i.preventDefault())},l.prototype.destroy=function(i){var e=this;e.autoPlayClear(),e.touchObject={},e.cleanUpEvents(),d(".slick-cloned",e.$slider).detach(),e.options.autoplay&&e.options.useAutoplayToggleButton&&e.$pauseButton.remove(),e.$dots&&e.$dots.remove(),e.$prevArrow&&e.$prevArrow.length&&(e.$prevArrow.removeClass("slick-disabled slick-arrow slick-hidden").prop("disabled",!1).css("display",""),e.htmlExpr.test(e.options.prevArrow)&&e.$prevArrow.remove()),e.$nextArrow&&e.$nextArrow.length&&(e.$nextArrow.removeClass("slick-disabled slick-arrow slick-hidden").prop("disabled",!1).css("display",""),e.htmlExpr.test(e.options.nextArrow)&&e.$nextArrow.remove()),e.$slides&&(e.$slides.removeClass("slick-slide slick-active slick-center slick-visible slick-current").removeAttr("aria-hidden").removeAttr("data-slick-index").each(function(){d(this).attr("style",d(this).data("originalStyling"))}),e.$slideTrack.children(this.options.slide).detach(),e.$slideTrack.detach(),e.$list.detach(),e.$slider.append(e.$slides)),e.cleanUpRows(),e.$slider.removeClass("slick-slider"),e.$slider.removeClass("slick-initialized"),e.$slider.removeClass("slick-dotted"),e.unslicked=!0,i||e.$slider.trigger("destroy",[e])},l.prototype.disableTransition=function(i){var e={};e[this.transitionType]="",!1===this.options.fade?this.$slideTrack.css(e):this.$slides.eq(i).css(e)},l.prototype.fadeSlide=function(i,e){var t=this;!1===t.cssTransitions?(t.$slides.eq(i).css({zIndex:t.options.zIndex}),t.$slides.eq(i).animate({opacity:1},t.options.speed,t.options.easing,e)):(t.applyTransition(i),t.$slides.eq(i).css({opacity:1,zIndex:t.options.zIndex}),e&&setTimeout(function(){t.disableTransition(i),e.call()},t.options.speed))},l.prototype.fadeSlideOut=function(i){var e=this;!1===e.cssTransitions?e.$slides.eq(i).animate({opacity:0,zIndex:e.options.zIndex-2},e.options.speed,e.options.easing):(e.applyTransition(i),e.$slides.eq(i).css({opacity:0,zIndex:e.options.zIndex-2}))},l.prototype.filterSlides=l.prototype.slickFilter=function(i){var e=this;null!==i&&(e.$slidesCache=e.$slides,e.unload(),e.$slideTrack.children(this.options.slide).detach(),e.$slidesCache.filter(i).appendTo(e.$slideTrack),e.reinit())},l.prototype.focusHandler=function(){var t=this;t.$slider.off("focus.slick blur.slick").on("focus.slick","*",function(i){var e=d(this);setTimeout(function(){t.options.pauseOnFocus&&e.is(":focus")&&(t.focussed=!0,t.autoPlay())},0)}).on("blur.slick","*",function(i){d(this);t.options.pauseOnFocus&&(t.focussed=!1,t.autoPlay())})},l.prototype.getCurrent=l.prototype.slickCurrentSlide=function(){return this.currentSlide},l.prototype.getDotCount=function(){var i=this,e=0,t=0,o=0;if(!0===i.options.infinite)if(i.slideCount<=i.options.slidesToShow)++o;else for(;e<i.slideCount;)++o,e=t+i.options.slidesToScroll,t+=i.options.slidesToScroll<=i.options.slidesToShow?i.options.slidesToScroll:i.options.slidesToShow;else if(!0===i.options.centerMode)o=i.slideCount;else if(i.options.asNavFor)for(;e<i.slideCount;)++o,e=t+i.options.slidesToScroll,t+=i.options.slidesToScroll<=i.options.slidesToShow?i.options.slidesToScroll:i.options.slidesToShow;else o=1+Math.ceil((i.slideCount-i.options.slidesToShow)/i.options.slidesToScroll);return o-1},l.prototype.getLeft=function(i){var e,t,o,s,n=this,l=0;return n.slideOffset=0,t=n.$slides.first().outerHeight(!0),!0===n.options.infinite?(n.slideCount>n.options.slidesToShow&&(n.slideOffset=n.slideWidth*n.options.slidesToShow*-1,s=-1,!0===n.options.vertical&&!0===n.options.centerMode&&(2===n.options.slidesToShow?s=-1.5:1===n.options.slidesToShow&&(s=-2)),l=t*n.options.slidesToShow*s),n.slideCount%n.options.slidesToScroll!=0&&i+n.options.slidesToScroll>n.slideCount&&n.slideCount>n.options.slidesToShow&&(l=i>n.slideCount?(n.slideOffset=(n.options.slidesToShow-(i-n.slideCount))*n.slideWidth*-1,(n.options.slidesToShow-(i-n.slideCount))*t*-1):(n.slideOffset=n.slideCount%n.options.slidesToScroll*n.slideWidth*-1,n.slideCount%n.options.slidesToScroll*t*-1))):i+n.options.slidesToShow>n.slideCount&&(n.slideOffset=(i+n.options.slidesToShow-n.slideCount)*n.slideWidth,l=(i+n.options.slidesToShow-n.slideCount)*t),n.slideCount<=n.options.slidesToShow&&(l=n.slideOffset=0),!0===n.options.centerMode&&n.slideCount<=n.options.slidesToShow?n.slideOffset=n.slideWidth*Math.floor(n.options.slidesToShow)/2-n.slideWidth*n.slideCount/2:!0===n.options.centerMode&&!0===n.options.infinite?n.slideOffset+=n.slideWidth*Math.floor(n.options.slidesToShow/2)-n.slideWidth:!0===n.options.centerMode&&(n.slideOffset=0,n.slideOffset+=n.slideWidth*Math.floor(n.options.slidesToShow/2)),e=!1===n.options.vertical?i*n.slideWidth*-1+n.slideOffset:i*t*-1+l,!0===n.options.variableWidth&&(o=n.slideCount<=n.options.slidesToShow||!1===n.options.infinite?n.$slideTrack.children(".slick-slide").eq(i):n.$slideTrack.children(".slick-slide").eq(i+n.options.slidesToShow),e=!0===n.options.rtl?o[0]?-1*(n.$slideTrack.width()-o[0].offsetLeft-o.width()):0:o[0]?-1*o[0].offsetLeft:0,!0===n.options.centerMode&&(o=n.slideCount<=n.options.slidesToShow||!1===n.options.infinite?n.$slideTrack.children(".slick-slide").eq(i):n.$slideTrack.children(".slick-slide").eq(i+n.options.slidesToShow+1),e=!0===n.options.rtl?o[0]?-1*(n.$slideTrack.width()-o[0].offsetLeft-o.width()):0:o[0]?-1*o[0].offsetLeft:0,e+=(n.$list.width()-o.outerWidth())/2)),e},l.prototype.getOption=l.prototype.slickGetOption=function(i){return this.options[i]},l.prototype.getNavigableIndexes=function(){for(var i=this,e=0,t=0,o=[],s=!1===i.options.infinite?i.slideCount:(e=-1*i.options.slidesToScroll,t=-1*i.options.slidesToScroll,2*i.slideCount);e<s;)o.push(e),e=t+i.options.slidesToScroll,t+=i.options.slidesToScroll<=i.options.slidesToShow?i.options.slidesToScroll:i.options.slidesToShow;return o},l.prototype.getSlick=function(){return this},l.prototype.getSlideCount=function(){var s,n=this,i=!0===n.options.centerMode?Math.floor(n.$list.width()/2):0,l=-1*n.swipeLeft+i;return!0===n.options.swipeToSlide?(n.$slideTrack.find(".slick-slide").each(function(i,e){var t=d(e).outerWidth(),o=e.offsetLeft;if(!0!==n.options.centerMode&&(o+=t/2),l<o+t)return s=e,!1}),Math.abs(d(s).attr("data-slick-index")-n.currentSlide)||1):n.options.slidesToScroll},l.prototype.goTo=l.prototype.slickGoTo=function(i,e){this.changeSlide({data:{message:"index",index:parseInt(i)}},e)},l.prototype.init=function(i){var e=this;d(e.$slider).hasClass("slick-initialized")||(d(e.$slider).addClass("slick-initialized"),e.buildRows(),e.buildOut(),e.setProps(),e.startLoad(),e.loadSlider(),e.initializeEvents(),e.updateArrows(),e.updateDots(),e.checkResponsive(!0),e.focusHandler()),i&&e.$slider.trigger("init",[e]),e.options.autoplay&&(e.paused=!1,e.autoPlay()),e.updateSlideVisibility(),null!=e.options.accessibility&&console.warn("accessibility setting is no longer supported."),null!=e.options.focusOnChange&&console.warn("focusOnChange is no longer supported."),null!=e.options.focusOnSelect&&console.warn("focusOnSelect is no longer supported.")},l.prototype.initArrowEvents=function(){var i=this;!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&(i.$prevArrow.off("click.slick").on("click.slick",{message:"previous"},i.changeSlide),i.$nextArrow.off("click.slick").on("click.slick",{message:"next"},i.changeSlide))},l.prototype.initDotEvents=function(){var i=this;!0===i.options.dots&&i.slideCount>i.options.slidesToShow&&d("li",i.$dots).on("click.slick",{message:"index"},i.changeSlide),!0===i.options.dots&&!0===i.options.pauseOnDotsHover&&i.slideCount>i.options.slidesToShow&&d("li",i.$dots).on("mouseenter.slick",d.proxy(i.interrupt,i,!0)).on("mouseleave.slick",d.proxy(i.interrupt,i,!1))},l.prototype.initSlideEvents=function(){var i=this;i.options.pauseOnHover&&(i.$list.on("mouseenter.slick",d.proxy(i.interrupt,i,!0)),i.$list.on("mouseleave.slick",d.proxy(i.interrupt,i,!1)))},l.prototype.initializeEvents=function(){var i=this;i.initArrowEvents(),i.initDotEvents(),i.initSlideEvents(),i.options.autoplay&&i.options.useAutoplayToggleButton&&i.$pauseButton.on("click.slick",i.autoPlayToggleHandler),i.$list.on("touchstart.slick mousedown.slick",{action:"start"},i.swipeHandler),i.$list.on("touchmove.slick mousemove.slick",{action:"move"},i.swipeHandler),i.$list.on("touchend.slick mouseup.slick",{action:"end"},i.swipeHandler),i.$list.on("touchcancel.slick mouseleave.slick",{action:"end"},i.swipeHandler),i.$list.on("click.slick",i.clickHandler),d(document).on(i.visibilityChange,d.proxy(i.visibility,i)),d(window).on("orientationchange.slick.slick-"+i.instanceUid,d.proxy(i.orientationChange,i)),d(window).on("resize.slick.slick-"+i.instanceUid,d.proxy(i.resize,i)),d("[draggable!=true]",i.$slideTrack).on("dragstart",i.preventDefault),d(window).on("load.slick.slick-"+i.instanceUid,i.setPosition),d(i.setPosition)},l.prototype.initUI=function(){var i=this;!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&(i.$prevArrow.show(),i.$nextArrow.show()),!0===i.options.dots&&i.slideCount>i.options.slidesToShow&&i.$dots.show()},l.prototype.lazyLoad=function(){var i,e,t,n=this;function o(i){d("img[data-lazy]",i).each(function(){var i=d(this),e=d(this).attr("data-lazy"),t=d(this).attr("data-srcset"),o=d(this).attr("data-sizes")||n.$slider.attr("data-sizes"),s=document.createElement("img");s.onload=function(){i.animate({opacity:0},100,function(){t&&(i.attr("srcset",t),o&&i.attr("sizes",o)),i.attr("src",e).animate({opacity:1},200,function(){i.removeAttr("data-lazy data-srcset data-sizes").removeClass("slick-loading")}),n.$slider.trigger("lazyLoaded",[n,i,e])})},s.onerror=function(){i.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),n.$slider.trigger("lazyLoadError",[n,i,e])},s.src=e})}if(!0===n.options.centerMode?t=!0===n.options.infinite?(e=n.currentSlide+(n.options.slidesToShow/2+1))+n.options.slidesToShow+2:(e=Math.max(0,n.currentSlide-(n.options.slidesToShow/2+1)),n.options.slidesToShow/2+1+2+n.currentSlide):(e=n.options.infinite?n.options.slidesToShow+n.currentSlide:n.currentSlide,t=Math.ceil(e+n.options.slidesToShow),!0===n.options.fade&&(0<e&&e--,t<=n.slideCount&&t++)),i=n.$slider.find(".slick-slide").slice(e,t),"anticipated"===n.options.lazyLoad)for(var s=e-1,l=t,r=n.$slider.find(".slick-slide"),a=0;a<n.options.slidesToScroll;a++)s<0&&(s=n.slideCount-1),i=(i=i.add(r.eq(s))).add(r.eq(l)),s--,l++;o(i),n.slideCount<=n.options.slidesToShow?o(n.$slider.find(".slick-slide")):n.currentSlide>=n.slideCount-n.options.slidesToShow?o(n.$slider.find(".slick-cloned").slice(0,n.options.slidesToShow)):0===n.currentSlide&&o(n.$slider.find(".slick-cloned").slice(-1*n.options.slidesToShow))},l.prototype.loadSlider=function(){var i=this;i.setPosition(),i.$slideTrack.css({opacity:1}),i.$slider.removeClass("slick-loading"),i.initUI(),"progressive"===i.options.lazyLoad&&i.progressiveLazyLoad()},l.prototype.next=l.prototype.slickNext=function(){this.changeSlide({data:{message:"next"}})},l.prototype.orientationChange=function(){this.checkResponsive(),this.setPosition()},l.prototype.pause=l.prototype.slickPause=function(){this.autoPlayClear(),this.paused=!0},l.prototype.play=l.prototype.slickPlay=function(){var i=this;i.autoPlay(),i.options.autoplay=!0,i.paused=!1,i.focussed=!1,i.interrupted=!1},l.prototype.postSlide=function(i){var e=this;e.unslicked||(e.$slider.trigger("afterChange",[e,i]),e.animating=!1,e.slideCount>e.options.slidesToShow&&e.setPosition(),e.swipeLeft=null,e.options.autoplay&&e.autoPlay(),e.updateSlideVisibility())},l.prototype.prev=l.prototype.slickPrev=function(){this.changeSlide({data:{message:"previous"}})},l.prototype.preventDefault=function(i){i.preventDefault()},l.prototype.progressiveLazyLoad=function(i){i=i||1;var e,t,o,s,n,l=this,r=d("img[data-lazy]",l.$slider);r.length?(e=r.first(),t=e.attr("data-lazy"),o=e.attr("data-srcset"),s=e.attr("data-sizes")||l.$slider.attr("data-sizes"),(n=document.createElement("img")).onload=function(){o&&(e.attr("srcset",o),s&&e.attr("sizes",s)),e.attr("src",t).removeAttr("data-lazy data-srcset data-sizes").removeClass("slick-loading"),!0===l.options.adaptiveHeight&&l.setPosition(),l.$slider.trigger("lazyLoaded",[l,e,t]),l.progressiveLazyLoad()},n.onerror=function(){i<3?setTimeout(function(){l.progressiveLazyLoad(i+1)},500):(e.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),l.$slider.trigger("lazyLoadError",[l,e,t]),l.progressiveLazyLoad())},n.src=t):l.$slider.trigger("allImagesLoaded",[l])},l.prototype.refresh=function(i){var e,t=this,o=t.slideCount-t.options.slidesToShow;!t.options.infinite&&t.currentSlide>o&&(t.currentSlide=o),t.slideCount<=t.options.slidesToShow&&(t.currentSlide=0),e=t.currentSlide,t.destroy(!0),d.extend(t,t.initials,{currentSlide:e}),t.init(),i||t.changeSlide({data:{message:"index",index:e}},!1)},l.prototype.registerBreakpoints=function(){var i,e,t,o=this,s=o.options.responsive||null;if("array"===d.type(s)&&s.length){for(i in o.respondTo=o.options.respondTo||"window",s)if(t=o.breakpoints.length-1,s.hasOwnProperty(i)){for(e=s[i].breakpoint;0<=t;)o.breakpoints[t]&&o.breakpoints[t]===e&&o.breakpoints.splice(t,1),t--;o.breakpoints.push(e),o.breakpointSettings[e]=s[i].settings}o.breakpoints.sort(function(i,e){return o.options.mobileFirst?i-e:e-i})}},l.prototype.reinit=function(){var i=this;i.$slides=i.$slideTrack.children(i.options.slide).addClass("slick-slide"),i.slideCount=i.$slides.length,i.currentSlide>=i.slideCount&&0!==i.currentSlide&&(i.currentSlide=i.currentSlide-i.options.slidesToScroll),i.slideCount<=i.options.slidesToShow&&(i.currentSlide=0),i.registerBreakpoints(),i.setProps(),i.setupInfinite(),i.buildArrows(),i.updateArrows(),i.initArrowEvents(),i.buildDots(),i.updateDots(),i.initDotEvents(),i.cleanUpSlideEvents(),i.initSlideEvents(),i.checkResponsive(!1,!0),i.setSlideClasses("number"==typeof i.currentSlide?i.currentSlide:0),i.setPosition(),i.focusHandler(),i.paused=!i.options.autoplay,i.autoPlay(),i.$slider.trigger("reInit",[i])},l.prototype.resize=function(){var i=this;d(window).width()!==i.windowWidth&&(clearTimeout(i.windowDelay),i.windowDelay=window.setTimeout(function(){i.windowWidth=d(window).width(),i.checkResponsive(),i.unslicked||i.setPosition()},50))},l.prototype.removeSlide=l.prototype.slickRemove=function(i,e,t){var o=this;if(i="boolean"==typeof i?!0===(e=i)?0:o.slideCount-1:!0===e?--i:i,o.slideCount<1||i<0||i>o.slideCount-1)return!1;o.unload(),!0===t?o.$slideTrack.children().remove():o.$slideTrack.children(this.options.slide).eq(i).remove(),o.$slides=o.$slideTrack.children(this.options.slide),o.$slideTrack.children(this.options.slide).detach(),o.$slideTrack.append(o.$slides),o.$slidesCache=o.$slides,o.reinit()},l.prototype.setCSS=function(i){var e,t,o=this,s={};!0===o.options.rtl&&(i=-i),e="left"==o.positionProp?Math.ceil(i)+"px":"0px",t="top"==o.positionProp?Math.ceil(i)+"px":"0px",s[o.positionProp]=i,!1===o.transformsEnabled||(!(s={})===o.cssTransitions?s[o.animType]="translate("+e+", "+t+")":s[o.animType]="translate3d("+e+", "+t+", 0px)"),o.$slideTrack.css(s)},l.prototype.setDimensions=function(){var i=this;!1===i.options.vertical?!0===i.options.centerMode&&i.$list.css({padding:"0px "+i.options.centerPadding}):(i.$list.height(i.$slides.first().outerHeight(!0)*i.options.slidesToShow),!0===i.options.centerMode&&i.$list.css({padding:i.options.centerPadding+" 0px"})),i.listWidth=i.$list.width(),i.listHeight=i.$list.height(),!1===i.options.vertical&&!1===i.options.variableWidth?(i.slideWidth=Math.ceil(i.listWidth/i.options.slidesToShow),i.$slideTrack.width(Math.ceil(i.slideWidth*i.$slideTrack.children(".slick-slide").length))):!0===i.options.variableWidth?i.$slideTrack.width(5e3*i.slideCount):(i.slideWidth=Math.ceil(i.listWidth),i.$slideTrack.height(Math.ceil(i.$slides.first().outerHeight(!0)*i.$slideTrack.children(".slick-slide").length)));var e=i.$slides.first().outerWidth(!0)-i.$slides.first().width();!1===i.options.variableWidth&&i.$slideTrack.children(".slick-slide").width(i.slideWidth-e)},l.prototype.setFade=function(){var t,o=this;o.$slides.each(function(i,e){t=o.slideWidth*i*-1,!0===o.options.rtl?d(e).css({position:"relative",right:t,top:0,zIndex:o.options.zIndex-2,opacity:0}):d(e).css({position:"relative",left:t,top:0,zIndex:o.options.zIndex-2,opacity:0})}),o.$slides.eq(o.currentSlide).css({zIndex:o.options.zIndex-1,opacity:1})},l.prototype.setHeight=function(){var i,e=this;1===e.options.slidesToShow&&!0===e.options.adaptiveHeight&&!1===e.options.vertical&&(i=e.$slides.eq(e.currentSlide).outerHeight(!0),e.$list.css("height",i))},l.prototype.setOption=l.prototype.slickSetOption=function(){var i,e,t,o,s,n=this,l=!1;if("object"===d.type(arguments[0])?(t=arguments[0],l=arguments[1],s="multiple"):"string"===d.type(arguments[0])&&(o=arguments[1],l=arguments[2],"responsive"===(t=arguments[0])&&"array"===d.type(arguments[1])?s="responsive":void 0!==arguments[1]&&(s="single")),"single"===s)n.options[t]=o;else if("multiple"===s)d.each(t,function(i,e){n.options[i]=e});else if("responsive"===s)for(e in o)if("array"!==d.type(n.options.responsive))n.options.responsive=[o[e]];else{for(i=n.options.responsive.length-1;0<=i;)n.options.responsive[i].breakpoint===o[e].breakpoint&&n.options.responsive.splice(i,1),i--;n.options.responsive.push(o[e])}l&&(n.unload(),n.reinit())},l.prototype.setPosition=function(){var i=this;i.setDimensions(),i.setHeight(),!1===i.options.fade?i.setCSS(i.getLeft(i.currentSlide)):i.setFade(),i.$slider.trigger("setPosition",[i])},l.prototype.setProps=function(){var i=this,e=document.body.style;i.positionProp=!0===i.options.vertical?"top":"left","top"===i.positionProp?i.$slider.addClass("slick-vertical"):i.$slider.removeClass("slick-vertical"),void 0===e.WebkitTransition&&void 0===e.MozTransition&&void 0===e.msTransition||!0===i.options.useCSS&&(i.cssTransitions=!0),i.options.fade&&("number"==typeof i.options.zIndex?i.options.zIndex<3&&(i.options.zIndex=3):i.options.zIndex=i.defaults.zIndex),void 0!==e.OTransform&&(i.animType="OTransform",i.transformType="-o-transform",i.transitionType="OTransition",void 0===e.perspectiveProperty&&void 0===e.webkitPerspective&&(i.animType=!1)),void 0!==e.MozTransform&&(i.animType="MozTransform",i.transformType="-moz-transform",i.transitionType="MozTransition",void 0===e.perspectiveProperty&&void 0===e.MozPerspective&&(i.animType=!1)),void 0!==e.webkitTransform&&(i.animType="webkitTransform",i.transformType="-webkit-transform",i.transitionType="webkitTransition",void 0===e.perspectiveProperty&&void 0===e.webkitPerspective&&(i.animType=!1)),void 0!==e.msTransform&&(i.animType="msTransform",i.transformType="-ms-transform",i.transitionType="msTransition",void 0===e.msTransform&&(i.animType=!1)),void 0!==e.transform&&!1!==i.animType&&(i.animType="transform",i.transformType="transform",i.transitionType="transition"),i.transformsEnabled=i.options.useTransform&&null!==i.animType&&!1!==i.animType},l.prototype.setSlideClasses=function(i){var e,t,o,s,n=this,l=n.$slider.find(".slick-slide").removeClass("slick-active slick-center slick-current").attr("aria-hidden","true").attr("aria-label",function(){return d(this).attr("aria-label").replace(" (centered)","")});n.$slides.eq(i).addClass("slick-current"),!0===n.options.centerMode?(o=n.options.slidesToShow%2==0?1:0,s=Math.floor(n.options.slidesToShow/2),!0===n.options.infinite&&(s<=i&&i<=n.slideCount-1-s?n.$slides.slice(i-s+o,i+s+1).addClass("slick-active").removeAttr("aria-hidden"):(e=n.options.slidesToShow+i,l.slice(e-s+1+o,e+s+2).addClass("slick-active").removeAttr("aria-hidden")),0===i?l.eq(n.options.slidesToShow+n.slideCount+1).addClass("slick-center").attr("aria-label",function(){return d(this).attr("aria-label")+" (centered)"}):i===n.slideCount-1&&l.eq(n.options.slidesToShow).addClass("slick-center").attr("aria-label",function(){return d(this).attr("aria-label")+" (centered)"})),n.$slides.eq(i).addClass("slick-center").attr("aria-label",function(){return d(this).attr("aria-label")+" (centered)"})):0<=i&&i<=n.slideCount-n.options.slidesToShow?n.$slides.slice(i,i+n.options.slidesToShow).addClass("slick-active").removeAttr("aria-hidden"):l.length<=n.options.slidesToShow?l.addClass("slick-active").removeAttr("aria-hidden"):(t=n.slideCount%n.options.slidesToShow,e=!0===n.options.infinite?n.options.slidesToShow+i:i,n.options.slidesToShow==n.options.slidesToScroll&&n.slideCount-i<n.options.slidesToShow?l.slice(e-(n.options.slidesToShow-t),e+t).addClass("slick-active").removeAttr("aria-hidden"):l.slice(e,e+n.options.slidesToShow).addClass("slick-active").removeAttr("aria-hidden")),"ondemand"!==n.options.lazyLoad&&"anticipated"!==n.options.lazyLoad||n.lazyLoad()},l.prototype.setupInfinite=function(){var i,e,t,o=this;if(!0===o.options.fade&&(o.options.centerMode=!1),!0===o.options.infinite&&!1===o.options.fade&&(e=null,o.slideCount>o.options.slidesToShow)){for(t=!0===o.options.centerMode?o.options.slidesToShow+1:o.options.slidesToShow,i=o.slideCount;i>o.slideCount-t;--i)e=i-1,d(o.$slides[e]).clone(!0).attr("id","").attr("data-slick-index",e-o.slideCount).prependTo(o.$slideTrack).addClass("slick-cloned");for(i=0;i<t+o.slideCount;i+=1)e=i,d(o.$slides[e]).clone(!0).attr("id","").attr("data-slick-index",e+o.slideCount).appendTo(o.$slideTrack).addClass("slick-cloned");o.$slideTrack.find(".slick-cloned").find("[id]").each(function(){d(this).attr("id","")})}},l.prototype.interrupt=function(i){i||this.autoPlay(),this.interrupted=i},l.prototype.selectHandler=function(i){var e=d(i.target).is(".slick-slide")?d(i.target):d(i.target).parents(".slick-slide"),t=(t=parseInt(e.attr("data-slick-index")))||0;this.slideCount<=this.options.slidesToShow?this.slideHandler(t,!1,!0):this.slideHandler(t)},l.prototype.slideHandler=function(i,e,t){var o,s,n,l,r,a,d=this;if(e=e||!1,!(!0===d.animating&&!0===d.options.waitForAnimate||!0===d.options.fade&&d.currentSlide===i))if(!1===e&&d.asNavFor(i),o=i,r=d.getLeft(o),l=d.getLeft(d.currentSlide),d.currentLeft=null===d.swipeLeft?l:d.swipeLeft,!1===d.options.infinite&&!1===d.options.centerMode&&(i<0||i>d.getDotCount()*d.options.slidesToScroll))!1===d.options.fade&&(o=d.currentSlide,!0!==t&&d.slideCount>d.options.slidesToShow?d.animateSlide(l,function(){d.postSlide(o)}):d.postSlide(o));else if(!1===d.options.infinite&&!0===d.options.centerMode&&(i<0||i>d.slideCount-d.options.slidesToScroll))!1===d.options.fade&&(o=d.currentSlide,!0!==t&&d.slideCount>d.options.slidesToShow?d.animateSlide(l,function(){d.postSlide(o)}):d.postSlide(o));else{if(d.options.autoplay&&clearInterval(d.autoPlayTimer),s=o<0?d.slideCount%d.options.slidesToScroll!=0?d.slideCount-d.slideCount%d.options.slidesToScroll:d.slideCount+o:o>=d.slideCount?d.slideCount%d.options.slidesToScroll!=0?0:o-d.slideCount:o,d.animating=!0,d.$slider.trigger("beforeChange",[d,d.currentSlide,s]),n=d.currentSlide,d.currentSlide=s,d.setSlideClasses(d.currentSlide),d.options.asNavFor&&(a=(a=d.getNavTarget()).slick("getSlick")).slideCount<=a.options.slidesToShow&&a.setSlideClasses(d.currentSlide),d.updateDots(),d.updateArrows(),!0===d.options.fade)return!0!==t?(d.fadeSlideOut(n),d.fadeSlide(s,function(){d.postSlide(s)})):d.postSlide(s),void d.animateHeight();!0!==t&&d.slideCount>d.options.slidesToShow?d.animateSlide(r,function(){d.postSlide(s)}):d.postSlide(s)}},l.prototype.startLoad=function(){var i=this;!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&(i.$prevArrow.hide(),i.$nextArrow.hide()),!0===i.options.dots&&i.slideCount>i.options.slidesToShow&&i.$dots.hide(),i.$slider.addClass("slick-loading")},l.prototype.swipeDirection=function(){var i=this,e=i.touchObject.startX-i.touchObject.curX,t=i.touchObject.startY-i.touchObject.curY,o=Math.atan2(t,e),s=Math.round(180*o/Math.PI);return s<0&&(s=360-Math.abs(s)),s<=45&&0<=s||s<=360&&315<=s?!1===i.options.rtl?"left":"right":135<=s&&s<=225?!1===i.options.rtl?"right":"left":!0===i.options.verticalSwiping?35<=s&&s<=135?"down":"up":"vertical"},l.prototype.swipeEnd=function(i){var e,t,o=this;if(o.dragging=!1,o.swiping=!1,o.scrolling)return o.scrolling=!1;if(o.interrupted=!1,o.shouldClick=!(10<o.touchObject.swipeLength),void 0===o.touchObject.curX)return!1;if(!0===o.touchObject.edgeHit&&o.$slider.trigger("edge",[o,o.swipeDirection()]),o.touchObject.swipeLength>=o.touchObject.minSwipe){switch(t=o.swipeDirection()){case"left":case"down":e=o.options.swipeToSlide?o.checkNavigable(o.currentSlide+o.getSlideCount()):o.currentSlide+o.getSlideCount(),o.currentDirection=0;break;case"right":case"up":e=o.options.swipeToSlide?o.checkNavigable(o.currentSlide-o.getSlideCount()):o.currentSlide-o.getSlideCount(),o.currentDirection=1}"vertical"!=t&&(o.slideHandler(e),o.touchObject={},o.$slider.trigger("swipe",[o,t]))}else o.touchObject.startX!==o.touchObject.curX&&(o.slideHandler(o.currentSlide),o.touchObject={})},l.prototype.swipeHandler=function(i){var e=this;if(!(!1===e.options.swipe||"ontouchend"in document&&!1===e.options.swipe||!1===e.options.draggable&&-1!==i.type.indexOf("mouse")))switch(e.touchObject.fingerCount=i.originalEvent&&void 0!==i.originalEvent.touches?i.originalEvent.touches.length:1,e.touchObject.minSwipe=e.listWidth/e.options.touchThreshold,!0===e.options.verticalSwiping&&(e.touchObject.minSwipe=e.listHeight/e.options.touchThreshold),i.data.action){case"start":e.swipeStart(i);break;case"move":e.swipeMove(i);break;case"end":e.swipeEnd(i)}},l.prototype.swipeMove=function(i){var e,t,o,s,n,l=this,r=void 0!==i.originalEvent?i.originalEvent.touches:null;return!(!l.dragging||l.scrolling||r&&1!==r.length)&&(e=l.getLeft(l.currentSlide),l.touchObject.curX=void 0!==r?r[0].pageX:i.clientX,l.touchObject.curY=void 0!==r?r[0].pageY:i.clientY,l.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(l.touchObject.curX-l.touchObject.startX,2))),n=Math.round(Math.sqrt(Math.pow(l.touchObject.curY-l.touchObject.startY,2))),!l.options.verticalSwiping&&!l.swiping&&4<n?!(l.scrolling=!0):(!0===l.options.verticalSwiping&&(l.touchObject.swipeLength=n),t=l.swipeDirection(),void 0!==i.originalEvent&&4<l.touchObject.swipeLength&&(l.swiping=!0,i.preventDefault()),s=(!1===l.options.rtl?1:-1)*(l.touchObject.curX>l.touchObject.startX?1:-1),!0===l.options.verticalSwiping&&(s=l.touchObject.curY>l.touchObject.startY?1:-1),o=l.touchObject.swipeLength,(l.touchObject.edgeHit=!1)===l.options.infinite&&(0===l.currentSlide&&"right"===t||l.currentSlide>=l.getDotCount()&&"left"===t)&&(o=l.touchObject.swipeLength*l.options.edgeFriction,l.touchObject.edgeHit=!0),!1===l.options.vertical?l.swipeLeft=e+o*s:l.swipeLeft=e+o*(l.$list.height()/l.listWidth)*s,!0===l.options.verticalSwiping&&(l.swipeLeft=e+o*s),!0!==l.options.fade&&!1!==l.options.touchMove&&(!0===l.animating?(l.swipeLeft=null,!1):void l.setCSS(l.swipeLeft))))},l.prototype.swipeStart=function(i){var e,t=this;if(t.interrupted=!0,1!==t.touchObject.fingerCount||t.slideCount<=t.options.slidesToShow)return!(t.touchObject={});void 0!==i.originalEvent&&void 0!==i.originalEvent.touches&&(e=i.originalEvent.touches[0]),t.touchObject.startX=t.touchObject.curX=void 0!==e?e.pageX:i.clientX,t.touchObject.startY=t.touchObject.curY=void 0!==e?e.pageY:i.clientY,t.dragging=!0},l.prototype.unfilterSlides=l.prototype.slickUnfilter=function(){var i=this;null!==i.$slidesCache&&(i.unload(),i.$slideTrack.children(this.options.slide).detach(),i.$slidesCache.appendTo(i.$slideTrack),i.reinit())},l.prototype.unload=function(){var i=this;d(".slick-cloned",i.$slider).remove(),i.$dots&&i.$dots.remove(),i.$prevArrow&&i.htmlExpr.test(i.options.prevArrow)&&i.$prevArrow.remove(),i.$nextArrow&&i.htmlExpr.test(i.options.nextArrow)&&i.$nextArrow.remove(),i.$slides.removeClass("slick-slide slick-active slick-visible slick-current").attr("aria-hidden","true").css("width","")},l.prototype.unslick=function(i){this.$slider.trigger("unslick",[this,i]),this.destroy()},l.prototype.updateArrows=function(){var i=this;Math.floor(i.options.slidesToShow/2);!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&!i.options.infinite&&(i.$prevArrow.removeClass("slick-disabled").prop("disabled",!1),i.$nextArrow.removeClass("slick-disabled").prop("disabled",!1),0===i.currentSlide?(i.$prevArrow.addClass("slick-disabled").prop("disabled",!0),i.$nextArrow.removeClass("slick-disabled").prop("disabled",!1)):(i.currentSlide>=i.slideCount-i.options.slidesToShow&&!1===i.options.centerMode||i.currentSlide>=i.slideCount-1&&!0===i.options.centerMode)&&(i.$nextArrow.addClass("slick-disabled").prop("disabled",!0),i.$prevArrow.removeClass("slick-disabled").prop("disabled",!1)))},l.prototype.updateDots=function(){var i=this;null!==i.$dots&&(i.$dots.find("li").removeClass("slick-active").find("button").removeAttr("aria-current").end().end(),i.$dots.find("li").eq(Math.floor(i.currentSlide/i.options.slidesToScroll)).addClass("slick-active").find("button").attr("aria-current",!0).end().end())},l.prototype.updateSlideVisibility=function(){this.$slideTrack.find(".slick-slide").attr("aria-hidden","true").find("a, input, button, select").attr("tabindex","-1"),this.$slideTrack.find(".slick-active").removeAttr("aria-hidden").find("a, input, button, select").removeAttr("tabindex")},l.prototype.visibility=function(){this.options.autoplay&&(document[this.hidden]?this.interrupted=!0:this.interrupted=!1)},d.fn.slick=function(){for(var i,e=this,t=arguments[0],o=Array.prototype.slice.call(arguments,1),s=e.length,n=0;n<s;n++)if("object"==typeof t||void 0===t?e[n].slick=new l(e[n],t):i=e[n].slick[t].apply(e[n].slick,o),void 0!==i)return i;return e}});

(function(h,g){"function"===typeof define&&define.amd?define([],g):"object"===typeof module&&module.exports?module.exports=g():h.Rellax=g()})(this,function(){var h=function(g,n){var a=Object.create(h.prototype),k=0,p=0,l=0,q=0,e=[],r=!0,z=window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.msRequestAnimationFrame||window.oRequestAnimationFrame||function(a){setTimeout(a,1E3/60)},A=window.transformProp||function(){var a=document.createElement("div");
if(null===a.style.transform){var b=["Webkit","Moz","ms"],d;for(d in b)if(void 0!==a.style[b[d]+"Transform"])return b[d]+"Transform"}return"transform"}();a.options={speed:-2,center:!1,wrapper:null,round:!0,vertical:!0,horizontal:!1,callback:function(){}};n&&Object.keys(n).forEach(function(c){a.options[c]=n[c]});g||(g=".rellax");var m="string"===typeof g?document.querySelectorAll(g):[g];if(0<m.length)a.elems=m;else throw Error("The elements you're trying to select don't exist.");if(a.options.wrapper&&
!a.options.wrapper.nodeType)if(m=document.querySelector(a.options.wrapper))a.options.wrapper=m;else throw Error("The wrapper you're trying to use don't exist.");var u=function(){for(var c=0;c<e.length;c++)a.elems[c].style.cssText=e[c].style;e=[];p=window.innerHeight;q=window.innerWidth;v();for(c=0;c<a.elems.length;c++){var b=a.elems[c],d=b.getAttribute("data-rellax-percentage"),t=b.getAttribute("data-rellax-speed"),g=b.getAttribute("data-rellax-zindex")||0,h=a.options.wrapper?a.options.wrapper.scrollTop:
window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop,f=a.options.vertical?d||a.options.center?h:0:0,k=a.options.horizontal?d||a.options.center?window.pageXOffset||document.documentElement.scrollLeft||document.body.scrollLeft:0:0;h=f+b.getBoundingClientRect().top;var l=b.clientHeight||b.offsetHeight||b.scrollHeight,m=k+b.getBoundingClientRect().left,n=b.clientWidth||b.offsetWidth||b.scrollWidth;f=d?d:(f-h+p)/(l+p);d=d?d:(k-m+q)/(n+q);a.options.center&&(f=d=.5);t=t?t:a.options.speed;
d=w(d,f,t);b=b.style.cssText;f="";0<=b.indexOf("transform")&&(f=b.indexOf("transform"),f=b.slice(f),f=(k=f.indexOf(";"))?" "+f.slice(11,k).replace(/\s/g,""):" "+f.slice(11).replace(/\s/g,""));e.push({baseX:d.x,baseY:d.y,top:h,left:m,height:l,width:n,speed:t,style:b,transform:f,zindex:g})}r&&(window.addEventListener("resize",u),r=!1);x()},v=function(){var c=k,b=l;k=a.options.wrapper?a.options.wrapper.scrollTop:(document.documentElement||document.body.parentNode||document.body).scrollTop||window.pageYOffset;
l=a.options.wrapper?a.options.wrapper.scrollLeft:(document.documentElement||document.body.parentNode||document.body).scrollLeft||window.pageXOffset;return c!=k&&a.options.vertical||b!=l&&a.options.horizontal?!0:!1},w=function(c,b,d){var e={};c=100*d*(1-c);b=100*d*(1-b);e.x=a.options.round?Math.round(c):Math.round(100*c)/100;e.y=a.options.round?Math.round(b):Math.round(100*b)/100;return e},y=function(){v()&&!1===r&&x();z(y)},x=function(){for(var c,b=0;b<a.elems.length;b++){c=w((l-e[b].left+q)/(e[b].width+
q),(k-e[b].top+p)/(e[b].height+p),e[b].speed);var d=c.y-e[b].baseY,g=c.x-e[b].baseX;a.elems[b].style[A]="translate3d("+(a.options.horizontal?g:"0")+"px,"+(a.options.vertical?d:"0")+"px,"+e[b].zindex+"px) "+e[b].transform}a.options.callback(c)};a.destroy=function(){for(var c=0;c<a.elems.length;c++)a.elems[c].style.cssText=e[c].style;r||(window.removeEventListener("resize",u),r=!0)};u();y();a.refresh=u;return a};return h});

!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.AOS=t():e.AOS=t()}(this,function(){return function(e){function t(o){if(n[o])return n[o].exports;var i=n[o]={exports:{},id:o,loaded:!1};return e[o].call(i.exports,i,i.exports,t),i.loaded=!0,i.exports}var n={};return t.m=e,t.c=n,t.p="dist/",t(0)}([function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}var i=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(e[o]=n[o])}return e},r=n(1),a=(o(r),n(6)),u=o(a),c=n(7),f=o(c),s=n(8),d=o(s),l=n(9),p=o(l),m=n(10),b=o(m),v=n(11),y=o(v),g=n(14),h=o(g),w=[],k=!1,x={offset:120,delay:0,easing:"ease",duration:400,disable:!1,once:!1,startEvent:"DOMContentLoaded",throttleDelay:99,debounceDelay:50,disableMutationObserver:!1},j=function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(e&&(k=!0),k)return w=(0,y.default)(w,x),(0,b.default)(w,x.once),w},O=function(){w=(0,h.default)(),j()},_=function(){w.forEach(function(e,t){e.node.removeAttribute("data-aos"),e.node.removeAttribute("data-aos-easing"),e.node.removeAttribute("data-aos-duration"),e.node.removeAttribute("data-aos-delay")})},S=function(e){return e===!0||"mobile"===e&&p.default.mobile()||"phone"===e&&p.default.phone()||"tablet"===e&&p.default.tablet()||"function"==typeof e&&e()===!0},z=function(e){x=i(x,e),w=(0,h.default)();var t=document.all&&!window.atob;return S(x.disable)||t?_():(document.querySelector("body").setAttribute("data-aos-easing",x.easing),document.querySelector("body").setAttribute("data-aos-duration",x.duration),document.querySelector("body").setAttribute("data-aos-delay",x.delay),"DOMContentLoaded"===x.startEvent&&["complete","interactive"].indexOf(document.readyState)>-1?j(!0):"load"===x.startEvent?window.addEventListener(x.startEvent,function(){j(!0)}):document.addEventListener(x.startEvent,function(){j(!0)}),window.addEventListener("resize",(0,f.default)(j,x.debounceDelay,!0)),window.addEventListener("orientationchange",(0,f.default)(j,x.debounceDelay,!0)),window.addEventListener("scroll",(0,u.default)(function(){(0,b.default)(w,x.once)},x.throttleDelay)),x.disableMutationObserver||(0,d.default)("[data-aos]",O),w)};e.exports={init:z,refresh:j,refreshHard:O}},function(e,t){},,,,,function(e,t){(function(t){"use strict";function n(e,t,n){function o(t){var n=b,o=v;return b=v=void 0,k=t,g=e.apply(o,n)}function r(e){return k=e,h=setTimeout(s,t),_?o(e):g}function a(e){var n=e-w,o=e-k,i=t-n;return S?j(i,y-o):i}function c(e){var n=e-w,o=e-k;return void 0===w||n>=t||n<0||S&&o>=y}function s(){var e=O();return c(e)?d(e):void(h=setTimeout(s,a(e)))}function d(e){return h=void 0,z&&b?o(e):(b=v=void 0,g)}function l(){void 0!==h&&clearTimeout(h),k=0,b=w=v=h=void 0}function p(){return void 0===h?g:d(O())}function m(){var e=O(),n=c(e);if(b=arguments,v=this,w=e,n){if(void 0===h)return r(w);if(S)return h=setTimeout(s,t),o(w)}return void 0===h&&(h=setTimeout(s,t)),g}var b,v,y,g,h,w,k=0,_=!1,S=!1,z=!0;if("function"!=typeof e)throw new TypeError(f);return t=u(t)||0,i(n)&&(_=!!n.leading,S="maxWait"in n,y=S?x(u(n.maxWait)||0,t):y,z="trailing"in n?!!n.trailing:z),m.cancel=l,m.flush=p,m}function o(e,t,o){var r=!0,a=!0;if("function"!=typeof e)throw new TypeError(f);return i(o)&&(r="leading"in o?!!o.leading:r,a="trailing"in o?!!o.trailing:a),n(e,t,{leading:r,maxWait:t,trailing:a})}function i(e){var t="undefined"==typeof e?"undefined":c(e);return!!e&&("object"==t||"function"==t)}function r(e){return!!e&&"object"==("undefined"==typeof e?"undefined":c(e))}function a(e){return"symbol"==("undefined"==typeof e?"undefined":c(e))||r(e)&&k.call(e)==d}function u(e){if("number"==typeof e)return e;if(a(e))return s;if(i(e)){var t="function"==typeof e.valueOf?e.valueOf():e;e=i(t)?t+"":t}if("string"!=typeof e)return 0===e?e:+e;e=e.replace(l,"");var n=m.test(e);return n||b.test(e)?v(e.slice(2),n?2:8):p.test(e)?s:+e}var c="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},f="Expected a function",s=NaN,d="[object Symbol]",l=/^\s+|\s+$/g,p=/^[-+]0x[0-9a-f]+$/i,m=/^0b[01]+$/i,b=/^0o[0-7]+$/i,v=parseInt,y="object"==("undefined"==typeof t?"undefined":c(t))&&t&&t.Object===Object&&t,g="object"==("undefined"==typeof self?"undefined":c(self))&&self&&self.Object===Object&&self,h=y||g||Function("return this")(),w=Object.prototype,k=w.toString,x=Math.max,j=Math.min,O=function(){return h.Date.now()};e.exports=o}).call(t,function(){return this}())},function(e,t){(function(t){"use strict";function n(e,t,n){function i(t){var n=b,o=v;return b=v=void 0,O=t,g=e.apply(o,n)}function r(e){return O=e,h=setTimeout(s,t),_?i(e):g}function u(e){var n=e-w,o=e-O,i=t-n;return S?x(i,y-o):i}function f(e){var n=e-w,o=e-O;return void 0===w||n>=t||n<0||S&&o>=y}function s(){var e=j();return f(e)?d(e):void(h=setTimeout(s,u(e)))}function d(e){return h=void 0,z&&b?i(e):(b=v=void 0,g)}function l(){void 0!==h&&clearTimeout(h),O=0,b=w=v=h=void 0}function p(){return void 0===h?g:d(j())}function m(){var e=j(),n=f(e);if(b=arguments,v=this,w=e,n){if(void 0===h)return r(w);if(S)return h=setTimeout(s,t),i(w)}return void 0===h&&(h=setTimeout(s,t)),g}var b,v,y,g,h,w,O=0,_=!1,S=!1,z=!0;if("function"!=typeof e)throw new TypeError(c);return t=a(t)||0,o(n)&&(_=!!n.leading,S="maxWait"in n,y=S?k(a(n.maxWait)||0,t):y,z="trailing"in n?!!n.trailing:z),m.cancel=l,m.flush=p,m}function o(e){var t="undefined"==typeof e?"undefined":u(e);return!!e&&("object"==t||"function"==t)}function i(e){return!!e&&"object"==("undefined"==typeof e?"undefined":u(e))}function r(e){return"symbol"==("undefined"==typeof e?"undefined":u(e))||i(e)&&w.call(e)==s}function a(e){if("number"==typeof e)return e;if(r(e))return f;if(o(e)){var t="function"==typeof e.valueOf?e.valueOf():e;e=o(t)?t+"":t}if("string"!=typeof e)return 0===e?e:+e;e=e.replace(d,"");var n=p.test(e);return n||m.test(e)?b(e.slice(2),n?2:8):l.test(e)?f:+e}var u="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},c="Expected a function",f=NaN,s="[object Symbol]",d=/^\s+|\s+$/g,l=/^[-+]0x[0-9a-f]+$/i,p=/^0b[01]+$/i,m=/^0o[0-7]+$/i,b=parseInt,v="object"==("undefined"==typeof t?"undefined":u(t))&&t&&t.Object===Object&&t,y="object"==("undefined"==typeof self?"undefined":u(self))&&self&&self.Object===Object&&self,g=v||y||Function("return this")(),h=Object.prototype,w=h.toString,k=Math.max,x=Math.min,j=function(){return g.Date.now()};e.exports=n}).call(t,function(){return this}())},function(e,t){"use strict";function n(e,t){var n=window.document,r=window.MutationObserver||window.WebKitMutationObserver||window.MozMutationObserver,a=new r(o);i=t,a.observe(n.documentElement,{childList:!0,subtree:!0,removedNodes:!0})}function o(e){e&&e.forEach(function(e){var t=Array.prototype.slice.call(e.addedNodes),n=Array.prototype.slice.call(e.removedNodes),o=t.concat(n).filter(function(e){return e.hasAttribute&&e.hasAttribute("data-aos")}).length;o&&i()})}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){};t.default=n},function(e,t){"use strict";function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function o(){return navigator.userAgent||navigator.vendor||window.opera||""}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}return function(t,n,o){return n&&e(t.prototype,n),o&&e(t,o),t}}(),r=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i,a=/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i,u=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i,c=/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i,f=function(){function e(){n(this,e)}return i(e,[{key:"phone",value:function(){var e=o();return!(!r.test(e)&&!a.test(e.substr(0,4)))}},{key:"mobile",value:function(){var e=o();return!(!u.test(e)&&!c.test(e.substr(0,4)))}},{key:"tablet",value:function(){return this.mobile()&&!this.phone()}}]),e}();t.default=new f},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=function(e,t,n){var o=e.node.getAttribute("data-aos-once");t>e.position?e.node.classList.add("aos-animate"):"undefined"!=typeof o&&("false"===o||!n&&"true"!==o)&&e.node.classList.remove("aos-animate")},o=function(e,t){var o=window.pageYOffset,i=window.innerHeight;e.forEach(function(e,r){n(e,i+o,t)})};t.default=o},function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=n(12),r=o(i),a=function(e,t){return e.forEach(function(e,n){e.node.classList.add("aos-init"),e.position=(0,r.default)(e.node,t.offset)}),e};t.default=a},function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=n(13),r=o(i),a=function(e,t){var n=0,o=0,i=window.innerHeight,a={offset:e.getAttribute("data-aos-offset"),anchor:e.getAttribute("data-aos-anchor"),anchorPlacement:e.getAttribute("data-aos-anchor-placement")};switch(a.offset&&!isNaN(a.offset)&&(o=parseInt(a.offset)),a.anchor&&document.querySelectorAll(a.anchor)&&(e=document.querySelectorAll(a.anchor)[0]),n=(0,r.default)(e).top,a.anchorPlacement){case"top-bottom":break;case"center-bottom":n+=e.offsetHeight/2;break;case"bottom-bottom":n+=e.offsetHeight;break;case"top-center":n+=i/2;break;case"bottom-center":n+=i/2+e.offsetHeight;break;case"center-center":n+=i/2+e.offsetHeight/2;break;case"top-top":n+=i;break;case"bottom-top":n+=e.offsetHeight+i;break;case"center-top":n+=e.offsetHeight/2+i}return a.anchorPlacement||a.offset||isNaN(t)||(o=t),n+o};t.default=a},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=function(e){for(var t=0,n=0;e&&!isNaN(e.offsetLeft)&&!isNaN(e.offsetTop);)t+=e.offsetLeft-("BODY"!=e.tagName?e.scrollLeft:0),n+=e.offsetTop-("BODY"!=e.tagName?e.scrollTop:0),e=e.offsetParent;return{top:n,left:t}};t.default=n},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=function(e){return e=e||document.querySelectorAll("[data-aos]"),Array.prototype.map.call(e,function(e){return{node:e}})};t.default=n}])});

/* IE Polyfull
* Fix for the IE issue.
*/

if (typeof Object.assign != "function") {
  Object.defineProperty(Object, "assign", {
    value: function assign(target, varArgs) {
      "use strict"
      if (target == null) {
        throw new TypeError("Cannot convert undefined or null to object")
      }
      var to = Object(target)
      for (var index = 1; index < arguments.length; index++) {
        var nextSource = arguments[index]
        if (nextSource != null) {
          for (var nextKey in nextSource) {
            if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
              to[nextKey] = nextSource[nextKey]
            }
          }
        }
      }
      return to
    },
    writable: true,
    configurable: true
  })
}

if (!Array.from) {
  Array.from = (function() {
    var toStr = Object.prototype.toString
    var isCallable = function(fn) {
      return typeof fn === "function" || toStr.call(fn) === "[object Function]"
    }
    var toInteger = function(value) {
      var number = Number(value)
      if (isNaN(number)) {
        return 0
      }
      if (number === 0 || !isFinite(number)) {
        return number
      }
      return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number))
    }
    var maxSafeInteger = Math.pow(2, 53) - 1
    var toLength = function(value) {
      var len = toInteger(value)
      return Math.min(Math.max(len, 0), maxSafeInteger)
    }

    return function from(arrayLike) {
      var C = this
      var items = Object(arrayLike)
      if (arrayLike == null) {
        throw new TypeError(
          "Array.from requires an array-like object - not null or undefined"
        )
      }
      var mapFn = arguments.length > 1 ? arguments[1] : void undefined
      var T
      if (typeof mapFn !== "undefined") {
        if (!isCallable(mapFn)) {
          throw new TypeError(
            "Array.from: when provided, the second argument must be a function"
          )
        }
        if (arguments.length > 2) {
          T = arguments[2]
        }
      }
      var len = toLength(items.length)
      var A = isCallable(C) ? Object(new C(len)) : new Array(len)
      var k = 0
      var kValue
      while (k < len) {
        kValue = items[k]
        if (mapFn) {
          A[k] =
            typeof T === "undefined"
              ? mapFn(kValue, k)
              : mapFn.call(T, kValue, k)
        } else {
          A[k] = kValue
        }
        k += 1
      }
      A.length = len
      return A
    }
  })()
}

(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global.MicroModal = factory());
}(this, (function () { 'use strict';

var version = "0.3.1";

var classCallCheck = function (instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
};

var createClass = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);
    if (staticProps) defineProperties(Constructor, staticProps);
    return Constructor;
  };
}();

var toConsumableArray = function (arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

    return arr2;
  } else {
    return Array.from(arr);
  }
};

var MicroModal = function () {

  var FOCUSABLE_ELEMENTS = ['a[href]', 'area[href]', 'input:not([disabled]):not([type="hidden"]):not([aria-hidden])', 'select:not([disabled]):not([aria-hidden])', 'textarea:not([disabled]):not([aria-hidden])', 'button:not([disabled]):not([aria-hidden])', 'iframe', 'object', 'embed', '[contenteditable]', '[tabindex]:not([tabindex^="-"])'];

  var Modal = function () {
    function Modal(_ref) {
      var targetModal = _ref.targetModal,
          _ref$triggers = _ref.triggers,
          triggers = _ref$triggers === undefined ? [] : _ref$triggers,
          _ref$onShow = _ref.onShow,
          onShow = _ref$onShow === undefined ? function () {} : _ref$onShow,
          _ref$onClose = _ref.onClose,
          onClose = _ref$onClose === undefined ? function () {} : _ref$onClose,
          _ref$openTrigger = _ref.openTrigger,
          openTrigger = _ref$openTrigger === undefined ? 'data-micromodal-trigger' : _ref$openTrigger,
          _ref$closeTrigger = _ref.closeTrigger,
          closeTrigger = _ref$closeTrigger === undefined ? 'data-micromodal-close' : _ref$closeTrigger,
          _ref$disableScroll = _ref.disableScroll,
          disableScroll = _ref$disableScroll === undefined ? false : _ref$disableScroll,
          _ref$disableFocus = _ref.disableFocus,
          disableFocus = _ref$disableFocus === undefined ? false : _ref$disableFocus,
          _ref$awaitCloseAnimat = _ref.awaitCloseAnimation,
          awaitCloseAnimation = _ref$awaitCloseAnimat === undefined ? false : _ref$awaitCloseAnimat,
          _ref$debugMode = _ref.debugMode,
          debugMode = _ref$debugMode === undefined ? false : _ref$debugMode;
      classCallCheck(this, Modal);

      // Save a reference of the modal
      this.modal = document.getElementById(targetModal);

      // Save a reference to the passed config
      this.config = { debugMode: debugMode, disableScroll: disableScroll, openTrigger: openTrigger, closeTrigger: closeTrigger, onShow: onShow, onClose: onClose, awaitCloseAnimation: awaitCloseAnimation, disableFocus: disableFocus

        // Register click events only if prebinding eventListeners
      };if (triggers.length > 0) this.registerTriggers.apply(this, toConsumableArray(triggers));

      // prebind functions for event listeners
      this.onClick = this.onClick.bind(this);
      this.onKeydown = this.onKeydown.bind(this);
    }

    /**
     * Loops through all openTriggers and binds click event
     * @param  {array} triggers [Array of node elements]
     * @return {void}
     */


    createClass(Modal, [{
      key: 'registerTriggers',
      value: function registerTriggers() {
        var _this = this;

        for (var _len = arguments.length, triggers = Array(_len), _key = 0; _key < _len; _key++) {
          triggers[_key] = arguments[_key];
        }

        triggers.forEach(function (trigger) {
          trigger.addEventListener('click', function () {
            return _this.showModal();
          });
        });
      }
    }, {
      key: 'showModal',
      value: function showModal() {
        this.activeElement = document.activeElement;
        this.modal.setAttribute('aria-hidden', 'false');
        this.modal.classList.add('is-open');
        this.setFocusToFirstNode();
        this.scrollBehaviour('disable');
        this.addEventListeners();
        this.config.onShow(this.modal);
      }
    }, {
      key: 'closeModal',
      value: function closeModal() {
        var modal = this.modal;
        this.modal.setAttribute('aria-hidden', 'true');
        this.removeEventListeners();
        this.scrollBehaviour('enable');
        this.activeElement.focus();
        this.config.onClose(this.modal);

        if (this.config.awaitCloseAnimation) {
          this.modal.addEventListener('animationend', function handler() {
            modal.classList.remove('is-open');
            modal.removeEventListener('animationend', handler, false);
          }, false);
        } else {
          modal.classList.remove('is-open');
        }
      }
    }, {
      key: 'scrollBehaviour',
      value: function scrollBehaviour(toggle) {
        if (!this.config.disableScroll) return;
        var body = document.querySelector('body');
        switch (toggle) {
          case 'enable':
            Object.assign(body.style, { overflow: 'initial', height: 'initial' });
            break;
          case 'disable':
            Object.assign(body.style, { overflow: 'hidden', height: '100vh' });
            break;
          default:
        }
      }
    }, {
      key: 'addEventListeners',
      value: function addEventListeners() {
        this.modal.addEventListener('touchstart', this.onClick);
        this.modal.addEventListener('click', this.onClick);
        document.addEventListener('keydown', this.onKeydown);
      }
    }, {
      key: 'removeEventListeners',
      value: function removeEventListeners() {
        this.modal.removeEventListener('touchstart', this.onClick);
        this.modal.removeEventListener('click', this.onClick);
        document.removeEventListener('keydown', this.onKeydown);
      }
    }, {
      key: 'onClick',
      value: function onClick(event) {
        if (event.target.hasAttribute(this.config.closeTrigger)) {
          this.closeModal();
          event.preventDefault();
        }
      }
    }, {
      key: 'onKeydown',
      value: function onKeydown(event) {
        if (event.keyCode === 27) this.closeModal(event);
        if (event.keyCode === 9) this.maintainFocus(event);
      }
    }, {
      key: 'getFocusableNodes',
      value: function getFocusableNodes() {
        var nodes = this.modal.querySelectorAll(FOCUSABLE_ELEMENTS);
        return Object.keys(nodes).map(function (key) {
          return nodes[key];
        });
      }
    }, {
      key: 'setFocusToFirstNode',
      value: function setFocusToFirstNode() {
        if (this.config.disableFocus) return;
        var focusableNodes = this.getFocusableNodes();
        if (focusableNodes.length) focusableNodes[0].focus();
      }
    }, {
      key: 'maintainFocus',
      value: function maintainFocus(event) {
        var focusableNodes = this.getFocusableNodes();

        // if disableFocus is true
        if (!this.modal.contains(document.activeElement)) {
          focusableNodes[0].focus();
        } else {
          var focusedItemIndex = focusableNodes.indexOf(document.activeElement);

          if (event.shiftKey && focusedItemIndex === 0) {
            focusableNodes[focusableNodes.length - 1].focus();
            event.preventDefault();
          }

          if (!event.shiftKey && focusedItemIndex === focusableNodes.length - 1) {
            focusableNodes[0].focus();
            event.preventDefault();
          }
        }
      }
    }]);
    return Modal;
  }();

  /**
   * Modal prototype ends.
   * Here on code is reposible for detecting and
   * autobinding event handlers on modal triggers
   */

  // Keep a reference to the opened modal


  var activeModal = null;

  /**
   * Generates an associative array of modals and it's
   * respective triggers
   * @param  {array} triggers     An array of all triggers
   * @param  {string} triggerAttr The data-attribute which triggers the module
   * @return {array}
   */
  var generateTriggerMap = function generateTriggerMap(triggers, triggerAttr) {
    var triggerMap = [];

    triggers.forEach(function (trigger) {
      var targetModal = trigger.attributes[triggerAttr].value;
      if (triggerMap[targetModal] === undefined) triggerMap[targetModal] = [];
      triggerMap[targetModal].push(trigger);
    });

    return triggerMap;
  };

  /**
   * Validates whether a modal of the given id exists
   * in the DOM
   * @param  {number} id  The id of the modal
   * @return {boolean}
   */
  var validateModalPresence = function validateModalPresence(id) {
    if (!document.getElementById(id)) {
      console.warn('MicroModal v' + version + ': \u2757Seems like you have missed %c\'' + id + '\'', 'background-color: #f8f9fa;color: #50596c;font-weight: bold;', 'ID somewhere in your code. Refer example below to resolve it.');
      console.warn('%cExample:', 'background-color: #f8f9fa;color: #50596c;font-weight: bold;', '<div class="modal" id="' + id + '"></div>');
      return false;
    }
  };

  /**
   * Validates if there are modal triggers present
   * in the DOM
   * @param  {array} triggers An array of data-triggers
   * @return {boolean}
   */
  var validateTriggerPresence = function validateTriggerPresence(triggers) {
    if (triggers.length <= 0) {
      console.warn('MicroModal v' + version + ': \u2757Please specify at least one %c\'micromodal-trigger\'', 'background-color: #f8f9fa;color: #50596c;font-weight: bold;', 'data attribute.');
      console.warn('%cExample:', 'background-color: #f8f9fa;color: #50596c;font-weight: bold;', '<a href="#" data-micromodal-trigger="my-modal"></a>');
      return false;
    }
  };

  /**
   * Checks if triggers and their corresponding modals
   * are present in the DOM
   * @param  {array} triggers   Array of DOM nodes which have data-triggers
   * @param  {array} triggerMap Associative array of modals and thier triggers
   * @return {boolean}
   */
  var validateArgs = function validateArgs(triggers, triggerMap) {
    validateTriggerPresence(triggers);
    if (!triggerMap) return true;
    for (var id in triggerMap) {
      validateModalPresence(id);
    }return true;
  };

  /**
   * Binds click handlers to all modal triggers
   * @param  {object} config [description]
   * @return void
   */
  var init = function init(config) {
    // Create an config object with default openTrigger
    var options = Object.assign({}, { openTrigger: 'data-micromodal-trigger' }, config);

    // Collects all the nodes with the trigger
    var triggers = [].concat(toConsumableArray(document.querySelectorAll('[' + options.openTrigger + ']')));

    // Makes a mappings of modals with their trigger nodes
    var triggerMap = generateTriggerMap(triggers, options.openTrigger);

    // Checks if modals and triggers exist in dom
    if (options.debugMode === true && validateArgs(triggers, triggerMap) === false) return;

    // For every target modal creates a new instance
    for (var key in triggerMap) {
      var value = triggerMap[key];
      options.targetModal = key;
      options.triggers = [].concat(toConsumableArray(value));
      new Modal(options); // eslint-disable-line no-new
    }
  };

  /**
   * Shows a particular modal
   * @param  {string} targetModal [The id of the modal to display]
   * @param  {object} config [The configuration object to pass]
   * @return {void}
   */
  var show = function show(targetModal, config) {
    var options = config || {};
    options.targetModal = targetModal;

    // Checks if modals and triggers exist in dom
    if (options.debugMode === true && validateModalPresence(targetModal) === false) return;

    // stores reference to active modal
    activeModal = new Modal(options); // eslint-disable-line no-new
    activeModal.showModal();
  };

  /**
   * Closes the active modal
   * @return {void}
   */
  var close = function close() {
    activeModal.closeModal();
  };

  return { init: init, show: show, close: close };
}();

return MicroModal;

})));


// Loading modules
var ac,
	accordions = {
		settings: {
		},
		init: function() {
			ac = this.settings;
			this.bindUIActions();
			console.log('accordions loaded!');
		},
		bindUIActions: function() {
			jQuery('.layout.accordions.tabbed .acc-item').hide();
			jQuery('.layout.accordions.tabbed #item-1').show();

			jQuery('.layout.accordions.tabbed .title a').on('click', function(e) {
				e.preventDefault();
				var anchor = jQuery(this).attr('href');
				jQuery('.layout.accordions.tabbed .acc-title').removeClass('active');
				jQuery('.layout.accordions.tabbed .acc-item').hide();
				jQuery('.layout.accordions.tabbed').find(anchor).show();
				jQuery(this).closest('.acc-title').addClass('active');
			});
		},
	};

var autocompleteBilling, autocompleteShipping;


function initAutocomplete() {
  // Set up autocomplete for the billing fields
  if(document.getElementById('billing_address_1')){
    if(!document.getElementById('billing_address_1').hasAttribute('readonly')){
      autocompleteBilling = new google.maps.places.Autocomplete(
        document.getElementById('billing_address_1'), {types: ['geocode']}
      );  
      autocompleteBilling.setFields(['address_component']);
      autocompleteBilling.addListener('place_changed', fillBillingAddress);  
      document.getElementById('billing_address_1').setAttribute('autocomplete', 'false');
      document.getElementById('billing_address_1').setAttribute('onfocus', 'geolocate()');
    }
  }
  // Set up autocomplete for the shipping fields
  if(document.getElementById('shipping_address_1')){
    autocompleteShipping = new google.maps.places.Autocomplete(
      document.getElementById('shipping_address_1'), {types: ['geocode']}
    );  
    autocompleteShipping.setFields(['address_component']);
    autocompleteShipping.addListener('place_changed', fillShippingAddress);  
    document.getElementById('shipping_address_1').setAttribute('autocomplete', 'false');
    document.getElementById('shipping_address_1').setAttribute('onfocus', 'geolocate()');
  }  
}

function fillBillingAddress() {
  // Get the place details from the autocomplete object.
  var place = autocompleteBilling.getPlace();

  document.getElementById('billing_address_1').value = '';
  document.getElementById('billing_address_2').value = '';
  document.getElementById('billing_city').value = '';
  document.getElementById('billing_state').value = '';
  document.getElementById('billing_postcode').value = '';
  document.getElementById('billing_country').value = '';

  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    console.log(place.address_components[i].types[0]);  
    if(addressType == 'street_number'){
        document.getElementById('billing_address_1').value = place.address_components[i]['short_name'] + ' ';
    }
    if(addressType == 'route'){
        document.getElementById('billing_address_1').value = document.getElementById('billing_address_1').value + place.address_components[i]['long_name'];
    }
    if(addressType == 'locality'){
        document.getElementById('billing_city').value = place.address_components[i]['long_name'];
    }
    if(addressType == 'postal_code'){
        document.getElementById('billing_postcode').value = place.address_components[i]['short_name'];  
    }    
    if(addressType == 'country'){
        document.getElementById('billing_country').value = place.address_components[i]['short_name'];  
        $('#billing_country').trigger('change.select2');
        $('#billing_country').trigger('change');
        $('#billing_country').trigger('click');        
    }
    if(addressType == 'administrative_area_level_1'){
      var tempI = i;
      setTimeout(function(){
        document.getElementById('billing_state').value = place.address_components[tempI]['short_name'];
        $('#billing_state').trigger('change.select2');
        $('#billing_state').trigger('change');
        $('#billing_state').trigger('click');
      }, 500);
    }
    
  }
  jQuery('body').trigger('update_checkout');
}

function fillShippingAddress() {
  // Get the place details from the autocomplete object.
  var place = autocompleteShipping.getPlace();

  document.getElementById('shipping_address_1').value = '';
  document.getElementById('shipping_address_2').value = '';
  document.getElementById('shipping_city').value = '';
  document.getElementById('shipping_state').value = '';
  document.getElementById('shipping_postcode').value = '';
  document.getElementById('shipping_country').value = '';

  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if(addressType == 'street_number'){
        document.getElementById('shipping_address_1').value = place.address_components[i]['short_name'] + ' ';
    }
    if(addressType == 'route'){
        document.getElementById('shipping_address_1').value = document.getElementById('shipping_address_1').value + place.address_components[i]['long_name'];
    }
    if(addressType == 'locality'){
        document.getElementById('shipping_city').value = place.address_components[i]['long_name'];
    }
    if(addressType == 'postal_code'){
        document.getElementById('shipping_postcode').value = place.address_components[i]['short_name'];  
    }    
    if(addressType == 'country'){
        document.getElementById('shipping_country').value = place.address_components[i]['short_name'];
        $('#shipping_country').trigger('change.select2');
        $('#shipping_country').trigger('change');
        $('#shipping_country').trigger('click');        
    }
    if(addressType == 'administrative_area_level_1'){
      var tempI = i;
      setTimeout(function(){
        document.getElementById('shipping_state').value = place.address_components[tempI]['short_name'];
        $('#shipping_state').trigger('change.select2');
        $('#shipping_state').trigger('change');
        $('#shipping_state').trigger('click');
      }, 500);      
    }    
  }
  jQuery('body').trigger('update_checkout');
}

// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
      var circle = new google.maps.Circle(
          {center: geolocation, radius: position.coords.accuracy});
      if(autocompleteBilling){
        autocompleteBilling.setBounds(circle.getBounds());
      }
      if(autocompleteShipping){
        autocompleteShipping.setBounds(circle.getBounds());
      }
    });
  }
}
var gs,
	glideSlide = {

		settings: {
			sliderXLarge: document.querySelectorAll('.slider.x-large .glide'),
			sliderLarge: document.querySelectorAll('.slider.large .glide'),
			sliderMedium: document.querySelectorAll('.slider.medium .glide'),
			sliderSmall: document.querySelectorAll('.slider.small .glide'),
			sliderProducts: document.querySelectorAll('.slider.products .glide'),
			sliderTimeline: document.querySelectorAll('.slider.timeline .glide'),
			sliderTimelineBtnLeft: document.querySelectorAll('.slider.timeline .glide__arrow--left'),
			sliderTimelineBtnRight: document.querySelectorAll('.slider.timeline .glide__arrow--right'),
			sliderTimelineSlides: document.querySelectorAll('.slider.timeline .glide__slide')
			// sliderXLarge: $('.slider.x-large .glide'),
			// sliderLarge: $('.slider.large .glide'),
			// sliderMedium: $('.slider.medium .glide'),
			// sliderSmall: $('.slider.small .glide'),
			// sliderProducts: $('.slider.products .glide'),
			// sliderTimeline: $('.slider.timeline .glide'),
			// sliderTimelineBtnLeft: $('.slider.timeline .glide__arrow--left'),
			// sliderTimelineBtnRight: $('.slider.timeline .glide__arrow--right'),
			// sliderTimelineSlides: $('.slider.timeline .glide__slide')
		},

		init: function() {
			gs = this.settings;
			this.bindUIActions();
			console.log('glideSlide loaded!');
		},

		bindUIActions: function() {

			function adaptiveHeight(ele) {
				ele.on('build.after', function() {
					var slideHeight = $('.glide__slide--active').outerHeight();
					var glideTrack = $('.glide__track').outerHeight();
					if (slideHeight != glideTrack) {
						var newHeight = slideHeight;
						$('.glide__track').css('height', newHeight);
					}
					console.log('Glide.build active slide height: ' + slideHeight);
				});

				ele.on('run.after', function() {
					var slideHeight = $('.glide__slide--active').outerHeight();
					var glideTrack = $('.glide__track').outerHeight();
					if (slideHeight != glideTrack) {
						var newHeight = slideHeight;
						$('.glide__track').css('height', newHeight);
					}
					console.log('Glide.run active slide height: ' + slideHeight);
				});
			}

			for (var i = 0; i < gs.sliderTimeline.length; i++) {
				// console.log(i); // index
				// console.log(gs.sliderTimeline[i]); // value
				var sliderTimeline = new Glide(gs.sliderTimeline[i], {
					// type: 'carousel',
					perView: 3,
					gap: 0,
					breakpoints: {
						1215: {
							perView: 3
						},
						960: {
							perView: 2
						},
						680: {
							perView: 1
						}
					}
				});
				adaptiveHeight(sliderTimeline);
				sliderTimeline.mount();

				for (var x = 0; x < gs.sliderTimelineSlides.length; x++) {
					// Looping through all of the slide for the timeline
				}
					// Substracting one from the variable to match the length number
				x = x - 1;
				sliderTimeline.on(['move.after'], function() {
					// Hiding the left button if the user is on the first slide
					// and hiding the right button if the user is on the last slide
					if (sliderTimeline.index == 0) {
						gs.sliderTimelineBtnLeft[i].style.display = 'none';
					} else {
						gs.sliderTimelineBtnLeft[i].style.display = 'block';
					}

					if (sliderTimeline.index == x) {
						gs.sliderTimelineBtnRight[i].style.display = 'none';
					} else {
						gs.sliderTimelineBtnRight[i].style.display = 'block';
					}
				})
			}

			for (var i = 0; i < gs.sliderXLarge.length; i++) {
				// console.log(i); // index
				// console.log(gs.sliderLarge[i]); // value

				var sliderXLarge = new Glide(gs.sliderXLarge[i], {
					type: 'carousel',
					perView: 1
				});

				adaptiveHeight(sliderXLarge);

				sliderXLarge.mount();
			}

			for (var i = 0; i < gs.sliderLarge.length; i++) {
				// console.log(i); // index
				// console.log(gs.sliderLarge[i]); // value

				var sliderLarge = new Glide(gs.sliderLarge[i], {
					type: 'carousel',
					perView: 1
				});
				adaptiveHeight(sliderLarge);
				sliderLarge.mount();
			}

			for (var i = 0; i < gs.sliderMedium.length; i++) {
				var sliderMedium = new Glide(gs.sliderMedium[i], {
					type: 'carousel',
					perView: 3,
					gap: 60,
					breakpoints: {
						960: {
							perView: 2
						},
						680: {
							perView: 1
						}
					}
				});
				adaptiveHeight(sliderMedium);
				sliderMedium.mount();
			}

			for (var i = 0; i < gs.sliderSmall.length; i++) {
				var sliderSmall = new Glide(gs.sliderSmall[i], {
					type: 'carousel',
					perView: 2,
					gap: 100,
					breakpoints: {
						960: {
							perView: 1
						}
					}
				});
				adaptiveHeight(sliderSmall);
				sliderSmall.mount();
			}
		}

	};

var ss,
	slickSlide = {

		settings: {
            sliderXLarge: document.querySelectorAll('.slider.x-large .slide-list'),
			sliderLarge: document.querySelectorAll('.slider.large .slide-list'),
			sliderMedium: document.querySelectorAll('.slider.medium .slide-list'),
			sliderSmall: document.querySelectorAll('.slider.small .slide-list'),
            sliderTimeline: document.querySelectorAll('.slider.timeline .slide-list'),
            sliderProducts: document.querySelectorAll('.product-carousel'),
            sliderProductVideos: document.querySelectorAll('.product-videos-slider')
		},

		init: function() {
			ss = this.settings;
			this.bindUIActions();
			console.log('slickSlide loaded!');
		},

		bindUIActions: function() {

            for (var i = 0; i < ss.sliderTimeline.length; i++) {
                $('.slider.timeline .slide-list').not('.slick-initialized').slick({
                    accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: false,
                    adaptiveHeight: true,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 960,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 680,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                    ]
                });
            }

            for (var i = 0; i < ss.sliderSmall.length; i++) {
                $('.slider.small .slide-list').not('.slick-initialized').slick({
                    accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 960,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                    ]
                });
            }

            for (var i = 0; i < ss.sliderMedium.length; i++) {
                $('.slider.medium .slide-list').not('.slick-initialized').slick({
                    accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 960,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 680,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        },
                    ]
                });
            }

            for (var i = 0; i < ss.sliderLarge.length; i++) {
                $('.slider.large .slide-list').not('.slick-initialized').slick({
                    accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    cssEase: 'linear'
                });
            }

            for (var i = 0; i < ss.sliderXLarge.length; i++) {
                $('.slider.x-large').on('init', function(){
                    // console.log($(this));
                    // $(this).find('.slick-list').attr('role', 'region');
                    // $(this).find('.slick-list').attr('aria-live', 'polite');
                    $(this).find('.slick-next').prependTo($(this).find('.slick-slider')).attr('aria-label', 'Next Carousel Slide');
                    $(this).find('.slick-prev').prependTo($(this).find('.slick-slider')).attr('aria-label', 'Previous Carousel Slide');

                    var liveregion = document.createElement('div');
                    liveregion.setAttribute('aria-live', 'polite');
                    liveregion.setAttribute('aria-atomic', 'true');
                    liveregion.setAttribute('class', 'liveregion visually-hidden');
                    this.appendChild(liveregion);                    
                });
                $('.slider.x-large').on('afterChange', function(event, slick, currentSlide, nextSlide){
                    var currentSlideInt = parseInt(currentSlide)+1;
                    $(this).find('.liveregion').text('Slide ' + currentSlideInt + ' of ' + slick.slideCount + $(this).find('.slick-current .title').text());
                });
                $('.slider.x-large .slide-list').not('.slick-initialized').slick({
                    accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    cssEase: 'linear'
                });
            }

            for (var i = 0; i < ss.sliderProducts.length; i++) {
                $($('.product-carousel .line-items').get(i)).on('init breakpoint', function(){
                    //alert('wooorks');
                    slickSlide.forceEqualHeights($($('.product-carousel .line-items').get(i)));
                });                
                $($('.product-carousel .line-items').get(i)).slick({
                    accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: false,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 1100,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 680,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        },
                    ]                    
                });
            }
            $('.product-videos-slider').on('beforeChange', function(event, slick, currentSlide, nextSlide){
                // Pause all iframes when the slider changes.
                $('.vimeo-iframe').each(function(){
                    var vimeoPlayer = new Vimeo.Player(this);
                    vimeoPlayer.pause();
                });
            });
            $('.product-videos-slider').slick({
                accessibility: true,
                speed: 400,
                arrows: false,
                infinite: true,
                adaptiveHeight: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                cssEase: 'linear',
                asNavFor: '.product-videos-slider-nav'                   
            });
            $('.product-videos-slider-nav').slick({
                accessibility: true,
                speed: 400,
                arrows: true,
                infinite: true,
                adaptiveHeight: false,
                slidesToShow: 3,
                slidesToScroll: 1,
                cssEase: 'linear',
                asNavFor: '.product-videos-slider',
                centerMode: true,
                centerPadding: 0,
                focusOnSelect: true                                
            });                                   
        },
        forceEqualHeights: function(lineItems){
            var slideItems = lineItems.find('.slick-slide .item');

            var elementHeights = slideItems.map(function() {
                return $(this).height();
            }).get();
            
            // Math.max takes a variable number of arguments
            // `apply` is equivalent to passing each height as an argument
            var maxHeight = Math.max.apply(null, elementHeights);
        
            // Set each height to the max height
            slideItems.height(maxHeight);

        }

	};

var s,
mobileMenu = {

    settings: {
        hamburger: jQuery('.hamburger'),
        header: jQuery('.header'),
        search: jQuery('.header .mobile-search'),
        searchSubmit: jQuery('.header form .search-submit'),
    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        console.log('mobileMenu loaded!');
    },

    bindUIActions: function() {

      // Setting up Mobile Menu with drop-down sub-menus, and Cart link
      if (jQuery(window).width() < 769) {
        // Setting up cart link
        jQuery('a.cart-preview-link').attr('href', '/cart');
        // Adding parent links to sub-menus
        jQuery('ul.main-menu li.menu-item-has-children > a').on('click', function(event) {
          event.preventDefault();
          jQuery(this).addClass("no-link")
          jQuery(this).next().slideToggle();
          console.log("no click for you!");
        });
        // Adding toggle to sub-menu when parent link is clicked
        jQuery('ul.main-menu li.menu-item-has-children > a').each(function() {
          var subMenu = jQuery(this).siblings('.sub-menu');
          jQuery(this).clone().prependTo(subMenu).wrap('<li class="menu-item menu-item-type-post_type menu-item-object-page"></li>');
        });
      }


      s.hamburger.on('click', function() {
          jQuery('.hamburger').toggleClass('is-active');
          jQuery('.header').toggleClass('reveal');
          jQuery('#main').toggleClass('hide');
          jQuery('body').toggleClass('no-scroll');
      });



        // s.search.on('click', function() {
        //     jQuery('.site-search').toggleClass('is-active');
        //     jQuery('header form').removeClass('move');
        //     jQuery('header .search-results').hide();
        // });
        // s.searchSubmit.on('click', function(e) {
        //     e.preventDefault();
        //     jQuery('header form').addClass('move');
        //     jQuery('header .search-results').fadeIn('slow', function() {
        //         // Animation complete
        //     });
        // });
    }

};

var lf,
	LayoutFilter = {
		settings: {
			mainWindow: jQuery(window),
			menuToggle: '',
			filterGroups: jQuery('.filter.active'),
			textFilters: jQuery('.text-filter'),
			textSubmitButton: jQuery('input.text-filter-button'),
			selectFilters: jQuery('.select-filter')
		},
		init: function () {
			lf = this.settings;
			this.bindUIActions();
			console.log('LayoutFilter loaded!');
		},
		bindUIActions: function () {
			lf.filterGroups.each(function () {
				var filterGroup = jQuery(this);
				// changing the filter search to work with the search button for accessibility purposes
				if (filterGroup.find('.text-filter').length > 0) {
					var textFilter = filterGroup.find('.text-filter');
					lf.textSubmitButton.on('click', function (evt) {
						evt.preventDefault();
						LayoutFilter.filterData(filterGroup);
					});
					// textFilter.keydown(function(evt){
					// 	if(evt.which == 13){
					// 		evt.preventDefault();
					// 	}
					// 	});
					// textFilter.keyup(function(evt){
					// 	evt.preventDefault();
					// 	// console.log('text change');
					// 	LayoutFilter.filterData(filterGroup);
					// 	});
				}
				if (filterGroup.find('.select-filter').length > 0) {
					var selectFilter = filterGroup.find('.select-filter');
					selectFilter.change(function () {
						// console.log('select change');
						LayoutFilter.filterData(filterGroup);
					});
				}
				// Create the temp element that is used to hide the omitted results
				filterGroup.next().after('<ul id="filter-temp"></ul>');
				// Add a data attribute for sorting later
				filterGroup.next().find('li,tbody tr').each(function (index, element) {
					jQuery(this).data('sort', index);
					jQuery(this).attr('data-sort', index);
				});
			});
		},
		filterData: function (ele) {
			// ele returns jquery object of filter div that contains all filter fields
			var textValue = ele.find('.text-filter').val();
			var selectActive = false;
			var selectElement = ele.find('.select-filter');
			var selectValue = '';
			var count = 0;
			var resultsContainer = ele.next();
			var tempContainer = ele.next().next();
			if (resultsContainer.hasClass('wl-tab-wrap')) {
				resultsContainer = resultsContainer.find('.bx--data-table tbody');
			}
			console.log(tempContainer);
			// Move all elements from results container to temp container
			resultsContainer.find('li,tr.cart_table_item').each(function () {
				var thisItem = jQuery(this);
				thisItem.appendTo(tempContainer);
			});
			// if the select element is present
			if (selectElement.length > 0) {
				var selectTaxonomy = selectElement.data('taxonomy');
				if (selectElement.val() != 'all') {
					selectActive = true;
					selectValue = selectElement.val();
				}
			}
			tempContainer.find('.no-results').hide();
			// Loop through the list elements from temp container
			tempContainer.find('li,tr.cart_table_item').each(function () {
				var thisItem = jQuery(this);
				var thisItemData = thisItem.data(selectTaxonomy),
					thisItemData = "" + thisItemData;
				if (selectActive) {
					// If the list item does not contain the text phrase fade it out
					if (thisItem.text().search(new RegExp(textValue, "i")) < 0 || thisItemData.search(new RegExp(selectValue, "i")) < 0) {
						// thisItem.fadeOut();
						// Show the list item if the phrase matches and increase the count by 1
					} else {
						thisItem.prependTo(resultsContainer);
						count++;
					}
				} else {
					// If the list item does not contain the text phrase fade it out
					if (thisItem.text().search(new RegExp(textValue, "i")) < 0) {
						// thisItem.fadeOut();
						// Show the list item if the phrase matches and increase the count by 1
					} else {
						thisItem.prependTo(resultsContainer);
						count++;
					}
				}
			});
			if (count === 0) {
				tempContainer.find('.no-results').appendTo(resultsContainer);
				resultsContainer.find('.no-results').show();
			}
			// Sort by data-sort attribute
			resultsContainer.find('li, tbody tr').sort(function (a, b) {
				return +a.dataset.sort - +b.dataset.sort;
			}).appendTo(resultsContainer);
		}
	}

var s,
accessibleTabbing = {

    settings: {

    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        console.log('accessible tabbing loaded!');
    },

    bindUIActions: function() {

        jQuery(document)
            .keyup(function(evt) {
                if (evt.which == 9) {
                    var focused = jQuery(':focus');
                	var closestParent = focused.closest('.menu > li');
                	if (closestParent) {
                		var focusedParent = jQuery('.menu .focused');
                		if (focusedParent.length != 0 && !closestParent.hasClass('focused')) {
                			focusedParent.removeClass('focused');
                			closestParent.addClass('focused');
                		} else {
                			closestParent.addClass('focused');
                		}
                	}

                }
            });
            
    }

};

var df,
donateForm = {

    settings: {
		layout: jQuery('.layout.donate'),
        amount: jQuery('.amount'),
		description: jQuery('.description'),
        buttons: jQuery('button.donate')
    },

    init: function() {
        df = this.settings;
        this.bindUIActions();
        console.log('donate form loaded!');
    },

    bindUIActions: function() {
		if (df.layout.length > 0) {
			df.buttons.each(function() {
				var button = jQuery(this);
				button.click(function(evt) {
					// evt.preventDefault();
					if (button.hasClass('active')) {
						// remove class
						button.removeClass('active');
					} else {
						// remove active class from all other buttons
						df.buttons.removeClass('active');
						// add class
						button.addClass('active');
						// get data value
						var newAmount = button.data('value');
						var newDescription = button.data('description');
						// change out heading with new amount
						df.amount.html(newAmount);
						df.description.html(newDescription);
					}
				});
			});
		}
    }

};

var sf,
	searchForm = {

		settings: {
			form: jQuery('form.site-search'),
			open: jQuery('.btn.search'),
			close: jQuery('button.close'),
			hasLoaded: false
		},

		init: function () {
			sf = this.settings;
			this.bindUIActions();
			console.log('search form loaded!');
		},

		bindUIActions: function () {
			$(document).on('facetwp-refresh', function () {
				console.log('FWP Refresh');

				//$('.loading-search').show();
				$('#search-results').addClass('loading');
			});

			$(document).on('facetwp-loaded', function () {
				console.log('FWP Loaded');

				//$('.loading-search').hide();
				$('#search-results').removeClass('loading');
				searchForm.setupFacets();
				sf.hasLoaded = true;
			});

			$('#open-filters').on('click', function () {
				$('.search-filters-aside').toggleClass('active');
				$('.line-items').toggleClass('active');
			});
		
			$('#close-filters').on('click', function () {
				$('.search-filters-aside').removeClass('active');
				$('.line-items').removeClass('active');
			});

			if (sf.form.length > 0) {

				// Open search form
				sf.open.click(function (evt) {
					sf.form.addClass('open');
				});

				// Close search form
				sf.close.click(function (evt) {
					if (sf.form.hasClass('open')) {
						sf.form.removeClass('open');
					}
				});

			}
		},
		setupFacets: function(){
			// If discontinued facet is there, move some options around
			if($('.facetwp-facet-product_discontinued').length > 0){
				$('.facetwp-facet-product_discontinued').find('[data-value="0"]').remove();
			}
		}
	};

var s,
notice = {

    settings: {
        notice: jQuery('.notice.cookie'),
        noticeClose: jQuery('.notice.cookie .cookie-notice-close'),
        sitenotice: jQuery('.notice.site-wide.notice-small'),
        sitenoticeClose: jQuery('.notice.site-wide .site-wide-notice-close'),
        sitenoticeToggle: true
    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        console.log('notice loaded!');
    },

    bindUIActions: function() {
        jQuery(document).ready(function(){
            notice.checkNoticeCookie();
            notice.adjustForNotice();
        });
        jQuery(window).resize(function(){
            notice.adjustForNotice();
        });
        s.noticeClose.on('click', function() {
            document.cookie = "cookie_notice=accepted; expires=Thu, 18 Dec 2020 12:00:00 UTC";
            jQuery('.notice.cookie').toggleClass('disable');
        });
        s.sitenoticeClose.on('click', function(evt) {
            evt.preventDefault(jQuery(this));
            notice.removeNotice(jQuery(this));
        });        
    },
    setCookie: function(name,value,days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    },
    getCookie: function(name) {
        var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    },
    removeNotice: function(el){
        notice.setCookie('sitewide_notice','accepted',1);
        el.parents('.notice').remove();
        notice.adjustForNotice();    
        s.sitenoticeToggle = false;
    },
    adjustForNotice: function(){
        var allChildHeights = 0;
        var noticeHeights = 0;
        var navHeights = 0;
        var topNavHeight = jQuery('header.header .nav-top').outerHeight() + jQuery('header.header .header-btns').outerHeight()
        jQuery('header.header > *').each(function(){
            allChildHeights += $(this).outerHeight();
        });
        jQuery('header.header > .notice').each(function(){
            noticeHeights += $(this).outerHeight();
        });
        jQuery('header.header > *:not(.notice)').each(function(){
            navHeights += $(this).outerHeight();
        });        
             
        //jQuery('header.header').css('height', allChildHeights);
        jQuery('#main').css('padding-top', allChildHeights);
        // If we're on smaller screen sizes, we need to add padding to the top of the header to make room for the notices
        if(jQuery(window).width() <= 1100){
            jQuery('header.header').css('padding-top', topNavHeight);
        } else {
            jQuery('header.header').css('padding-top', 0);
        }
    },
    checkNoticeCookie: function(){
        console.log('the cookie');
        console.log(notice.getCookie('sitewide_notice'));
        if(notice.getCookie('sitewide_notice') && notice.getCookie('sitewide_notice') == 'accepted'){
            s.sitenoticeToggle = false;
        }
    }
};
var ec,
emailCheck = {

    settings: {

    },

    init: function() {
        ec = this.settings;
        this.bindUIActions();
        console.log('emailCheck loaded!');
    },

    bindUIActions: function() {
      // var username_state = false;
      // 
      // var email_state = false;
      //
      // jQuery('#reg_email').on('blur', function(){
      //   var email = jQuery('#reg_email').val();
      //   console.log('blur');
      //   if (email == '') {
      //    email_state = false;
      //    return;
      //   }
      //   $.ajax({
      //     url: '../app/themes/mightily/woocommerce/myaccount/form-login.php',
      //     type: 'POST',
      //     data: {
      //       'email_check' : 1,
      //       'user_email' : email,
      //     },
      //     success: function(response){
      //       if (response == 'taken' ) {
      //         email_state = false;
      //         jQuery('#reg_email').parent().removeClass();
      //         jQuery('#reg_email').parent().addClass("form_error");
      //         jQuery('#reg_email').siblings("span").text('Sorry... Email already taken');
      //       }else if (response == 'not_taken') {
      //         email_state = true;
      //         jQuery('#reg_email').parent().removeClass();
      //         jQuery('#reg_email').parent().addClass("form_success");
      //         jQuery('#reg_email').siblings("span").text('Email available');
      //       }
      //     }
      //   });
      // });




    }
};

var mk,
	manualKern = {
		settings: {
			kernEls: $('.manual-kern'),
		},
		init: function () {
			mk = this.settings;
			console.log('manual kern loaded!');
			this.runDuplication();
		},
		runSpans: function ($this) {
			// Build an array of letters
			var charArray = $this.text().split('');
			// Remove all characters from this element
			$this.empty();
			// Loop through the character array and add them back this element with <span> tags.
			$.each(charArray, function (index, value) {
				$this.append('<span class="char-' + index + ' char-' + value + '">' + value + '</span>');
			});
		},
		runDuplication: function () {
			mk.kernEls.each(function () {
				var $this = $(this);
				var $clone = $this.clone();
				$clone.addClass('screen-reader-text');
				$clone.removeClass('manual-kern');
				$clone.insertBefore($(this));
				$this.attr('aria-hidden', 'true');
				$this.addClass($this.text().toLowerCase().replace(/[\W_]+/g, '').substring(10, 0));
				manualKern.runSpans($this);
			});
		},
	};

var cp,
cartPreview = {

    settings: {

    },

    init: function() {
        cp = this.settings;
        this.bindUIActions();
        console.log('cartPreview loaded!');
    },

    bindUIActions: function() {
      
      var cart_link = $('a.cart-preview-link');
      var cart_submenu = $('ul.ajax-sub-menu.sub-menu');

      if(cart_submenu.css('display') == 'none') {
        cart_link.attr('href', '/cart');
      } else {
        cart_link.attr('href', '#');
      }

      $(window).resize(function() {
        if(cart_submenu.css('display') == 'none') {
        cart_link.attr('href', '/cart');
      } else {
        cart_link.attr('href', '#');
      }
      });
      
    }

};

var st,
	scrollTo = {
		settings: {
		},
		init: function () {
			ac = this.settings;
			this.bindUIActions();
			console.log('scrollTo loaded!');
		},
		bindUIActions: function () {
			jQuery('a[href*="#"]:not(a[href="#"])').on('click', function (e) {
				//e.preventDefault();
				jQuery('html, body').animate({
					scrollTop: jQuery(jQuery(this).attr('href')).offset().top - 150,
				}, 0, 'linear');
				console.log('scroll');
			});
		},
	};

var ct,
    carbonTables = {
        settings: {
        },
        init: function () {
            ac = this.settings;
            this.updateWishlistTables();
            this.updateBasicContentTables();
            console.log('scrollTo loaded!');
        },
        updateWishlistTables: function () {
            jQuery('.wl-table').each(function () {
                jQuery(this).addClass('bx--data-table');
                jQuery(this).removeClass('wl-table');
            });
        },
        updateBasicContentTables: function () {
            jQuery('.layout.basic-content table').each(function () {
                jQuery(this).addClass('bx--data-table');
                // If a thead is not present, move the first row to the thead
                if (!jQuery(this).find('thead').length) {
                    console.log('appending head');
                    jQuery(this).prepend('<thead></thead>');
                    var newHead = jQuery(this).find('thead');
                    jQuery(this).find('tr').first().appendTo(newHead);
                }
            });
        }
    };

var ao,
	adminOrderScreen = {
		settings: {
		},
		init: function() {
			ao = this.settings;
			this.bindUIActions();
			console.log('adminOrderPage loaded!');
		},
		bindUIActions: function() {
			
		},
	};


jQuery(document).ready(function () {

  mobileMenu.init();
  accordions.init();
  carbonTables.init();
  LayoutFilter.init();
  donateForm.init();
  emailCheck.init();
  glideSlide.init();
  slickSlide.init();
  MicroModal.init();
  manualKern.init();
  cartPreview.init();
  scrollTo.init();
  adminOrderScreen.init();

  CarbonComponents.Accordion.init();
  CarbonComponents.Modal.init();

  AOS.init({
    duration: 1000,
    delay: 200,
    mirror: true,
    easing: 'easeInOut'
  });


  // Animating the accordions
  // $('.accordion-wrapper .bx--accordion__heading').on('click', function() {
  //   $(this).parent().toggleClass('bx--accordion__item');
  //   $(this).parent().toggleClass('bx--accordion__item--active');
  //   //$(this).next('.bx--accordion__content').slideToggle();
  // });


  var urlParams = new URLSearchParams(window.location.search);
  // console.log(urlParams.has('shopper_email_address'));
  if (urlParams.has('shopper_email_address') || urlParams.has('shopper_invite_open')) {
    MicroModal.show('invite-shopper');
  }

  accessibleTabbing.init();
  notice.init();

  if (jQuery('.rellax').length) {
    // Center all the things!
    var rellax = new Rellax('.rellax', {
      center: true
    });
  }

  // magicalUnderline.init();
  // formLabels.init();

  searchForm.init();

  if (jQuery('body').hasClass('role-teacher')) {
    jQuery('ul.ajax-sub-menu li.checkout a').text('Proceed to Request');
  }

  // This disables the select dropdown for users on the checkout screen
  jQuery('select[readonly="readonly"] option:not(:selected)').attr('disabled', true);

  // Replace string in state dropdown with 'select a state' instead of 'select an option'
  function replaceStatePlaceholder() {
    if(jQuery('#billing_country').val() == 'US'){
      console.log('us was selected');
      jQuery("#billing_state option, #billing_state_field .select2-selection__placeholder").each(function(){
        //console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select an option…/g,'Select a state…'));
      });
    } else {
      jQuery("#billing_state option, #billing_state_field .select2-selection__placeholder").each(function(){
        //console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select a state…/g,'Select an option…'));
      });
    }

    if(jQuery('#shipping_country').val() == 'US'){
      console.log('us was selected');
      jQuery("#shipping_state option, #shipping_state_field .select2-selection__placeholder").each(function(){
        //console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select an option…/g,'Select a state…'));
      });
    } else {
      jQuery("#shipping_state option, #shipping_state_field .select2-selection__placeholder").each(function(){
        //console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select a state…/g,'Select an option…'));
      });
    }    
  }
  if(jQuery('#billing_state_field').length > 0 || jQuery('#shipping_state_field').length > 0){
    setTimeout(function(){
        replaceStatePlaceholder();
    }, 500);
    $('#billing_country').on('change', function(){
        setTimeout(function(){
            replaceStatePlaceholder();
        }, 50);
    });
    $('#shipping_country').on('change', function(){
      setTimeout(function(){
          replaceStatePlaceholder();
      }, 50);
  });    
  } 


  // Disables default Woocommerce Orderby form functionality, and adds a submit buttons
  jQuery('.woocommerce-ordering').unbind();

  // // Allows click on WooCommerce tab links
  jQuery('body').off('click', '.wc-tabs li a, ul.tabs li a');

  jQuery('body').on('click', '.wc-tabs li a.tab-link, ul.tabs li a.tab-link', function (e) {
    e.preventDefault ? e.preventDefault() : (e.returnValue = false);
    // e.preventDefault();

    var $tab = $(this);
    var $tabs_wrapper = $tab.closest('.wc-tabs-wrapper, .woocommerce-tabs');
    var $tabs = $tabs_wrapper.find('.wc-tabs, ul.tabs');

    $tabs.find('li').removeClass('active');
    $tabs_wrapper.find('.wc-tab, .panel:not(.panel .panel)').hide();

    $tab.closest('li').addClass('active');
    $tabs_wrapper.find($tab.attr('href')).show();
  });

  // Controls layout view toggle button on List Of Items
  var layoutButtons = jQuery('.layout-button');

  layoutButtons.on('click', function (evt) {
    evt.preventDefault ? evt.preventDefault() : (evt.returnValue = false);
    // evt.preventDefault();
    jQuery(this).toggleClass('active');
    jQuery(this).siblings('.layout-button').toggleClass('active');
    var layoutWrapper = jQuery(this).closest('.list-of-items');
    var clickedView = jQuery(this).data('view');
    if (clickedView == 'grid') {
      layoutWrapper.removeClass('list-view');
    } else if (clickedView == 'list') {
      layoutWrapper.addClass('list-view');
    }
  });

  // Add target="_blank" attribute to download links
  if (jQuery('.woocommerce-MyAccount-downloads-file').length > 0) {
    jQuery('.woocommerce-MyAccount-downloads-file').attr('target', '_blank');
  }

  // Remove aria-describedby attribute from wp-caption figure elements
  if (jQuery('figure.wp-caption').length > 0) {
    jQuery('figure.wp-caption').removeAttr('aria-describedby');
  }

  jQuery('#wl-wrapper select.select-move').on('change', function () {
    //window.location.href = this.value;
    var thisEl = this;
    postData = JSON.parse(thisEl.value);
    console.log(postData);
    $(thisEl).parents('.select-url-wrapper').find('img').show();
    jQuery.post(postData.url, postData).done(function (data) {
      $(thisEl).parents('tr.cart_table_item').remove();
    });
  });

  jQuery('#wl-wrapper select.select-privacy').on('change', function () {
    //window.location.href = this.value;
    var thisEl = this;
    postData = JSON.parse(thisEl.value);
    console.log(postData);
    $(thisEl).parents('.select-url-wrapper').find('img').show();
    jQuery.post(postData.url, postData).done(function (data) {
      $(thisEl).parents('.select-url-wrapper').find('img').hide();
    });
  });

  var prevQty = 1;
  var newQty = 1;
  jQuery('#wl-wrapper input.qty').on('focus', function(evt){
    prevQty = $(this).val();
    console.log('prev: ' + prevQty);
  });
  jQuery('#wl-wrapper input.qty').on('blur', function (evt) {
    //window.location.href = this.value;
    var thisEl = this;
    newQty = $(thisEl).val();
    // If qty changes then proceed
    if(prevQty != newQty){
      itemKey = $(thisEl).data('key');
      postData = $(thisEl).data('query');
      postData.cart[itemKey].qty = newQty;
      $(thisEl).parents('div.quantity').find('img').show();
      jQuery.post(postData.url, postData).done(function (data) {
        $(thisEl).parents('div.quantity').find('img').hide();
      });
    }
  });
  $(document.body).on( 'checkout_error', function(){
    if($('.woocommerce-error').length > 0){
      $('.woocommerce-error').attr('tabindex', '0');
      $('.woocommerce-error').focus();
    }
  });

});


