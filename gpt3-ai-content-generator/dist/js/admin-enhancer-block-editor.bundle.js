(()=>{(function(){"use strict";if(window.aipkit_openEnhancerActionsManager)return;let n=["replace","after","before"],nt="new-",s=null,E={},a={},v="",m=null,j=null,J=null,r=!1,f=window.aipkit_escapeHtml||(t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")),ot=()=>window.aipkit_post_enhancer||{},ft=()=>{E=ot(),a=E.text||{}},it=(t={})=>({id:String(t.id||""),label:String(t.label||""),prompt:String(t.prompt||""),insert_position:W(t.insert_position)}),U=t=>Array.isArray(t)?t.map(it).filter(e=>e.id):[],W=t=>n.includes(String(t||""))?String(t):"replace",w=()=>U(E.actions||[]),rt=()=>{let t=parseInt(E.max_actions,10);return Number.isFinite(t)&&t>0?t:20},V=async(t,e={})=>{if(typeof window.aipkit_apiRequest!="function")throw new Error(a.api_missing||"API function is not available.");return window.aipkit_apiRequest(t,{...e,_ajax_nonce:E.nonce_manage_actions})},I=()=>({actionSelect:s?.querySelector(".aipkit_editor_assistant_action_select"),label:s?.querySelector(".aipkit_editor_assistant_label"),position:s?.querySelector(".aipkit_editor_assistant_position"),prompt:s?.querySelector(".aipkit_editor_assistant_prompt"),status:s?.querySelector(".aipkit_editor_assistant_manager_status"),errors:s?.querySelector(".aipkit_editor_assistant_errors"),add:s?.querySelector(".aipkit_editor_assistant_add"),delete:s?.querySelector(".aipkit_editor_assistant_delete"),moveUp:s?.querySelector(".aipkit_editor_assistant_move_up"),moveDown:s?.querySelector(".aipkit_editor_assistant_move_down"),save:s?.querySelector(".aipkit_editor_assistant_save")}),$=t=>{let e=ot();e.actions=U(t),window.aipkit_post_enhancer=e,E=e,a=E.text||{},window.dispatchEvent(new CustomEvent("aipkit:enhancerActionsUpdated",{detail:{actions:E.actions}})),typeof J=="function"&&J(E.actions)},p=(t,e)=>{let{status:i}=I();i&&(i.textContent=e||"",i.className="aipkit_editor_assistant_manager_status",t&&e&&i.classList.add(`is-${t}`))},at=()=>{let t=I();t.errors&&(t.errors.innerHTML=""),[t.label,t.prompt].forEach(e=>{e&&(e.classList.remove("aipkit_editor_assistant_field_error"),e.removeAttribute("aria-invalid"))})},ct=(t,e)=>{t&&(t.classList.add("aipkit_editor_assistant_field_error"),t.setAttribute("aria-invalid","true"));let{errors:i}=I();if(!i)return;let d=document.createElement("div");d.textContent=e,i.appendChild(d)},_=()=>!!(m&&v===m.id),u=()=>w().findIndex(t=>t.id===v),C=()=>_()?it(m):w().find(t=>t.id===v)||null,b=(t,e="")=>{r=t,s&&(s.classList.toggle("is-busy",t),s.querySelectorAll("button, input, select, textarea").forEach(i=>{if(i.classList.contains("aipkit_editor_assistant_modal_close")){i.disabled=!1;return}i.disabled=t})),t&&e&&p("loading",e)},D=()=>{if(document.getElementById("aipkit_editor_assistant_manager_styles"))return;let t=document.createElement("style");t.id="aipkit_editor_assistant_manager_styles",t.textContent=`
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
    `,document.head.appendChild(t)},h=()=>{let{actionSelect:t}=I();if(!t)return;let e=w();if(t.innerHTML="",m){let i=document.createElement("option");i.value=m.id,i.textContent=a.new_action||"New menu item",t.appendChild(i)}if(e.forEach((i,d)=>{let k=document.createElement("option");k.value=i.id,k.textContent=i.label||`${a.action_label||"Action"} ${d+1}`,t.appendChild(k)}),!t.options.length){let i=document.createElement("option");i.value="",i.textContent=a.no_actions||"No menu items yet",t.appendChild(i)}t.value=v},S=()=>{let t=I(),e=w(),i=u(),d=!!C(),k=_();t.moveUp&&(t.moveUp.disabled=r||k||i<=0),t.moveDown&&(t.moveDown.disabled=r||k||i<0||i>=e.length-1),t.delete&&(t.delete.disabled=r||!d||k),t.save&&(t.save.disabled=r||!d),[t.label,t.position,t.prompt].forEach(y=>{y&&(y.disabled=r||!d)})},g=()=>{let t=I(),e=C();t.label&&(t.label.value=e?e.label:""),t.position&&(t.position.value=e?W(e.insert_position):"replace"),t.prompt&&(t.prompt.value=e?e.prompt:""),at(),S()},X=t=>{let e=w(),i=m&&t===m.id;v=i?m.id:e.some(d=>d.id===t)?t:e[0]?.id||"",i||(m=null),h(),g()},Y=()=>{let t=w();v=m?.id||t[0]?.id||"",h(),g()},O=()=>{s&&(s.classList.remove("aipkit-active"),s.setAttribute("aria-hidden","true"),s.__aipkitKeydownHandler&&(document.removeEventListener("keydown",s.__aipkitKeydownHandler),s.__aipkitKeydownHandler=null),m=null,j&&typeof j.focus=="function"&&j.focus(),j=null,J=null)},Q=()=>{if(!s)return;let t=e=>{e.key==="Escape"&&O()};s.__aipkitKeydownHandler=t,document.addEventListener("keydown",t),s.setAttribute("aria-hidden","false"),window.requestAnimationFrame(()=>{s.classList.add("aipkit-active");let{actionSelect:e}=I();e&&typeof e.focus=="function"&&e.focus()})},A=async(t,e)=>{b(!0,a.saving_order||"Saving order...");try{let i=await V("aipkit_reorder_enhancer_actions",{order:t.map(d=>d.id)});$(i.actions||t),h(),g(),p("success",a.order_saved||"Order saved.")}catch(i){$(e),h(),g(),p("error",`${a.error||"Error"}: ${i.message}`)}finally{b(!1),S()}},Z=async t=>{if(r||_())return;let e=w(),i=u(),d=i+t;if(i<0||d<0||d>=e.length)return;let k=e.slice(),y=e.slice(),[F]=y.splice(i,1);y.splice(d,0,F),v=F.id,$(y),h(),g(),await A(y,k)},T=()=>{if(r)return;if(w().length>=rt()){p("error",a.max_actions_reached||"Maximum actions reached.");return}m={id:`${nt}${Date.now()}`,label:"",prompt:"",insert_position:"replace"},v=m.id,h(),g();let{label:t}=I();t&&t.focus()},st=async()=>{if(r||_())return;let t=w(),e=u(),i=t[e];if(i&&window.confirm(a.confirm_delete_action||"Are you sure you want to delete this action? This cannot be undone.")){b(!0,a.deleting_action||"Deleting...");try{let d=await V("aipkit_delete_enhancer_action",{id:i.id}),k=U(d.actions||t.filter(y=>y.id!==i.id));$(k),v=k[Math.min(e,k.length-1)]?.id||"",m=null,h(),g(),p("success",a.action_deleted||"Action deleted.")}catch(d){p("error",`${a.error||"Error"}: ${d.message}`)}finally{b(!1),S()}}},c=async()=>{if(window.confirm(a.confirm_reset_actions||"Reset all actions to the default set? This will replace current customizations.")){b(!0,a.resetting_actions||"Resetting...");try{let t=await V("aipkit_reset_enhancer_actions");$(t.actions||[]),m=null,v=w()[0]?.id||"",h(),g(),p("success",a.actions_reset||"Actions reset to defaults.")}catch(t){p("error",`${a.error||"Error"}: ${t.message}`)}finally{b(!1),S()}}},lt=async()=>{if(r)return;let t=I(),e=C();if(!e||!t.label||!t.prompt)return;at();let i=t.label.value.trim(),d=t.prompt.value.trim(),k=W(t.position?t.position.value:"replace"),y=!1;if(i||(ct(t.label,a.label_required||"Label is required."),y=!0),d||(ct(t.prompt,a.prompt_required||"Prompt is required."),y=!0),!y){b(!0,a.saving_action||"Saving...");try{let F=await V("aipkit_save_enhancer_action",{id:e.id,label:i,prompt:d,insert_position:k}),K=it(F.action||{id:e.id,label:i,prompt:d,insert_position:k}),q=w(),o=q.findIndex(l=>l.id===K.id);o>=0?q[o]=K:e.id.startsWith(nt)?q=[...q,K]:q=q.map(l=>l.id===e.id?K:l),m=null,v=K.id,$(q),h(),g(),p("success",a.action_saved||"Action saved.")}catch(F){ct(null,`${a.error||"Error"}: ${F.message}`)}finally{b(!1),S()}}},ut=async()=>{b(!0,a.loading_actions||"Loading actions...");try{let t=await V("aipkit_get_enhancer_actions");$(t.actions||[]),w().some(e=>e.id===v)||(v=w()[0]?.id||""),m=null,h(),g(),p("","")}catch(t){h(),g(),p("error",t.message||a.loading_failed||"Failed to load actions.")}finally{b(!1),S()}},gt=()=>{if(D(),s&&document.body.contains(s))return s;s=document.createElement("div"),s.className="aipkit_editor_assistant_manager",s.setAttribute("aria-hidden","true"),s.innerHTML=`
      <div class="aipkit_editor_assistant_manager_shell" role="dialog" aria-modal="true" aria-labelledby="aipkit_editor_assistant_manager_title" aria-describedby="aipkit_editor_assistant_manager_desc">
        <div class="aipkit_editor_assistant_manager_header">
          <div>
            <h2 class="aipkit_editor_assistant_manager_title" id="aipkit_editor_assistant_manager_title">${f(a.assistant_menu_title||a.config_modal_title||"Assistant Menu")}</h2>
            <p class="aipkit_editor_assistant_manager_copy" id="aipkit_editor_assistant_manager_desc">${f(a.assistant_menu_description||"Customize editor Assistant menu items.")}</p>
          </div>
          <button type="button" class="aipkit_editor_assistant_modal_close aipkit_editor_assistant_close" aria-label="${f(a.close||"Close")}">
            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
          </button>
        </div>
        <form class="aipkit_editor_assistant_form">
          <div class="aipkit_editor_assistant_manager_body">
            <div class="aipkit_editor_assistant_picker">
              <select class="aipkit_editor_assistant_action_select" aria-label="${f(a.assistant_menu_items||"Menu item")}"></select>
              <div class="aipkit_editor_assistant_tool_buttons">
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_move_up" aria-label="${f(a.move_up||"Move up")}">
                  <span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_move_down" aria-label="${f(a.move_down||"Move down")}">
                  <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_icon_btn--primary aipkit_editor_assistant_add" aria-label="${f(a.add_action||"Add item")}">
                  <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                </button>
                <button type="button" class="aipkit_editor_assistant_icon_btn aipkit_editor_assistant_icon_btn--danger aipkit_editor_assistant_delete" aria-label="${f(a.delete_action||"Delete")}">
                  <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                </button>
              </div>
            </div>
            <div class="aipkit_editor_assistant_edit_area">
              <div class="aipkit_editor_assistant_editor_grid">
                <label class="aipkit_editor_assistant_field_label">
                  <span>${f(a.action_label||"Action Label")}</span>
                  <input type="text" class="aipkit_editor_assistant_label" maxlength="50" autocomplete="off" />
                </label>
                <label class="aipkit_editor_assistant_field_label">
                  <span>${f(a.insert_position||"Position")}</span>
                  <select class="aipkit_editor_assistant_position">
                    <option value="replace">${f(a.replace_selection||"Replace selection")}</option>
                    <option value="after">${f(a.insert_after||"Insert after")}</option>
                    <option value="before">${f(a.insert_before||"Insert before")}</option>
                  </select>
                </label>
              </div>
              <label class="aipkit_editor_assistant_field_label">
                <span>${f(a.action_prompt||"Action Prompt")}</span>
                <textarea class="aipkit_editor_assistant_prompt" rows="7" placeholder="${f(a.prompt_placeholder_info||"Use %s as a placeholder for the selected text.")}"></textarea>
              </label>
              <div class="aipkit_editor_assistant_errors" aria-live="polite"></div>
            </div>
          </div>
          <div class="aipkit_editor_assistant_footer">
            <div class="aipkit_editor_assistant_footer_group">
              <button type="button" class="aipkit_editor_assistant_btn aipkit_editor_assistant_btn--danger aipkit_editor_assistant_reset">
                ${f(a.reset_actions||"Reset")}
              </button>
            </div>
            <div class="aipkit_editor_assistant_manager_status" aria-live="polite"></div>
            <div class="aipkit_editor_assistant_footer_group">
              <button type="submit" class="aipkit_editor_assistant_btn aipkit_editor_assistant_btn--primary aipkit_editor_assistant_save">
                ${f(a.save_action||a.save||"Save")}
              </button>
            </div>
          </div>
        </form>
      </div>
    `,s.addEventListener("click",e=>{if(e.target.closest(".aipkit_editor_assistant_close")){O();return}e.target.closest(".aipkit_editor_assistant_add")?T():e.target.closest(".aipkit_editor_assistant_delete")?st():e.target.closest(".aipkit_editor_assistant_move_up")?Z(-1):e.target.closest(".aipkit_editor_assistant_move_down")?Z(1):e.target.closest(".aipkit_editor_assistant_reset")&&c()}),s.addEventListener("change",e=>{let i=I();e.target===i.actionSelect&&X(i.actionSelect.value)});let t=s.querySelector(".aipkit_editor_assistant_form");return t&&t.addEventListener("submit",e=>{e.preventDefault(),lt()}),document.body.appendChild(s),s};window.aipkit_openEnhancerActionsManager=(t={})=>{ft(),!(!E.can_manage_actions||!E.nonce_manage_actions)&&(J=typeof t.onUpdated=="function"?t.onUpdated:null,j=document.activeElement instanceof HTMLElement?document.activeElement:null,gt(),m=null,Y(),p("",""),Q(),ut())}})();(function(){"use strict";let n=window.wp;if(!n||!n.richText||!n.components||!n.element||!n.i18n||!n.blockEditor||!n.blocks||!n.data){console.error("AIPKit Block Editor Enhancer: One or more required WordPress script dependencies are missing.");return}let{registerFormatType:nt,insert:s,getTextContent:E,slice:a,applyFormat:v}=n.richText,{ToolbarGroup:m,ToolbarDropdownMenu:j}=n.components,{BlockControls:J}=n.blockEditor,{__:r}=n.i18n,{createElement:f,Fragment:ot,useEffect:ft,useState:it}=n.element,U=!1;function W(){try{return n.data.select("core/editor").getCurrentPostType()||"post"}catch{return"post"}}function w(_){return`aipkit_enhancer_recent_${_}`}function rt(_){try{let u=localStorage.getItem(w(_));return u?JSON.parse(u):[]}catch{return[]}}function V(_,u){try{localStorage.setItem(w(_),JSON.stringify(u))}catch{}}function I(_){let u=W(),C=rt(u).filter(b=>b!==_);C.unshift(_),V(u,C.slice(0,5))}let $="aipkit-assistant-notice";function p(_,u,C={}){try{n.data.dispatch("core/notices").removeNotice($)}catch{}n.data.dispatch("core/notices").createNotice(_,u,{id:$,isDismissible:!0,type:"snackbar",...C})}async function at(_,u,C,b,D){let h=E(a(_));if(!h){p("warning",r("Please select some text to process.","gpt3-ai-content-generator"));return}let S=_,g=null,X=null,Y=null;try{let A=n.data.select("core/block-editor");g=A.getSelectedBlockClientId(),X=g?A.getBlockRootClientId(g):void 0,Y=g!=null?A.getBlockIndex(g,X):void 0}catch{}U=!0,p("info",r("Processing...","gpt3-ai-content-generator"),{isDismissible:!1});let O=window.aipkit_post_enhancer?.nonce_process_text;if(!O){p("error",r("An error occurred: Security token missing.","gpt3-ai-content-generator"));return}let Q=C.replace("%s",h);try{let i=function(){let o=0;for(let l=t.length-1;l>=0&&t[l]===`
`;l--)o++;o===0?t+=`
`:o>1&&(t=t.slice(0,t.length-(o-1)))},d=function(o){if(o.nodeType===Node.TEXT_NODE){t+=o.nodeValue;return}if(o.nodeType===Node.ELEMENT_NODE){let l=o.tagName;if(l==="BR"){t+=`
`;return}let z=t.length;for(let L of Array.from(o.childNodes))d(L);let N=t.length,x=ut[l];x&&z!==N&&e.push({type:x,start:z,end:N}),(l==="LI"||gt.has(l))&&i();return}},A=await window.aipkit_apiRequest("aipkit_process_enhancer_text",{_ajax_nonce:O,editor_context:"block",text_to_process:h,final_prompt:Q});if(!A.text)throw new Error("AI did not return any text.");let Z=window.aipkit_post_enhancer?.parse_html_formats!==void 0?!!window.aipkit_post_enhancer.parse_html_formats:!0,T=A.text;if(/(^\s{0,3}#{1,6}\s)|(^\s{0,3}[-*+]\s)|(```[\s\S]*?```)|(__|\*\*)|(_|\*)/m.test(T)&&typeof window.aipkit_getMarkdownRenderer=="function")try{let o=window.aipkit_getMarkdownRenderer();o&&(T=o.render(T))}catch(o){console.warn("AIPKit: markdown render failed",o)}let c=document.createElement("div");if(c.innerHTML=T,/<\s*(h1|h2|h3|h4|h5|h6|p|ul|ol|li|blockquote|pre)\b/i.test(T))try{let o=typeof n.blocks.rawHandler=="function"?n.blocks.rawHandler({HTML:T}):n.blocks.parse(T),l=n.data.select("core/block-editor"),z=n.data.dispatch("core/block-editor"),N=l.getSelectedBlockClientId(),x=N?l.getBlockRootClientId(N):void 0,L=N!=null?l.getBlockIndex(N,x):void 0,ht=L!=null?L+1:void 0,xt=L??void 0,dt=N?l.getBlock(N):null,Et=dt&&dt.name==="core/freeform",pt=(l.getBlocks(x)||[]).map(B=>B.clientId),_t=b&&["replace","after","before"].includes(b)?b:"replace",yt=B=>{let G=!0;(()=>{let M=[];G&&M.push({label:r("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:R=>{R&&R.preventDefault&&R.preventDefault();try{B.length&&n.data.dispatch("core/block-editor").removeBlocks(B,x);try{g&&n.data.dispatch("core/block-editor").selectBlock(g)}catch{}setTimeout(()=>u(S),0),G=!1,p("success",r("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),p("success",r("Text updated successfully!","gpt3-ai-content-generator"),{actions:M})})()};if(Et){if(_t==="replace"){let M=dt,R=L??0;z.replaceBlocks(N,o);let H=(l.getBlocks(x)||[]).map(P=>P.clientId).filter(P=>!pt.includes(P)),tt=!0;(()=>{let P=[];tt&&P.push({label:r("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:et=>{et&&et.preventDefault&&et.preventDefault();try{H.length&&n.data.dispatch("core/block-editor").removeBlocks(H,x),n.data.dispatch("core/block-editor").insertBlocks(M,R,x);try{let vt=n.data.select("core/block-editor").getBlockOrder(x),At=vt&&typeof R=="number"?vt[R]:null;At&&n.data.dispatch("core/block-editor").selectBlock(At)}catch{}setTimeout(()=>u(S),0),tt=!1,p("success",r("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),p("success",r("Text updated successfully!","gpt3-ai-content-generator"),{actions:P})})();return}let B=_t==="before"?xt:ht;z.insertBlocks(o,B,x);let mt=(l.getBlocks(x)||[]).map(M=>M.clientId).filter(M=>!pt.includes(M));yt(mt);return}if(_t==="replace"&&N){let B=dt,G=L??0;z.replaceBlocks(N,o);let M=(l.getBlocks(x)||[]).map(H=>H.clientId).filter(H=>!pt.includes(H)),R=!0;(()=>{let H=[];R&&H.push({label:r("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:tt=>{tt&&tt.preventDefault&&tt.preventDefault();try{M.length&&n.data.dispatch("core/block-editor").removeBlocks(M,x),n.data.dispatch("core/block-editor").insertBlocks(B,G,x);try{let P=n.data.select("core/block-editor").getBlockOrder(x),et=P&&typeof G=="number"?P[G]:null;et&&n.data.dispatch("core/block-editor").selectBlock(et)}catch{}setTimeout(()=>u(S),0),R=!1,p("success",r("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),p("success",r("Text updated successfully!","gpt3-ai-content-generator"),{actions:H})})();return}let It=_t==="before"?xt:ht;z.insertBlocks(o,It,x);let St=(l.getBlocks(x)||[]).map(B=>B.clientId).filter(B=>!pt.includes(B));yt(St);try{D&&I(D)}catch{}return}catch(o){console.warn("AIPKit: Failed to insert blocks, falling back to inline text.",o)}let ut={STRONG:"core/bold",B:"core/bold",EM:"core/italic",I:"core/italic",U:"core/text-highlight"},gt=new Set(["P","H1","H2","H3","H4","H5","H6","BLOCKQUOTE","PRE"]),t="",e=[];for(let o of Array.from(c.childNodes))d(o);let k=_.start,y=s(_,t);Z&&e.length&&e.forEach(o=>{try{y=v(y,{type:o.type},k+o.start,k+o.end)}catch(l){console.warn("AIPKit formatting apply failed",l)}}),u(y);let F=S,K=!0,q=()=>{let o=[];K&&o.push({label:r("Undo","gpt3-ai-content-generator"),url:"#",className:"is-link is-small",onClick:l=>{l&&l.preventDefault&&l.preventDefault();try{try{g&&n.data.dispatch("core/block-editor").selectBlock(g)}catch{}setTimeout(()=>u(F),0),K=!1,p("success",r("Changes reverted.","gpt3-ai-content-generator"))}catch{}}}),p("success",r("Text updated successfully!","gpt3-ai-content-generator"),{actions:o})};try{D&&I(D)}catch{}q()}catch(A){console.error("AIPKit Block Editor Enhancer Error:",A),p("error",r("An error occurred:","gpt3-ai-content-generator")+" "+A.message)}finally{U=!1}}nt("aipkit/assistant-format",{title:r("Assistant","gpt3-ai-content-generator"),tagName:"span",className:"aipkit-assistant-placeholder-format",edit:({value:_,onChange:u,isActive:C})=>{let[,b]=it(0);ft(()=>{let c=()=>{b(lt=>lt+1)};return window.addEventListener("aipkit:enhancerActionsUpdated",c),()=>{window.removeEventListener("aipkit:enhancerActionsUpdated",c)}},[]);let D=window.aipkit_post_enhancer||{},h=D.actions||[],S=D.text||{},g=!!D.can_manage_actions&&typeof window.aipkit_openEnhancerActionsManager=="function",X=W(),Y=rt(X),O={};(h||[]).forEach(c=>{c&&c.id&&(O[c.id]=c)});let Q=Y.map(c=>O[c]).filter(Boolean),A=Q.length?[{title:r("Recent","gpt3-ai-content-generator"),isDisabled:!0,className:"aipkit-menu-header"},...Q.map(c=>({title:r(c.label,"gpt3-ai-content-generator"),onClick:()=>at(_,u,c.prompt,c.insert_position||null,c.id)}))]:[],Z=(h||[]).filter(c=>c&&c.label&&c.prompt&&!Y.includes(c.id)),T=[{title:r("All Actions","gpt3-ai-content-generator"),isDisabled:!0,className:"aipkit-menu-header"},...Z.map(c=>({title:r(c.label,"gpt3-ai-content-generator"),onClick:()=>at(_,u,c.prompt,c.insert_position||null,c.id)}))],st=A.length?[A,T]:[T];return g&&st.push([{title:S.customize_actions||r("Customize menu...","gpt3-ai-content-generator"),onClick:()=>{window.aipkit_openEnhancerActionsManager({onUpdated:()=>b(c=>c+1)})}}]),f(ot,null,f(J,null,f(m,null,f(j,{icon:()=>f("span",{style:{fontSize:"16px",display:"inline-block",lineHeight:"1"}},U?"\u23F3":"\u270D\uFE0F"),label:r("Assistant","gpt3-ai-content-generator"),controls:st,isDisabled:U,className:"aipkit-content-assistant-menu"}))))}})})();})();
