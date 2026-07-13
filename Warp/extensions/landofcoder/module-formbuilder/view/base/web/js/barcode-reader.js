;(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.SignaturePad = factory());
}(this, (function () {
  'use strict';
  var BarcodeScanner = (function () {
    function BarcodeScanner(field_id) {
      var _this = this;
    
      this.handlers = [];
      var scanningBarcode = false;
      var lastKey = '';
      var chars = [];
      if(field_id){
        document.getElementById(field_id).focus();
      }
    
      window.addEventListener('keydown', function (e) {
        switch (e.key) {
          case 'Shift':
            if (lastKey === 'F11') {
              scanningBarcode = true;
              e.preventDefault();
              chars = [];
            }
            break;
          case 'Enter':
            if (scanningBarcode) {
              e.preventDefault();
              _this.value = chars.join('');
              var event = { target: _this, data: _this.value };
              _this.dispatchEvent('scan', event);
              scanningBarcode = false;
            }else {
              var event = { target: _this, data: "" };
              _this.dispatchEvent('doEnter', event);
            }
            break;
          default:
            if (scanningBarcode) {
              e.preventDefault();
              chars.push(e.key);
            }
            break;
        }
        lastKey = e.key;
      }, true);
    }
    BarcodeScanner.prototype.addEventListener = function addEventListener(eventName, eventHandler) {
      if (!this.handlers[eventName]) {
        this.handlers[eventName] = [];
      }
      this.handlers[eventName].push(eventHandler);
    };
    BarcodeScanner.prototype.removeEventListener = function removeEventListener(eventName, eventHandler) {
      this.handlers[eventName] = this.handlers[eventName].filter(function (handler) {
        return handler !== eventHandler;
      });
    };
    BarcodeScanner.prototype.dispatchEvent = function dispatchEvent(eventName, eventObject) {
      var _this2 = this;
    
      if (this.handlers && this.handlers[eventName]) {
        this.handlers[eventName].forEach(function (handler) {
          handler.call(_this2, eventObject);
        });
      }
    };
    return BarcodeScanner;
  }());
  return BarcodeScanner;
})));