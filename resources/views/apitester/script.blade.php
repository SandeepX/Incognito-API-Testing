<script>
// ========= STATE =========
let tabs = [], activeTab = null, tabCounter = 0, activeRespFormat = 'pretty', currentAbort = null;
let envs = [], colls = [], works = [];

function defaultReq() {
    return { method:'GET', url:'', params:[], headers:[], bodyType:'none', body:'', formData:[], auth:{ type:'none', bearer:'', username:'', password:'', keyName:'X-API-Key', keyValue:'' } };
}

// ========= UTILITY =========
function esc(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmtSize(b){if(!b)return'0 B';const u=['B','KB','MB'];let i=0,s=b;while(s>=1024&&i<2){s/=1024;i++}return(i?s.toFixed(1):s)+' '+u[i];}
function mColor(m){
    return{GET:'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300',POST:'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',PUT:'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300',PATCH:'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',DELETE:'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',HEAD:'bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300',OPTIONS:'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300'}[m]||'bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300';
}
function isJson(s){if(!s||typeof s!='string')return 0;try{JSON.parse(s);return 1}catch{return 0}}
function synHl(j){return j.replace(/("(?:[^"\\]|\\.)*")\s*:/g,'<span class="json-key">$1</span>:').replace(/:(\s*)"((?:[^"\\]|\\.)*)"/g,':<span class="json-string">"$2"</span>').replace(/:\s*(\d+(?:\.\d+)?)/g,':<span class="json-number">$1</span>').replace(/:\s*(true|false)/g,':<span class="json-bool">$1</span>').replace(/:\s*(null)/g,':<span class="json-null">$1</span>');}
function uid(){return Date.now().toString(36)+Math.random().toString(36).slice(2,8);}
function csrf(){return $('meta[name="csrf-token"]').attr('content')||'';}

// ========= CONTEXT MENU =========
let ctxMenu = null;
function showCtxMenu(x, y, items){
    hideCtxMenu();
    const isDark = $('html').hasClass('dark');
    const $m=$('<div>',{class:'ctx-menu',css:{position:'fixed',left:x+'px',top:y+'px',zIndex:9999,background:isDark?'#1e293b':'#fff',border:'1px solid '+(isDark?'#334155':'#e2e8f0'),borderRadius:'8px',boxShadow:'0 8px 32px rgba(0,0,0,0.2)',padding:'4px',minWidth:'150px',fontSize:'12px',fontWeight:'500',color:isDark?'#e2e8f0':'#334155'}});
    $.each(items,(i,it)=>{
        if(it.divider){$m.append($('<div>',{css:{borderTop:'1px solid '+(isDark?'#334155':'#e2e8f0'),margin:'4px 0'}}));return;}
        const $btn=$('<div>',{css:{padding:'6px 10px',cursor:'pointer',borderRadius:'4px',display:'flex',alignItems:'center',gap:'6px',color:it.danger&&(isDark?'#fca5a5':'#ef4444')||(isDark?'#e2e8f0':'#334155')},title:it.label}).html(it.label).on('mouseenter',function(){$(this).css('background',isDark?'#334155':'#f1f5f9');}).on('mouseleave',function(){$(this).css('background','transparent');}).on('click',function(){hideCtxMenu();it.action();});
        $m.append($btn);
    });
    $('body').append($m);
    setTimeout(()=>$(document).one('click',hideCtxMenu),0);
}
function hideCtxMenu(){if(ctxMenu){ctxMenu.remove();ctxMenu=null;}}
function api(path, opts){
    return fetch('/api'+path, {headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf()}, ...opts}).then(r=>r.json());
}

// ========= TOAST =========
function toast(m){const $e=$('#toast');$e.text(m).addClass('show');clearTimeout($e[0]._t);$e[0]._t=setTimeout(()=>$e.removeClass('show'),2000);}

// ========= THEME =========
function toggleTheme(){
    $('html').toggleClass('dark');
    const d=$('html').hasClass('dark');
    localStorage.setItem('incognito-theme',d?'dark':'light');
    $('#theme-icon').html(d?'<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>':'<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>');
}
function initTheme(){
    const s=localStorage.getItem('incognito-theme'), p=window.matchMedia('(prefers-color-scheme:dark)').matches, d=s?s==='dark':p;
    $('html').toggleClass('dark',d);
    if(d)$('#theme-icon').html('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>');
}

// ========= TABS =========
function newTab(d, name){
    const id=++tabCounter;
    tabs.push({id,name:name||(d&&d.name)||'Request '+id,data:JSON.parse(JSON.stringify(d||defaultReq())),response:null});
    renderTabs(); activateTab(id); return id;
}
function promptNewTab(){
    Swal.fire({title:'New Request',input:'text',inputPlaceholder:'Request name',showCancelButton:true,confirmButtonText:'Create',preConfirm:n=>{if(!n.trim())Swal.showValidationMessage('Name required');}})
        .then(r=>{if(r.isConfirmed)newTab(null, r.value.trim());});
}
function renderTabs(){
    const $l=$('#tabs-list'); $l.empty();
    $.each(tabs,(i,t)=>{
        const $d=$('<div>',{class:'flex items-center gap-1.5 px-3 py-1.5 text-xs border-r border-surface-100 dark:border-surface-700 cursor-pointer transition'+(t.id===activeTab?' bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-semibold':' text-surface-500 dark:text-surface-400 hover:bg-surface-50 dark:hover:bg-surface-700')}).on('click',()=>activateTab(t.id)).on('contextmenu',function(e){
            e.preventDefault(); const self=this;
            showCtxMenu(e.clientX,e.clientY,[
                {label:'&#9998; Rename',action:()=>{$(self).find('.tab-label').dblclick();}},
                {divider:true},
                {label:'&#10005; Close',action:()=>closeTab(t.id)},
                {label:'&#10005; Close Others',action:()=>{const ct=tabs.find(ta=>ta.id===t.id);tabs=[ct];renderTabs();activateTab(ct.id);}},
                {label:'&#10005; Close All',action:()=>{tabs=[tabs[0]];renderTabs();activateTab(tabs[0].id);}}
            ]);
        });
        $d.append($('<span>',{class:'tab-label',contenteditable:false}).text(esc(t.name)).on('dblclick',function(){
            $(this).attr('contenteditable',true).focus();
            document.execCommand('selectAll');
        }).on('blur',function(){
            const n=$(this).text().trim()||t.name; $(this).text(esc(n)).attr('contenteditable',false); t.name=n;
        }).on('keydown',function(e){if(e.key==='Enter'){e.preventDefault();$(this).blur();}}));
        $d.append($('<span>',{class:'ml-0.5 text-surface-300 dark:text-surface-600 hover:text-red-500 dark:hover:text-red-400 text-sm leading-none rounded hover:bg-red-50 dark:hover:bg-red-900/30 px-0.5 transition'}).html('&times;').on('click',function(e){e.stopPropagation();closeTab(t.id)}));
        $l.append($d);
    });
}
function activateTab(id){
    if(id===activeTab)return;
    activeTab=id; renderTabs(); loadTabData(); loadResp();
}
function closeTab(id){
    if(tabs.length<=1)return;
    const idx=tabs.findIndex(t=>t.id===id);
    tabs=$.grep(tabs,t=>t.id!==id);
    if(activeTab===id)activateTab(tabs[Math.min(idx,tabs.length-1)].id); else renderTabs();
}
function curTab(){return tabs.find(t=>t.id===activeTab);}

// ========= TAB DATA =========
function loadTabData(){
    const d=curTab()?.data; if(!d)return;
    $('#method').val(d.method); setUrl(d.url);
    renderKv('params-list',d.params); renderKv('headers-list',d.headers); renderKv('form-list',d.formData);
    $('#json-body').val(d.body);
    $(`input[name="bodyType"][value="${esc(d.bodyType||'none')}"]`).prop('checked',true); toggleBodyType();
    $('#auth-type').val(d.auth?.type||'none');
    $('#auth-bearer-token').val(d.auth?.bearer||''); $('#auth-username').val(d.auth?.username||''); $('#auth-password').val(d.auth?.password||'');
    $('#auth-key-name').val(d.auth?.keyName||'X-API-Key'); $('#auth-key-value').val(d.auth?.keyValue||'');
    toggleAuth(); updateVarPreview(); updateFieldVarBadges();
}
function saveTabData(){
    const d=curTab()?.data; if(!d)return;
    d.method=$('#method').val(); d.url=getUrl();
    d.params=readKv('params-list'); d.headers=readKv('headers-list'); d.formData=readKv('form-list');
    d.body=$('#json-body').val(); d.bodyType=$('input[name="bodyType"]:checked').val()||'none';
    const a=$('#auth-type').val();
    d.auth={type:a,bearer:$('#auth-bearer-token').val(),username:$('#auth-username').val(),password:$('#auth-password').val(),keyName:$('#auth-key-name').val(),keyValue:$('#auth-key-value').val()};
}

// ========= KV LIST =========
function renderKv(cid,items){
    const $c=$('#'+cid); $c.empty();
    $.each(items||[],(i,it)=>{
        $c.append($('<div>',{class:'kv-row fade-in'}).append(
            $('<input>',{placeholder:'Key',class:'flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors'}).val(it.key||'').on('input',saveTabData),
            $('<input>',{placeholder:'Value',class:'flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors'}).val(it.value||'').on('input',saveTabData),
            $('<button>',{class:'text-red-300 dark:text-red-500 hover:text-red-500 dark:hover:text-red-400 text-sm px-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition leading-none'}).html('&times;').on('click',function(){$(this).closest('.kv-row').remove();saveTabData()})
        ));
    });
}
function readKv(cid){
    const r=[]; $('#'+cid+' .kv-row').each(function(){r.push({key:$(this).find('input:first').val()||'',value:$(this).find('input:nth-child(2)').val()||''})}); return r;
}
function addKvRow(cid){
    const $c=$('#'+cid);
    $c.append($('<div>',{class:'kv-row fade-in'}).append(
        $('<input>',{placeholder:'Key',class:'flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors'}).on('input',saveTabData),
        $('<input>',{placeholder:'Value',class:'flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors'}).on('input',saveTabData),
        $('<button>',{class:'text-red-300 dark:text-red-500 hover:text-red-500 dark:hover:text-red-400 text-sm px-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition leading-none'}).html('&times;').on('click',function(){$(this).closest('.kv-row').remove();saveTabData()})
    ));
}

// ========= VARIABLE SUBSTITUTION =========
function getActiveEnv(){
    const id=localStorage.getItem('incognito-active-env');
    return id?envs.find(e=>e.id===id)||null:null;
}
function subVars(s){
    if(!s||typeof s!='string')return s;
    const env=getActiveEnv(); if(!env||!env.variables)return s;
    return s.replace(/\{\{(\w+)\}\}/g,(m,k)=>{const v=env.variables.find(x=>x.key===k&&x.enabled!==false);return v?v.value:m;});
}
function getUrl(){return $('#url').text();}
function setUrl(val){$('#url').html(esc(val||''));highlightVars();}
function getCaret(el){
    const sel=window.getSelection(); if(!sel.rangeCount)return 0;
    const range=sel.getRangeAt(0); if(!$.contains(el,range.commonAncestorContainer))return 0;
    const pre=document.createRange(); pre.selectNodeContents(el); pre.setEnd(range.endContainer,range.endOffset);
    return pre.toString().length;
}
function setCaret(el,pos){
    const sel=window.getSelection(), walk=document.createTreeWalker(el,4,{acceptNode:()=>1});
    let charCount=0, node=null;
    while(walk.nextNode()){node=walk.currentNode; const len=node.textContent.length;
        if(charCount+len>=pos){const range=document.createRange();range.setStart(node,pos-charCount);range.collapse(true);sel.removeAllRanges();sel.addRange(range);return;}
        charCount+=len;}
    const range=document.createRange();range.selectNodeContents(el);range.collapse(false);sel.removeAllRanges();sel.addRange(range);
}
function highlightVars(){
    const $url=$('#url'), env=getActiveEnv();
    const text=$url.text(); if(!text){$url.children().removeAttr('style class');return;}
    const pos=getCaret($url[0]);
    const vars=[],re=/\{\{(\w+)\}\}/g;let m;while((m=re.exec(text))!==null)vars.push({raw:m[0],name:m[1],idx:m.index});
    if(!vars.length||!env){$url.children().removeAttr('style class');setCaret($url[0],pos);return;}
    let html='',last=0;
    $.each(vars,(i,v)=>{
        const vDef=env.variables.find(x=>x.key===v.name&&x.enabled!==false);
        html+=esc(text.slice(last,v.idx));
        if(vDef)html+=`<span class="url-var resolved" data-val="${esc(vDef.value)}" style="color:#6366f1;font-weight:600;cursor:help">${esc(v.raw)}</span>`;
        else html+=`<span class="url-var missing" style="color:#ef4444;font-weight:600;cursor:help">${esc(v.raw)}</span>`;
        last=v.idx+v.raw.length;
    });
    html+=esc(text.slice(last));
    $url.html(html);
    setCaret($url[0],pos);
    $url.find('.url-var').on('mouseenter',function(){
        const isDark=$('html').hasClass('dark');
        const $t=$('<div>',{class:'var-hover-tip',css:{position:'fixed',zIndex:9999,background:isDark?'#334155':'#1e293b',color:isDark?'#e2e8f0':'#f8fafc',padding:'4px 8px',borderRadius:'4px',fontSize:'11px',lineHeight:'1.4',whiteSpace:'nowrap',boxShadow:'0 4px 12px rgba(0,0,0,0.2)',pointerEvents:'none'}});
        if($(this).hasClass('resolved'))$t.text($(this).data('val'));
        else $t.text('Not found');
        $('body').append($t);
        const off=$(this).offset();
        $t.css({left:Math.min(off.left,$(window).width()-$t.outerWidth()-10)+'px',top:(off.top-parseInt($t.css('line-height'))-6)+'px'});
    }).on('mouseleave',function(){$('.var-hover-tip').remove();});
}
function findVars(str){const vars=[],re=/\{\{(\w+)\}\}/g;let m;while((m=re.exec(str))!==null)vars.push({raw:m[0],name:m[1],idx:m.index});return vars;}
function updateVarPreview(){highlightVars();}
function updateFieldVarBadges(){
    const env=getActiveEnv();
    $('.kv-row input:nth-child(2)').each(function(){
        const v=findVars($(this).val()||''), $badge=$(this).siblings('.field-var-badge');
        if(v.length&&env){
            if(!$badge.length){$badge=$('<span>',{class:'field-var-badge'});$(this).after($badge);}
            $badge.html(v.map(x=>{const vd=env.variables.find(ev=>ev.key===x.name&&ev.enabled!==false);return vd?`<span class="var-badge var-badge-found" title="${esc(vd.value)}">${esc(x.raw)}</span>`:`<span class="var-badge var-badge-missing" title="Not found">${esc(x.raw)}</span>`;}).join(' '));
        }else if($badge.length)$badge.remove();
    });
    const bodyVars=findVars($('#json-body').val()||''), $bodyBadge=$('#body-var-badge');
    if(bodyVars.length&&env){
        const names=bodyVars.map(x=>{const vd=env.variables.find(ev=>ev.key===x.name&&ev.enabled!==false);return vd?`<span class="var-badge var-badge-found" title="${esc(vd.value)}">${esc(x.raw)}</span>`:`<span class="var-badge var-badge-missing" title="Not found">${esc(x.raw)}</span>`;}).join(' ');
        if(!$bodyBadge.length)$('#body-json').append($('<div>',{id:'body-var-badge',class:'flex flex-wrap gap-1 mt-1'}).html(names));
        else $bodyBadge.html(names);
    }else if($bodyBadge.length)$bodyBadge.remove();
    $('#auth-bearer-token, #auth-username, #auth-password, #auth-key-name, #auth-key-value').each(function(){
        const v=findVars($(this).val()||''), $badge=$(this).siblings('.field-var-badge');
        if(v.length&&env){
            if(!$badge.length){$badge=$('<span>',{class:'field-var-badge'});$(this).after($badge);}
            $badge.html(v.map(x=>{const vd=env.variables.find(ev=>ev.key===x.name&&ev.enabled!==false);return vd?`<span class="var-badge var-badge-found" title="${esc(vd.value)}">${esc(x.raw)}</span>`:`<span class="var-badge var-badge-missing" title="Not found">${esc(x.raw)}</span>`;}).join(' '));
        }else if($badge.length)$badge.remove();
    });
}

// ========= API HELPERS =========
async function fetchEnvs(){try{envs=await api('/environments');}catch{}}
async function fetchColls(){try{colls=await api('/collections');}catch{}}
async function fetchWorks(){try{works=await api('/workspaces');}catch{}}

// ========= ENVIRONMENTS =========
async function renderEnvironments(){
    await fetchEnvs();
    const active=localStorage.getItem('incognito-active-env');
    const $sel=$('#env-select'); $sel.empty();
    $sel.append($('<option>',{value:''}).text('No Environment'));
    $.each(envs,(i,e)=>{$sel.append($('<option>',{value:e.id}).text(e.name+(e.id===active?' (active)':'')));});
    if(active)$sel.val(active); else $sel.val('');
    const $el=$('#sb-environments'); $el.empty();
    if(!envs.length){$el.html('<div class="flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>No environments</span></div>');return;}
    $.each(envs,(i,e)=>{
        const $d=$('<div>',{class:'sidebar-item p-2 rounded-md flex gap-2 items-start'+(e.id===active?' active':'')}).on('click',()=>{localStorage.setItem('incognito-active-env',e.id);renderEnvironments();toast('Active: '+e.name);});
        $d.append($('<span>',{class:'badge-dot bg-brand-500 shrink-0 mt-1'}));
        $d.append($('<div>',{class:'min-w-0 flex-1'}).append($('<p>',{class:'text-xs font-semibold text-surface-700 dark:text-surface-300 truncate'}).text(e.name+(e.id===active?' (active)':'')),$('<p>',{class:'text-[10px] text-surface-400 dark:text-surface-500'}).text((e.variables?e.variables.length:0)+' variable'+(e.variables&&e.variables.length!==1?'s':''))));
        $d.append($('<button>',{class:'text-red-300 dark:text-red-500 hover:text-red-500 dark:hover:text-red-400 text-sm px-0.5 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition leading-none'}).html('&times;').on('click',function(ev){ev.stopPropagation();deleteEnv(e.id);}));
        $el.append($d);
    });
}
function manageEnvironments(){
    let html='<div style="max-height:60vh;overflow-y:auto"><div class="flex gap-2 mb-2"><button class="add-env-btn" style="padding:6px 12px;background:#4f46e5;color:white;border-radius:6px;font-size:12px;border:none;cursor:pointer">+ New Environment</button></div><div id="env-manager-list">';
    $.each(envs,(i,e)=>{
        html+=`<div class="env-card" data-id="${e.id}" style="border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:8px;background:#f8fafc">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                <input class="env-name" value="${esc(e.name)}" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600">
                <button class="del-env-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button>
            </div>
            <div class="env-vars-list">${(e.variables||[]).map(v=>`<div class="ev-row" style="display:flex;gap:4px;margin-bottom:3px;align-items:center">
                <input class="ev-key" value="${esc(v.key)}" placeholder="Key" style="flex:1;padding:3px 6px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px">
                <input class="ev-val" value="${esc(v.value)}" placeholder="Value" style="flex:1;padding:3px 6px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px">
                <button class="del-ev-btn" style="color:#ef4444;font-size:14px;border:none;background:none;cursor:pointer">&times;</button>
            </div>`).join('')}</div>
            <button class="add-ev-btn" style="font-size:11px;color:#4f46e5;border:none;background:none;cursor:pointer">+ Add variable</button>
        </div>`;
    });
    html+='</div></div>';
    Swal.fire({title:'Manage Environments',html,width:'600px',showCancelButton:true,confirmButtonText:'Save Changes',
        didOpen:()=>{
            $('.add-env-btn').on('click',function(){
                const $card=$('<div>',{class:'env-card','data-id':'new',css:{border:'1px solid #e2e8f0',borderRadius:'8px',padding:'10px',marginBottom:'8px',background:'#f8fafc'}}).html(`<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px"><input class="env-name" placeholder="Environment Name" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600"><button class="del-env-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button></div><div class="env-vars-list"></div><button class="add-ev-btn" style="font-size:11px;color:#4f46e5;border:none;background:none;cursor:pointer">+ Add variable</button>`);
                $('#env-manager-list').append($card);
                $card.find('.del-env-btn').on('click',function(){$(this).closest('.env-card').remove();});
                $card.find('.add-ev-btn').on('click',function(){addEnvVarRow($(this).closest('.env-card').find('.env-vars-list'));});
            });
            $('.del-env-btn').on('click',function(){$(this).closest('.env-card').remove();});
            $('.add-ev-btn').on('click',function(){addEnvVarRow($(this).closest('.env-card').find('.env-vars-list'));});
        },
        preConfirm:async()=>{
            for(const card of $('.env-card')){
                const name=$(card).find('.env-name').val().trim(); if(!name)continue;
                const vars=[]; $(card).find('.ev-row').each(function(){const k=$(this).find('.ev-key').val().trim();if(k)vars.push({key:k,value:$(this).find('.ev-val').val(),enabled:true});});
                const id=$(card).attr('data-id');
                if(id&&id!=='new'){await api('/environments/'+id,{method:'PUT',body:JSON.stringify({name,variables:vars})});}
                else{await api('/environments',{method:'POST',body:JSON.stringify({name,variables:vars})});}
            }
            await renderEnvironments(); renderCollections(); toast('Environments saved');
        }
    });
}
function addEnvVarRow($list){
    $list.append($('<div>',{class:'ev-row',css:{display:'flex',gap:'4px',marginBottom:'3px',alignItems:'center'}}).append(
        $('<input>',{class:'ev-key',placeholder:'Key',css:{flex:1,padding:'3px 6px',border:'1px solid #e2e8f0',borderRadius:'4px',fontSize:'11px'}}),
        $('<input>',{class:'ev-val',placeholder:'Value',css:{flex:1,padding:'3px 6px',border:'1px solid #e2e8f0',borderRadius:'4px',fontSize:'11px'}}),
        $('<button>',{class:'del-ev-btn',html:'&times;',css:{color:'#ef4444',fontSize:'14px',border:'none',background:'none',cursor:'pointer'}}).on('click',function(){$(this).closest('.ev-row').remove();})
    ));
}
async function deleteEnv(id){
    Swal.fire({title:'Delete Environment?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Delete'})
        .then(async r=>{if(r.isConfirmed){await api('/environments/'+id,{method:'DELETE'});await renderEnvironments();toast('Deleted');}});
}

// ========= COLLECTIONS =========
async function renderCollections(){
    await fetchColls();
    const $el=$('#sb-collections'); $el.empty();
    $el.append($('<div>',{class:'flex gap-1 px-2 py-1.5 border-b border-surface-100 dark:border-surface-700'}).append(
        $('<button>',{class:'text-[10px] px-2 py-1 rounded bg-brand-500 text-white hover:bg-brand-400 transition font-medium'}).text('+ Collection').on('click',createCollection),
        $('<button>',{class:'text-[10px] px-2 py-1 rounded bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-600 transition font-medium ml-auto'}).text('Import').on('click',importCollection)
    ));
    if(!colls.length){$el.append($('<div>',{class:'flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2'}).append($('<svg>',{class:'w-6 h-6',fill:'none',stroke:'currentColor',viewBox:'0 0 24 24'}).html('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>'),$('<span>').text('No collections')));return;}
    $.each(colls,(i,c)=>{const $c=$('<div>');$c.append(renderCollItem(c));$el.append($c);});
}
function renderCollItem(c){
    const $d=$('<div>',{class:'collection-item group'}).on('contextmenu',function(e){
        e.preventDefault();
        showCtxMenu(e.clientX,e.clientY,[
            {label:'&#9998; Rename',action:()=>renameCollection(c.id,$(this).find('span:first')[0])},
            {label:'&#128193; Add Folder',action:()=>addFolderToColl(c.id)},
            {label:'&#43; Add Request',action:()=>addReqToColl(c.id)},
            {divider:true},
            {label:'&#128229; Export',action:()=>exportColl(c.id)},
            {label:'&#10005; Delete',danger:true,action:()=>deleteColl(c.id)}
        ]);
    });
    $d.append($('<span>',{class:'flex-1 truncate text-surface-700 dark:text-surface-300 font-medium'}).text(c.name).on('dblclick',function(){renameCollection(c.id,this);}));
    const $actions=$('<span>',{class:'ml-auto flex gap-0.5 opacity-0 group-hover:opacity-100 transition text-surface-400'});
    $actions.append($('<button>',{class:'hover:text-brand-500 px-0.5',title:'Add Folder'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-5 4h10a2 2 0 002-2V9a2 2 0 00-2-2h-2.586A2 2 0 0012 4.414L10.586 3H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>').on('click',function(e){e.stopPropagation();addFolderToColl(c.id);}));
    $actions.append($('<button>',{class:'hover:text-brand-500 px-0.5',title:'Add Request'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>').on('click',function(e){e.stopPropagation();addReqToColl(c.id);}));
    $actions.append($('<button>',{class:'hover:text-blue-500 px-0.5',title:'Export'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 11l5 5 5-5M12 4v11"/></svg>').on('click',function(e){e.stopPropagation();exportColl(c.id);}));
    $actions.append($('<button>',{class:'hover:text-red-500 px-0.5',title:'Delete'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>').on('click',function(e){e.stopPropagation();deleteColl(c.id);}));
    $d.append($actions);
    if(c.items&&c.items.length){
        const $child=$('<div>',{class:'tree-children'});
        $.each(c.items,(j,item)=>{$child.append(renderCollTreeItem(item,c.id));});
        $d.wrap('<div></div>'); const $wrap=$d.parent(); $wrap.append($child); return $wrap;
    }
    return $d;
}
function renderCollTreeItem(item,collectionId){
    if(item.type==='folder'){
        const $d=$('<div>');
        const $h=$('<div>',{class:'flex items-center gap-1 py-0.5 text-xs text-surface-600 dark:text-surface-400 hover:text-surface-800 dark:hover:text-surface-200 cursor-pointer group rounded px-1 hover:bg-surface-50 dark:hover:bg-surface-700'}).on('contextmenu',function(e){
            e.preventDefault();
            showCtxMenu(e.clientX,e.clientY,[
                {label:'&#9998; Rename',action:()=>renameItem(item.id,$(this).find('.truncate'))},
                {label:'&#43; Add Request',action:()=>addReqToFolder(collectionId,item.id)},
                {divider:true},
                {label:'&#10005; Delete Folder',danger:true,action:()=>deleteCollItem(collectionId,item.id)}
            ]);
        });
        const $tog=$('<span>',{class:'text-surface-400 text-[10px] w-3 text-center transition-transform'}).html('&#9654;');
        $h.append($tog);
        $h.append($('<span>',{class:'text-surface-500'}).html('&#128193;'));
        $h.append($('<span>',{class:'flex-1 truncate font-medium'}).text(item.name));
        const $actions=$('<span>',{class:'ml-auto flex gap-0.5 opacity-0 group-hover:opacity-100 transition'});
        $actions.append($('<button>',{class:'hover:text-brand-500 text-[10px] px-0.5',title:'Add Request'}).html('+').on('click',function(e){e.stopPropagation();addReqToFolder(collectionId,item.id);}));
        $actions.append($('<button>',{class:'hover:text-red-500 text-[10px] px-0.5',html:'&times;'}).on('click',function(e){e.stopPropagation();deleteCollItem(collectionId,item.id);}));
        $h.append($actions);
        $h.on('click',function(){const $children=$d.find('> .tree-children');$children.toggleClass('hidden');$tog.toggleClass('rotate-90');if($children.hasClass('hidden'))$tog.css('transform','');else $tog.css('transform','rotate(90deg)');});
        $d.append($h);
        const $child=$('<div>',{class:'tree-children'});
        if(item.children){$.each(item.children,(k,ch)=>{$child.append(renderCollTreeItem(ch,collectionId));});}
        $d.append($child); return $d;
    } else {
        const $d=$('<div>',{class:'flex items-center gap-1.5 py-0.5 px-1 text-xs cursor-pointer rounded hover:bg-surface-50 dark:hover:bg-surface-700 group'}).on('click',function(){loadCollReq(collectionId,item.id);}).on('contextmenu',function(e){
            e.preventDefault();
            showCtxMenu(e.clientX,e.clientY,[
                {label:'&#9998; Rename',action:()=>renameItem(item.id,$(this).find('.truncate'))},
                {divider:true},
                {label:'&#10005; Delete',danger:true,action:()=>deleteCollItem(collectionId,item.id)}
            ]);
        });
        $d.append($('<span>',{class:'pill '+mColor(item.request_data?item.request_data.method:'GET'),text:item.request_data?item.request_data.method:'GET'}));
        $d.append($('<span>',{class:'flex-1 truncate text-surface-700 dark:text-surface-300'}).text(item.name));
        const $actions=$('<span>',{class:'ml-auto flex gap-0.5 opacity-0 group-hover:opacity-100 transition text-surface-400'});
        $actions.append($('<button>',{class:'hover:text-brand-500 text-[10px] px-0.5',title:'Rename'}).html('&#9998;').on('click',function(e){e.stopPropagation();renameItem(item.id,$(this).closest('.group').find('.truncate'));}));
        $actions.append($('<button>',{class:'hover:text-red-500 text-[10px] px-0.5',html:'&times;'}).on('click',function(e){e.stopPropagation();deleteCollItem(collectionId,item.id);}));
        $d.append($actions); return $d;
    }
}
async function createCollection(){
    Swal.fire({title:'New Collection',input:'text',inputPlaceholder:'Collection name',showCancelButton:true,confirmButtonText:'Create',preConfirm:n=>{if(!n.trim())Swal.showValidationMessage('Name required');}})
        .then(async r=>{if(r.isConfirmed){await api('/collections',{method:'POST',body:JSON.stringify({name:r.value.trim()})});await renderCollections();toast('Created');}});
}
async function renameCollection(id,el){
    const $span=$(el); const old=$span.text();
    $span.attr('contenteditable',true).focus();
    document.execCommand('selectAll');
    $span.on('blur',async function(){
        const n=$(this).text().trim()||old; $(this).text(esc(n)).attr('contenteditable',false);
        if(n!==old)await api('/collections/'+id,{method:'PUT',body:JSON.stringify({name:n})});
    }).on('keydown',function(e){if(e.key==='Enter'){e.preventDefault();$(this).blur();}});
}
async function renameItem(id,el){
    const $span=$(el).is('.group')?$(el).find('.truncate'):$(el); const old=$span.text();
    if(!$span.length)return;
    $span.attr('contenteditable',true).focus();
    document.execCommand('selectAll');
    $span.on('blur',async function(){
        const n=$(this).text().trim()||old; $(this).text(esc(n)).attr('contenteditable',false);
        if(n!==old)await api('/collections/items/'+id,{method:'PUT',body:JSON.stringify({name:n})});
    }).on('keydown',function(e){if(e.key==='Enter'){e.preventDefault();$(this).blur();}});
}
async function deleteColl(id){
    Swal.fire({title:'Delete Collection?',text:'This cannot be undone',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Delete'})
        .then(async r=>{if(r.isConfirmed){await api('/collections/'+id,{method:'DELETE'});await renderCollections();toast('Deleted');}});
}
async function addFolderToColl(collectionId){
    Swal.fire({title:'New Folder',input:'text',inputPlaceholder:'Folder name',showCancelButton:true,confirmButtonText:'Create',preConfirm:n=>{if(!n.trim())Swal.showValidationMessage('Name required');}})
        .then(async r=>{if(r.isConfirmed){await api('/collections/'+collectionId+'/items',{method:'POST',body:JSON.stringify({type:'folder',name:r.value.trim()})});await renderCollections();toast('Folder created');}});
}
async function addReqToColl(collectionId){
    saveTabData(); const d=curTab()?.data; if(!d||!d.url){toast('Open a request first');return;}
    Swal.fire({title:'Save to Collection',input:'text',inputPlaceholder:'Request name',inputValue:curTab()?.name||'',showCancelButton:true,confirmButtonText:'Save',preConfirm:n=>{if(!n.trim())Swal.showValidationMessage('Name required');}})
        .then(async r=>{if(r.isConfirmed){await api('/collections/'+collectionId+'/items',{method:'POST',body:JSON.stringify({type:'request',name:r.value.trim(),request_data:d,response_data:curTab()?.response||null})});await renderCollections();toast('Saved');}});
}
async function addReqToFolder(collectionId,parentId){
    saveTabData(); const d=curTab()?.data; if(!d||!d.url){toast('Open a request first');return;}
    Swal.fire({title:'Save to Folder',input:'text',inputPlaceholder:'Request name',inputValue:curTab()?.name||'',showCancelButton:true,confirmButtonText:'Save',preConfirm:n=>{if(!n.trim())Swal.showValidationMessage('Name required');}})
        .then(async r=>{if(r.isConfirmed){await api('/collections/'+collectionId+'/items',{method:'POST',body:JSON.stringify({type:'request',name:r.value.trim(),parent_id:parentId,request_data:d,response_data:curTab()?.response||null})});await renderCollections();toast('Saved to folder');}});
}
async function deleteCollItem(collectionId,itemId){
    Swal.fire({title:'Delete?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Delete'})
        .then(async r=>{if(r.isConfirmed){await api('/collections/items/'+itemId,{method:'DELETE'});await renderCollections();toast('Deleted');}});
}
async function loadCollReq(collectionId,itemId){
    const res=await api('/collections/'+collectionId+'/items');
    function findItem(items){for(const it of items){if(it.id===itemId)return it;if(it.children){const f=findItem(it.children);if(f)return f;}}return null;}
    const item=findItem(res);
    if(!item||!item.request_data){toast('No request data');return;}
    const id=newTab(item.request_data, item.name);
    const t=tabs.find(tab=>tab.id===id);
    if(t&&item.response_data)t.response=JSON.parse(JSON.stringify(item.response_data));
    activateTab(id); if(t&&t.response)loadResp(); toast('Loaded: '+item.name);
}
function exportColl(id){
    const c=colls.find(x=>x.id===id); if(!c)return;
    const blob=new Blob([JSON.stringify(c,null,2)],{type:'application/json'});
    const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=c.name.replace(/[^a-z0-9]/gi,'_')+'.json'; a.click();
    URL.revokeObjectURL(a.href); toast('Exported');
}
function importCollection(){
    const input=$('<input>',{type:'file',accept:'.json',css:{display:'none'}});
    input.on('change',function(){
        if(!this.files[0])return;
        const reader=new FileReader();
        reader.onload=async function(e){
            try{
                const data=JSON.parse(e.target.result);
                if(!data.name||!Array.isArray(data.items)){toast('Invalid collection format');return;}
                const coll=await api('/collections',{method:'POST',body:JSON.stringify({name:data.name})});
                for(const item of data.items)await importItem(coll.id,null,item);
                await renderCollections(); toast('Imported: '+data.name);
            }catch{toast('Invalid JSON file');}
        };
        reader.readAsText(this.files[0]);
    });
    input.click();
}
async function importItem(collId,parentId,item){
    const created=await api('/collections/'+collId+'/items',{method:'POST',body:JSON.stringify({type:item.type,name:item.name,parent_id:parentId,request_data:item.request_data||null,response_data:item.response_data||null})});
    if(item.type==='folder'&&item.children){for(const ch of item.children)await importItem(collId,created.id,ch);}
}

// ========= WORKSPACES =========
async function renderWorkspaces(){
    await fetchWorks();
    const $sel=$('#workspace-select'); $sel.empty();
    $sel.append($('<option>',{value:''}).text('No Workspace'));
    $.each(works,(i,w)=>{$sel.append($('<option>',{value:w.id}).text(w.name));});
}
function manageWorkspaces(){
    let html='<div style="max-height:60vh;overflow-y:auto"><div class="flex gap-2 mb-2"><button class="add-ws-btn" style="padding:6px 12px;background:#4f46e5;color:white;border-radius:6px;font-size:12px;border:none;cursor:pointer">+ New Workspace</button></div><div id="ws-manager-list">';
    if(works.length){
        $.each(works,(i,w)=>{
            html+=`<div class="ws-card" data-id="${w.id}" style="border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:8px;background:#f8fafc">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <input class="ws-name" value="${esc(w.name)}" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600">
                    <button class="del-ws-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button>
                </div>
                <div style="font-size:11px;color:#64748b;margin-bottom:4px">Collections in workspace:</div>
                <div class="ws-colls">${colls.map(c=>`<label style="display:flex;align-items:center;gap:4px;font-size:11px;margin:2px 0;cursor:pointer"><input type="checkbox" class="ws-coll-chk" value="${c.id}" ${(w.collections||[]).find(x=>x.id===c.id)?'checked':''}>${esc(c.name)}</label>`).join('')}</div></div>`;
        });
    } else html+='<p style="color:#94a3b8;font-size:12px">No workspaces yet</p>';
    html+='</div></div>';
    Swal.fire({title:'Manage Workspaces',html,width:'500px',showCancelButton:true,confirmButtonText:'Save',
        didOpen:()=>{
            $('.add-ws-btn').on('click',function(){
                const $card=$('<div>',{class:'ws-card','data-id':'new',css:{border:'1px solid #e2e8f0',borderRadius:'8px',padding:'10px',marginBottom:'8px',background:'#f8fafc'}});
                $card.html(`<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px"><input class="ws-name" placeholder="Workspace Name" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600"><button class="del-ws-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button></div><div style="font-size:11px;color:#64748b;margin-bottom:4px">Collections:</div><div class="ws-colls">${colls.map(c=>`<label style="display:flex;align-items:center;gap:4px;font-size:11px;margin:2px 0;cursor:pointer"><input type="checkbox" class="ws-coll-chk" value="${c.id}">${esc(c.name)}</label>`).join('')}</div>`);
                $('#ws-manager-list').append($card);
                $card.find('.del-ws-btn').on('click',function(){$(this).closest('.ws-card').remove();});
            });
            $('.del-ws-btn').on('click',function(){$(this).closest('.ws-card').remove();});
        },
        preConfirm:async()=>{
            for(const card of $('.ws-card')){
                const name=$(card).find('.ws-name').val().trim(); if(!name)continue;
                const data={name};
                const id=$(card).attr('data-id');
                if(id&&id!=='new'){await api('/workspaces/'+id,{method:'PUT',body:JSON.stringify(data)});}
                else{await api('/workspaces',{method:'POST',body:JSON.stringify(data)});}
            }
            await renderWorkspaces(); toast('Workspaces saved');
        }
    });
}

// ========= HISTORY =========
function getHist(){return JSON.parse(localStorage.getItem('apiTesterHistory')||'[]');}
function saveHist(h){localStorage.setItem('apiTesterHistory',JSON.stringify(h));}
function renderHistory(){
    const hist=getHist(); const $el=$('#sb-history'); $el.empty();
    $el.append($('<div>',{class:'flex items-center px-2 py-1.5 border-b border-surface-100 dark:border-surface-700'}).append($('<span>',{class:'text-[10px] font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider'}).text('Recent'),$('<button>',{class:'ml-auto text-[10px] px-2 py-0.5 rounded text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition font-medium'}).text('Clear All').on('click',clearHistory)));
    if(!hist.length){$el.append($('<div>',{class:'flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2'}).append($('<svg>',{class:'w-6 h-6',fill:'none',stroke:'currentColor',viewBox:'0 0 24 24'}).html('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'),$('<span>').text('No requests yet')));return;}
    $.each(hist,(i,h)=>{
        const $d=$('<div>',{class:'sidebar-item p-2 rounded-md flex gap-2 items-start'}).on('click',()=>{const d=curTab()?.data;if(d){d.url=h.url;d.method=h.method;loadTabData();}});
        $d.append($('<span>',{class:'pill '+mColor(h.method)+' shrink-0 mt-0.5 text-[10px]'}).text(h.method));
        $d.append($('<div>',{class:'min-w-0 flex-1'}).append($('<p>',{class:'truncate text-surface-700 dark:text-surface-300 text-xs'}).text(h.url),$('<span>',{class:'text-[10px] '+(h.status>=400?'text-red-400':'text-green-500')+' font-medium'}).html((h.status||'ERR')+' &middot; '+(h.time||'&mdash;')+'ms')));
        $el.append($d);
    });
}
function clearHistory(){Swal.fire({title:'Clear History?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Clear'}).then(r=>{if(r.isConfirmed){saveHist([]);renderHistory();toast('History cleared');}});}
function addHistory(data,resp){const hist=getHist();hist.unshift({method:data.method,url:data.url,status:resp?.status,time:resp?.time,ts:Date.now()});if(hist.length>50)hist.length=50;saveHist(hist);renderHistory();}

// ========= SEND REQUEST =========
async function sendReq(){
    saveTabData();
    const d=curTab()?.data; if(!d||!d.url){toast('Enter a URL first');return;}
    if(currentAbort){currentAbort.abort();currentAbort=null;}
    currentAbort=new AbortController();
    $('#send-text').text('Sending'); $('#send-icon').html('<span class="spinner"></span>'); $('#cancel-btn').removeClass('hidden');
    const allHeaders=[...d.headers.filter(h=>h.key)];
    const allParams=d.params.filter(p=>p.key);
    let url=subVars(d.url);
    if(allParams.length){const qs=allParams.map(p=>encodeURIComponent(p.key)+'='+encodeURIComponent(p.value)).join('&');url+=(url.includes('?')?'&':'?')+qs;}
    if(d.auth?.type==='bearer'&&d.auth.bearer)allHeaders.push({key:'Authorization',value:'Bearer '+subVars(d.auth.bearer)});
    if(d.auth?.type==='basic'&&d.auth.username)allHeaders.push({key:'Authorization',value:'Basic '+btoa(subVars(d.auth.username)+':'+subVars(d.auth.password||''))});
    if(d.auth?.type==='apikey'&&d.auth.keyName)allHeaders.push({key:subVars(d.auth.keyName),value:subVars(d.auth.keyValue)});
    $.each(allHeaders,(i,h)=>{h.key=subVars(h.key);h.value=subVars(h.value);});
    let body=subVars(d.body);
    try{
        const res=await fetch('/api-tester/proxy',{method:'POST',signal:currentAbort.signal,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf()},body:JSON.stringify({method:d.method,url,headers:allHeaders,body,bodyType:d.bodyType==='json'?'json':d.bodyType==='form-data'?'form-data':'none',formData:d.formData})});
        const json=await res.json(); resetSendBtn();
        if(json.error){showResp({status:0,statusText:'Error',headers:{},body:json.error,size:0,time:json.time||0});toast('Error: '+json.error);return;}
        showResp(json); addHistory(d,json);
    }catch(e){resetSendBtn();if(e.name==='AbortError'){toast('Request cancelled');return;}showResp({status:0,statusText:'Error',headers:{},body:e.message,size:0,time:0});toast(e.message.includes('Failed to fetch')?'Connection failed':'Network Error');}
}
function cancelReq(){if(currentAbort){currentAbort.abort();currentAbort=null;}}
function resetSendBtn(){currentAbort=null;$('#send-text').text('Send');$('#send-icon').html('<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>');$('#cancel-btn').addClass('hidden');}

// ========= SHOW RESPONSE =========
function showResp(r){
    const d=curTab(); if(d)d.response=r;
    const color=r.status>=200&&r.status<300?'text-green-600 dark:text-green-400':r.status>=400?'text-red-500 dark:text-red-400':'text-surface-500 dark:text-surface-400';
    $('#resp-status').html('Status: <span class="'+color+' font-semibold">'+(r.status||'&mdash;')+'</span> '+(r.statusText||''));
    $('#resp-time').html('Time: <span class="text-surface-700 dark:text-surface-300 font-semibold">'+(r.time||0)+'ms</span>');
    $('#resp-size').html('Size: <span class="text-surface-700 dark:text-surface-300 font-semibold">'+fmtSize(r.size||0)+'</span>');
    const hEl=$('#resp-headers'); hEl.empty();
    if(r.headers&&Object.keys(r.headers).length){$.each(r.headers,(k,v)=>{hEl.append($('<div>',{class:'flex gap-2 py-1 border-b border-surface-100 dark:border-surface-700'}).append($('<span>',{class:'font-medium text-surface-600 dark:text-surface-300 shrink-0 min-w-[200px]'}).text(esc(k)),$('<span>',{class:'text-surface-500 dark:text-surface-400 break-all font-mono text-[11px]'}).text(esc(v))));});}
    else hEl.html('<p class="text-surface-400 dark:text-surface-500 text-xs">No headers</p>');
    $('#resp-cookies').html(r.headers?.['set-cookie']?'<pre class="text-xs font-mono leading-relaxed">'+esc(r.headers['set-cookie'])+'</pre>':'<p class="text-surface-400 dark:text-surface-500 text-xs">No cookies</p>');
    applyFmt(activeRespFormat);
}
function loadResp(){const r=curTab()?.response;if(r)showResp(r);else clearResp();}
function clearResp(){
    $('#resp-status').html('Status: <span class="text-surface-400 dark:text-surface-500">&mdash;</span>');
    $('#resp-time').html('Time: <span class="text-surface-400 dark:text-surface-500">&mdash;</span>');
    $('#resp-size').html('Size: <span class="text-surface-400 dark:text-surface-500">&mdash;</span>');
    $('#resp-body').html('<div class="flex items-center justify-center h-full text-surface-300 dark:text-surface-600 text-sm font-medium">Send a request to see the response</div>');
    $('#resp-headers').empty(); $('#resp-cookies').empty(); $('#resp-preview').attr('src','about:blank');
}
function copyResp(){const t=$('#resp-body').text().trim();if(!t){toast('Nothing to copy');return;}navigator.clipboard?.writeText(t).then(()=>toast('Copied!')).catch(()=>{});}
function applyFmt(fmt){
    const r=curTab()?.response; if(!r)return;
    const $body=$('#resp-body'), $prev=$('#resp-preview');
    $body.toggleClass('hidden',fmt==='preview'); $prev.toggleClass('hidden',fmt!=='preview');
    if(fmt==='pretty'){if(isJson(r.body))$body.html('<pre class="text-xs font-mono leading-relaxed">'+synHl(JSON.stringify(JSON.parse(r.body),null,2))+'</pre>');else $body.html('<pre class="text-xs font-mono leading-relaxed whitespace-pre-wrap">'+esc(r.body||'(empty)')+'</pre>');}
    else if(fmt==='raw')$body.html('<pre class="text-xs font-mono leading-relaxed whitespace-pre-wrap">'+esc(r.body||'(empty)')+'</pre>');
    else if(fmt==='preview'){try{$prev[0].src='data:text/html;charset=utf-8,'+encodeURIComponent(r.body||'');}catch{$prev[0].src='about:blank';}}
}
function saveResp(){
    const d=curTab()?.data, r=curTab()?.response;
    if(!d||!d.url){toast('Send a request first');return;} if(!r){toast('No response to save');return;}
    if(!colls.length){toast('No collections exist. Create one first.');return;}
    let html='<div style="margin-bottom:8px"><label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;color:#475569">Request Name</label><input id="save-req-name" value="'+esc(curTab()?.name||'')+'" style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box"></div>';
    html+='<div style="margin-bottom:8px"><label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;color:#475569">Collection</label><select id="save-coll-select" style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px"><option value="">Select a collection...</option>';
    $.each(colls,(i,c)=>{html+='<option value="'+c.id+'">'+esc(c.name)+'</option>';});
    html+='</select></div><div id="save-folder-wrap" style="display:none"><label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;color:#475569">Folder (optional)</label><select id="save-folder-select" style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px"><option value="">Root (no folder)</option></select></div>';
    Swal.fire({title:'Save to Collection',html,width:'450px',showCancelButton:true,confirmButtonText:'Save',
        didOpen:()=>{
            $('#save-coll-select').on('change',function(){
                const cid=$(this).val();
                if(!cid){$('#save-folder-wrap').hide();return;}
                const coll=colls.find(x=>x.id===cid);
                const $sel=$('#save-folder-select').empty().append($('<option>',{value:''}).text('Root (no folder)'));
                if(coll&&coll.items){
                    (function walk(items,depth){
                        $.each(items,(i,it)=>{
                            if(it.type==='folder'){
                                $sel.append($('<option>',{value:it.id}).text(Array(depth+1).join('  ')+it.name));
                                if(it.children)walk(it.children,depth+1);
                            }
                        });
                    })(coll.items,0);
                }
                $('#save-folder-wrap').show();
            });
        },
        preConfirm:()=>{
            const name=$('#save-req-name').val().trim();
            const collId=$('#save-coll-select').val();
            const folderId=$('#save-folder-select').val()||null;
            if(!name){Swal.showValidationMessage('Name required');return false;}
            if(!collId){Swal.showValidationMessage('Select a collection');return false;}
            return {name,collId,folderId};
        }
    }).then(async res=>{
        if(res.isConfirmed){
            const {name,collId,folderId}=res.value;
            const tabData=JSON.parse(JSON.stringify(d));
            const tabResp=JSON.parse(JSON.stringify(r));
            await api('/collections/'+collId+'/items',{method:'POST',body:JSON.stringify({type:'request',name,parent_id:folderId,request_data:tabData,response_data:tabResp})});
            await renderCollections(); toast('Saved to collection');
        }
    });
}

// ========= SIDEBAR =========
function showSidebar(sb){
    ['history','collections','environments'].forEach(s=>{$('#sb-'+s).toggleClass('hidden',s!==sb);const $btn=$('#sb-'+s+'-btn');$btn.toggleClass('text-brand-600 dark:text-brand-400',s===sb).toggleClass('text-surface-400 dark:text-surface-500',s!==sb).toggleClass('border-brand-500 dark:border-brand-400',s===sb).toggleClass('border-transparent',s!==sb);});
}

// ========= CURL IMPORT/EXPORT =========
function importCurl(){$('#curl-input').val('');$('#curl-modal').removeClass('hidden');setTimeout(()=>$('#curl-input').trigger('focus'),100);}
function closeCurlModal(){$('#curl-modal').addClass('hidden');}
function parseAndImportCurl(){
    const raw=$('#curl-input').val().trim(); if(!raw){toast('Paste a cURL command first');return;}
    const p=parseCurl(raw); if(!p||!p.url){toast('Could not parse');return;}
    const d=curTab()?.data; if(!d)return;
    d.method=p.method||'GET'; d.url=p.url; d.headers=p.headers||[]; d.body=p.body||''; d.bodyType=p.body?'json':'none';
    if(p.authType){d.auth.type=p.authType;if(p.authType==='bearer')d.auth.bearer=p.authValue||'';if(p.authType==='basic'){d.auth.username=p.authUser||'';d.auth.password=p.authPass||'';}if(p.authType==='apikey'){d.auth.keyName=p.authKey||'X-API-Key';d.auth.keyValue=p.authValue||'';}}
    loadTabData(); closeCurlModal(); toast('Imported');
}
function parseCurl(str){
    let s=str.replace(/\\\n/g,' ').replace(/\\\r\n/g,' ').trim();
    const r={method:'GET',url:'',headers:[],body:'',authType:null,authValue:null,authUser:null,authPass:null,authKey:null};
    const tokens=tokenizeCurl(s); let url='',i=0;
    while(i<tokens.length){const t=tokens[i];if(t==='curl'){i++;continue;}if(t.startsWith('-')){if((t==='-X'||t==='--request')&&i+1<tokens.length){r.method=tokens[i+1].toUpperCase();i+=2;continue;}if((t==='-H'||t==='--header')&&i+1<tokens.length){const hv=parseHVal(tokens[i+1]);if(hv)r.headers.push(hv);i+=2;continue;}if((t==='-d'||t==='--data'||t==='--data-raw'||t==='--data-binary')&&i+1<tokens.length){r.body=tokens[i+1];if(r.method==='GET')r.method='POST';i+=2;continue;}if((t==='-u'||t==='--user')&&i+1<tokens.length){const u=tokens[i+1],colon=u.indexOf(':');if(colon>0){r.authType='basic';r.authUser=u.slice(0,colon);r.authPass=u.slice(colon+1);}else{r.authType='basic';r.authUser=u;r.authPass='';}i+=2;continue;}if((t==='-b'||t==='--cookie')&&i+1<tokens.length){r.headers.push({key:'Cookie',value:tokens[i+1]});i+=2;continue;}if(t==='-k'||t==='--insecure'){i++;continue;}if(t.includes('=')){i++;continue;}if(i+1<tokens.length&&!tokens[i+1].startsWith('-'))i+=2;else i++;continue;}if(!url)url=t;i++;}
    url=stripQ(url); r.url=url;
    const authH=r.headers.find(h=>h.key.toLowerCase()==='authorization');
    if(authH&&!r.authType){const v=authH.value.trim();if(v.toLowerCase().startsWith('bearer ')){r.authType='bearer';r.authValue=v.slice(7).trim();r.headers=$.grep(r.headers,h=>h.key.toLowerCase()!=='authorization');}else if(v.toLowerCase().startsWith('basic ')){r.authType='basic';try{const dec=atob(v.slice(6).trim()),colon=dec.indexOf(':');if(colon>0){r.authUser=dec.slice(0,colon);r.authPass=dec.slice(colon+1);}else r.authUser=dec;}catch{}r.headers=$.grep(r.headers,h=>h.key.toLowerCase()!=='authorization');}}
    if(r.body&&isJson(stripQ(r.body)))r.body=stripQ(r.body);
    return r;
}
function tokenizeCurl(s){const tokens=[];let i=0;while(i<s.length){if(s[i]===' '||s[i]==='\t'||s[i]==='\n'){i++;continue;}if(s[i]==="'"){let j=i+1;while(j<s.length&&s[j]!=="'")j++;tokens.push(s.slice(i+1,j));i=j+1;}else if(s[i]==='"'){let j=i+1;while(j<s.length){if(s[j]==='\\'&&j+1<s.length)j+=2;else if(s[j]==='"')break;else j++;}tokens.push(s.slice(i+1,j).replace(/\\"/g,'"').replace(/\\n/g,'\n').replace(/\\t/g,'\t'));i=j+1;}else{let j=i;while(j<s.length&&s[j]!==' '&&s[j]!=='\t'&&s[j]!=='\n')j++;tokens.push(s.slice(i,j));i=j;}}return tokens;}
function parseHVal(str){const colon=str.indexOf(':');if(colon<=0)return null;return{key:str.slice(0,colon).trim(),value:str.slice(colon+1).trim()};}
function stripQ(s){if(!s)return s;if((s.startsWith("'")&&s.endsWith("'"))||(s.startsWith('"')&&s.endsWith('"')))return s.slice(1,-1);return s;}
function exportCurl(){
    saveTabData(); const d=curTab()?.data; if(!d||!d.url){toast('Nothing to export');return;}
    let parts=['curl']; if(d.method&&d.method!=='GET')parts.push('-X',d.method);
    const allH=[...d.headers.filter(h=>h.key)];
    if(d.auth?.type==='bearer'&&d.auth.bearer)allH.push({key:'Authorization',value:'Bearer '+d.auth.bearer});
    if(d.auth?.type==='basic'&&d.auth.username)allH.push({key:'Authorization',value:'Basic '+btoa(d.auth.username+':'+(d.auth.password||''))});
    if(d.auth?.type==='apikey'&&d.auth.keyName)allH.push({key:d.auth.keyName,value:d.auth.keyValue});
    $.each(allH,(i,h)=>{parts.push('-H',"'"+h.key+': '+h.value+"'");});
    let url=d.url; const allP=d.params.filter(p=>p.key); if(allP.length){const qs=allP.map(p=>encodeURIComponent(p.key)+'='+encodeURIComponent(p.value)).join('&');url+=(url.includes('?')?'&':'?')+qs;}
    parts.push("'"+url+"'"); if(d.body&&d.bodyType==='json')parts.push('-d',"'"+d.body+"'");
    else if(d.bodyType==='form-data'){const fd=d.formData.filter(f=>f.key);if(fd.length){const fs=fd.map(f=>encodeURIComponent(f.key)+'='+encodeURIComponent(f.value)).join('&');parts.push('-d',"'"+fs+"'");}}
    navigator.clipboard?.writeText(parts.join(' \\\n  ')).then(()=>toast('cURL copied!')).catch(()=>{});
}

// ========= TOGGLES =========
function toggleBodyType(){const v=$('input[name="bodyType"]:checked').val();$('#body-json').toggleClass('hidden',v!=='json');$('#body-form').toggleClass('hidden',v!=='form-data');saveTabData();}
function toggleAuth(){const v=$('#auth-type').val();$.each(['auth-bearer','auth-basic','auth-apikey'],function(i,id){$('#'+id).toggleClass('hidden',!id.includes(v));});saveTabData();}

// ========= INIT =========
$(async function(){
    initTheme(); newTab();
    await renderEnvironments();
    await renderCollections();
    await renderWorkspaces();
    renderHistory();

    $(document).on('click','.tab-btn',function(){
        $('.tab-btn').removeClass('text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400 font-semibold').addClass('text-surface-400 dark:text-surface-500 border-transparent font-medium');
        $(this).addClass('text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400 font-semibold');
        $('.tab-content').addClass('hidden'); $('#tab-'+$(this).data('tab')).removeClass('hidden');
    });
    $(document).on('click','.resp-tab-btn',function(){
        $('.resp-tab-btn').removeClass('active text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400').addClass('text-surface-400 dark:text-surface-500 border-transparent');
        $(this).addClass('active text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400');
        $('.resp-content').addClass('hidden'); $('#resp-'+$(this).data('respTab')).removeClass('hidden');
        const t=$(this).data('respTab'); $('#resp-format-toggles').toggleClass('hidden',t!=='body'); if(t==='body')applyFmt(activeRespFormat);
    });
    $(document).on('click','.resp-format-btn',function(){
        $('.resp-format-btn').removeClass('active'); $(this).addClass('active'); activeRespFormat=$(this).data('format'); applyFmt(activeRespFormat);
    });
    $(document).on('change','#env-select',function(){
        const v=$(this).val(); if(v)localStorage.setItem('incognito-active-env',v); else localStorage.removeItem('incognito-active-env');
        renderEnvironments(); updateVarPreview(); updateFieldVarBadges(); toast(v?'Switched':'Deselected');
    });
    $(document).on('input','#url',function(){updateVarPreview();updateFieldVarBadges();});
    $(document).on('paste','#url',function(e){e.preventDefault();const text=(e.originalEvent.clipboardData||window.clipboardData).getData('text/plain');document.execCommand('insertText',false,text);});
    $(document).on('input','#json-body',updateFieldVarBadges);
    $(document).on('input','.kv-row input',updateFieldVarBadges);
    $(document).on('change','#workspace-select',function(){const wid=$(this).val();if(wid)localStorage.setItem('incognito-active-workspace',wid);else localStorage.removeItem('incognito-active-workspace');});
    $(document).on('keydown',function(e){if((e.ctrlKey||e.metaKey)&&e.key==='Enter'){e.preventDefault();sendReq();}});
    setInterval(()=>{const d=curTab()?.data;if(d&&d.url)saveTabData();},2000);
});
</script>