(window["aioseopjsonp"]=window["aioseopjsonp"]||[]).push([["chunk-33f42421","chunk-0777c24a","chunk-2244391f","chunk-3e49c691","chunk-8602db3a","chunk-2d21e3d6","chunk-2d0cb704","chunk-2d21676b"],{"0866":function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"aioseo-link-assistant"},[i("core-main",{attrs:{"page-name":t.strings.pageName,"exclude-tabs":t.excludedTabs,showTabs:"post-report"!==t.$route.name}},[i(t.$route.name,{tag:"component"})],1),i("transition",{attrs:{name:"fade-processing-popup"}},[!t.suggestionsScan.showProcessingPopup||"links-report"!==t.$route.name&&"overview"!==t.$route.name||100===t.suggestionsScan.percent?t._e():i("core-processing-popup",{on:{"close-processing-popup":t.toggleProcessingPopup}})],1)],1)},n=[],a=i("5530"),o=i("ff7d"),r=i("d3eb"),l=i("9920"),d=i("a16c"),c=i("9fa6"),u=i("9c0e"),h=i("2f62"),p={mixins:[u["k"],u["l"]],components:{Overview:o["default"],LinksReport:r["default"],DomainsReport:l["default"],PostReport:d["default"],Settings:c["default"]},data:function(){return{strings:{pageName:this.$t.__("Link Assistant",this.$td)}}},computed:Object(a["a"])(Object(a["a"])({},Object(h["e"])("linkAssistant",["suggestionsScan"])),{},{excludedTabs:function(){var t=(this.$addons.isActive("aioseo-link-assistant")?this.getExcludedUpdateTabs("aioseo-link-assistant"):this.getExcludedActivationTabs("aioseo-link-assistant"))||[];return t.push("post-report"),t}}),methods:Object(a["a"])(Object(a["a"])({},Object(h["d"])("linkAssistant",["toggleProcessingPopup"])),Object(h["b"])("linkAssistant",["pollSuggestionsScan","getMenuData"])),mounted:function(){var t=this;this.$bus.$on("changes-saved",(function(){t.getMenuData()})),this.$isPro&&100!==this.suggestionsScan.percent&&this.$addons.isActive("aioseo-link-assistant")&&!this.$addons.requiresUpgrade("aioseo-link-assistant")&&this.$addons.hasMinimumVersion("aioseo-link-assistant")&&this.pollSuggestionsScan()}},g=p,f=(i("dd40"),i("2877")),k=Object(f["a"])(g,e,n,!1,null,null,null);s["default"]=k.exports},"0d56":function(t,s,i){},"2d72":function(t,s,i){},"35dd":function(t,s,i){"use strict";i("0d56")},"4a56":function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",[i("blur"),i("cta",{attrs:{"cta-link":t.$aioseo.urls.aio.featureManager+"&aioseo-activate=aioseo-link-assistant","cta-button-action":"","cta-button-loading":t.activationLoading,"same-tab":"","button-text":t.strings.ctaButtonText,"learn-more-link":t.$links.getDocUrl("link-assistant"),"feature-list":[t.strings.linkOpportunities,t.strings.domainReports,t.strings.orphanedPosts,t.strings.affiliateLinks]},on:{"cta-button-click":t.activateAddon},scopedSlots:t._u([{key:"header-text",fn:function(){return[t._v(" "+t._s(t.strings.ctaHeader)+" ")]},proxy:!0},{key:"description",fn:function(){return[t.failed?i("core-alert",{attrs:{type:"red"}},[t._v(" "+t._s(t.strings.activateError)+" ")]):t._e(),t._v(" "+t._s(t.strings.ctaDescription)+" ")]},proxy:!0},{key:"learn-more-text",fn:function(){return[t._v(" "+t._s(t.strings.learnMoreText)+" ")]},proxy:!0}])})],1)},n=[],a=i("5530"),o=(i("d3b7"),i("3ca3"),i("ddb0"),i("2f62")),r=i("2585"),l={components:{Blur:r["default"]},data:function(){return{strings:{ctaButtonText:this.$t.__("Activate Link Assistant",this.$tdPro),ctaHeader:this.$t.__("Enable Link Assistant on your Site",this.$tdPro),ctaDescription:this.$t.__("Get relevant suggestions for adding internal links to all your content as well as finding any orphaned posts that have no internal links.",this.$td),linkOpportunities:this.$t.__("Actionable Link Suggestions",this.$td),orphanedPosts:this.$t.__("See Orphaned Posts",this.$td),affiliateLinks:this.$t.__("See Affiliate Links",this.$td),domainReports:this.$t.__("Top Domain Reports",this.$td),activateError:this.$t.__("An error occurred while activating the addon. Please upload it manually or contact support for more information.",this.$td),permissionWarning:this.$t.__("You currently don't have permission to activate this addon. Please ask a site administrator to activate first.",this.$td)},failed:!1,activationLoading:!1}},methods:Object(a["a"])(Object(a["a"])(Object(a["a"])(Object(a["a"])({},Object(o["b"])("linkAssistant",["getMenuData"])),Object(o["b"])(["installPlugins"])),Object(o["d"])(["updateAddon"])),{},{activateAddon:function(){var t=this;this.failed=!1,this.activationLoading=!0;var s=this.$addons.getAddon("aioseo-link-assistant");this.installPlugins([{plugin:s.basename}]).then((function(i){if(i.body.failed.length)return t.activationLoading=!1,void(t.failed=!0);var e=[t.getMenuData()];Promise.all(e).then((function(){t.activationLoading=!1,s.isActive=!0,t.updateAddon(s)}))})).catch((function(){t.activationLoading=!1}))}})},d=l,c=i("2877"),u=Object(c["a"])(d,e,n,!1,null,null,null);s["default"]=u.exports},"4f0a":function(t,s,i){"use strict";i("84bd")},"6bfe":function(t,s,i){"use strict";i("ba4a")},"729c":function(t,s,i){},"7bd8":function(t,s,i){"use strict";i("2d72")},"84bd":function(t,s,i){},9920:function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"aioseo-link-assistant-domains-report"},[i("base-wp-table",{key:t.tableKey,ref:"table",attrs:{columns:t.columns,rows:t.linkAssistant.domainsReport.rows,totals:t.linkAssistant.domainsReport.totals,"bulk-options":t.bulkOptions,loading:t.wpTableLoading,initialPageNumber:t.linkAssistant.domainsReport.tableFields.paginatedPage,initialSearchTerm:t.linkAssistant.domainsReport.tableFields.searchTerm},on:{"process-bulk-action":t.maybeDoBulkAction,paginate:t.processPagination,search:t.processSearch},scopedSlots:t._u([{key:"domain",fn:function(s){var e=s.row,n=s.index,a=s.editRow;return[i("div",{staticClass:"domain-name"},[i("a",{class:{active:t.isRowActive(n)},attrs:{href:"#"},on:{click:function(s){a(n),t.toggleRow(n)}}},[i("img",{staticClass:"favicon",attrs:{src:"https://www.google.com/s2/favicons?sz=32&domain="+Object.keys(e)[0]}}),i("span",[t._v(" "+t._s(Object.keys(e)[0])+" ")])])]),i("div",{staticClass:"row-actions"},[i("span",[i("a",{staticClass:"view",attrs:{href:"https://"+Object.keys(e)[0],target:"_blank"}},[i("span",[t._v(t._s(t.strings.view))])]),t._v(" | ")]),i("span",[i("a",{staticClass:"delete-all-links",attrs:{href:"#"},on:{click:function(s){return s.preventDefault(),t.maybeDoBulkAction({action:"delete",selectedRows:n})}}},[i("span",[t._v(t._s(t.strings.deleteAllLinks))])])])])]}},{key:"posts",fn:function(s){var e=s.row;return[i("svg-file"),i("span",[t._v(t._s(t.$numbers.numberFormat(t.postCount(e))))])]}},{key:"links",fn:function(s){var e=s.row;return[i("svg-link-external"),i("span",[t._v(t._s(t.$numbers.numberFormat(t.linkCount(e))))])]}},{key:"toggle-button",fn:function(s){var e=s.index,n=s.editRow;return[i("button",{staticClass:"toggle-row-button",class:{active:t.isRowActive(e)},on:{click:function(s){n(e),t.toggleRow(e)}}},[i("svg-caret")],1)]}},{key:"edit-row",fn:function(s){var e=s.row;return[i("DomainsReportInner",{key:t.innerTableKey,attrs:{domain:t.getInnerDomain(e),rows:t.getInnerDomainRows(e),activeDomain:t.activeRow},on:{updated:function(s){t.innerTableKey++}}})]}}])}),i("link-assistant-confirmation-modal",{attrs:{strings:t.modalStrings,showModal:t.showModal,selectedRows:t.selectedRows},on:{doBulkAction:t.doBulkAction,closeModal:function(s){t.showModal=!1}}})],1)},n=[],a=i("5530"),o=(i("b64b"),i("d3b7"),i("2f62")),r=i("c1a2"),l={components:{DomainsReportInner:r["default"]},beforeMount:function(){this.$route.query&&(this.$route.query.hostname&&(this.linkAssistant.domainsReport.tableFields.searchTerm=this.$route.query.hostname,this.processSearch(this.$route.query.hostname)),this.$route.query.fullReport&&(this.linkAssistant.domainsReport.tableFields.searchTerm="",this.processPagination(1)))},data:function(){return{tableKey:0,innerTableKey:0,activeRow:-1,wpTableLoading:!1,showModal:!1,selectedRows:null,action:null,bulkOptions:[{label:this.$t.__("Delete",this.$tdPro),value:"delete"}],strings:{view:this.$t.__("View",this.$tdPro),deleteAllLinks:this.$t.__("Delete All Links",this.$tdPro)},modalStrings:{areYouSureSingle:this.$t.__("Are you sure you want to delete all links for this domain?",this.$tdPro),areYouSureMultiple:this.$t.__("Are you sure you want to delete all links for these domains?",this.$tdPro),areYouSureAll:this.$t.__("Are you sure you want to delete all links for all domains?",this.$tdPro),actionCannotBeUndone:this.$t.__("This action cannot be undone.",this.$tdPro),yesSingle:this.$t.__("Yes, I want to delete this link",this.$tdPro),yesMultiple:this.$t.__("Yes, I want to delete these links",this.$tdPro),yesAll:this.$t.__("Yes, I want to delete all links",this.$tdPro),noChangedMind:this.$t.__("No, I changed my mind",this.$tdPro)}}},computed:Object(a["a"])(Object(a["a"])({},Object(o["e"])(["linkAssistant"])),{},{innerRows:function(){return this.linkAssistant.domainsReport.rows},columns:function(){return[{slug:"domain",label:this.$t.__("Domain",this.$tdPro)},{slug:"posts",label:this.$t.__("Posts",this.$tdPro),width:"90px"},{slug:"links",label:this.$t.__("Links",this.$tdPro),width:"90px"},{slug:"toggle-button",label:"",width:"60px"}]}}),methods:Object(a["a"])(Object(a["a"])(Object(a["a"])({},Object(o["b"])("linkAssistant",["domainsReportPaginate","domainsReportBulk","domainsReportSearch"])),Object(o["d"])("linkAssistant",["setPaginatedPage"])),{},{postCount:function(t){var s=Object.keys(t)[0];return t[s][0].totals.total},linkCount:function(t){var s=Object.keys(t)[0];return t[s][0].totals.totalLinks},isRowActive:function(t){return t===this.activeRow},toggleRow:function(t){this.activeRow!==t?this.activeRow=t:this.activeRow=-1},maybeDoBulkAction:function(t){var s=t.action,i=t.selectedRows;!1!==i&&s&&(this.action=s,this.selectedRows=i,this.showModal=!0)},doBulkAction:function(){var t=this;return this.showModal=!1,this.wpTableLoading=!0,this.domainsReportBulk({action:this.action,searchTerm:this.linkAssistant.domainsReport.tableFields.searchTerm,rowIndexes:this.selectedRows}).finally((function(){t.activeRow=-1,t.wpTableLoading=!1,t.tableKey++}))},processPagination:function(t){var s=this;this.setPaginatedPage({group:"domainsReport",page:t}),this.wpTableLoading=!0,this.domainsReportPaginate({page:t,searchTerm:this.linkAssistant.domainsReport.tableFields.searchTerm}).finally((function(){s.activeRow=-1,s.wpTableLoading=!1,s.tableKey++}))},processSearch:function(t){var s=this;this.wpTableLoading=!0,this.linkAssistant.domainsReport.tableFields.searchTerm=t,this.domainsReportSearch({searchTerm:t,page:1}).finally((function(){s.activeRow=-1,s.wpTableLoading=!1,s.tableKey++}))},getInnerDomain:function(t){return Object.keys(t)[0]},getInnerDomainRows:function(t){return t[this.getInnerDomain(t)]}})},d=l,c=(i("6bfe"),i("2877")),u=Object(c["a"])(d,e,n,!1,null,null,null);s["default"]=u.exports},"9fa6":function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"aioseo-link-assistant-settings"},[i("core-card",{attrs:{slug:"linkAssistantSettings","header-text":t.strings.settings}},[i("core-settings-row",{attrs:{name:t.strings.postTypes},scopedSlots:t._u([{key:"content",fn:function(){return[i("base-checkbox",{attrs:{size:"medium"},model:{value:t.linkAssistantOptions.postTypes.all,callback:function(s){t.$set(t.linkAssistantOptions.postTypes,"all",s)},expression:"linkAssistantOptions.postTypes.all"}},[t._v(" "+t._s(t.strings.includeAllPostTypes)+" ")]),t.linkAssistantOptions.postTypes.all?t._e():i("core-post-type-options",{attrs:{id:"postTypes",options:t.linkAssistantOptions,excluded:["attachment"],type:"postTypes"}}),i("div",{staticClass:"aioseo-description"},[t._v(" "+t._s(t.strings.selectPostTypes)+" "),i("span",{domProps:{innerHTML:t._s(t.$links.getDocLink(t.$constants.GLOBAL_STRINGS.learnMore,"linkAssistantPostTypes",!0))}})])]},proxy:!0}])}),i("core-settings-row",{attrs:{name:t.strings.postStatuses},scopedSlots:t._u([{key:"content",fn:function(){return[i("base-checkbox",{attrs:{size:"medium"},model:{value:t.linkAssistantOptions.postStatuses.all,callback:function(s){t.$set(t.linkAssistantOptions.postStatuses,"all",s)},expression:"linkAssistantOptions.postStatuses.all"}},[t._v(" "+t._s(t.strings.includeAllPostStatuses)+" ")]),t.linkAssistantOptions.postStatuses.all?t._e():i("core-post-status-options",{attrs:{id:"postStatuses",options:t.linkAssistantOptions,type:"postStatuses"}}),i("div",{staticClass:"aioseo-description"},[t._v(" "+t._s(t.strings.selectPostStatuses)+" "),i("span",{domProps:{innerHTML:t._s(t.$links.getDocLink(t.$constants.GLOBAL_STRINGS.learnMore,"linkAssistantPostStatuses",!0))}})])]},proxy:!0}])}),i("core-settings-row",{attrs:{name:t.strings.skipSentences,align:""},scopedSlots:t._u([{key:"content",fn:function(){return[i("base-input",{staticClass:"settings-skip-sentences",attrs:{type:"number",size:"medium",min:0},on:{keyup:function(s){return t.validateSkipSentences(s)}},model:{value:t.linkAssistantOptions.skipSentences,callback:function(s){t.$set(t.linkAssistantOptions,"skipSentences",s)},expression:"linkAssistantOptions.skipSentences"}}),i("div",{staticClass:"aioseo-description"},[t._v(" "+t._s(t.strings.skipSentencesDescription)+" ")])]},proxy:!0}])}),i("core-settings-row",{staticClass:"affiliate-prefix",attrs:{name:t.strings.affiliatePrefix,align:""},scopedSlots:t._u([{key:"content",fn:function(){return[i("base-select",{attrs:{multiple:"",taggable:"",options:t.getJsonValue(t.linkAssistantOptions.affiliatePrefix)||[],value:t.getJsonValue(t.linkAssistantOptions.affiliatePrefix)||[]},on:{input:function(s){return t.linkAssistantOptions.affiliatePrefix=t.setJsonValue(s)}}}),i("div",{staticClass:"aioseo-description"},[t._v(" "+t._s(t.strings.affiliatePrefixDescription)+" ")])]},proxy:!0}])}),i("core-settings-row",{attrs:{name:t.strings.excludePostsPages},scopedSlots:t._u([{key:"content",fn:function(){return[i("core-exclude-posts",{attrs:{options:t.linkAssistantOptions,type:"posts"}})]},proxy:!0}])}),i("core-settings-row",{attrs:{name:t.strings.wordsToIgnore},scopedSlots:t._u([{key:"content",fn:function(){return[i("base-textarea",{attrs:{minHeight:200,autosize:!1},model:{value:t.linkAssistantOptions.wordsToIgnore,callback:function(s){t.$set(t.linkAssistantOptions,"wordsToIgnore",s)},expression:"linkAssistantOptions.wordsToIgnore"}})]},proxy:!0}])})],1)],1)},n=[],a=i("5530"),o=i("2f62"),r=i("9c0e"),l={mixins:[r["f"]],data:function(){return{initialAffiliateLinkPrefixes:"",strings:{settings:this.$t.__("Link Settings",this.$td),postTypes:this.$t.__("Post Types",this.$td),includeAllPostTypes:this.$t.__("Include All Post Types",this.$td),selectPostTypes:this.$t.__("Select which Post Types you want to enable Link Assistant for.",this.$td),postStatuses:this.$t.__("Post Statuses",this.$td),includeAllPostStatuses:this.$t.__("Include All Post Statuses",this.$td),selectPostStatuses:this.$t.__("Select which Post Statuses you want to enable Link Assistant for.",this.$td),skipSentences:this.$t.__("Number of Sentences to Skip at Beginning",this.$td),skipSentencesDescription:this.$t.sprintf(this.$t.__("The amount of sentences at the beginning of the article that %1$s should not suggest internal links for.",this.$tdPro),"AIOSEO"),affiliatePrefix:this.$t.__("Affiliate Link Prefix",this.$td),affiliatePrefixDescription:this.$t.sprintf(this.$t.__('Enter one or multiple link prefixes that %1$s should consider as affiliate links, e.g. "/go/", "/refer/" or "https://amazn.to".',this.$td),"AIOSEO"),excludePostsPages:this.$t.__("Exclude Posts / Pages",this.$td),wordsToIgnore:this.$t.__("Words to Ignore",this.$td)}}},computed:Object(a["a"])({},Object(o["e"])({linkAssistantOptions:function(t){return t.linkAssistant.options.main}})),methods:{validateSkipSentences:function(t){0>t.target.value&&(t.target.value=0)}},beforeMount:function(){var t=this;this.initialAffiliateLinkPrefixes=this.linkAssistantOptions.affiliatePrefix,this.$bus.$on("changes-saved",(function(){t.initialAffiliateLinkPrefixes=t.linkAssistantOptions.affiliatePrefix}))}},d=l,c=(i("7bd8"),i("2877")),u=Object(c["a"])(d,e,n,!1,null,null,null);s["default"]=u.exports},a16c:function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"aioseo-link-assistant-post-report"},[t.loadingInitialData?i("div",{staticClass:"header-container"},[i("div",{staticClass:"first-row"},[i("a",{attrs:{href:"#"},domProps:{innerHTML:t._s(t.strings.backToLinksReport)},on:{click:function(s){return s.stopPropagation(),s.preventDefault(),t.closePostReport.apply(null,arguments)}}})]),i("div",{staticClass:"second-row"},[i("div",{staticClass:"first-column"},[i("span",{staticClass:"header"},[t._v(t._s(t.strings.loadingHeader))])])])]):t._e(),t.showLoadingBar?i("div",{staticClass:"load-progress"},[i("div",{staticClass:"load-progress-value",style:{animationDuration:t.loadTime+"s"}})]):t._e(),t.loadingInitialData?t._e():i("div",{staticClass:"header-container"},[i("div",{staticClass:"first-row"},[i("a",{attrs:{href:"#"},domProps:{innerHTML:t._s(t.strings.backToLinksReport)},on:{click:function(s){return s.stopPropagation(),s.preventDefault(),t.closePostReport.apply(null,arguments)}}})]),i("div",{staticClass:"second-row"},[i("div",{staticClass:"first-column"},[i("span",{staticClass:"header"},[t._v(t._s(t.header))]),i("a",{staticClass:"view-post-link",attrs:{href:t.post.context.permalink,target:"_blank"}},[t._v(t._s(t.strings.viewPost))])]),i("div",{staticClass:"second-column"},[i("base-button",{attrs:{type:"blue",tag:"button",loading:t.refreshLoading,disabled:t.isPrioritizedPost,icon:"svg-refresh"},on:{click:t.doRefresh}},[t._v(" "+t._s(t.strings.refresh)+" ")])],1)])]),i("core-main-tabs",{staticClass:"link-tabs",attrs:{internal:"",tabs:t.tabs,active:t.activeTab,showSaveButton:!1},on:{changed:function(s){return t.processChangeTab(s)}}}),"outbound-internal"===t.activeTab?i("link-assistant-outbound-internal",{attrs:{post:t.getPost,postIndex:t.postIndex,postId:t.postId,postReport:""},on:{openSuggestions:function(s){return t.processChangeTab("link-suggestions","suggestions-outbound")}}}):t._e(),"inbound-internal"===t.activeTab?i("link-assistant-inbound-internal",{attrs:{post:t.getPost,postIndex:t.postIndex,postId:t.postId,postReport:""},on:{openSuggestions:function(s){return t.processChangeTab("link-suggestions","suggestions-inbound")}}}):t._e(),"affiliate"===t.activeTab?i("link-assistant-affiliate",{attrs:{post:t.getPost,postIndex:t.postIndex,postId:t.postId,postReport:""}}):t._e(),"external"===t.activeTab?i("link-assistant-external",{attrs:{post:t.getPost,postIndex:t.postIndex,postId:t.postId,postReport:""}}):t._e(),"link-suggestions"===t.activeTab?i("link-assistant-suggestions",{attrs:{post:t.getPost,postIndex:t.postIndex,postId:t.postId,filteredSuggestionsOutbound:t.filteredSuggestionsOutbound(t.post),initialTab:t.activeSuggestionTab,postReport:""},on:{showStandalone:function(s){t.showStandalone=!0},suggestionsTabChanged:function(s){return t.activeSuggestionTab=s}}}):t._e()],1)},n=[],a=i("5530"),o=(i("d3b7"),i("2f62")),r=i("1b6a"),l=i("039e"),d={mixins:[r["a"],l["a"]],beforeMount:function(){this.$route.query&&(this.$route.query.postId?(this.postId=parseInt(this.$route.query.postId),this.activeTab=this.$route.query.initialTab,this.activeSuggestionTab=this.$route.query.initialSuggestionTab,this.post={ID:this.postId,context:{},links:Object(a["a"])({},this.linkAssistant.postReport)}):this.$router.push({name:"links-report"}))},mounted:function(){var t=this;this.resetPostReportState(),this.$bus.$emit("updatingLinks",!0),this.showLoadingBar=!0,this.loadingInitialData=!0,setTimeout(this.checkProgress,1e3*this.loadTime),this.postReportInitial(this.postId).then((function(s){s.body.links&&(t.post.links=s.body.links),s.body.context&&(t.post.context=s.body.context),t.setPostReportLinks({links:Object(a["a"])({},s.body.links)})})).catch((function(s){console.error(s),t.$router.push({name:"links-report"})})).finally((function(){t.$bus.$emit("updatingLinks",!1),t.showLoadingBar=!1,t.loadingInitialData=!1}))},data:function(){return{postId:null,postIndex:0,post:{},activeTab:"inbound-internal",activeSuggestionTab:"suggestions-inbound",refreshLoading:!1,loadTime:4,showLoadingBar:!1,loadingInitialData:!1,strings:{backToLinksReport:this.$t.sprintf(this.$t.__("%1$s Back to Links Report",this.$t.tdPro),"<span>←</span>"),loadingHeader:this.$t.__("Loading Link Suggestions. Please wait...",this.$t.tdPro),viewPost:this.$t.__("view post",this.$t.tdPro),refresh:this.$t.__("Refresh",this.$t.tdPro)}}},computed:Object(a["a"])(Object(a["a"])({},Object(o["e"])("linkAssistant",["postReport"])),{},{getPost:function(){var t=Object(a["a"])(Object(a["a"])({},this.post),{},{links:this.postReport});return t},header:function(){return this.$t.sprintf(this.$t.__('Internal Links & Suggestions for "%1$s"',this.$t.tdPro),this.post.context.postTitle)},tabs:function(){return[{slug:"inbound-internal",icon:"svg-link-internal-inbound",name:this.$t.sprintf("%1$s %2$s",this.post.links.inboundInternal.totals.total,this.$t.__("Inbound Internal",this.$tdPro))},{slug:"outbound-internal",icon:"svg-link-internal-outbound",name:this.$t.sprintf("%1$s %2$s",this.post.links.outboundInternal.totals.total,this.$t.__("Outbound Internal",this.$tdPro))},{slug:"affiliate",icon:"svg-link-affiliate",name:this.$t.sprintf("%1$s %2$s",this.post.links.affiliate.totals.total,this.$t.__("Affiliate",this.$tdPro))},{slug:"external",icon:"svg-link-external",name:this.$t.sprintf("%1$s %2$s",this.post.links.external.totals.total,this.$t.__("External",this.$tdPro))},{slug:"link-suggestions",icon:"svg-link-suggestion",name:this.$t.sprintf("%1$s %2$s",this.post.links.suggestionsOutbound.totals.total+this.post.links.suggestionsInbound.totals.total,this.$t.__("Link Suggestions",this.$tdPro))}]}}),methods:Object(a["a"])(Object(a["a"])(Object(a["a"])({},Object(o["b"])("linkAssistant",["postReportInitial","linksRefresh"])),Object(o["d"])("linkAssistant",["setPostReportLinks","resetPostReportState"])),{},{closePostReport:function(){this.$router.push({name:"links-report"})},processChangeTab:function(t,s){this.activeTab=t,s&&(this.activeSuggestionTab=s)},doRefresh:function(){var t=this;this.refreshLoading=!0,this.$bus.$emit("updatingLinks",!0),this.linksRefresh({postIndex:this.postIndex,postId:this.post.ID,linksReport:!0}).finally((function(){t.refreshLoading=!1,t.$bus.$emit("updatingLinks",!1)}))},checkProgress:function(){var t=this;this.showLoadingBar=!1,this.loadingInitialData&&this.$nextTick((function(){t.showLoadingBar=!0,2>t.loadTime&&(t.loadTime=4),t.loadTime=t.loadTime/2,setTimeout(t.checkProgress,1e3*t.loadTime)}))}})},c=d,u=(i("4f0a"),i("2877")),h=Object(u["a"])(c,e,n,!1,null,null,null);s["default"]=h.exports},ba4a:function(t,s,i){},c1a2:function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"domains-report-inner"},[i("base-wp-table",{key:t.tableKey,staticClass:"link-assistant-inner-table",attrs:{columns:t.columns,rows:t.rows,totals:t.rows[0].totals,"bulk-options":t.bulkOptions,loading:t.wpTableLoading,showSearch:!1,showPagination:t.shouldShowPagination,showTableFooter:!1,initialPageNumber:t.initialPageNumber},on:{"process-bulk-action":t.maybeDoBulkAction,paginate:t.processPagination},scopedSlots:t._u([{key:"post_title",fn:function(s){var e=s.row;return[i("strong",[i("a",{staticClass:"edit-link",attrs:{href:e.context.permalink,target:"_blank"}},[t._v(t._s(e.context.postTitle))])]),i("div",{staticClass:"row-actions"},[i("span",{staticClass:"view"},[i("a",{attrs:{href:e.context.permalink,target:"_blank"}},[t._v(t._s(t.strings.viewPost))]),t._v(" | ")]),i("span",{staticClass:"edit"},[i("a",{attrs:{href:e.context.editLink,target:"_blank"}},[t._v(t._s(t.strings.editPost))])])])]}},{key:"phrases",fn:function(s){var e=s.row,n=s.index;return[i("link-assistant-editable-phrase",{attrs:{row:e,rowIndex:n,activeRow:t.activePost,domainsReport:""},on:{delete:t.deleteLink,toggleShowMorePhrases:t.toggleShowMorePhrases,saveModifiedPhrase:t.saveModifiedPhrase}})]}},{key:"publish_date",fn:function(s){var e=s.row;return[i("span",{staticClass:"date"},[t._v(t._s(t.$moment.utc(e.context.publishDate).tz(t.$moment.tz.guess()).format("MMMM D, YYYY")))])]}},{key:"delete",fn:function(s){var e=s.index;return[i("core-tooltip",{attrs:{type:"action"},scopedSlots:t._u([{key:"tooltip",fn:function(){return[t._v(" "+t._s(t.strings.delete)+" ")]},proxy:!0}],null,!0)},[i("svg-trash",{nativeOn:{click:function(s){return t.maybeDoBulkAction({action:"delete",selectedRows:[e]})}}})],1)]}}])}),i("link-assistant-confirmation-modal",{attrs:{strings:t.modalStrings,showModal:t.showModal,selectedRows:t.selectedRows},on:{doBulkAction:t.doBulkAction,closeModal:function(s){t.showModal=!1}}})],1)},n=[],a=i("5530"),o=(i("a9e3"),i("d3b7"),i("b64b"),i("18a5"),i("2f62")),r={props:{domain:{type:String,required:!0},rows:{type:Array,required:!0},activeDomain:{type:Number}},data:function(){return{tableKey:0,activePost:-1,wpTableLoading:!1,showModal:!1,action:"",selectedRows:null,bulkOptions:[{label:this.$t.__("Delete",this.$tdPro),value:"delete"}],strings:{delete:this.$t.__("Delete",this.$tdPro),viewPost:this.$t.__("View Post",this.$tdPro),editPost:this.$t.__("Edit Post",this.$tdPro)},modalStrings:{areYouSureSingle:this.$t.__("Are you sure you want to delete these links for this domain?",this.$tdPro),areYouSureMultiple:this.$t.__("Are you sure you want to delete these links for this domain?",this.$tdPro),areYouSureAll:this.$t.__("Are you sure you want to delete all links for this domain?",this.$tdPro),actionCannotBeUndone:this.$t.__("This action cannot be undone.",this.$tdPro),yesSingle:this.$t.__("Yes, I want to delete these links",this.$tdPro),yesMultiple:this.$t.__("Yes, I want to delete these links",this.$tdPro),yesAll:this.$t.__("Yes, I want to delete all links",this.$tdPro),noChangedMind:this.$t.__("No, I changed my mind",this.$tdPro)}}},computed:Object(a["a"])(Object(a["a"])({},Object(o["e"])(["linkAssistant"])),{},{columns:function(){return[{slug:"post_title",label:this.$t.__("Post Title",this.$tdPro)},{slug:"phrases",label:this.$t.__("Phrases with Links",this.$tdPro)},{slug:"publish_date",label:this.$t.__("Publish Date",this.$tdPro),width:"160px"},{slug:"delete",width:"50px"}]},shouldShowPagination:function(){return 1<this.rows[0].totals.pages},initialPageNumber:function(){if(void 0===this.linkAssistant.domainsReport.innerPagination||void 0===this.linkAssistant.domainsReport.innerPagination[this.domain])return 1;var t=this.linkAssistant.domainsReport.innerPagination[this.domain];return t||1}}),methods:Object(a["a"])(Object(a["a"])(Object(a["a"])({},Object(o["b"])("linkAssistant",["domainsReportInnerBulk","domainsReportInnerPaginate","domainsReportInnerLinkDelete","domainsReportInnerLinkUpdate"])),Object(o["d"])("linkAssistant",["setDomainsReportInnerPaginatedPage"])),{},{toggleShowMorePhrases:function(t){this.activePost!==t?this.activePost=t:this.activePost=-1},deleteLink:function(t){var s=this,i=t.postIndex,e=t.linkIndex;this.wpTableLoading=!0,this.domainsReportInnerLinkDelete({searchTerm:this.linkAssistant.domainsReport.tableFields.searchTerm,rows:this.rows,postIndex:i,linkIndex:e}).then((function(){s.tableKey++,s.$emit("updated")})).finally((function(){s.wpTableLoading=!1}))},saveModifiedPhrase:function(t){var s=this;if(this.linkAssistant.domainsReport.rows[this.activeDomain]){var i=Object.keys(this.linkAssistant.domainsReport.rows[this.activeDomain])[0];i&&this.linkAssistant.domainsReport.rows[this.activeDomain][i][t.postIndex]&&this.linkAssistant.domainsReport.rows[this.activeDomain][i][t.postIndex].links[t.phraseIndex]&&this.linkAssistant.domainsReport.rows[this.activeDomain][i][t.postIndex].links[t.phraseIndex].phrase_html!==t.phraseHtml&&(this.linkAssistant.domainsReport.rows[this.activeDomain][i][t.postIndex].links[t.phraseIndex].phrase=t.phrase,this.linkAssistant.domainsReport.rows[this.activeDomain][i][t.postIndex].links[t.phraseIndex].phrase_html=t.phraseHtml,this.linkAssistant.domainsReport.rows[this.activeDomain][i][t.postIndex].links[t.phraseIndex].anchor=t.anchor,this.wpTableLoading=!0,this.domainsReportInnerLinkUpdate({domainIndex:this.activeDomain,domain:i,link:this.linkAssistant.domainsReport.rows[this.activeDomain][i][t.postIndex].links[t.phraseIndex]}).then((function(){s.tableKey++,s.$emit("updated")})).finally((function(){s.wpTableLoading=!1})))}},maybeDoBulkAction:function(t){var s=t.action,i=t.selectedRows;s&&i.length&&(this.action=s,this.selectedRows=i,this.showModal=!0)},doBulkAction:function(){var t=this;if(this.showModal=!1,"delete"===this.action)return this.wpTableLoading=!0,this.domainsReportInnerBulk({searchTerm:this.linkAssistant.domainsReport.tableFields.searchTerm,action:this.action,domainIndex:this.activeDomain,linkIndexes:this.selectedRows}).then((function(){t.tableKey++,t.$emit("updated")})).finally((function(){t.wpTableLoading=!1}))},processPagination:function(t){var s=this;this.setDomainsReportInnerPaginatedPage({domain:this.domain,page:t}),this.wpTableLoading=!0,this.domainsReportInnerPaginate({domainIndex:this.activeDomain,domain:this.domain,page:t}).then((function(){s.$emit("updated")})).finally((function(){s.wpTableLoading=!1}))}})},l=r,d=(i("35dd"),i("2877")),c=Object(d["a"])(l,e,n,!1,null,null,null);s["default"]=c.exports},c327:function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",[i("blur"),i("cta",{attrs:{"cta-link":t.$aioseo.urls.aio.featureManager+"&aioseo-activate=aioseo-link-assistant","cta-button-action":"","cta-button-loading":t.activationLoading,"same-tab":"","button-text":t.strings.ctaButtonText,"learn-more-link":t.$links.getDocUrl("link-assistant"),"feature-list":[t.strings.linkOpportunities,t.strings.domainReports,t.strings.orphanedPosts,t.strings.affiliateLinks]},on:{"cta-button-click":t.upgradeAddon},scopedSlots:t._u([{key:"header-text",fn:function(){return[t._v(" "+t._s(t.strings.ctaHeader)+" ")]},proxy:!0},{key:"description",fn:function(){return[i("core-alert",{attrs:{type:"yellow"}},[t._v(" "+t._s(t.strings.updateRequired)+" ")]),t.failed?i("core-alert",{attrs:{type:"red"}},[t._v(" "+t._s(t.strings.activateError)+" ")]):t._e(),t._v(" "+t._s(t.strings.ctaDescription)+" ")]},proxy:!0},{key:"learn-more-text",fn:function(){return[t._v(" "+t._s(t.strings.learnMoreText)+" ")]},proxy:!0}])})],1)},n=[],a=i("5530"),o=(i("d3b7"),i("3ca3"),i("ddb0"),i("2f62")),r=i("2585"),l={components:{Blur:r["default"]},data:function(){return{strings:{ctaButtonText:this.$t.__("Update Link Assistant",this.$tdPro),ctaHeader:this.$t.__("Enable Link Assistant on your Site",this.$tdPro),ctaDescription:this.$t.__("Get relevant suggestions for adding internal links to all your content as well as finding any orphaned posts that have no internal links.",this.$td),linkOpportunities:this.$t.__("Actionable Link Suggestions",this.$td),orphanedPosts:this.$t.__("See Orphaned Posts",this.$td),affiliateLinks:this.$t.__("See Affiliate Links",this.$td),domainReports:this.$t.__("Top Domain Reports",this.$td),activateError:this.$t.__("An error occurred while activating the addon. Please upload it manually or contact support for more information.",this.$td),permissionWarning:this.$t.__("You currently don't have permission to update this addon. Please ask a site administrator to update.",this.$td),updateRequired:this.$t.sprintf(this.$t.__("This addon requires an update. %1$s %2$s requires a minimum version of %3$s for the %4$s addon. You currently have %5$s installed.",this.$td),"AIOSEO","Pro",this.$addons.getAddon("aioseo-link-assistant").minimumVersion,"Link Assistant",this.$addons.getAddon("aioseo-link-assistant").installedVersion)},failed:!1,activationLoading:!1}},computed:Object(a["a"])({},Object(o["e"])("linkAssistant",["suggestionsScan"])),methods:Object(a["a"])(Object(a["a"])(Object(a["a"])(Object(a["a"])({},Object(o["b"])("linkAssistant",["getMenuData","pollSuggestionsScan"])),Object(o["b"])(["upgradePlugins"])),Object(o["d"])(["updateAddon"])),{},{upgradeAddon:function(){var t=this;this.failed=!1,this.activationLoading=!0;var s=this.$addons.getAddon("aioseo-link-assistant");this.upgradePlugins([{plugin:s.sku}]).then((function(i){if(i.body.failed.length)return t.activationLoading=!1,void(t.failed=!0);var e=[t.getMenuData()];Promise.all(e).then((function(){var e=i.body.completed[s.sku];t.activationLoading=!1,s.hasMinimumVersion=!0,s.isActive=!0,s.installedVersion=e.installedVersion,t.updateAddon(s),t.$isPro&&100!==t.suggestionsScan.percent&&t.pollSuggestionsScan()}))})).catch((function(){t.activationLoading=!1}))}})},d=l,c=i("2877"),u=Object(c["a"])(d,e,n,!1,null,null,null);s["default"]=u.exports},d55d:function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",[i("blur"),i("cta",{attrs:{"cta-link":t.$links.getPricingUrl("link-assistant","link-assistant-upsell","overview"),"button-text":t.strings.ctaButtonText,"learn-more-link":t.$links.getUpsellUrl("link-assistant","overview","home"),"feature-list":[t.strings.linkOpportunities,t.strings.domainReports,t.strings.orphanedPosts,t.strings.affiliateLinks]},scopedSlots:t._u([{key:"header-text",fn:function(){return[t._v(" "+t._s(t.strings.ctaHeader)+" ")]},proxy:!0},{key:"description",fn:function(){return[t.$isPro&&t.$addons.requiresUpgrade("aioseo-link-assistant")&&t.$addons.currentPlans("aioseo-link-assistant")?i("core-alert",{attrs:{type:"red"}},[t._v(" "+t._s(t.strings.thisFeatureRequires)+" "),i("strong",[t._v(t._s(t.$addons.currentPlans("aioseo-link-assistant")))])]):t._e(),t._v(" "+t._s(t.strings.linkAssistantDescription)+" ")]},proxy:!0}])})],1)},n=[],a=i("2585"),o={components:{Blur:a["default"]},data:function(){return{strings:{ctaButtonText:this.$t.sprintf(this.$t.__("Upgrade to %1$s and Unlock Link Assistant",this.$td),"Pro"),ctaHeader:this.$t.sprintf(this.$t.__("Link Assistant is only available for licensed %1$s %2$s users.",this.$td),"AIOSEO","Pro"),linkAssistantDescription:this.$t.__("Get relevant suggestions for adding internal links to all your content as well as finding any orphaned posts that have no internal links.",this.$td),thisFeatureRequires:this.$t.__("This feature requires one of the following plans:",this.$td),linkOpportunities:this.$t.__("Actionable Link Suggestions",this.$td),orphanedPosts:this.$t.__("See Orphaned Posts",this.$td),affiliateLinks:this.$t.__("See Affiliate Links",this.$td),domainReports:this.$t.__("Top Domain Reports",this.$td)}}}},r=o,l=i("2877"),d=Object(l["a"])(r,e,n,!1,null,null,null);s["default"]=d.exports},dd40:function(t,s,i){"use strict";i("729c")},ff7d:function(t,s,i){"use strict";i.r(s);var e=function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"aioseo-link-assistant-overview"},[t.shouldShowMain?i("overview"):t._e(),t.shouldShowActivate?i("activate"):t._e(),t.shouldShowUpdate?i("update"):t._e(),t.shouldShowLite?i("lite"):t._e()],1)},n=[],a=i("4a56"),o=i("d55d"),r=i("c327"),l=i("9c0e"),d={mixins:[l["a"]],components:{Activate:a["default"],Lite:o["default"],Overview:o["default"],Update:r["default"]},data:function(){return{addonSlug:"aioseo-link-assistant"}}},c=d,u=i("2877"),h=Object(u["a"])(c,e,n,!1,null,null,null);s["default"]=h.exports}}]);