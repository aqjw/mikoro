import{h as N}from"./index-BEdA3Klw.js";import"./index-Bscdw9CF.js";import{l as S,r as o,n as T,j as q,y as C,z as $,a as _,o as a,c as m,d as r,t as j,b as d,w as f,g as h,q as z,F,k as I,u as L,A as M}from"./app-BtiNWB6H.js";import{_ as A}from"./InfiniteScroll-DhUsxcnK.js";import{_ as D}from"./CardTitle-wRnwXS3-.js";import"./TitleRating-DgB4xwie.js";const P={key:0,class:"flex justify-between items-center mt-2"},R={class:"font-semibold"},U={class:"grid grid-cols-6 gap-4 mb-4"},W={__name:"PartBookmarkTab",props:{userId:{type:Number,required:!0},bookmark:{type:String,required:!0}},setup(y){const k=y,b=S.useToast(),p=o(null),l=o(!1),n=o("latest"),v=o(0),i=o(1),u=o([]),g=o(!0),x=T(()=>!l.value&&v.value==0);q(n,()=>{setTimeout(()=>{var e;i.value=1,(e=p.value)==null||e.reload()},200)}),C(()=>{$(()=>{var e;(e=p.value)==null||e.load()})});const V=e=>{l.value=!0;const s=route("upi.profile.bookmarks",{user:k.userId,bookmark:k.bookmark});axios.get(s,{params:{page:i.value,sorting:n.value}}).then(({data:t})=>{i.value===1&&(u.value=[]),u.value.push(...t.items),v.value=t.total,g.value=t.has_more,i.value+=1}).catch(({response:t})=>{b.error(N(t))}).finally(()=>{l.value=!1,e()})};return(e,s)=>{const t=_("v-progress-circular"),w=_("v-icon"),B=_("v-select");return a(),m("div",null,[x.value?z("",!0):(a(),m("div",P,[r("div",null,[s[1]||(s[1]=r("span",{class:"mr-2"},"Всего тайтлов в списке:",-1)),r("span",R,j(v.value),1)]),r("div",null,[d(B,{items:[{value:"latest",title:"Сначала последние"},{value:"oldest",title:"Сначала старые"}],modelValue:n.value,"onUpdate:modelValue":s[0]||(s[0]=c=>n.value=c),variant:"solo",color:"primary",density:"compact",rounded:"lg","hide-details":""},{prepend:f(()=>[l.value?(a(),h(t,{key:0,color:"primary",indeterminate:"",size:20,width:2})):(a(),h(w,{key:1,icon:"mdi-sort"}))]),_:1},8,["modelValue"])])])),d(A,{ref_key:"infiniteScroll",ref:p,items:u.value,"has-more":g.value,"on-load":V,class:"mt-4"},{default:f(()=>[r("div",U,[(a(!0),m(F,null,I(u.value,(c,E)=>(a(),m("div",{key:E,class:"hover:scale-105 duration-200"},[d(L(M),{href:e.route("title",c.slug)},{default:f(()=>[d(D,{item:c,small:""},null,8,["item"])]),_:2},1032,["href"])]))),128))])]),_:1},8,["items","has-more"])])}}};export{W as default};