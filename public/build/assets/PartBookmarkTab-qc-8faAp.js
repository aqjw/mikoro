import{h as N}from"./index-BEdA3Klw.js";import"./index-Bscdw9CF.js";import{p as S,r as o,k as T,l as C,z as $,A as q,a as _,o as a,c as m,d as r,t as z,b as d,w as f,g as h,v as F,F as I,n as L,u as M,B as j}from"./app-Dxtfsv7s.js";import{_ as A}from"./InfiniteScroll-D2XMK2I2.js";import{_ as D}from"./CardTitle-ilGdD2m7.js";import"./TitleRating-Cq9kWLOw.js";const P={key:0,class:"flex justify-between items-center mt-2"},R={class:"font-semibold"},U={class:"grid grid-cols-6 gap-4 mb-4"},W={__name:"PartBookmarkTab",props:{userId:{type:Number,required:!0},bookmark:{type:String,required:!0}},setup(y){const k=y,b=S.useToast(),p=o(null),l=o(!1),n=o("latest"),v=o(0),i=o(1),u=o([]),g=o(!0),x=T(()=>!l.value&&v.value==0);C(n,()=>{setTimeout(()=>{var e;i.value=1,(e=p.value)==null||e.reload()},200)}),$(()=>{q(()=>{var e;(e=p.value)==null||e.load()})});const B=e=>{l.value=!0;const s=route("upi.profile.bookmarks",{user:k.userId,bookmark:k.bookmark});axios.get(s,{params:{page:i.value,sorting:n.value}}).then(({data:t})=>{i.value===1&&(u.value=[]),u.value.push(...t.items),v.value=t.total,g.value=t.has_more,i.value+=1}).catch(({response:t})=>{b.error(N(t))}).finally(()=>{l.value=!1,e()})};return(e,s)=>{const t=_("v-progress-circular"),V=_("v-icon"),w=_("v-select");return a(),m("div",null,[x.value?F("",!0):(a(),m("div",P,[r("div",null,[s[1]||(s[1]=r("span",{class:"mr-2"},"Всего тайтлов в списке:",-1)),r("span",R,z(v.value),1)]),r("div",null,[d(w,{items:[{value:"latest",title:"Сначала последние"},{value:"oldest",title:"Сначала старые"}],modelValue:n.value,"onUpdate:modelValue":s[0]||(s[0]=c=>n.value=c),variant:"solo",color:"primary",density:"compact",rounded:"lg","hide-details":""},{prepend:f(()=>[l.value?(a(),h(t,{key:0,color:"primary",indeterminate:"",size:20,width:2})):(a(),h(V,{key:1,icon:"mdi-sort"}))]),_:1},8,["modelValue"])])])),d(A,{ref_key:"infiniteScroll",ref:p,items:u.value,"has-more":g.value,"on-load":B,class:"mt-4"},{default:f(()=>[r("div",U,[(a(!0),m(I,null,L(u.value,(c,E)=>(a(),m("div",{key:E,class:"hover:scale-105 duration-200"},[d(M(j),{href:e.route("title",c.slug)},{default:f(()=>[d(D,{item:c,small:""},null,8,["item"])]),_:2},1032,["href"])]))),128))])]),_:1},8,["items","has-more"])])}}};export{W as default};