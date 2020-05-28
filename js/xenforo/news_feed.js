/*
 * XenForo news_feed.min.js
 * Copyright 2010-2019 XenForo Ltd.
 * Released under the XenForo License Agreement: http://xenforo.com/license-agreement
 */
!function(b,e,f,g){XenForo.NewsFeedLoader=function(a){this.__construct(a)};XenForo.NewsFeedLoader.prototype={__construct:function(a){this.$link=a.click(b.context(this,"load"));this.xhr=null},load:function(a){a.preventDefault();a.target.blur();null===this.xhr&&this.$link.attr("href")&&(this.xhr=XenForo.ajax(this.$link.attr("href"),{news_feed_id:this.$link.data("oldestitemid")},b.context(this,"display")));return!1},display:function(a,h){this.xhr=null;if(XenForo.hasResponseError(a))return!1;var c=this.$link;
c.data("oldestitemid",a.oldestItemId);XenForo.hasTemplateHtml(a)&&new XenForo.ExtLoader(a,function(){var d=b(a.templateHtml);d.length&&d.xfInsert("insertBefore",c.closest(".NewsFeedEnd"),"xfSlideDown",XenForo.speed.slow)});a.feedEnds&&this.$link.closest(".NewsFeedEnd").xfFadeOut()}};XenForo.NewsFeedItemHider=function(a){this.__construct(a)};XenForo.NewsFeedItemHider.prototype={__construct:function(a){this.$link=a.click(b.context(this,"requestHide"))},requestHide:function(a){a.preventDefault();b(this.$link.closest(".NewsFeedItem")).xfRemove();
XenForo.ajax(this.$link.attr("href"),"",b.context(this,"hide"))},hide:function(a,b){if(XenForo.hasResponseError(a))return!1}};XenForo.register("a.NewsFeedLoader","XenForo.NewsFeedLoader");XenForo.register("a.NewsFeedItemHider","XenForo.NewsFeedItemHider")}(jQuery,this,document);
