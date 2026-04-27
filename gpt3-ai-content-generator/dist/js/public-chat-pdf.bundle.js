(()=>{(function(){"use strict";function l(t,e){let n=t&&t.messagesEl?t.messagesEl:null;return function(i,o="info",s=!0,r=4500){if(typeof window.aipkit_chatUI_showInlineNotice=="function"){window.aipkit_chatUI_showInlineNotice(i,o,{elements:t,config:e,messagesEl:n},s,r);return}o==="error"&&alert(i)}}function a(t){return String(t??"").replace(/[&<>"']/g,e=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"})[e])}function c(t){return a(t).replace(/\n/g,"<br>")}function d(t){return t==="user"?"user":t==="error"?"error":"bot"}function u(t){let e=t.title||"Chat Transcript",n=t.entries.map((i,o)=>`
            <section class="aipkit-print-message aipkit-print-message--${d(i.role)}">
                <header>
                    <span>${a(i.label||"Bot")}</span>
                    <small>#${o+1}</small>
                </header>
                <div class="aipkit-print-message-body">${c(i.text)}</div>
            </section>
        `).join("");return`<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>${a(e)}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            color: #111827;
            font: 14px/1.55 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #ffffff;
        }
        main {
            width: min(760px, calc(100% - 48px));
            margin: 36px auto;
        }
        h1 {
            margin: 0 0 6px;
            font-size: 22px;
            line-height: 1.25;
        }
        .aipkit-print-meta {
            margin: 0 0 22px;
            color: #64748b;
            font-size: 12px;
        }
        .aipkit-print-message {
            break-inside: avoid;
            margin: 0 0 18px;
            padding: 0 0 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .aipkit-print-message header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .aipkit-print-message header small {
            color: #94a3b8;
            font-weight: 600;
        }
        .aipkit-print-message--user header { color: #1d4ed8; }
        .aipkit-print-message--bot header { color: #111827; }
        .aipkit-print-message--error header { color: #b91c1c; }
        .aipkit-print-message-body {
            white-space: normal;
            overflow-wrap: anywhere;
        }
        @page { margin: 16mm; }
        @media print {
            main {
                width: auto;
                margin: 0;
            }
            .aipkit-print-message {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <main>
        <h1>${a(e)}</h1>
        <p class="aipkit-print-meta">
            ${a(t.botName||"Bot")} - ${a(t.exportedAt||"")}
        </p>
        ${n}
    </main>
    <script>
        window.addEventListener('load', function() {
            window.focus();
            setTimeout(function() { window.print(); }, 100);
        });
    <\/script>
</body>
</html>`}function p(t){t&&!t.closed&&typeof t.close=="function"&&t.close()}function m(t,e,n=null){let i=t&&t.messagesEl?t.messagesEl:null,o=l(t,e);if(!i||!e||!e.text){console.error("AIPKit Print PDF: Missing messages element or config."),p(n);return}if(typeof window.aipkit_chatUI_buildTranscriptDocument!="function"){console.error("AIPKit Print PDF: Transcript export helper not found."),p(n),o(e.text?.downloadPrepareError||"Error: Could not prepare download.","error",!0,7e3);return}let s=window.aipkit_chatUI_buildTranscriptDocument(i,e);if(!s.entries.length){console.warn("AIPKit Chat Print PDF: No transcript content found."),p(n),o(e.text.downloadEmpty||"Nothing to download.","info",!0);return}let r=n||window.open("","_blank");if(!r){o(e.text?.pdfError||"Could not open the print window. Please allow popups and try again.","error",!0,7e3);return}r.opener=null,r.document.open(),r.document.write(u(s)),r.document.close()}window.aipkit_chatUI_downloadTranscriptActionPdf=m})();})();
