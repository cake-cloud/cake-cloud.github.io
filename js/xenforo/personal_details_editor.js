/*
 * XenForo personal_details_editor.min.js
 * Copyright 2010-2019 XenForo Ltd.
 * Released under the XenForo License Agreement: http://xenforo.com/license-agreement
 */
var $jscomp=$jscomp||{};$jscomp.scope={};$jscomp.findInternal=function(a,d,b){a instanceof String&&(a=String(a));for(var e=a.length,c=0;c<e;c++){var f=a[c];if(d.call(b,f,c,a))return{i:c,v:f}}return{i:-1,v:void 0}};$jscomp.ASSUME_ES5=!1;$jscomp.ASSUME_NO_NATIVE_MAP=!1;$jscomp.ASSUME_NO_NATIVE_SET=!1;$jscomp.defineProperty=$jscomp.ASSUME_ES5||"function"==typeof Object.defineProperties?Object.defineProperty:function(a,d,b){a!=Array.prototype&&a!=Object.prototype&&(a[d]=b.value)};
$jscomp.getGlobal=function(a){return"undefined"!=typeof window&&window===a?a:"undefined"!=typeof global&&null!=global?global:a};$jscomp.global=$jscomp.getGlobal(this);$jscomp.polyfill=function(a,d,b,e){if(d){b=$jscomp.global;a=a.split(".");for(e=0;e<a.length-1;e++){var c=a[e];c in b||(b[c]={});b=b[c]}a=a[a.length-1];e=b[a];d=d(e);d!=e&&null!=d&&$jscomp.defineProperty(b,a,{configurable:!0,writable:!0,value:d})}};
$jscomp.polyfill("Array.prototype.find",function(a){return a?a:function(a,b){return $jscomp.findInternal(this,a,b).v}},"es6","es3");
!function(a,d,b,e){XenForo.AvatarGenderUpdater=function(a){this.__construct(a)};XenForo.AvatarGenderUpdater.prototype={__construct:function(c){c.find('input[name="gender"]').length&&c.bind("AutoValidationComplete",a.context(this,"updateAvatars"))},updateAvatars:function(a){a.ajaxData.userId&&a.ajaxData.avatarUrls&&XenForo.updateUserAvatars(a.ajaxData.userId,a.ajaxData.avatarUrls)}};XenForo.register("form.AutoValidator","XenForo.AvatarGenderUpdater")}(jQuery,this,document);
