import{l as y,T as b,r as d,a as t,o as x,c as V,d as k,b as r,w as n,m as g,u as a,f as C,e as E}from"./app-dxeBQ7nv.js";import{u as B}from"./useFormError-gRTHq1f6.js";const I={class:"-mx-4"},D={__name:"PartDestroyAccount",setup(N){const i=y.useToast(),e=b({password:null}),l=d(null),s=d(!1),{errorAttributes:u}=B(e),c=()=>{e.delete(route("upi.profile.destroy"),{preserveScroll:!0,onError:()=>{e.errors.password?(e.reset("password"),l.value.focus()):i.error("Что-то пошло не так.")}})};return(P,o)=>{const m=t("v-text-field"),_=t("v-col"),f=t("v-row"),v=t("v-container"),w=t("v-btn");return x(),V("form",{onSubmit:E(c,["prevent"])},[k("div",I,[r(v,null,{default:n(()=>[r(f,null,{default:n(()=>[r(_,{cols:"12",md:"6"},{default:n(()=>[r(m,g({ref_key:"passwordInput",ref:l,label:"Current Password",variant:"outlined",modelValue:a(e).password,"onUpdate:modelValue":o[0]||(o[0]=p=>a(e).password=p),required:"",autocomplete:"current_password",density:"comfortable",color:"primary","append-inner-icon":s.value?"mdi-eye-off":"mdi-eye",type:s.value?"text":"password","prepend-inner-icon":"mdi-lock-outline","onClick:appendInner":o[1]||(o[1]=p=>s.value=!s.value)},a(u)("password")),null,16,["modelValue","append-inner-icon","type"])]),_:1})]),_:1})]),_:1})]),r(w,{class:"mt-2 text-none",variant:"tonal",type:"submit",color:"red",loading:a(e).processing},{default:n(()=>o[2]||(o[2]=[C(" Destroy ")])),_:1},8,["loading"])],32)}}};export{D as default};
