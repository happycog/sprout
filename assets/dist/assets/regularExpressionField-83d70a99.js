typeof Craft.SproutRegularExpressionField>"u"&&(Craft.SproutRegularExpressionField={});Craft.SproutRegularExpressionField=Garnish.Base.extend({init:function(e,s,t){this.checkSproutRegularExpressionField(e,s,t)},checkSproutRegularExpressionField:function(e,s,t){let i="#"+e,r="."+e;setTimeout(function(){let l={fieldHandle:s,fieldContext:t,value:$(i).val()};Craft.postActionRequest("sprout-module-forms/fields/validate-regular-expression",l,function(o){o.success?$(r).addClass("fade"):$(r).removeClass("fade")},[])},500)}});
//# sourceMappingURL=regularExpressionField-83d70a99.js.map
