(()=>{(function(){"use strict";if(window.aipkit_openEnhancerActionsManager)return;let P=["replace","after","before"],H="new-",n=null,y={},s={},f="",i=null,p=null,u=null,r=!1,o=window.aipkit_escapeHtml||(t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")),b=()=>window.aipkit_post_enhancer||{},N=()=>{y=b(),s=y.text||{}},I=(t={})=>({id:String(t.id||""),label:String(t.label||""),prompt:String(t.prompt||""),insert_position:_(t.insert_position)}),c=t=>Array.isArray(t)?t.map(I).filter(e=>e.id):[],_=t=>P.includes(String(t||""))?String(t):"replace",k=()=>c(y.actions||[]),x=()=>{let t=parseInt(y.max_actions,10);return Number.isFinite(t)&&t>0?t:20},q=async(t,e={})=>{if(typeof window.aipkit_apiRequest!="function")throw new Error(s.api_missing||"API function is not available.");return window.aipkit_apiRequest(t,{...e,_ajax_nonce:y.nonce_manage_actions})},w=()=>({actionSelect:n?.querySelector(".aipkit_editor_assistant_action_select"),label:n?.querySelector(".aipkit_editor_assistant_label"),position:n?.querySelector(".aipkit_editor_assistant_position"),prompt:n?.querySelector(".aipkit_editor_assistant_prompt"),status:n?.querySelector(".aipkit_editor_assistant_manager_status"),errors:n?.querySelector(".aipkit_editor_assistant_errors"),add:n?.querySelector(".aipkit_editor_assistant_add"),delete:n?.querySelector(".aipkit_editor_assistant_delete"),moveUp:n?.querySelector(".aipkit_editor_assistant_move_up"),moveDown:n?.querySelector(".aipkit_editor_assistant_move_down"),save:n?.querySelector(".aipkit_editor_assistant_save")}),d=t=>{let e=b();e.actions=c(t),window.aipkit_post_enhancer=e,y=e,s=y.text||{},window.dispatchEvent(new CustomEvent("aipkit:enhancerActionsUpdated",{detail:{actions:y.actions}})),typeof u=="function"&&u(y.actions)},m=(t,e)=>{let{status:a}=w();a&&(a.textContent=e||"",a.className="aipkit_editor_assistant_manager_status",t&&e&&a.classList.add(`is-${t}`))},$=()=>{let t=w();t.errors&&(t.errors.innerHTML=""),[t.label,t.prompt].forEach(e=>{e&&(e.classList.remove("aipkit_editor_assistant_field_error"),e.removeAttribute("aria-invalid"))})},v=(t,e)=>{t&&(t.classList.add("aipkit_editor_assistant_field_error"),t.setAttribute("aria-invalid","true"));let{errors:a}=w();if(!a)return;let l=document.createElement("div");l.textContent=e,a.appendChild(l)},A=()=>!!(i&&f===i.id),R=()=>k().findIndex(t=>t.id===f),h=()=>A()?I(i):k().find(t=>t.id===f)||null,S=(t,e="")=>{r=t,n&&(n.classList.toggle("is-busy",t),n.querySelectorAll("button, input, select, textarea").forEach(a=>{if(a.classList.contains("aipkit_editor_assistant_modal_close")){a.disabled=!1;return}a.disabled=t})),t&&e&&m("loading",e)},D=()=>{if(document.getElementById("aipkit_editor_assistant_manager_styles"))return;let t=document.createElement("style");t.id="aipkit_editor_assistant_manager_styles",t.textContent=`
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
    `,document.head.appendChild(t)},C=()=>{let{actionSelect:t}=w();if(!t)return;let e=k();if(t.innerHTML="",i){let a=document.createElement("option");a.value=i.id,a.textContent=s.new_action||"New menu item",t.appendChild(a)}if(e.forEach((a,l)=>{let g=document.createElement("option");g.value=a.id,g.textContent=a.label||`${s.action_label||"Action"} ${l+1}`,t.appendChild(g)}),!t.options.length){let a=document.createElement("option");a.value="",a.textContent=s.no_actions||"No menu items yet",t.appendChild(a)}t.value=f},T=()=>{let t=w(),e=k(),a=R(),l=!!h(),g=A();t.moveUp&&(t.moveUp.disabled=r||g||a<=0),t.moveDown&&(t.moveDown.disabled=r||g||a<0||a>=e.length-1),t.delete&&(t.delete.disabled=r||!l||g),t.save&&(t.save.disabled=r||!l),[t.label,t.position,t.prompt].forEach(E=>{E&&(E.disabled=r||!l)})},M=()=>{let t=w(),e=h();t.label&&(t.label.value=e?e.label:""),t.position&&(t.position.value=e?_(e.insert_position):"replace"),t.prompt&&(t.prompt.value=e?e.prompt:""),$(),T()},K=t=>{let e=k(),a=i&&t===i.id;f=a?i.id:e.some(l=>l.id===t)?t:e[0]?.id||"",a||(i=null),C(),M()},J=()=>{let t=k();f=i?.id||t[0]?.id||"",C(),M()},U=()=>{n&&(n.classList.remove("aipkit-active"),n.setAttribute("aria-hidden","true"),n.__aipkitKeydownHandler&&(document.removeEventListener("keydown",n.__aipkitKeydownHandler),n.__aipkitKeydownHandler=null),i=null,p&&typeof p.focus=="function"&&p.focus(),p=null,u=null)},Z=()=>{if(!n)return;let t=e=>{e.key==="Escape"&&U()};n.__aipkitKeydownHandler=t,document.addEventListener("keydown",t),n.setAttribute("aria-hidden","false"),window.requestAnimationFrame(()=>{n.classList.add("aipkit-active");let{actionSelect:e}=w();e&&typeof e.focus=="function"&&e.focus()})},G=async(t,e)=>{S(!0,s.saving_order||"Saving order...");try{let a=await q("aipkit_reorder_enhancer_actions",{order:t.map(l=>l.id)});d(a.actions||t),C(),M(),m("success",s.order_saved||"Order saved.")}catch(a){d(e),C(),M(),m("error",`${s.error||"Error"}: ${a.message}`)}finally{S(!1),T()}},j=async t=>{if(r||A())return;let e=k(),a=R(),l=a+t;if(a<0||l<0||l>=e.length)return;let g=e.slice(),E=e.slice(),[z]=E.splice(a,1);E.splice(l,0,z),f=z.id,d(E),C(),M(),await G(E,g)},Q=()=>{if(r)return;if(k().length>=x()){m("error",s.max_actions_reached||"Maximum actions reached.");return}i={id:`${H}${Date.now()}`,label:"",prompt:"",insert_position:"replace"},f=i.id,C(),M();let{label:t}=w();t&&t.focus()},W=async()=>{if(r||A())return;let t=k(),e=R(),a=t[e];if(a&&window.confirm(s.confirm_delete_action||"Are you sure you want to delete this action? This cannot be undone.")){S(!0,s.deleting_action||"Deleting...");try{let l=await q("aipkit_delete_enhancer_action",{id:a.id}),g=c(l.actions||t.filter(E=>E.id!==a.id));d(g),f=g[Math.min(e,g.length-1)]?.id||"",i=null,C(),M(),m("success",s.action_deleted||"Action deleted.")}catch(l){m("error",`${s.error||"Error"}: ${l.message}`)}finally{S(!1),T()}}},X=async()=>{if(window.confirm(s.confirm_reset_actions||"Reset all actions to the default set? This will replace current customizations.")){S(!0,s.resetting_actions||"Resetting...");try{let t=await q("aipkit_reset_enhancer_actions");d(t.actions||[]),i=null,f=k()[0]?.id||"",C(),M(),m("success",s.actions_reset||"Actions reset to defaults.")}catch(t){m("error",`${s.error||"Error"}: ${t.message}`)}finally{S(!1),T()}}},V=async()=>{if(r)return;let t=w(),e=h();if(!e||!t.label||!t.prompt)return;$();let a=t.label.value.trim(),l=t.prompt.value.trim(),g=_(t.position?t.position.value:"replace"),E=!1;if(a||(v(t.label,s.label_required||"Label is required."),E=!0),l||(v(t.prompt,s.prompt_required||"Prompt is required."),E=!0),!E){S(!0,s.saving_action||"Saving...");try{let z=await q("aipkit_save_enhancer_action",{id:e.id,label:a,prompt:l,insert_position:g}),B=I(z.action||{id:e.id,label:a,prompt:l,insert_position:g}),L=k(),F=L.findIndex(O=>O.id===B.id);F>=0?L[F]=B:e.id.startsWith(H)?L=[...L,B]:L=L.map(O=>O.id===e.id?B:O),i=null,f=B.id,d(L),C(),M(),m("success",s.action_saved||"Action saved.")}catch(z){v(null,`${s.error||"Error"}: ${z.message}`)}finally{S(!1),T()}}},Y=async()=>{S(!0,s.loading_actions||"Loading actions...");try{let t=await q("aipkit_get_enhancer_actions");d(t.actions||[]),k().some(e=>e.id===f)||(f=k()[0]?.id||""),i=null,C(),M(),m("","")}catch(t){C(),M(),m("error",t.message||s.loading_failed||"Failed to load actions.")}finally{S(!1),T()}},tt=()=>{if(D(),n&&document.body.contains(n))return n;n=document.createElement("div"),n.className="aipkit_editor_assistant_manager",n.setAttribute("aria-hidden","true"),n.innerHTML=`
      <div class="aipkit_editor_assistant_manager_shell" role="dialog" aria-modal="true" aria-labelledby="aipkit_editor_assistant_manager_title" aria-describedby="aipkit_editor_assistant_manager_desc">
        <div class="aipkit_editor_assistant_manager_header">
          <div>
            <h2 class="aipkit_editor_assistant_manager_title" id="aipkit_editor_assistant_manager_title">${o(s.assistant_menu_title||s.config_modal_title||"Assistant Menu")}</h2>
            <p class="aipkit_editor_assistant_manager_copy" id="aipkit_editor_assistant_manager_desc">${o(s.assistant_menu_description||"Customize editor Assistant menu items.")}</p>
          </div>
          <button type="button" class="aipkit_editor_assistant_modal_close aipkit_editor_assistant_close" aria-label="${o(s.close||"Close")}">
            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
          </button>
        </div>
        <form class="aipkit_editor_assistant_form">
          <div class="aipkit_editor_assistant_manager_body">
            <div class="aipkit_editor_assistant_picker">
              <select class="aipkit_editor_assistant_action_select" aria-label="${o(s.assistant_menu_items||"Menu item")}"></select>
              <div class="aipkit_editor_assistant_tool_buttons">
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_move_up" aria-label="${o(s.move_up||"Move up")}">
                  <span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_move_down" aria-label="${o(s.move_down||"Move down")}">
                  <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_icon_btn--primary aipkit_editor_assistant_add" aria-label="${o(s.add_action||"Add item")}">
                  <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_icon_btn--danger aipkit_editor_assistant_delete" aria-label="${o(s.delete_action||"Delete")}">
                  <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                </button>
              </div>
            </div>
            <div class="aipkit_editor_assistant_edit_area">
              <div class="aipkit_editor_assistant_editor_grid">
                <label class="aipkit_editor_assistant_field_label">
                  <span>${o(s.action_label||"Action Label")}</span>
                  <input type="text" class="aipkit_editor_assistant_label" maxlength="50" autocomplete="off" />
                </label>
                <label class="aipkit_editor_assistant_field_label">
                  <span>${o(s.insert_position||"Position")}</span>
                  <select class="aipkit_editor_assistant_position">
                    <option value="replace">${o(s.replace_selection||"Replace selection")}</option>
                    <option value="after">${o(s.insert_after||"Insert after")}</option>
                    <option value="before">${o(s.insert_before||"Insert before")}</option>
                  </select>
                </label>
              </div>
              <label class="aipkit_editor_assistant_field_label">
                <span>${o(s.action_prompt||"Action Prompt")}</span>
                <textarea class="aipkit_editor_assistant_prompt" rows="7" placeholder="${o(s.prompt_placeholder_info||"Use %s as a placeholder for the selected text.")}"></textarea>
              </label>
              <div class="aipkit_editor_assistant_errors" aria-live="polite"></div>
            </div>
          </div>
          <div class="aipkit_editor_assistant_footer">
            <div class="aipkit_editor_assistant_footer_group">
              <button type="button" class="aipkit_editor_assistant_btn aipkit_editor_assistant_btn--danger aipkit_editor_assistant_reset">
                ${o(s.reset_actions||"Reset")}
              </button>
            </div>
            <div class="aipkit_editor_assistant_manager_status" aria-live="polite"></div>
            <div class="aipkit_editor_assistant_footer_group">
              <button type="submit" class="aipkit_editor_assistant_btn aipkit_editor_assistant_btn--primary aipkit_editor_assistant_save">
                ${o(s.save_action||s.save||"Save")}
              </button>
            </div>
          </div>
        </form>
      </div>
    `,n.addEventListener("click",e=>{if(e.target.closest(".aipkit_editor_assistant_close")){U();return}e.target.closest(".aipkit_editor_assistant_add")?Q():e.target.closest(".aipkit_editor_assistant_delete")?W():e.target.closest(".aipkit_editor_assistant_move_up")?j(-1):e.target.closest(".aipkit_editor_assistant_move_down")?j(1):e.target.closest(".aipkit_editor_assistant_reset")&&X()}),n.addEventListener("change",e=>{let a=w();e.target===a.actionSelect&&K(a.actionSelect.value)});let t=n.querySelector(".aipkit_editor_assistant_form");return t&&t.addEventListener("submit",e=>{e.preventDefault(),V()}),document.body.appendChild(n),n};window.aipkit_openEnhancerActionsManager=(t={})=>{N(),!(!y.can_manage_actions||!y.nonce_manage_actions)&&(u=typeof t.onUpdated=="function"?t.onUpdated:null,p=document.activeElement instanceof HTMLElement?document.activeElement:null,tt(),i=null,J(),m("",""),Z(),Y())}})();(function(){"use strict";let P=new Map;function H(i,p,u,r){let o=i.selection.getContent({format:"text"});if(!o){i.windowManager.alert("Please select some text to process.");return}i.setProgressState(!0);try{let c=document.querySelector(".aipkit-tinymce-assistant-button button");c&&(c.disabled=!0,c.dataset._origText=c.textContent,c.textContent="\u23F3 Assistant")}catch{}let b=window.aipkit_post_enhancer?.nonce_process_text;if(!b){console.error("AIPKit Enhancer: Nonce not found for text processing."),i.setProgressState(!1),i.windowManager.alert("An error occurred: Security token missing.");return}let N=p.replace("%s",o),I={_ajax_nonce:b,editor_context:"classic",text_to_process:o,final_prompt:N};if(typeof window.aipkit_apiRequest!="function"){console.error("AIPKit Enhancer: aipkit_apiRequest function not available."),i.setProgressState(!1),i.windowManager.alert("An error occurred: API function missing.");return}window.aipkit_apiRequest("aipkit_process_enhancer_text",I).then(c=>{if(!c.text)throw new Error("AI did not return any text.");let _=window.aipkit_post_enhancer||{},k=_.parse_html_formats!==void 0?!!_.parse_html_formats:!0,x=c.text;if(/(^\s{0,3}#{1,6}\s)|(^\s{0,3}[-*+]\s)|(```[\s\S]*?```)|(__|\*\*)|(_|\*)/m.test(x)&&typeof window.aipkit_getMarkdownRenderer=="function")try{let d=window.aipkit_getMarkdownRenderer();d&&(x=d.render(x))}catch(d){console.warn("AIPKit: markdown render failed",d)}if(k){let d=document.createElement("div");d.innerHTML=x;let m=new Set(["STRONG","B","EM","I","U","A","P","BR","H1","H2","H3","H4","H5","H6","UL","OL","LI","BLOCKQUOTE","PRE","CODE"]),$=v=>{if(!v||typeof v!="string")return!1;let A=v.trim();return/^(https?:|mailto:|tel:|#|\/)/i.test(A)||!/^[a-zA-Z][a-zA-Z0-9+.-]*:/.test(A)};(function v(A){let R=Array.from(A.childNodes);for(let h of R)if(h.nodeType===Node.ELEMENT_NODE){let S=h.tagName;if(!m.has(S)){for(v(h);h.firstChild;)A.insertBefore(h.firstChild,h);A.removeChild(h);continue}for(let D of Array.from(h.attributes))S==="A"&&D.name.toLowerCase()==="href"&&$(h.getAttribute("href"))||h.removeAttribute(D.name);v(h)}})(d),x=d.innerHTML}else x=x.replace(/<[^>]*>/g,""),x=i.dom.encode(x);let w=u&&["replace","after","before"].includes(u)?u:"replace";if(w==="before"){try{i.selection.collapse(!0)}catch{}i.insertContent(x)}else if(w==="after"){try{i.selection.collapse(!1)}catch{}i.insertContent(x)}else i.selection.setContent(x);try{let d=document.getElementById("post_type"),m=d&&d.value?d.value:(document.body.className.match(/post-type-([\w-]+)/)||[])[1]||"post";if(r&&window.localStorage){let $=`aipkit_enhancer_recent_${m}`,v=JSON.parse(localStorage.getItem($)||"[]").filter(A=>A!==r);v.unshift(r),localStorage.setItem($,JSON.stringify(v.slice(0,5)))}}catch{}}).catch(c=>{console.error("AIPKit Enhancer: Error during AI processing action.",c),i.windowManager.alert(`An error occurred: ${c.message||"Request failed."}`)}).finally(()=>{i.setProgressState(!1);try{let c=document.querySelector(".aipkit-tinymce-assistant-button button");c&&(c.disabled=!1,c.textContent=c.dataset._origText||"\u270D\uFE0F Assistant")}catch{}})}function n(){try{let i=document.getElementById("post_type");return i&&i.value?i.value:(document.body.className.match(/post-type-([\w-]+)/)||[])[1]||"post"}catch{return"post"}}function y(i,p){return((window.aipkit_post_enhancer||{}).text||{})[i]||p}function s(i){let p=window.aipkit_post_enhancer||{},u=Array.isArray(p.actions)?p.actions:[],r=!!p.can_manage_actions&&typeof window.aipkit_openEnhancerActionsManager=="function",o=[];o.push({text:"\u270D\uFE0F ASSISTANT",classes:"aipkit-tinymce-menu-header",disabled:!0}),u.length>0&&o.push({text:"-"});try{let b=n(),N=JSON.parse(localStorage.getItem(`aipkit_enhancer_recent_${b}`)||"[]"),I={};(u||[]).forEach(_=>{_&&_.id&&(I[_.id]=_)});let c=N.map(_=>I[_]).filter(Boolean);c.length&&(o.push({text:"Recent",classes:"aipkit-tinymce-menu-header",disabled:!0}),c.forEach(_=>{_.label&&_.prompt&&o.push({text:_.label,classes:"aipkit-tinymce-menu-item",onclick:function(){H(i,_.prompt,_.insert_position||null,_.id)}})}),o.push({text:"-"}))}catch{}return u.forEach(b=>{b.label&&b.prompt&&o.push({text:b.label,classes:"aipkit-tinymce-menu-item",onclick:function(){H(i,b.prompt,b.insert_position||null,b.id)}})}),r&&(o.push({text:"-"}),o.push({text:y("customize_actions","Customize menu..."),classes:"aipkit-tinymce-menu-item aipkit-tinymce-menu-configure",onclick:function(){window.aipkit_openEnhancerActionsManager({onUpdated:f})}})),o}function f(){P.forEach((i,p)=>{let u=s(p);i.splice(0,i.length,...u);try{let r=p.controlManager?.buttons?.aipkit_assistant_button||p.controlManager?.get?.("aipkit_assistant_button");r?.settings&&(r.settings.menu=i),r?.state&&typeof r.state.set=="function"&&r.state.set("menu",i),r?.menu&&typeof r.menu.remove=="function"&&(r.menu.remove(),r.menu=null)}catch{}})}window.addEventListener("aipkit:enhancerActionsUpdated",f),tinymce.PluginManager.add("aipkit_assistant",function(i,p){let u=s(i);P.set(i,u),i.on("remove",function(){P.delete(i)}),i.addButton("aipkit_assistant_button",{text:"\u270D\uFE0F Assistant",icon:!1,type:"menubutton",classes:"aipkit-tinymce-assistant-button",menu:u})})})();})();
