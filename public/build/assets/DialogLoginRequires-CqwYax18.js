import{r as g,a as t,o as v,g as f,w as n,b as a,f as l}from"./app-dxeBQ7nv.js";const b={__name:"DialogLoginRequires",setup(x,{expose:i}){const e=g(!1),p=()=>{e.value=!0},c=()=>{e.value=!1};return i({open:p}),(r,o)=>{const u=t("v-spacer"),s=t("v-btn"),d=t("v-card"),m=t("v-dialog");return v(),f(m,{modelValue:e.value,"onUpdate:modelValue":o[0]||(o[0]=_=>e.value=_),"max-width":"300",opacity:.6,"onClick:outside":c},{default:n(()=>[a(d,{"prepend-icon":"mdi-information-outline",title:"Login Requires",text:"Please login or register to continue."},{actions:n(()=>[a(u),a(s,{to:r.route("register"),class:"text-none",color:"primary",variant:"text"},{default:n(()=>o[1]||(o[1]=[l(" Register ")])),_:1},8,["to"]),a(s,{to:r.route("login"),class:"text-none",color:"primary",variant:"tonal"},{default:n(()=>o[2]||(o[2]=[l(" Login ")])),_:1},8,["to"])]),_:1})]),_:1},8,["modelValue"])}}};export{b as _};
