(()=>{"use strict";(new class{constructor(e){this.checks={},this.userId=e}submit(e){let t=!0,r=Object.keys(this.checks);for(let e=0;e<r.length;e++){let l=this.checks[r[e]];if(!l){console.debug(`[controls] ${l} failed`),t=!1;break}}console.debug("[controls] ok?",t);let l=e.id;t?(this.clearErrors(`form-${l}`),e.submit()):this.setErrors([e],`form-${l}`,"Form has errors")}checkEmail(e){let t=/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*))@((([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(String(e.value).toLowerCase()),r=e.id;t?this.clearErrors(`email-${r}`):this.setErrors([e],`email-${r}`,"Invalid email format"),this.checks[`email-${r}`]=t}checkUnique(e){let t=e.getAttribute("x-unique");e.value.length<3||this.isUnique(t,e.value).then((r=>{console.log("isunique?",r),r?this.clearErrors(`unique-${t}`):this.setErrors([e],`unique-${t}`,"Already used"),this.checks[`unique-${t}`]=r}))}isUnique(e,t){return new Promise(((r,l)=>{const o=new Headers;o.append("Content-Type","application/json"),o.append("Accept","application/json");const s={headers:o,method:"POST",body:JSON.stringify({kind:e,value:t,user:this.userId})};fetch("coreaccountisunique",s).then((e=>e.json())).then((e=>{console.debug("data",e),r(e.isUnique)}))}))}checkEquals(e){let t=document.querySelectorAll(`[x-equal='${e}']`),r=null,l=!0;t.forEach((e=>{null!=r?""!==e.value&&e.value!=r&&(console.log("not equal",e.value,r),l=!1):r=e.value})),l?this.clearErrors(`equal-${e}`):this.setErrors(t,`equal-${e}`,"Inputs are different"),this.checks[`equal-${e}`]=l}setErrors(e,t,r){document.querySelectorAll(`[x-error='${t}']`).forEach((e=>{e.remove()})),e.forEach((e=>{let l=document.createElement("div");l.className="alert alert-danger",l.setAttribute("x-error",t),l.innerHTML=r,e.parentElement.append(l)}))}clearErrors(e){document.querySelectorAll(`[x-error='${e}']`).forEach((e=>{e.remove()}))}load(){let e=document.querySelectorAll("[x-unique]");if(e)for(let t=0;t<e.length;t++)e[t].oninput=e=>{let t=document.getElementById(e.target.id);this.checkUnique(t)};let t=document.querySelectorAll("[x-equal]");if(t)for(let e=0;e<t.length;e++)t[e].oninput=e=>{let t=document.getElementById(e.target.id);return this.checkEquals(t.getAttribute("x-equal"))};let r=document.querySelectorAll("[x-email]");if(r)for(let e=0;e<r.length;e++)r[e].onchange=e=>{let t=document.getElementById(e.target.id);return this.checkEmail(t)};let l=document.querySelectorAll("[x-form]");if(l)for(let e=0;e<l.length;e++)l[e].onsubmit=e=>{e.preventDefault();let t=document.getElementById(e.target.id);return this.submit(t)}}}).load()})();