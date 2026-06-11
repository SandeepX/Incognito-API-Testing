<script>
// ========= STATE =========
let tabs = [], activeTab = null, tabCounter = 0, activeRespFormat = 'pretty', currentAbort = null;

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
function newTab(d){
    const id=++tabCounter;
    tabs.push({id,name:(d&&d.name)||'Request '+id,data:JSON.parse(JSON.stringify(d||defaultReq())),response:null});
    renderTabs(); activateTab(id); return id;
}
function renderTabs(){
    const $l=$('#tabs-list');
    $l.empty();
    $.each(tabs,(i,t)=>{
        const $d=$('<div>',{class:'flex items-center gap-1.5 px-3 py-1.5 text-xs border-r border-surface-100 dark:border-surface-700 cursor-pointer transition'+(t.id===activeTab?' bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-semibold':' text-surface-500 dark:text-surface-400 hover:bg-surface-50 dark:hover:bg-surface-700 hover:text-surface-700 dark:hover:text-surface-200')}).on('click',()=>activateTab(t.id));
        $d.append($('<span>').text(esc(t.name)));
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
    $('#method').val(d.method); $('#url').val(d.url);
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
    d.method=$('#method').val(); d.url=$('#url').val();
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
    if(!id)return null;
    const envs=JSON.parse(localStorage.getItem('incognito-envs')||'[]');
    return envs.find(e=>e.id===id)||null;
}
function subVars(s){
    if(!s||typeof s!='string')return s;
    const env=getActiveEnv(); if(!env||!env.variables)return s;
    return s.replace(/\{\{(\w+)\}\}/g,(m,k)=>{
        const v=env.variables.find(x=>x.key===k&&x.enabled!==false);
        return v?v.value:m;
    });
}

// ========= VARIABLE PREVIEW / HIGHLIGHT =========
function findVars(str){
    const vars=[]; const re=/\{\{(\w+)\}\}/g; let m;
    while((m=re.exec(str))!==null)vars.push({raw:m[0],name:m[1],idx:m.index});
    return vars;
}
function updateVarPreview(){
    const val=$('#url').val();
    const vars=findVars(val);
    const env=getActiveEnv();
    const $prev=$('#url-vars-preview');
    if(!vars.length||!env){$prev.addClass('hidden');return;}
    let html='';
    let last=0;
    $.each(vars,(i,v)=>{
        const vDef=env.variables.find(x=>x.key===v.name&&x.enabled!==false);
        html+=esc(val.slice(last,v.idx));
        if(vDef){
            html+=`<span class="var-chip var-chip-name var-tooltip" data-value="${esc(vDef.value)}">${esc(v.raw)} <span style="font-weight:400;opacity:0.7">→</span> <span class="var-chip var-chip-resolved">${esc(vDef.value)}</span></span>`;
        }else{
            html+=`<span class="var-chip var-chip-missing var-tooltip" data-value="Not found in ${esc(env.name)}">${esc(v.raw)}</span>`;
        }
        last=v.idx+v.raw.length;
    });
    html+=esc(val.slice(last));
    $prev.html(html).removeClass('hidden');
}

function updateFieldVarBadges(){
    const env=getActiveEnv();
    // Headers
    $('.kv-row input:nth-child(2)').each(function(){
        const v=findVars($(this).val()||'');
        const $badge=$(this).siblings('.field-var-badge');
        if(v.length&&env){
            if(!$badge.length){
                $badge=$('<span>',{class:'field-var-badge'});
                $(this).after($badge);
            }
            const names=v.map(x=>{
                const vd=env.variables.find(ev=>ev.key===x.name&&ev.enabled!==false);
                return vd?`<span class="var-badge var-badge-found" title="${esc(vd.value)}">${esc(x.raw)}</span>`:`<span class="var-badge var-badge-missing" title="Not found">${esc(x.raw)}</span>`;
            }).join(' ');
            $badge.html(names);
        }else if($badge.length){$badge.remove();}
    });
    // JSON body
    const bodyVars=findVars($('#json-body').val()||'');
    const $bodyBadge=$('#body-var-badge');
    if(bodyVars.length&&env){
        const names=bodyVars.map(x=>{
            const vd=env.variables.find(ev=>ev.key===x.name&&ev.enabled!==false);
            return vd?`<span class="var-badge var-badge-found" title="${esc(vd.value)}">${esc(x.raw)}</span>`:`<span class="var-badge var-badge-missing" title="Not found">${esc(x.raw)}</span>`;
        }).join(' ');
        if(!$bodyBadge.length){
            $('#body-json').append($('<div>',{id:'body-var-badge',class:'flex flex-wrap gap-1 mt-1'}).html(names));
        }else{$bodyBadge.html(names);}
    }else if($bodyBadge.length){$bodyBadge.remove();}
}

// ========= ENVIRONMENTS =========
function getEnvs(){return JSON.parse(localStorage.getItem('incognito-envs')||'[]');}
function saveEnvs(e){localStorage.setItem('incognito-envs',JSON.stringify(e));}

function renderEnvironments(){
    const envs=getEnvs(), active=localStorage.getItem('incognito-active-env');
    const $sel=$('#env-select'); $sel.empty();
    $sel.append($('<option>',{value:''}).text('No Environment'));
    $.each(envs,(i,e)=>{
        $sel.append($('<option>',{value:e.id}).text(e.name+(e.id===active?' (active)':'')));
    });
    if(active)$sel.val(active); else $sel.val('');
    // Sidebar env list
    const $el=$('#sb-environments'); $el.empty();
    if(!envs.length){
        $el.html('<div class="flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>No environments</span></div>'); return;
    }
    $.each(envs,(i,e)=>{
        const $d=$('<div>',{class:'sidebar-item p-2 rounded-md flex gap-2 items-start'+(e.id===active?' active':'')}).on('click',()=>{localStorage.setItem('incognito-active-env',e.id);renderEnvironments();toast('Active: '+e.name);});
        $d.append($('<span>',{class:'badge-dot bg-brand-500 shrink-0 mt-1'}));
        $d.append($('<div>',{class:'min-w-0 flex-1'}).append(
            $('<p>',{class:'text-xs font-semibold text-surface-700 dark:text-surface-300 truncate'}).text(e.name+(e.id===active?' (active)':'')),
            $('<p>',{class:'text-[10px] text-surface-400 dark:text-surface-500'}).text(e.variables.length+' variable'+(e.variables.length!==1?'s':''))
        ));
        $d.append($('<button>',{class:'text-red-300 dark:text-red-500 hover:text-red-500 dark:hover:text-red-400 text-sm px-0.5 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition leading-none'}).html('&times;').on('click',function(e){e.stopPropagation();deleteEnv(e.id);}));
        $el.append($d);
    });
}

function manageEnvironments(){
    const envs=getEnvs();
    let html='<div style="max-height:60vh;overflow-y:auto">';
    html+='<div class="flex gap-2 mb-2"><button class="add-env-btn" style="padding:6px 12px;background:#4f46e5;color:white;border-radius:6px;font-size:12px;border:none;cursor:pointer">+ New Environment</button></div>';
    html+='<div id="env-manager-list">';
    $.each(envs,(i,e)=>{
        html+=`<div class="env-card" style="border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:8px;background:#f8fafc">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                <input class="env-name" value="${esc(e.name)}" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600">
                <button class="del-env-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button>
            </div>
            <div class="env-vars-list">
                ${(e.variables||[]).map(v=>`<div class="ev-row" style="display:flex;gap:4px;margin-bottom:3px;align-items:center">
                    <input class="ev-key" value="${esc(v.key)}" placeholder="Key" style="flex:1;padding:3px 6px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px">
                    <input class="ev-val" value="${esc(v.value)}" placeholder="Value" style="flex:1;padding:3px 6px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px">
                    <button class="del-ev-btn" style="color:#ef4444;font-size:14px;border:none;background:none;cursor:pointer">&times;</button>
                </div>`).join('')}
            </div>
            <button class="add-ev-btn" style="font-size:11px;color:#4f46e5;border:none;background:none;cursor:pointer">+ Add variable</button>
        </div>`;
    });
    html+='</div></div>';

    Swal.fire({
        title:'Manage Environments',
        html:html,
        width:'600px',
        showCancelButton:true,
        confirmButtonText:'Save Changes',
        cancelButtonText:'Cancel',
        customClass:{confirmButton:'swal-confirm-btn',cancelButton:'swal-cancel-btn'},
        didOpen:()=>{
            $('.add-env-btn').on('click',function(){
                const $card=$('<div>',{class:'env-card',css:{border:'1px solid #e2e8f0',borderRadius:'8px',padding:'10px',marginBottom:'8px',background:'#f8fafc'}});
                $card.html(`<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px"><input class="env-name" placeholder="Environment Name" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600"><button class="del-env-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button></div><div class="env-vars-list"></div><button class="add-ev-btn" style="font-size:11px;color:#4f46e5;border:none;background:none;cursor:pointer">+ Add variable</button>`);
                $('#env-manager-list').append($card);
                $card.find('.del-env-btn').on('click',function(){$(this).closest('.env-card').remove();});
                $card.find('.add-ev-btn').on('click',function(){addEnvVarRow($(this).closest('.env-card').find('.env-vars-list'));});
            });
            $('.del-env-btn').on('click',function(){$(this).closest('.env-card').remove();});
            $('.add-ev-btn').on('click',function(){addEnvVarRow($(this).closest('.env-card').find('.env-vars-list'));});
        },
        preConfirm:()=>{
            const cards=[]; $('.env-card').each(function(){
                const name=$(this).find('.env-name').val().trim(); if(!name)return;
                const vars=[]; $(this).find('.ev-row').each(function(){
                    const k=$(this).find('.ev-key').val().trim(); if(!k)return;
                    vars.push({key:k,value:$(this).find('.ev-val').val(),enabled:true});
                });
                cards.push({name,variables:vars});
            });
            // Preserve IDs for existing envs by matching name
            const oldEnvs=getEnvs();
            const newEnvs=cards.map(c=>{
                const old=oldEnvs.find(o=>o.name===c.name);
                return{id:old?old.id:uid(),name:c.name,variables:c.variables};
            });
            saveEnvs(newEnvs);
            renderEnvironments();
            renderCollections(); // refresh sidebar env indicator
        }
    });
}
function addEnvVarRow($list){
    const $row=$('<div>',{class:'ev-row',css:{display:'flex',gap:'4px',marginBottom:'3px',alignItems:'center'}});
    $row.append($('<input>',{class:'ev-key',placeholder:'Key',css:{flex:1,padding:'3px 6px',border:'1px solid #e2e8f0',borderRadius:'4px',fontSize:'11px'}}));
    $row.append($('<input>',{class:'ev-val',placeholder:'Value',css:{flex:1,padding:'3px 6px',border:'1px solid #e2e8f0',borderRadius:'4px',fontSize:'11px'}}));
    $row.append($('<button>',{class:'del-ev-btn',html:'&times;',css:{color:'#ef4444',fontSize:'14px',border:'none',background:'none',cursor:'pointer'}}).on('click',function(){$(this).closest('.ev-row').remove();}));
    $list.append($row);
}

// ========= COLLECTIONS =========
function getColls(){return JSON.parse(localStorage.getItem('incognito-colls')||'[]');}
function saveColls(c){localStorage.setItem('incognito-colls',JSON.stringify(c));}

function renderCollections(){
    const colls=getColls();
    const $el=$('#sb-collections'); $el.empty();
    $el.append($('<div>',{class:'flex gap-1 px-2 py-1.5 border-b border-surface-100 dark:border-surface-700'}).append(
        $('<button>',{class:'text-[10px] px-2 py-1 rounded bg-brand-500 text-white hover:bg-brand-400 transition font-medium'}).text('+ Collection').on('click',createCollection),
        $('<button>',{class:'text-[10px] px-2 py-1 rounded bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-600 transition font-medium ml-auto'}).text('Import').on('click',importCollection)
    ));
    if(!colls.length){
        $el.append($('<div>',{class:'flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2'}).append(
            $('<svg>',{class:'w-6 h-6',fill:'none',stroke:'currentColor',viewBox:'0 0 24 24'}).html('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>'),
            $('<span>').text('No collections')
        )); return;
    }
    $.each(colls,(i,c)=>{
        const $c=$('<div>');
        $c.append(renderCollItem(c,i));
        $el.append($c);
    });
}
function renderCollItem(c,idx){
    const $d=$('<div>',{class:'collection-item group'});
    const $name=$('<span>',{class:'flex-1 truncate text-surface-700 dark:text-surface-300 font-medium'}).text(c.name);
    const activeEnv=getActiveEnv();
    const $actions=$('<span>',{class:'ml-auto flex gap-0.5 opacity-0 group-hover:opacity-100 transition text-surface-400'});
    $actions.append($('<button>',{class:'hover:text-brand-500 px-0.5',title:'Add Folder'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-5 4h10a2 2 0 002-2V9a2 2 0 00-2-2h-2.586A2 2 0 0012 4.414L10.586 3H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>').on('click',function(e){e.stopPropagation();addFolderToColl(idx);}));
    $actions.append($('<button>',{class:'hover:text-brand-500 px-0.5',title:'Add Request'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>').on('click',function(e){e.stopPropagation();addReqToColl(idx);}));
    $actions.append($('<button>',{class:'hover:text-blue-500 px-0.5',title:'Export'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 11l5 5 5-5M12 4v11"/></svg>').on('click',function(e){e.stopPropagation();exportColl(idx);}));
    $actions.append($('<button>',{class:'hover:text-red-500 px-0.5',title:'Delete'}).html('<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>').on('click',function(e){e.stopPropagation();deleteColl(idx);}));
    $d.append($name);
    $d.append($actions);
    $d.on('click',function(){loadCollection(idx);});
    if(c.items&&c.items.length){
        const $child=$('<div>',{class:'tree-children'});
        $.each(c.items,(j,item)=>{
            $child.append(renderCollTreeItem(item,idx,j));
        });
        $d.wrap('<div></div>');
        const $wrap=$d.parent();
        $wrap.append($child);
        return $wrap;
    }
    return $d;
}
function renderCollTreeItem(item,ci,parentPath){
    if(item.type==='folder'){
        const $d=$('<div>');
        const $h=$('<div>',{class:'flex items-center gap-1 py-0.5 text-xs text-surface-600 dark:text-surface-400 hover:text-surface-800 dark:hover:text-surface-200 cursor-pointer group rounded px-1 hover:bg-surface-50 dark:hover:bg-surface-700'});
        const $tog=$('<span>',{class:'text-surface-400 text-[10px] w-3 text-center transition-transform'}).html('&#9654;');
        $h.append($tog);
        $h.append($('<span>',{class:'text-surface-500'}).html('&#128193;'));
        $h.append($('<span>',{class:'flex-1 truncate font-medium'}).text(item.name));
        const $actions=$('<span>',{class:'ml-auto flex gap-0.5 opacity-0 group-hover:opacity-100 transition'});
        $actions.append($('<button>',{class:'hover:text-brand-500 text-[10px] px-0.5',title:'Add Request'}).html('+').on('click',function(e){e.stopPropagation();addReqToFolder(ci,parentPath);}));
        $actions.append($('<button>',{class:'hover:text-red-500 text-[10px] px-0.5',html:'&times;'}).on('click',function(e){e.stopPropagation();deleteCollItem(ci,parentPath);}));
        $h.append($actions);
        $h.on('click',function(){
            const $children=$d.find('> .tree-children'); $children.toggleClass('hidden');
            $tog.toggleClass('rotate-90');
            if($children.hasClass('hidden'))$tog.css('transform',''); else $tog.css('transform','rotate(90deg)');
        });
        $d.append($h);
        const $child=$('<div>',{class:'tree-children'});
        if(item.children){
            $.each(item.children,(k,ch)=>{
                $child.append(renderCollTreeItem(ch,ci,parentPath+'.children.'+k));
            });
        }
        $d.append($child);
        return $d;
    } else {
        const $d=$('<div>',{class:'flex items-center gap-1.5 py-0.5 px-1 text-xs cursor-pointer rounded hover:bg-surface-50 dark:hover:bg-surface-700 group'}).on('click',function(){loadCollReq(ci,parentPath);});
        $d.append($('<span>',{class:'pill '+mColor(item.request?item.request.method:'GET'),text:item.request?item.request.method:'GET'}));
        $d.append($('<span>',{class:'flex-1 truncate text-surface-700 dark:text-surface-300'}).text(item.name));
        const $actions=$('<span>',{class:'ml-auto flex gap-0.5 opacity-0 group-hover:opacity-100 transition text-surface-400'});
        $actions.append($('<button>',{class:'hover:text-red-500 text-[10px] px-0.5',html:'&times;'}).on('click',function(e){e.stopPropagation();deleteCollItem(ci,parentPath);}));
        $d.append($actions);
        return $d;
    }
}

function createCollection(){
    Swal.fire({title:'New Collection',input:'text',inputPlaceholder:'Collection name',showCancelButton:true,confirmButtonText:'Create',preConfirm:(n)=>{if(!n.trim()){Swal.showValidationMessage('Name required');}}})
        .then(r=>{if(r.isConfirmed){
            const colls=getColls();
            colls.push({id:uid(),name:r.value.trim(),items:[]});
            saveColls(colls); renderCollections(); toast('Collection created');
        }});
}
function deleteColl(idx){
    Swal.fire({title:'Delete Collection?',text:'This cannot be undone',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Delete'})
        .then(r=>{if(r.isConfirmed){
            const colls=getColls(); colls.splice(idx,1); saveColls(colls); renderCollections(); toast('Deleted');
        }});
}
function addFolderToColl(idx){
    Swal.fire({title:'New Folder',input:'text',inputPlaceholder:'Folder name',showCancelButton:true,confirmButtonText:'Create',preConfirm:(n)=>{if(!n.trim()){Swal.showValidationMessage('Name required');}}})
        .then(r=>{if(r.isConfirmed){
            const colls=getColls();
            if(!colls[idx].items)colls[idx].items=[];
            colls[idx].items.push({type:'folder',id:uid(),name:r.value.trim(),children:[]});
            saveColls(colls); renderCollections(); toast('Folder created');
        }});
}
function addReqToColl(idx){
    saveTabData();
    const d=curTab()?.data; if(!d||!d.url){toast('Open a request first');return;}
    Swal.fire({title:'Save to Collection',input:'text',inputPlaceholder:'Request name',inputValue:curTab()?.name||'',showCancelButton:true,confirmButtonText:'Save',preConfirm:(n)=>{if(!n.trim()){Swal.showValidationMessage('Name required');}}})
        .then(r=>{if(r.isConfirmed){
            const colls=getColls();
            if(!colls[idx].items)colls[idx].items=[];
            colls[idx].items.push({type:'request',id:uid(),name:r.value.trim(),request:JSON.parse(JSON.stringify(d)),savedResponse:curTab()?.response?JSON.parse(JSON.stringify(curTab().response)):null});
            saveColls(colls); renderCollections(); toast('Saved to collection');
        }});
}
function addReqToFolder(ci,path){
    saveTabData();
    const d=curTab()?.data; if(!d||!d.url){toast('Open a request first');return;}
    Swal.fire({title:'Save to Folder',input:'text',inputPlaceholder:'Request name',inputValue:curTab()?.name||'',showCancelButton:true,confirmButtonText:'Save',preConfirm:(n)=>{if(!n.trim()){Swal.showValidationMessage('Name required');}}})
        .then(r=>{if(r.isConfirmed){
            const colls=getColls();
            const parts=path.split('.');
            let obj=colls[ci];
            for(let p=0;p<parts.length;p++){if(obj[parts[p]]!==undefined)obj=obj[parts[p]]; else break;}
            if(obj&&obj.children)obj.children.push({type:'request',id:uid(),name:r.value.trim(),request:JSON.parse(JSON.stringify(d)),savedResponse:curTab()?.response?JSON.parse(JSON.stringify(curTab().response)):null});
            saveColls(colls); renderCollections(); toast('Saved to folder');
        }});
}
function deleteCollItem(ci,path){
    Swal.fire({title:'Delete?',text:'Remove this item from collection',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Delete'})
        .then(r=>{if(r.isConfirmed){
            const colls=getColls();
            const parts=path.split('.');
            const last=parseInt(parts.pop());
            let obj=colls[ci];
            for(let p=0;p<parts.length;p++){if(obj[parts[p]]!==undefined)obj=obj[parts[p]]; else break;}
            if(Array.isArray(obj)&&obj.length>last)obj.splice(last,1);
            saveColls(colls); renderCollections(); toast('Deleted');
        }});
}
function loadCollection(idx){
    const colls=getColls(); const c=colls[idx];
    Swal.fire({title:c.name,text:c.items.length+' item'+(c.items.length!==1?'s':'')+'. Click a request to load it.',icon:'info',confirmButtonText:'OK'});
}
function loadCollReq(ci,path){
    const colls=getColls();
    const parts=path.split('.');
    let obj=colls[ci];
    for(let p=0;p<parts.length;p++){if(obj[parts[p]]!==undefined)obj=obj[parts[p]]; else break;}
    if(!obj||!obj.request){toast('No request data');return;}
    const id=newTab(obj.request);
    const t=tabs.find(t=>t.id===id);
    if(t&&obj.savedResponse)t.response=JSON.parse(JSON.stringify(obj.savedResponse));
    activateTab(id);
    if(t&&t.response)loadResp();
    toast('Loaded: '+obj.name);
}
function exportColl(idx){
    const colls=getColls();
    const blob=new Blob([JSON.stringify(colls[idx],null,2)],{type:'application/json'});
    const a=document.createElement('a');
    a.href=URL.createObjectURL(blob); a.download=colls[idx].name.replace(/[^a-z0-9]/gi,'_')+'.json'; a.click();
    URL.revokeObjectURL(a.href); toast('Exported: '+colls[idx].name);
}
function importCollection(){
    const input=$('<input>',{type:'file',accept:'.json',css:{display:'none'}});
    input.on('change',function(){
        if(!this.files[0])return;
        const reader=new FileReader();
        reader.onload=function(e){
            try{
                const data=JSON.parse(e.target.result);
                if(!data.name||!Array.isArray(data.items)){toast('Invalid collection format');return;}
                const colls=getColls();
                data.id=uid(); colls.push(data); saveColls(colls); renderCollections(); toast('Imported: '+data.name);
            }catch{toast('Invalid JSON file');}
        };
        reader.readAsText(this.files[0]);
    });
    input.click();
}

// ========= WORKSPACES =========
function getWorks(){return JSON.parse(localStorage.getItem('incognito-works')||'[]');}
function saveWorks(w){localStorage.setItem('incognito-works',JSON.stringify(w));}
function renderWorkspaces(){
    const works=getWorks();
    const $sel=$('#workspace-select'); $sel.empty();
    $sel.append($('<option>',{value:''}).text('No Workspace'));
    $.each(works,(i,w)=>{
        $sel.append($('<option>',{value:w.id}).text(w.name));
    });
}
function manageWorkspaces(){
    const works=getWorks(); const colls=getColls();
    let html='<div style="max-height:60vh;overflow-y:auto">';
    html+='<div class="flex gap-2 mb-2"><button class="add-ws-btn" style="padding:6px 12px;background:#4f46e5;color:white;border-radius:6px;font-size:12px;border:none;cursor:pointer">+ New Workspace</button></div>';
    html+='<div id="ws-manager-list">';
    if(works.length){
        $.each(works,(i,w)=>{
            html+=`<div class="ws-card" data-id="${w.id}" style="border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:8px;background:#f8fafc">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <input class="ws-name" value="${esc(w.name)}" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600">
                    <button class="del-ws-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button>
                </div>
                <div style="font-size:11px;color:#64748b;margin-bottom:4px">Collections in workspace:</div>
                <div class="ws-colls">`;

            const wc=w.collectionIds||[];
            html+=colls.map((c,j)=>{
                const checked=wc.includes(c.id)?'checked':'';
                return `<label style="display:flex;align-items:center;gap:4px;font-size:11px;margin:2px 0;cursor:pointer"><input type="checkbox" class="ws-coll-chk" value="${c.id}" ${checked}>${esc(c.name)}</label>`;
            }).join('');

            html+=`</div></div>`;
        });
    } else {
        html+='<p style="color:#94a3b8;font-size:12px">No workspaces yet</p>';
    }
    html+='</div></div>';

    Swal.fire({
        title:'Manage Workspaces',
        html:html,
        width:'500px',
        showCancelButton:true,
        confirmButtonText:'Save',
        customClass:{confirmButton:'swal-confirm-btn',cancelButton:'swal-cancel-btn'},
        didOpen:()=>{
            $('.add-ws-btn').on('click',function(){
                const id=uid();
                const $card=$('<div>',{class:'ws-card',css:{border:'1px solid #e2e8f0',borderRadius:'8px',padding:'10px',marginBottom:'8px',background:'#f8fafc'},'data-id':id});
                let chkHtml=colls.map(c=>`<label style="display:flex;align-items:center;gap:4px;font-size:11px;margin:2px 0;cursor:pointer"><input type="checkbox" class="ws-coll-chk" value="${c.id}">${esc(c.name)}</label>`).join('');
                $card.html(`<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px"><input class="ws-name" placeholder="Workspace Name" style="flex:1;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px;font-weight:600"><button class="del-ws-btn" style="color:#ef4444;font-size:16px;border:none;background:none;cursor:pointer">&times;</button></div><div style="font-size:11px;color:#64748b;margin-bottom:4px">Collections:</div><div class="ws-colls">${chkHtml}</div>`);
                $('#ws-manager-list').append($card);
                $card.find('.del-ws-btn').on('click',function(){$(this).closest('.ws-card').remove();});
            });
            $('.del-ws-btn').on('click',function(){$(this).closest('.ws-card').remove();});
        },
        preConfirm:()=>{
            const ws=[]; $('.ws-card').each(function(){
                const name=$(this).find('.ws-name').val().trim(); if(!name)return;
                const ids=[]; $(this).find('.ws-coll-chk:checked').each(function(){ids.push($(this).val());});
                ws.push({id:$(this).attr('data-id')||uid(),name,collectionIds:ids});
            });
            saveWorks(ws); renderWorkspaces(); toast('Workspaces saved');
        }
    });
}

// ========= HISTORY =========
function getHist(){return JSON.parse(localStorage.getItem('apiTesterHistory')||'[]');}
function saveHist(h){localStorage.setItem('apiTesterHistory',JSON.stringify(h));}
function renderHistory(){
    const hist=getHist();
    const $el=$('#sb-history'); $el.empty();
    // Header with clear
    $el.append($('<div>',{class:'flex items-center px-2 py-1.5 border-b border-surface-100 dark:border-surface-700'}).append(
        $('<span>',{class:'text-[10px] font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider'}).text('Recent'),
        $('<button>',{class:'ml-auto text-[10px] px-2 py-0.5 rounded text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition font-medium'}).text('Clear All').on('click',clearHistory)
    ));
    if(!hist.length){
        $el.append($('<div>',{class:'flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2'}).append(
            $('<svg>',{class:'w-6 h-6',fill:'none',stroke:'currentColor',viewBox:'0 0 24 24'}).html('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'),
            $('<span>').text('No requests yet')
        )); return;
    }
    $.each(hist,(i,h)=>{
        const $d=$('<div>',{class:'sidebar-item p-2 rounded-md flex gap-2 items-start'}).on('click',()=>{const d=curTab()?.data;if(d){d.url=h.url;d.method=h.method;loadTabData();}});
        $d.append($('<span>',{class:'pill '+mColor(h.method)+' shrink-0 mt-0.5 text-[10px]'}).text(h.method));
        $d.append($('<div>',{class:'min-w-0 flex-1'}).append(
            $('<p>',{class:'truncate text-surface-700 dark:text-surface-300 text-xs'}).text(h.url),
            $('<span>',{class:'text-[10px] '+(h.status>=400?'text-red-400':'text-green-500')+' font-medium'}).html((h.status||'ERR')+' &middot; '+(h.time||'&mdash;')+'ms')
        ));
        $el.append($d);
    });
}
function clearHistory(){
    Swal.fire({title:'Clear History?',text:'Remove all request history',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Clear'})
        .then(r=>{if(r.isConfirmed){saveHist([]);renderHistory();toast('History cleared');}});
}
function addHistory(data,resp){
    const hist=getHist();
    hist.unshift({method:data.method,url:data.url,status:resp?.status,time:resp?.time,ts:Date.now()});
    if(hist.length>50)hist.length=50;
    saveHist(hist); renderHistory();
}

// ========= SEND REQUEST =========
async function sendReq(){
    saveTabData();
    const d=curTab()?.data; if(!d||!d.url){toast('Enter a URL first');return;}

    // Cancel any previous
    if(currentAbort){currentAbort.abort();currentAbort=null;}
    currentAbort=new AbortController();

    $('#send-text').text('Sending');
    $('#send-icon').html('<span class="spinner"></span>');
    $('#cancel-btn').removeClass('hidden');

    const allHeaders=[...d.headers.filter(h=>h.key)];
    const allParams=d.params.filter(p=>p.key);
    let url=subVars(d.url);
    if(allParams.length){
        const qs=allParams.map(p=>encodeURIComponent(p.key)+'='+encodeURIComponent(p.value)).join('&');
        url+=(url.includes('?')?'&':'?')+qs;
    }
    if(d.auth?.type==='bearer'&&d.auth.bearer)allHeaders.push({key:'Authorization',value:'Bearer '+d.auth.bearer});
    if(d.auth?.type==='basic'&&d.auth.username)allHeaders.push({key:'Authorization',value:'Basic '+btoa(d.auth.username+':'+(d.auth.password||''))});
    if(d.auth?.type==='apikey'&&d.auth.keyName)allHeaders.push({key:d.auth.keyName,value:d.auth.keyValue});

    // Substitute vars in headers
    $.each(allHeaders,(i,h)=>{h.key=subVars(h.key);h.value=subVars(h.value);});

    let body=subVars(d.body);
    const bodyType=d.bodyType;

    try{
        const res=await fetch('/api-tester/proxy',{
            method:'POST',
            signal:currentAbort.signal,
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')||'{{ csrf_token() }}'},
            body:JSON.stringify({method:d.method,url,headers:allHeaders,body,bodyType:bodyType==='json'?'json':bodyType==='form-data'?'form-data':'none',formData:d.formData}),
        });
        const json=await res.json();
        resetSendBtn();
        if(json.error){showResp({status:0,statusText:'Error',headers:{},body:json.error,size:0,time:json.time||0});toast('Error: '+json.error);return;}
        showResp(json);
        addHistory(d,json);
    }catch(e){
        resetSendBtn();
        if(e.name==='AbortError'){toast('Request cancelled');return;}
        showResp({status:0,statusText:'Error',headers:{},body:e.message,size:0,time:0});
        toast(e.message.includes('Failed to fetch')?'Connection failed':'Network Error');
    }
}
function cancelReq(){
    if(currentAbort){currentAbort.abort();currentAbort=null;}
}
function resetSendBtn(){
    currentAbort=null;
    $('#send-text').text('Send');
    $('#send-icon').html('<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>');
    $('#cancel-btn').addClass('hidden');
}

// ========= SHOW RESPONSE =========
function showResp(r){
    const d=curTab(); if(d)d.response=r;
    const statusEl=$('#resp-status');
    const color=r.status>=200&&r.status<300?'text-green-600 dark:text-green-400':r.status>=400?'text-red-500 dark:text-red-400':'text-surface-500 dark:text-surface-400';
    statusEl.html('Status: <span class="'+color+' font-semibold">'+(r.status||'&mdash;')+'</span> '+(r.statusText||''));
    $('#resp-time').html('Time: <span class="text-surface-700 dark:text-surface-300 font-semibold">'+(r.time||0)+'ms</span>');
    $('#resp-size').html('Size: <span class="text-surface-700 dark:text-surface-300 font-semibold">'+fmtSize(r.size||0)+'</span>');

    const hEl=$('#resp-headers');
    if(r.headers&&Object.keys(r.headers).length){
        hEl.empty();
        $.each(r.headers,(k,v)=>{
            hEl.append($('<div>',{class:'flex gap-2 py-1 border-b border-surface-100 dark:border-surface-700'}).append(
                $('<span>',{class:'font-medium text-surface-600 dark:text-surface-300 shrink-0 min-w-[200px]'}).text(esc(k)),
                $('<span>',{class:'text-surface-500 dark:text-surface-400 break-all font-mono text-[11px]'}).text(esc(v))
            ));
        });
    } else hEl.html('<p class="text-surface-400 dark:text-surface-500 text-xs">No headers</p>');

    $('#resp-cookies').html(r.headers?.['set-cookie']?'<pre class="text-xs font-mono leading-relaxed">'+esc(r.headers['set-cookie'])+'</pre>':'<p class="text-surface-400 dark:text-surface-500 text-xs">No cookies</p>');

    applyFmt(activeRespFormat);
}
function loadResp(){
    const r=curTab()?.response;
    if(r)showResp(r); else clearResp();
}
function clearResp(){
    $('#resp-status').html('Status: <span class="text-surface-400 dark:text-surface-500">&mdash;</span>');
    $('#resp-time').html('Time: <span class="text-surface-400 dark:text-surface-500">&mdash;</span>');
    $('#resp-size').html('Size: <span class="text-surface-400 dark:text-surface-500">&mdash;</span>');
    $('#resp-body').html('<div class="flex items-center justify-center h-full text-surface-300 dark:text-surface-600 text-sm font-medium">Send a request to see the response</div>');
    $('#resp-headers').empty(); $('#resp-cookies').empty(); $('#resp-preview').attr('src','about:blank');
}
function copyResp(){
    const t=$('#resp-body').text().trim();
    if(!t){toast('Nothing to copy');return;}
    navigator.clipboard?.writeText(t).then(()=>toast('Copied!')).catch(()=>{});
}
function applyFmt(fmt){
    const r=curTab()?.response; if(!r)return;
    const $body=$('#resp-body'), $prev=$('#resp-preview');
    $body.toggleClass('hidden',fmt==='preview'); $prev.toggleClass('hidden',fmt!=='preview');
    if(fmt==='pretty'){
        if(isJson(r.body))$body.html('<pre class="text-xs font-mono leading-relaxed">'+synHl(JSON.stringify(JSON.parse(r.body),null,2))+'</pre>');
        else $body.html('<pre class="text-xs font-mono leading-relaxed whitespace-pre-wrap">'+esc(r.body||'(empty)')+'</pre>');
    }else if(fmt==='raw')$body.html('<pre class="text-xs font-mono leading-relaxed whitespace-pre-wrap">'+esc(r.body||'(empty)')+'</pre>');
    else if(fmt==='preview'){try{$prev[0].src='data:text/html;charset=utf-8,'+encodeURIComponent(r.body||'');}catch{$prev[0].src='about:blank';}}
}
function saveResp(){
    const d=curTab()?.data, r=curTab()?.response;
    if(!d||!d.url){toast('Send a request first');return;}
    if(!r){toast('No response to save');return;}
    Swal.fire({title:'Save Response',input:'text',inputPlaceholder:'Request name',inputValue:curTab()?.name||'',showCancelButton:true,confirmButtonText:'Save',
        preConfirm:(n)=>{
            if(!n.trim()){Swal.showValidationMessage('Name required');return;}
            // Save to a special saved-responses storage
            const saved=JSON.parse(localStorage.getItem('incognito-saved-resp')||'[]');
            saved.push({id:uid(),name:n.trim(),request:JSON.parse(JSON.stringify(d)),response:JSON.parse(JSON.stringify(r)),ts:Date.now()});
            localStorage.setItem('incognito-saved-resp',JSON.stringify(saved));
            toast('Response saved');
        }
    });
}

// ========= SIDEBAR =========
function showSidebar(sb){
    ['history','collections','environments'].forEach(s=>{
        $('#sb-'+s).toggleClass('hidden',s!==sb);
        const $btn=$('#sb-'+s+'-btn');
        $btn.toggleClass('text-brand-600 dark:text-brand-400',s===sb).toggleClass('text-surface-400 dark:text-surface-500',s!==sb)
            .toggleClass('border-brand-500 dark:border-brand-400',s===sb).toggleClass('border-transparent',s!==sb);
    });
}

// ========= CURL IMPORT/EXPORT =========
function importCurl(){
    $('#curl-input').val('');
    $('#curl-modal').removeClass('hidden');
    setTimeout(()=>$('#curl-input').trigger('focus'),100);
}
function closeCurlModal(){$('#curl-modal').addClass('hidden');}
function parseAndImportCurl(){
    const raw=$('#curl-input').val().trim();
    if(!raw){toast('Paste a cURL command first');return;}
    const p=parseCurl(raw);
    if(!p||!p.url){toast('Could not parse cURL command');return;}
    const d=curTab()?.data; if(!d)return;
    d.method=p.method||'GET'; d.url=p.url; d.headers=p.headers||[]; d.body=p.body||''; d.bodyType=p.body?'json':'none';
    if(p.authType){d.auth.type=p.authType;
        if(p.authType==='bearer')d.auth.bearer=p.authValue||'';
        if(p.authType==='basic'){d.auth.username=p.authUser||'';d.auth.password=p.authPass||'';}
        if(p.authType==='apikey'){d.auth.keyName=p.authKey||'X-API-Key';d.auth.keyValue=p.authValue||'';}
    }
    loadTabData(); closeCurlModal(); toast('Imported from cURL');
}
function parseCurl(str){
    let s=str.replace(/\\\n/g,' ').replace(/\\\r\n/g,' ').trim();
    const r={method:'GET',url:'',headers:[],body:'',authType:null,authValue:null,authUser:null,authPass:null,authKey:null};
    const tokens=tokenizeCurl(s);
    let url='',i=0;
    while(i<tokens.length){
        const t=tokens[i];
        if(t==='curl'){i++;continue;}
        if(t.startsWith('-')){
            if((t==='-X'||t==='--request')&&i+1<tokens.length){r.method=tokens[i+1].toUpperCase();i+=2;continue;}
            if((t==='-H'||t==='--header')&&i+1<tokens.length){
                const hv=parseHVal(tokens[i+1]); if(hv)r.headers.push(hv); i+=2; continue;
            }
            if((t==='-d'||t==='--data'||t==='--data-raw'||t==='--data-binary')&&i+1<tokens.length){
                r.body=tokens[i+1]; if(r.method==='GET')r.method='POST'; i+=2; continue;
            }
            if((t==='-u'||t==='--user')&&i+1<tokens.length){
                const u=tokens[i+1], colon=u.indexOf(':');
                if(colon>0){r.authType='basic';r.authUser=u.slice(0,colon);r.authPass=u.slice(colon+1);}else{r.authType='basic';r.authUser=u;r.authPass='';}
                i+=2; continue;
            }
            if((t==='-b'||t==='--cookie')&&i+1<tokens.length){r.headers.push({key:'Cookie',value:tokens[i+1]});i+=2;continue;}
            if(t==='-k'||t==='--insecure'){i++;continue;}
            if(t.includes('=')){i++;continue;}
            if(i+1<tokens.length&&!tokens[i+1].startsWith('-'))i+=2; else i++;
            continue;
        }
        if(!url)url=t;
        i++;
    }
    url=stripQ(url); r.url=url;
    const authH=r.headers.find(h=>h.key.toLowerCase()==='authorization');
    if(authH&&!r.authType){
        const v=authH.value.trim();
        if(v.toLowerCase().startsWith('bearer ')){r.authType='bearer';r.authValue=v.slice(7).trim();r.headers=$.grep(r.headers,h=>h.key.toLowerCase()!=='authorization');}
        else if(v.toLowerCase().startsWith('basic ')){r.authType='basic';try{const dec=atob(v.slice(6).trim()),colon=dec.indexOf(':');if(colon>0){r.authUser=dec.slice(0,colon);r.authPass=dec.slice(colon+1);}else r.authUser=dec;}catch{}r.headers=$.grep(r.headers,h=>h.key.toLowerCase()!=='authorization');}
    }
    if(r.body&&isJson(stripQ(r.body)))r.body=stripQ(r.body);
    return r;
}
function tokenizeCurl(s){
    const tokens=[]; let i=0;
    while(i<s.length){
        if(s[i]===' '||s[i]==='\t'||s[i]==='\n'){i++;continue;}
        if(s[i]==="'"){let j=i+1;while(j<s.length&&s[j]!=="'")j++;tokens.push(s.slice(i+1,j));i=j+1;}
        else if(s[i]==='"'){let j=i+1;while(j<s.length){if(s[j]==='\\'&&j+1<s.length)j+=2;else if(s[j]==='"')break;else j++;}tokens.push(s.slice(i+1,j).replace(/\\"/g,'"').replace(/\\n/g,'\n').replace(/\\t/g,'\t'));i=j+1;}
        else{let j=i;while(j<s.length&&s[j]!==' '&&s[j]!=='\t'&&s[j]!=='\n')j++;tokens.push(s.slice(i,j));i=j;}
    }
    return tokens;
}
function parseHVal(str){const colon=str.indexOf(':');if(colon<=0)return null;return{key:str.slice(0,colon).trim(),value:str.slice(colon+1).trim()};}
function stripQ(s){if(!s)return s;if((s.startsWith("'")&&s.endsWith("'"))||(s.startsWith('"')&&s.endsWith('"')))return s.slice(1,-1);return s;}
function exportCurl(){
    saveTabData();
    const d=curTab()?.data; if(!d||!d.url){toast('Nothing to export');return;}
    let parts=['curl'];
    if(d.method&&d.method!=='GET')parts.push('-X',d.method);
    const allH=[...d.headers.filter(h=>h.key)];
    if(d.auth?.type==='bearer'&&d.auth.bearer)allH.push({key:'Authorization',value:'Bearer '+d.auth.bearer});
    if(d.auth?.type==='basic'&&d.auth.username)allH.push({key:'Authorization',value:'Basic '+btoa(d.auth.username+':'+(d.auth.password||''))});
    if(d.auth?.type==='apikey'&&d.auth.keyName)allH.push({key:d.auth.keyName,value:d.auth.keyValue});
    $.each(allH,(i,h)=>{parts.push('-H',"'"+h.key+': '+h.value+"'");});
    let url=d.url;
    const allP=d.params.filter(p=>p.key);
    if(allP.length){const qs=allP.map(p=>encodeURIComponent(p.key)+'='+encodeURIComponent(p.value)).join('&');url+=(url.includes('?')?'&':'?')+qs;}
    parts.push("'"+url+"'");
    if(d.body&&d.bodyType==='json')parts.push('-d',"'"+d.body+"'");
    else if(d.bodyType==='form-data'){const fd=d.formData.filter(f=>f.key);if(fd.length){const fs=fd.map(f=>encodeURIComponent(f.key)+'='+encodeURIComponent(f.value)).join('&');parts.push('-d',"'"+fs+"'");}}
    const cmd=parts.join(' \\\n  ');
    navigator.clipboard?.writeText(cmd).then(()=>toast('cURL copied!')).catch(()=>{});
}

// ========= TOGGLES =========
function toggleBodyType(){
    const v=$('input[name="bodyType"]:checked').val();
    $('#body-json').toggleClass('hidden',v!=='json'); $('#body-form').toggleClass('hidden',v!=='form-data');
    saveTabData();
}
function toggleAuth(){
    const v=$('#auth-type').val();
    $.each(['auth-bearer','auth-basic','auth-apikey'],function(i,id){$('#'+id).toggleClass('hidden',!id.includes(v));});
    saveTabData();
}

// ========= INIT =========
$(function(){
    initTheme();
    newTab();
    renderHistory();
    renderEnvironments();
    renderCollections();
    renderWorkspaces();

    // Request tab toggles
    $(document).on('click','.tab-btn',function(){
        $('.tab-btn').removeClass('text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400 font-semibold').addClass('text-surface-400 dark:text-surface-500 border-transparent font-medium');
        $(this).addClass('text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400 font-semibold').removeClass('text-surface-400 dark:text-surface-500 border-transparent font-medium');
        $('.tab-content').addClass('hidden');
        $('#tab-'+$(this).data('tab')).removeClass('hidden');
    });

    // Response tab toggles
    $(document).on('click','.resp-tab-btn',function(){
        $('.resp-tab-btn').removeClass('active text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400').addClass('text-surface-400 dark:text-surface-500 border-transparent');
        $(this).addClass('active text-brand-600 dark:text-brand-400 border-brand-500 dark:border-brand-400');
        $('.resp-content').addClass('hidden');
        $('#resp-'+$(this).data('respTab')).removeClass('hidden');
        const t=$(this).data('respTab');
        $('#resp-format-toggles').toggleClass('hidden',t!=='body');
        if(t==='body')applyFmt(activeRespFormat);
    });

    // Response format toggles
    $(document).on('click','.resp-format-btn',function(){
        $('.resp-format-btn').removeClass('active');
        $(this).addClass('active');
        activeRespFormat=$(this).data('format');
        applyFmt(activeRespFormat);
    });

    // Environment selector
    $(document).on('change','#env-select',function(){
        const v=$(this).val();
        if(v){localStorage.setItem('incognito-active-env',v);}else{localStorage.removeItem('incognito-active-env');}
        renderEnvironments();
        updateVarPreview(); updateFieldVarBadges();
        toast(v?'Environment switched':'Environment deselected');
    });

    // Variable preview & badges
    $(document).on('input','#url',updateVarPreview);
    $(document).on('input','#json-body',updateFieldVarBadges);
    $(document).on('input','.kv-row input',updateFieldVarBadges);

    // Workspace selector
    $(document).on('change','#workspace-select',function(){
        const wid=$(this).val();
        if(wid){localStorage.setItem('incognito-active-workspace',wid);toast('Workspace switched');}
        else{localStorage.removeItem('incognito-active-workspace');toast('Workspace deselected');}
    });

    // Keyboard shortcut
    $(document).on('keydown',function(e){
        if((e.ctrlKey||e.metaKey)&&e.key==='Enter'){e.preventDefault();sendReq();}
    });

    // Auto-save
    setInterval(()=>{const d=curTab()?.data;if(d&&d.url)saveTabData();},2000);
});
</script>
