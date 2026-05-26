(()=>{(function(){"use strict";if(window.aipkit_openEnhancerActionsManager)return;let c=["replace","after","before"],lt="new-",u=null,E={},d={},B="",x=null,X=null,Z=null,R=!1,p=window.aipkit_escapeHtml||(t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")),q=()=>window.aipkit_post_enhancer||{},dt=()=>{E=q(),d=E.text||{}},tt=(t={})=>({id:String(t.id||""),label:String(t.label||""),prompt:String(t.prompt||""),insert_position:rt(t.insert_position)}),et=t=>Array.isArray(t)?t.map(tt).filter(o=>o.id):[],rt=t=>c.includes(String(t||""))?String(t):"replace",y=()=>et(E.actions||[]),pt=()=>{let t=parseInt(E.max_actions,10);return Number.isFinite(t)&&t>0?t:20},J=async(t,o={})=>{if(typeof window.aipkit_apiRequest!="function")throw new Error(d.api_missing||"API function is not available.");return window.aipkit_apiRequest(t,{...o,_ajax_nonce:E.nonce_manage_actions})},O=()=>({actionSelect:u?.querySelector(".aipkit_editor_assistant_action_select"),label:u?.querySelector(".aipkit_editor_assistant_label"),position:u?.querySelector(".aipkit_editor_assistant_position"),prompt:u?.querySelector(".aipkit_editor_assistant_prompt"),status:u?.querySelector(".aipkit_editor_assistant_manager_status"),errors:u?.querySelector(".aipkit_editor_assistant_errors"),add:u?.querySelector(".aipkit_editor_assistant_add"),delete:u?.querySelector(".aipkit_editor_assistant_delete"),moveUp:u?.querySelector(".aipkit_editor_assistant_move_up"),moveDown:u?.querySelector(".aipkit_editor_assistant_move_down"),save:u?.querySelector(".aipkit_editor_assistant_save")}),z=t=>{let o=q();o.actions=et(t),window.aipkit_post_enhancer=o,E=o,d=E.text||{},window.dispatchEvent(new CustomEvent("aipkit:enhancerActionsUpdated",{detail:{actions:E.actions}})),typeof Z=="function"&&Z(E.actions)},I=(t,o)=>{let{status:r}=O();r&&(r.textContent=o||"",r.className="aipkit_editor_assistant_manager_status",t&&o&&r.classList.add(`is-${t}`))},ct=()=>{let t=O();t.errors&&(t.errors.innerHTML=""),[t.label,t.prompt].forEach(o=>{o&&(o.classList.remove("aipkit_editor_assistant_field_error"),o.removeAttribute("aria-invalid"))})},w=(t,o)=>{t&&(t.classList.add("aipkit_editor_assistant_field_error"),t.setAttribute("aria-invalid","true"));let{errors:r}=O();if(!r)return;let k=document.createElement("div");k.textContent=o,r.appendChild(k)},it=()=>!!(x&&B===x.id),nt=()=>y().findIndex(t=>t.id===B),Q=()=>it()?tt(x):y().find(t=>t.id===B)||null,T=(t,o="")=>{R=t,u&&(u.classList.toggle("is-busy",t),u.querySelectorAll("button, input, select, textarea").forEach(r=>{if(r.classList.contains("aipkit_editor_assistant_modal_close")){r.disabled=!1;return}r.disabled=t})),t&&o&&I("loading",o)},_t=()=>{if(document.getElementById("aipkit_editor_assistant_manager_styles"))return;let t=document.createElement("style");t.id="aipkit_editor_assistant_manager_styles",t.textContent=`
      .aipkit_editor_assistant_manager {
        position: fixed;
        inset: 0;
        z-index: 1000030;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, 0.46);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.18s ease, visibility 0s 0.18s;
        box-sizing: border-box;
      }
      .aipkit_editor_assistant_manager.aipkit-active {
        opacity: 1;
        visibility: visible;
        transition: opacity 0.18s ease;
      }
      .aipkit_editor_assistant_manager_shell {
        width: min(640px, calc(100vw - 36px));
        max-height: calc(100vh - 36px);
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #d8e1ec;
        border-radius: 12px;
        box-shadow: 0 24px 55px rgba(15, 23, 42, 0.24);
        overflow: hidden;
        transform: scale(0.985);
        transition: transform 0.18s ease;
      }
      .aipkit_editor_assistant_manager.aipkit-active .aipkit_editor_assistant_manager_shell {
        transform: scale(1);
      }
      .aipkit_editor_assistant_manager_header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px 14px;
        background: linear-gradient(180deg, #fbfdff 0%, #f5f8fc 100%);
        border-bottom: 1px solid #d8e1ec;
      }
      .aipkit_editor_assistant_manager_title {
        margin: 0;
        color: #1f2a3a;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.35;
      }
      .aipkit_editor_assistant_manager_copy {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
      }
      .aipkit_editor_assistant_modal_close {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        padding: 0;
        border: 1px solid #d8e1ec;
        border-radius: 999px;
        background: #fff;
        color: #64748b;
        cursor: pointer;
      }
      .aipkit_editor_assistant_modal_close:hover,
      .aipkit_editor_assistant_modal_close:focus-visible {
        background: #edf3fb;
        border-color: #c9d6e8;
        color: #1f2a3a;
      }
      .aipkit_editor_assistant_form {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
      }
      .aipkit_editor_assistant_manager_body {
        display: flex;
        flex-direction: column;
        gap: 14px;
        min-height: 0;
        padding: 16px 18px;
        overflow-y: auto;
      }
      .aipkit_editor_assistant_manager select,
      .aipkit_editor_assistant_manager input[type="text"],
      .aipkit_editor_assistant_manager textarea {
        width: 100%;
        max-width: 100%;
        min-height: 34px;
        padding: 7px 10px;
        border: 1px solid #d7e2f0;
        border-radius: 8px;
        background: #f9fbff;
        color: #1f2a3a;
        font-size: 13px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        box-sizing: border-box;
      }
      .aipkit_editor_assistant_manager select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        height: 34px;
        padding: 0 34px 0 10px;
        line-height: 32px;
        background-color: #f9fbff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.25' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px 14px;
      }
      .aipkit_editor_assistant_manager select::-ms-expand {
        display: none;
      }
      .aipkit_editor_assistant_manager textarea {
        min-height: 150px;
        line-height: 1.5;
        resize: vertical;
      }
      .aipkit_editor_assistant_manager :is(select, input[type="text"], textarea):focus {
        outline: none;
        background-color: #fff;
        border-color: #4a6fa5;
        box-shadow: 0 0 0 2px rgba(74, 111, 165, 0.16);
      }
      .aipkit_editor_assistant_picker {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 160px;
        gap: 12px;
        align-items: center;
        width: 100%;
        padding-bottom: 14px;
        border-bottom: 1px solid #edf2f7;
      }
      .aipkit_editor_assistant_tool_buttons {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: center;
        gap: 4px;
        width: 100%;
      }
      .aipkit_editor_assistant_icon_btn {
        width: 100%;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid #d7e2f0;
        border-radius: 7px;
        background: #f4f7fb;
        color: #475569;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
      }
      .aipkit_editor_assistant_icon_btn:hover:not(:disabled),
      .aipkit_editor_assistant_icon_btn:focus-visible:not(:disabled) {
        background: #edf3fb;
        border-color: #c9d6e8;
        color: #1f2937;
      }
      .aipkit_editor_assistant_icon_btn:disabled {
        cursor: not-allowed;
        opacity: 0.5;
      }
      .aipkit_editor_assistant_icon_btn--primary {
        background: #2271b1;
        border-color: #2271b1;
        color: #fff;
      }
      .aipkit_editor_assistant_icon_btn--primary:hover:not(:disabled),
      .aipkit_editor_assistant_icon_btn--primary:focus-visible:not(:disabled) {
        background: #135e96;
        border-color: #135e96;
        color: #fff;
      }
      .aipkit_editor_assistant_icon_btn--danger {
        background: #fff5f5;
        border-color: #fecaca;
        color: #b91c1c;
      }
      .aipkit_editor_assistant_icon_btn--danger:hover:not(:disabled),
      .aipkit_editor_assistant_icon_btn--danger:focus-visible:not(:disabled) {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
      }
      .aipkit_editor_assistant_edit_area {
        display: flex;
        flex-direction: column;
        gap: 12px;
      }
      .aipkit_editor_assistant_editor_grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 160px;
        gap: 12px;
      }
      .aipkit_editor_assistant_field_label {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
      }
      .aipkit_editor_assistant_field_label > span {
        color: #1f2937;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
      }
      .aipkit_editor_assistant_errors {
        display: flex;
        flex-direction: column;
        gap: 4px;
        color: #b91c1c;
        font-size: 12px;
      }
      .aipkit_editor_assistant_errors:empty {
        display: none;
      }
      .aipkit_editor_assistant_field_error {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.12) !important;
      }
      .aipkit_editor_assistant_manager_status {
        flex: 1 1 auto;
        min-width: 0;
        min-height: 18px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.45;
        overflow: hidden;
        text-align: center;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      .aipkit_editor_assistant_manager_status.is-success {
        color: #15803d;
      }
      .aipkit_editor_assistant_manager_status.is-error {
        color: #b91c1c;
      }
      .aipkit_editor_assistant_manager_status.is-loading {
        color: #2563eb;
      }
      .aipkit_editor_assistant_footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 18px 16px;
        border-top: 1px solid #edf2f7;
        background: #f9fbff;
      }
      .aipkit_editor_assistant_footer_group {
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .aipkit_editor_assistant_btn {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 13px;
        border: 1px solid #d7e2f0;
        border-radius: 8px;
        background: #f4f7fb;
        color: #1f2a3a;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
      }
      .aipkit_editor_assistant_btn:hover:not(:disabled),
      .aipkit_editor_assistant_btn:focus-visible:not(:disabled) {
        background: #edf3fb;
        border-color: #c9d6e8;
      }
      .aipkit_editor_assistant_btn:disabled {
        cursor: not-allowed;
        opacity: 0.58;
      }
      .aipkit_editor_assistant_btn--primary {
        background: #2271b1;
        border-color: #2271b1;
        color: #fff;
      }
      .aipkit_editor_assistant_btn--primary:hover:not(:disabled),
      .aipkit_editor_assistant_btn--primary:focus-visible:not(:disabled) {
        background: #135e96;
        border-color: #135e96;
        color: #fff;
      }
      .aipkit_editor_assistant_btn--danger {
        background: #fff5f5;
        border-color: #fecaca;
        color: #b91c1c;
      }
      .aipkit_editor_assistant_btn--danger:hover:not(:disabled),
      .aipkit_editor_assistant_btn--danger:focus-visible:not(:disabled) {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
      }
      @media (max-width: 640px) {
        .aipkit_editor_assistant_picker,
        .aipkit_editor_assistant_editor_grid {
          grid-template-columns: 1fr;
        }
        .aipkit_editor_assistant_tool_buttons {
          grid-template-columns: repeat(4, minmax(0, 1fr));
          width: 100%;
        }
        .aipkit_editor_assistant_icon_btn {
          width: 100%;
        }
        .aipkit_editor_assistant_footer {
          align-items: stretch;
          flex-direction: column;
        }
        .aipkit_editor_assistant_manager_status {
          order: -1;
          text-align: left;
        }
        .aipkit_editor_assistant_footer_group,
        .aipkit_editor_assistant_footer_group .aipkit_editor_assistant_btn {
          width: 100%;
        }
      }
    `,document.head.appendChild(t)},N=()=>{let{actionSelect:t}=O();if(!t)return;let o=y();if(t.innerHTML="",x){let r=document.createElement("option");r.value=x.id,r.textContent=d.new_action||"New menu item",t.appendChild(r)}if(o.forEach((r,k)=>{let e=document.createElement("option");e.value=r.id,e.textContent=r.label||`${d.action_label||"Action"} ${k+1}`,t.appendChild(e)}),!t.options.length){let r=document.createElement("option");r.value="",r.textContent=d.no_actions||"No menu items yet",t.appendChild(r)}t.value=B},H=()=>{let t=O(),o=y(),r=nt(),k=!!Q(),e=it();t.moveUp&&(t.moveUp.disabled=R||e||r<=0),t.moveDown&&(t.moveDown.disabled=R||e||r<0||r>=o.length-1),t.delete&&(t.delete.disabled=R||!k||e),t.save&&(t.save.disabled=R||!k),[t.label,t.position,t.prompt].forEach(i=>{i&&(i.disabled=R||!k)})},M=()=>{let t=O(),o=Q();t.label&&(t.label.value=o?o.label:""),t.position&&(t.position.value=o?rt(o.insert_position):"replace"),t.prompt&&(t.prompt.value=o?o.prompt:""),ct(),H()},vt=t=>{let o=y(),r=x&&t===x.id;B=r?x.id:o.some(k=>k.id===t)?t:o[0]?.id||"",r||(x=null),N(),M()},It=()=>{let t=y();B=x?.id||t[0]?.id||"",N(),M()},ut=()=>{u&&(u.classList.remove("aipkit-active"),u.setAttribute("aria-hidden","true"),u.__aipkitKeydownHandler&&(document.removeEventListener("keydown",u.__aipkitKeydownHandler),u.__aipkitKeydownHandler=null),x=null,X&&typeof X.focus=="function"&&X.focus(),X=null,Z=null)},st=()=>{if(!u)return;let t=o=>{o.key==="Escape"&&ut()};u.__aipkitKeydownHandler=t,document.addEventListener("keydown",t),u.setAttribute("aria-hidden","false"),window.requestAnimationFrame(()=>{u.classList.add("aipkit-active");let{actionSelect:o}=O();o&&typeof o.focus=="function"&&o.focus()})},ft=async(t,o)=>{T(!0,d.saving_order||"Saving order...");try{let r=await J("aipkit_reorder_enhancer_actions",{order:t.map(k=>k.id)});z(r.actions||t),N(),M(),I("success",d.order_saved||"Order saved.")}catch(r){z(o),N(),M(),I("error",`${d.error||"Error"}: ${r.message}`)}finally{T(!1),H()}},gt=async t=>{if(R||it())return;let o=y(),r=nt(),k=r+t;if(r<0||k<0||k>=o.length)return;let e=o.slice(),i=o.slice(),[n]=i.splice(r,1);i.splice(k,0,n),B=n.id,z(i),N(),M(),await ft(i,e)},St=()=>{if(R)return;if(y().length>=pt()){I("error",d.max_actions_reached||"Maximum actions reached.");return}x={id:`${lt}${Date.now()}`,label:"",prompt:"",insert_position:"replace"},B=x.id,N(),M();let{label:t}=O();t&&t.focus()},mt=async()=>{if(R||it())return;let t=y(),o=nt(),r=t[o];if(r&&window.confirm(d.confirm_delete_action||"Are you sure you want to delete this action? This cannot be undone.")){T(!0,d.deleting_action||"Deleting...");try{let k=await J("aipkit_delete_enhancer_action",{id:r.id}),e=et(k.actions||t.filter(i=>i.id!==r.id));z(e),B=e[Math.min(o,e.length-1)]?.id||"",x=null,N(),M(),I("success",d.action_deleted||"Action deleted.")}catch(k){I("error",`${d.error||"Error"}: ${k.message}`)}finally{T(!1),H()}}},kt=async()=>{if(window.confirm(d.confirm_reset_actions||"Reset all actions to the default set? This will replace current customizations.")){T(!0,d.resetting_actions||"Resetting...");try{let t=await J("aipkit_reset_enhancer_actions");z(t.actions||[]),x=null,B=y()[0]?.id||"",N(),M(),I("success",d.actions_reset||"Actions reset to defaults.")}catch(t){I("error",`${d.error||"Error"}: ${t.message}`)}finally{T(!1),H()}}},Bt=async()=>{if(R)return;let t=O(),o=Q();if(!o||!t.label||!t.prompt)return;ct();let r=t.label.value.trim(),k=t.prompt.value.trim(),e=rt(t.position?t.position.value:"replace"),i=!1;if(r||(w(t.label,d.label_required||"Label is required."),i=!0),k||(w(t.prompt,d.prompt_required||"Prompt is required."),i=!0),!i){T(!0,d.saving_action||"Saving...");try{let n=await J("aipkit_save_enhancer_action",{id:o.id,label:r,prompt:k,insert_position:e}),s=tt(n.action||{id:o.id,label:r,prompt:k,insert_position:e}),a=y(),g=a.findIndex(l=>l.id===s.id);g>=0?a[g]=s:o.id.startsWith(lt)?a=[...a,s]:a=a.map(l=>l.id===o.id?s:l),x=null,B=s.id,z(a),N(),M(),I("success",d.action_saved||"Action saved.")}catch(n){w(null,`${d.error||"Error"}: ${n.message}`)}finally{T(!1),H()}}},bt=async()=>{T(!0,d.loading_actions||"Loading actions...");try{let t=await J("aipkit_get_enhancer_actions");z(t.actions||[]),y().some(o=>o.id===B)||(B=y()[0]?.id||""),x=null,N(),M(),I("","")}catch(t){N(),M(),I("error",t.message||d.loading_failed||"Failed to load actions.")}finally{T(!1),H()}},ht=()=>{if(_t(),u&&document.body.contains(u))return u;u=document.createElement("div"),u.className="aipkit_editor_assistant_manager",u.setAttribute("aria-hidden","true"),u.innerHTML=`
      <div class="aipkit_editor_assistant_manager_shell" role="dialog" aria-modal="true" aria-labelledby="aipkit_editor_assistant_manager_title" aria-describedby="aipkit_editor_assistant_manager_desc">
        <div class="aipkit_editor_assistant_manager_header">
          <div>
            <h2 class="aipkit_editor_assistant_manager_title" id="aipkit_editor_assistant_manager_title">${p(d.assistant_menu_title||d.config_modal_title||"Assistant Menu")}</h2>
            <p class="aipkit_editor_assistant_manager_copy" id="aipkit_editor_assistant_manager_desc">${p(d.assistant_menu_description||"Customize editor Assistant menu items.")}</p>
          </div>
          <button type="button" class="aipkit_editor_assistant_modal_close aipkit_editor_assistant_close" aria-label="${p(d.close||"Close")}">
            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
          </button>
        </div>
        <form class="aipkit_editor_assistant_form">
          <div class="aipkit_editor_assistant_manager_body">
            <div class="aipkit_editor_assistant_picker">
              <select class="aipkit_editor_assistant_action_select" aria-label="${p(d.assistant_menu_items||"Menu item")}"></select>
              <div class="aipkit_editor_assistant_tool_buttons">
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_move_up" aria-label="${p(d.move_up||"Move up")}">
                  <span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_move_down" aria-label="${p(d.move_down||"Move down")}">
                  <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_icon_btn--primary aipkit_editor_assistant_add" aria-label="${p(d.add_action||"Add item")}">
                  <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_icon_btn--danger aipkit_editor_assistant_delete" aria-label="${p(d.delete_action||"Delete")}">
                  <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                </button>
              </div>
            </div>
            <div class="aipkit_editor_assistant_edit_area">
              <div class="aipkit_editor_assistant_editor_grid">
                <label class="aipkit_editor_assistant_field_label">
                  <span>${p(d.action_label||"Action Label")}</span>
                  <input type="text" class="aipkit_editor_assistant_label" maxlength="50" autocomplete="off" />
                </label>
                <label class="aipkit_editor_assistant_field_label">
                  <span>${p(d.insert_position||"Position")}</span>
                  <select class="aipkit_editor_assistant_position">
                    <option value="replace">${p(d.replace_selection||"Replace selection")}</option>
                    <option value="after">${p(d.insert_after||"Insert after")}</option>
                    <option value="before">${p(d.insert_before||"Insert before")}</option>
                  </select>
                </label>
              </div>
              <label class="aipkit_editor_assistant_field_label">
                <span>${p(d.action_prompt||"Action Prompt")}</span>
                <textarea class="aipkit_editor_assistant_prompt" rows="7" placeholder="${p(d.prompt_placeholder_info||"Use %s as a placeholder for the selected text.")}"></textarea>
              </label>
              <div class="aipkit_editor_assistant_errors" aria-live="polite"></div>
            </div>
          </div>
          <div class="aipkit_editor_assistant_footer">
            <div class="aipkit_editor_assistant_footer_group">
              <button type="button" class="aipkit_editor_assistant_btn aipkit_editor_assistant_btn--danger aipkit_editor_assistant_reset">
                ${p(d.reset_actions||"Reset")}
              </button>
            </div>
            <div class="aipkit_editor_assistant_manager_status" aria-live="polite"></div>
            <div class="aipkit_editor_assistant_footer_group">
              <button type="submit" class="aipkit_editor_assistant_btn aipkit_editor_assistant_btn--primary aipkit_editor_assistant_save">
                ${p(d.save_action||d.save||"Save")}
              </button>
            </div>
          </div>
        </form>
      </div>
    `,u.addEventListener("click",o=>{if(o.target.closest(".aipkit_editor_assistant_close")){ut();return}o.target.closest(".aipkit_editor_assistant_add")?St():o.target.closest(".aipkit_editor_assistant_delete")?mt():o.target.closest(".aipkit_editor_assistant_move_up")?gt(-1):o.target.closest(".aipkit_editor_assistant_move_down")?gt(1):o.target.closest(".aipkit_editor_assistant_reset")&&kt()}),u.addEventListener("change",o=>{let r=O();o.target===r.actionSelect&&vt(r.actionSelect.value)});let t=u.querySelector(".aipkit_editor_assistant_form");return t&&t.addEventListener("submit",o=>{o.preventDefault(),Bt()}),document.body.appendChild(u),u};window.aipkit_openEnhancerActionsManager=(t={})=>{dt(),!(!E.can_manage_actions||!E.nonce_manage_actions)&&(Z=typeof t.onUpdated=="function"?t.onUpdated:null,X=document.activeElement instanceof HTMLElement?document.activeElement:null,ht(),x=null,It(),I("",""),st(),bt())}})();(function(){"use strict";let c=window.wp;if(!c||!c.richText||!c.components||!c.element||!c.i18n||!c.blockEditor||!c.blocks||!c.data||!c.hooks){console.error("AIPKit Block Editor Enhancer: One or more required WordPress script dependencies are missing.");return}let{registerFormatType:lt,insert:u,getTextContent:E,slice:d,applyFormat:B,create:x}=c.richText,{ToolbarGroup:X,ToolbarDropdownMenu:Z}=c.components,{BlockControls:R}=c.blockEditor,{__:p}=c.i18n,{createElement:q,Fragment:dt,useEffect:tt,useState:et}=c.element,{addFilter:rt}=c.hooks,y=!1;function pt(){try{return c.data.select("core/editor").getCurrentPostType()||"post"}catch{return"post"}}function J(e){return`aipkit_enhancer_recent_${e}`}function O(e){try{let i=localStorage.getItem(J(e));return i?JSON.parse(i):[]}catch{return[]}}function z(e,i){try{localStorage.setItem(J(e),JSON.stringify(i))}catch{}}function I(e){let i=pt(),n=O(i).filter(s=>s!==e);n.unshift(e),z(i,n.slice(0,5))}let ct="aipkit-assistant-notice";function w(e,i,n={}){try{c.data.dispatch("core/notices").removeNotice(ct)}catch{}c.data.dispatch("core/notices").createNotice(e,i,{id:ct,isDismissible:!0,type:"snackbar",...n})}let it=["replace","after","before"];function nt(e){return it.includes(e)?e:"replace"}function Q(e){if(!e||typeof e!="string")return"";let i=document.createElement("div");return i.innerHTML=e,i.textContent||""}function T(e,i,n,s){let a=e?.attributes?.[i];if(typeof a!="string")return"";let g=x({html:a}),l=g.text.length,_=Number.isFinite(n)?Math.max(0,Math.min(n,l)):0,m=Number.isFinite(s)?Math.max(_,Math.min(s,l)):l;return E(d(g,_,m))}function _t(e){if(!e)return"";let i=typeof c.blocks.getBlockType=="function"?c.blocks.getBlockType(e.name):null,n=[];if(i?.attributes&&e.attributes&&Object.entries(i.attributes).forEach(([s,a])=>{if((a?.source==="html"||a?.source==="rich-text")&&typeof e.attributes[s]=="string"){let g=Q(e.attributes[s]).trim();g&&n.push(g)}}),!n.length&&e.attributes&&Object.values(e.attributes).forEach(s=>{if(typeof s=="string"){let a=Q(s).trim();a&&n.push(a)}}),!n.length&&typeof c.blocks.getBlockContent=="function"){let s=Q(c.blocks.getBlockContent(e)).trim();s&&n.push(s)}return n.join(`
`)}function N(e,i,n){if(!i?.clientId||!n?.clientId)return null;if(i.clientId===n.clientId){let _=Number.isFinite(i.offset)?i.offset:0,m=Number.isFinite(n.offset)?n.offset:_;return _<=m?{start:i,end:n}:{start:n,end:i}}let s=e.getBlockRootClientId(i.clientId);if(s!==e.getBlockRootClientId(n.clientId))return{start:i,end:n};let a=e.getBlockOrder(s)||[],g=a.indexOf(i.clientId),l=a.indexOf(n.clientId);return g<=l?{start:i,end:n}:{start:n,end:i}}function H(e,i){if(!Array.isArray(i)||i.length<2)return Array.isArray(i)?i:[];let n=e.getBlockRootClientId(i[0]);if(!i.every(a=>e.getBlockRootClientId(a)===n))return i;let s=e.getBlockOrder(n)||[];return i.every(a=>s.includes(a))?i.slice().sort((a,g)=>s.indexOf(a)-s.indexOf(g)):i}function M(e,i){if(typeof e.getMultiSelectedBlockClientIds=="function"){let l=e.getMultiSelectedBlockClientIds();if(Array.isArray(l)&&l.length)return H(e,l)}if(!i?.start?.clientId||!i?.end?.clientId){if(typeof e.getSelectedBlockClientIds=="function"){let l=e.getSelectedBlockClientIds();if(Array.isArray(l)&&l.length)return H(e,l)}return[]}if(i.start.clientId===i.end.clientId){if(typeof e.getSelectedBlockClientIds=="function"){let l=e.getSelectedBlockClientIds();if(Array.isArray(l)&&l.length)return H(e,l)}return[i.start.clientId]}let n=e.getBlockRootClientId(i.start.clientId);if(n!==e.getBlockRootClientId(i.end.clientId))return[];let s=e.getBlockOrder(n)||[],a=s.indexOf(i.start.clientId),g=s.indexOf(i.end.clientId);return a<0||g<0?[]:s.slice(a,g+1)}function vt(e,i,n){if(!i.length)return"";let s=i[0],a=i[i.length-1],g=!!(n?.start?.attributeKey&&n?.end?.attributeKey&&Number.isFinite(n.start.offset)&&Number.isFinite(n.end.offset));return i.map(l=>{let _=e.getBlock(l);return _?g&&l===s&&l===a?n.start.attributeKey===n.end.attributeKey?T(_,n.start.attributeKey,n.start.offset,n.end.offset):_t(_):g&&l===s?T(_,n.start.attributeKey,n.start.offset):g&&l===a?T(_,n.end.attributeKey,0,n.end.offset):_t(_):""}).map(l=>l.trim()).filter(Boolean).join(`

`)}function It(e){let i=e?E(d(e)):"",n={selectedText:i,selectedClientIds:[],selectedClientId:null,rootClientId:void 0,selectionStart:null,selectionEnd:null,hasBlockSelection:!1,hasRichTextRange:!1,canSplitSelection:!1};try{let s=c.data.select("core/block-editor"),a=c.data.dispatch("core/block-editor"),g=typeof s.getSelectionStart=="function"?s.getSelectionStart():null,l=typeof s.getSelectionEnd=="function"?s.getSelectionEnd():null,_=N(s,g,l),m=M(s,_),A=typeof s.getSelectedBlockClientId=="function"?s.getSelectedBlockClientId():m[0]||null,S=m[0]||_?.start?.clientId||A,D=m[m.length-1]||_?.end?.clientId||A,j=!!(S&&D&&S!==D),C=!!(_?.start?.attributeKey&&_?.end?.attributeKey&&Number.isFinite(_.start.offset)&&Number.isFinite(_.end.offset)),b=j?vt(s,m,_):i;n.selectedText=b||i,n.selectedClientIds=m,n.selectedClientId=A||S||null,n.rootClientId=S?s.getBlockRootClientId(S):void 0,n.selectionStart=_?.start||null,n.selectionEnd=_?.end||null,n.hasBlockSelection=j,n.hasRichTextRange=C,n.canSplitSelection=C&&typeof a.__unstableSplitSelection=="function"}catch{}return n}function ut(){try{let e=c.data.select("core/block-editor"),i=typeof e.getSelectionStart=="function"?e.getSelectionStart():null,n=typeof e.getSelectionEnd=="function"?e.getSelectionEnd():null,s=N(e,i,n);return M(e,s)}catch{return[]}}function st(){let e=ut();return e.length>1?e:[]}function ft(e){return typeof c.blocks.rawHandler=="function"?c.blocks.rawHandler({HTML:e}):c.blocks.parse(e)}function gt(e){let i=window.aipkit_escapeHtml||(s=>String(s??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")),n=String(e||"").trim().split(/\n{2,}/).map(s=>s.trim()).filter(Boolean).map(s=>`<p>${i(s).replace(/\n/g,"<br>")}</p>`).join("");return n?ft(n):[]}function St(e){let i=[],n=null;try{n=c.data.dispatch("core/editor")}catch{}n&&typeof n.undo=="function"&&i.push({label:p("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:s=>{s&&s.preventDefault&&s.preventDefault();try{n.undo(),e.selectedClientId&&c.data.dispatch("core/block-editor").selectBlock(e.selectedClientId),w("success",p("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),w("success",p("Text updated successfully!","gpt3-ai-content-generator"),{actions:i})}function mt(e,i,n,s){if(!Array.isArray(e)||!e.length)return!1;let a=nt(n),g=a==="replace"&&i.canSplitSelection&&i.hasBlockSelection,l=i.hasBlockSelection;if(!g&&!l)return!1;try{let _=c.data.select("core/block-editor"),m=c.data.dispatch("core/block-editor"),A=i.selectedClientIds||[],S=A[0]||i.selectedClientId,D=A[A.length-1]||S,j=S?_.getBlockRootClientId(S):void 0;if(!S)return!1;if(a==="replace")if(i.canSplitSelection)typeof m.selectionChange=="function"&&i.selectionStart&&i.selectionEnd&&m.selectionChange({start:i.selectionStart,end:i.selectionEnd}),m.__unstableSplitSelection(e);else if(A.length)m.replaceBlocks(A,e);else return!1;else{let C=a==="before"?S:D,b=_.getBlockIndex(C)+(a==="after"?1:0);m.insertBlocks(e,b,j)}try{s&&I(s)}catch{}return St(i),!0}catch(_){return console.warn("AIPKit: Failed to handle block selection, falling back.",_),!1}}async function kt(e,i,n,s,a){let g=It(e),l=g.selectedText;if(!l){w("warning",p("Please select some text to process.","gpt3-ai-content-generator"));return}let _=e,m=null;try{m=c.data.select("core/block-editor").getSelectedBlockClientId()}catch{}y=!0,w("info",p("Processing...","gpt3-ai-content-generator"),{isDismissible:!1});let A=window.aipkit_post_enhancer?.nonce_process_text;if(!A){y=!1,w("error",p("An error occurred: Security token missing.","gpt3-ai-content-generator"));return}let S=n.replace("%s",l);try{let Dt=function(){let f=0;for(let h=$.length-1;h>=0&&$[h]===`
`;h--)f++;f===0?$+=`
`:f>1&&($=$.slice(0,$.length-(f-1)))},Ot=function(f){if(f.nodeType===Node.TEXT_NODE){$+=f.nodeValue;return}if(f.nodeType===Node.ELEMENT_NODE){let h=f.tagName;if(h==="BR"){$+=`
`;return}let V=$.length;for(let P of Array.from(f.childNodes))Ot(P);let K=$.length,v=Ut[h];v&&V!==K&&At.push({type:v,start:V,end:K}),(h==="LI"||zt.has(h))&&Dt();return}},D=await window.aipkit_apiRequest("aipkit_process_enhancer_text",{_ajax_nonce:A,editor_context:"block",text_to_process:l,final_prompt:S});if(!D.text)throw new Error("AI did not return any text.");let j=window.aipkit_post_enhancer?.parse_html_formats!==void 0?!!window.aipkit_post_enhancer.parse_html_formats:!0,C=D.text;if(/(^\s{0,3}#{1,6}\s)|(^\s{0,3}[-*+]\s)|(```[\s\S]*?```)|(__|\*\*)|(_|\*)/m.test(C)&&typeof window.aipkit_getMarkdownRenderer=="function")try{let f=window.aipkit_getMarkdownRenderer();f&&(C=f.render(C))}catch(f){console.warn("AIPKit: markdown render failed",f)}let Mt=document.createElement("div");if(Mt.innerHTML=C,/<\s*(h1|h2|h3|h4|h5|h6|p|ul|ol|li|blockquote|pre)\b/i.test(C))try{let f=ft(C);if(mt(f,g,s,a))return;let h=c.data.select("core/block-editor"),V=c.data.dispatch("core/block-editor"),K=h.getSelectedBlockClientId(),v=K?h.getBlockRootClientId(K):void 0,P=K!=null?h.getBlockIndex(K,v):void 0,$t=P!=null?P+1:void 0,Kt=P??void 0,xt=K?h.getBlock(K):null,Wt=xt&&xt.name==="core/freeform",yt=(h.getBlocks(v)||[]).map(L=>L.clientId),wt=nt(s),qt=L=>{let Y=!0;(()=>{let F=[];Y&&F.push({label:p("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:U=>{U&&U.preventDefault&&U.preventDefault();try{L.length&&c.data.dispatch("core/block-editor").removeBlocks(L,v);try{m&&c.data.dispatch("core/block-editor").selectBlock(m)}catch{}setTimeout(()=>i(_),0),Y=!1,w("success",p("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),w("success",p("Text updated successfully!","gpt3-ai-content-generator"),{actions:F})})()};if(Wt){if(wt==="replace"){let F=xt,U=P??0;V.replaceBlocks(K,f);let W=(h.getBlocks(v)||[]).map(G=>G.clientId).filter(G=>!yt.includes(G)),ot=!0;(()=>{let G=[];ot&&G.push({label:p("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:at=>{at&&at.preventDefault&&at.preventDefault();try{W.length&&c.data.dispatch("core/block-editor").removeBlocks(W,v),c.data.dispatch("core/block-editor").insertBlocks(F,U,v);try{let Ht=c.data.select("core/block-editor").getBlockOrder(v),Pt=Ht&&typeof U=="number"?Ht[U]:null;Pt&&c.data.dispatch("core/block-editor").selectBlock(Pt)}catch{}setTimeout(()=>i(_),0),ot=!1,w("success",p("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),w("success",p("Text updated successfully!","gpt3-ai-content-generator"),{actions:G})})();return}let L=wt==="before"?Kt:$t;V.insertBlocks(f,L,v);let Et=(h.getBlocks(v)||[]).map(F=>F.clientId).filter(F=>!yt.includes(F));qt(Et);return}if(wt==="replace"&&K){let L=xt,Y=P??0;V.replaceBlocks(K,f);let F=(h.getBlocks(v)||[]).map(W=>W.clientId).filter(W=>!yt.includes(W)),U=!0;(()=>{let W=[];U&&W.push({label:p("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:ot=>{ot&&ot.preventDefault&&ot.preventDefault();try{F.length&&c.data.dispatch("core/block-editor").removeBlocks(F,v),c.data.dispatch("core/block-editor").insertBlocks(L,Y,v);try{let G=c.data.select("core/block-editor").getBlockOrder(v),at=G&&typeof Y=="number"?G[Y]:null;at&&c.data.dispatch("core/block-editor").selectBlock(at)}catch{}setTimeout(()=>i(_),0),U=!1,w("success",p("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),w("success",p("Text updated successfully!","gpt3-ai-content-generator"),{actions:W})})();return}let Gt=wt==="before"?Kt:$t;V.insertBlocks(f,Gt,v);let Jt=(h.getBlocks(v)||[]).map(L=>L.clientId).filter(L=>!yt.includes(L));qt(Jt);try{a&&I(a)}catch{}return}catch(f){console.warn("AIPKit: Failed to insert blocks, falling back to inline text.",f)}let Ut={STRONG:"core/bold",B:"core/bold",EM:"core/italic",I:"core/italic",U:"core/text-highlight"},zt=new Set(["P","H1","H2","H3","H4","H5","H6","BLOCKQUOTE","PRE"]),$="",At=[];for(let f of Array.from(Mt.childNodes))Ot(f);if(g.hasBlockSelection){let f=gt($);if(mt(f,g,s,a))return}if(!e||typeof i!="function")throw new Error("Unable to apply Assistant output to this selection.");let Lt=e.start,Ct=u(e,$);j&&At.length&&At.forEach(f=>{try{Ct=B(Ct,{type:f.type},Lt+f.start,Lt+f.end)}catch(h){console.warn("AIPKit formatting apply failed",h)}}),i(Ct);let jt=_,Rt=!0,Vt=()=>{let f=[];Rt&&f.push({label:p("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:h=>{h&&h.preventDefault&&h.preventDefault();try{try{m&&c.data.dispatch("core/block-editor").selectBlock(m)}catch{}setTimeout(()=>i(jt),0),Rt=!1,w("success",p("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),w("success",p("Text updated successfully!","gpt3-ai-content-generator"),{actions:f})};try{a&&I(a)}catch{}Vt()}catch(D){console.error("AIPKit Block Editor Enhancer Error:",D),w("error",p("An error occurred:","gpt3-ai-content-generator")+" "+D.message)}finally{y=!1}}let Bt="aipkit/assistant-format";function bt(){let[,e]=et(0);return tt(()=>{let i=()=>{e(n=>n+1)};return window.addEventListener("aipkit:enhancerActionsUpdated",i),()=>{window.removeEventListener("aipkit:enhancerActionsUpdated",i)}},[]),e}function ht(e,i){let n=window.aipkit_post_enhancer||{},s=n.actions||[],a=n.text||{},g=!!n.can_manage_actions&&typeof window.aipkit_openEnhancerActionsManager=="function",l=pt(),_=O(l),m={};(s||[]).forEach(b=>{b&&b.id&&(m[b.id]=b)});let A=_.map(b=>m[b]).filter(Boolean),S=A.length?[{title:p("Recent","gpt3-ai-content-generator"),isDisabled:!0,className:"aipkit-menu-header"},...A.map(b=>({title:p(b.label,"gpt3-ai-content-generator"),onClick:()=>e(b)}))]:[],D=(s||[]).filter(b=>b&&b.label&&b.prompt&&!_.includes(b.id)),j=[{title:p("All Actions","gpt3-ai-content-generator"),isDisabled:!0,className:"aipkit-menu-header"},...D.map(b=>({title:p(b.label,"gpt3-ai-content-generator"),onClick:()=>e(b)}))],C=S.length?[S,j]:[j];return g&&C.push([{title:a.customize_actions||p("Customize menu...","gpt3-ai-content-generator"),onClick:()=>{window.aipkit_openEnhancerActionsManager({onUpdated:()=>i(b=>b+1)})}}]),C}function t(e){return q(dt,null,q(R,null,q(X,null,q(Z,{icon:()=>q("span",{style:{fontSize:"16px",display:"inline-block",lineHeight:"1"}},y?"\u23F3":"\u270D\uFE0F"),label:p("Assistant","gpt3-ai-content-generator"),controls:e,isDisabled:y,className:"aipkit-content-assistant-menu"}))))}lt(Bt,{title:p("Assistant","gpt3-ai-content-generator"),tagName:"span",className:"aipkit-assistant-placeholder-format",edit:({value:e,onChange:i})=>{let n=bt();if(st().length>1)return null;let s=ht(a=>kt(e,i,a.prompt,a.insert_position||null,a.id),n);return t(s)}});let o=new Set(["core/paragraph","core/heading","core/list","core/list-item","core/quote","core/pullquote","core/preformatted","core/verse","core/table","core/freeform"]);function r({clientId:e}){let i=bt(),[n,s]=et(()=>st().join("|"));tt(()=>{let l=st().join("|");s(l);let _=c.data.subscribe(()=>{let m=st().join("|");m!==l&&(l=m,s(m))});return()=>{typeof _=="function"&&_()}},[]);let a=n?n.split("|").filter(Boolean):[];if(a.length<2||a[0]!==e)return null;let g=ht(l=>kt(null,null,l.prompt,l.insert_position||null,l.id),i);return t(g)}rt("editor.BlockEdit","aipkit/multi-block-assistant-controls",e=>i=>q(dt,null,q(e,i),o.has(i.name)?q(r,{clientId:i.clientId}):null))})();})();
