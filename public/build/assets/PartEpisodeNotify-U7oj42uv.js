import{h as R}from"./index-BEdA3Klw.js";import{_ as z}from"./DialogLoginRequires-CqwYax18.js";import{l as S,B as U,s as j,r as s,j as F,a,o as r,c as d,b as o,q as L,d as g,w as i,f as x,F as $,k as A,g as D,C as O,t as P,z as W}from"./app-dxeBQ7nv.js";const G={key:0,class:"absolute right-2 top-2"},H={class:"flex gap-2 text-center mb-2"},M={__name:"PartEpisodeNotify",props:{title:{type:Object,required:!0}},setup(m){const l=m,v=S.useToast(),k=U(),{isLogged:y}=j(k),_=s(null),c=s(!1),e=s(l.title.episode_subscriptions),f=s(null),u=s(!1);F(e,()=>{u.value||(clearTimeout(f.value),f.value=setTimeout(h,1e3))});const b=()=>{e.value.length===l.title.translations.length?e.value=[]:e.value=l.title.translations.map(p=>p.id)},h=p=>{if(!y.value){_.value.open(),u.value=!0,e.value=[],W(()=>u.value=!1);return}c.value=!0,axios.post(route("upi.title.episode_subscription_toggle",l.title.id),{value:e.value}).then(()=>{v.success("Сохранено успешно")}).catch(({response:t})=>{v.error(R(t))}).finally(()=>{c.value=!1})};return(p,t)=>{const C=a("v-progress-circular"),V=a("v-btn"),T=a("v-chip"),w=a("v-item"),B=a("v-item-group");return r(),d("div",null,[c.value?(r(),d("div",G,[o(C,{color:"primary",indeterminate:"",size:20,width:2})])):L("",!0),g("div",H,[t[2]||(t[2]=g("div",{class:"text-caption"},"Уведомить о выходе новой серии",-1)),o(V,{variant:"outlined",color:"primary",density:"compact",class:"text-none",onClick:b},{default:i(()=>t[1]||(t[1]=[x(" Все ")])),_:1})]),o(B,{modelValue:e.value,"onUpdate:modelValue":t[0]||(t[0]=n=>e.value=n),"selected-class":"bg-primary",class:"flex flex-wrap gap-2",multiple:""},{default:i(()=>[(r(!0),d($,null,A(m.title.translations,(n,N)=>(r(),D(w,{key:N,value:n.id},{default:i(({selectedClass:q,toggle:E})=>[o(T,{class:O(q),density:"compact",label:"",onClick:E},{default:i(()=>[x(P(n.title),1)]),_:2},1032,["class","onClick"])]),_:2},1032,["value"]))),128))]),_:1},8,["modelValue"]),o(z,{ref_key:"loginRequires",ref:_},null,512)])}}};export{M as default};