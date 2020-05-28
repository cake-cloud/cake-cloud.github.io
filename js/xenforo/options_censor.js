/*
 * XenForo options_censor.min.js
 * Copyright 2010-2019 XenForo Ltd.
 * Released under the XenForo License Agreement: http://xenforo.com/license-agreement
 */
var $jscomp=$jscomp||{};$jscomp.scope={};$jscomp.findInternal=function(a,d,c){a instanceof String&&(a=String(a));for(var e=a.length,b=0;b<e;b++){var f=a[b];if(d.call(c,f,b,a))return{i:b,v:f}}return{i:-1,v:void 0}};$jscomp.ASSUME_ES5=!1;$jscomp.ASSUME_NO_NATIVE_MAP=!1;$jscomp.ASSUME_NO_NATIVE_SET=!1;$jscomp.defineProperty=$jscomp.ASSUME_ES5||"function"==typeof Object.defineProperties?Object.defineProperty:function(a,d,c){a!=Array.prototype&&a!=Object.prototype&&(a[d]=c.value)};
$jscomp.getGlobal=function(a){return"undefined"!=typeof window&&window===a?a:"undefined"!=typeof global&&null!=global?global:a};$jscomp.global=$jscomp.getGlobal(this);$jscomp.polyfill=function(a,d,c,e){if(d){c=$jscomp.global;a=a.split(".");for(e=0;e<a.length-1;e++){var b=a[e];b in c||(c[b]={});c=c[b]}a=a[a.length-1];e=c[a];d=d(e);d!=e&&null!=d&&$jscomp.defineProperty(c,a,{configurable:!0,writable:!0,value:d})}};
$jscomp.polyfill("Array.prototype.find",function(a){return a?a:function(a,c){return $jscomp.findInternal(this,a,c).v}},"es6","es3");
!function(a,d,c,e){XenForo.CensorWordOptionListener=function(a){this.__construct(a)};XenForo.CensorWordOptionListener.prototype={__construct:function(b){b.one("keypress",a.context(this,"createChoice"));this.$element=b;this.$base||(this.$base=b.clone())},createChoice:function(){var b=this.$base.clone(),c=this.$element.parent().children().length;b.find("input[name]").each(function(){var b=a(this);b.attr("name",b.attr("name").replace(/\[(\d+)\]/,"["+c+"]"))});b.find("*[id]").each(function(){var b=a(this);
b.removeAttr("id");XenForo.uniqueId(b);XenForo.formCtrl&&XenForo.formCtrl.clean(b)});b.xfInsert("insertAfter",this.$element);this.__construct(b)}};XenForo.register("li.CensorWordOptionListener","XenForo.CensorWordOptionListener")}(jQuery,this,document);
