<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Incognito Testing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81' },
                        surface: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a' }
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; }
        .tab-btn { transition: color 0.15s ease; }
        .resp-format-btn { transition: all 0.15s ease; }
        .resp-format-btn.active { background: #4f46e5; color: white; box-shadow: 0 1px 3px rgba(79,70,229,0.3); }
        .dark .resp-format-btn.active { background: #6366f1; }
        .kv-row { display: flex; gap: 6px; align-items: center; }
        .kv-row input { flex: 1; }
        .fade-in { animation: fadeIn 0.2s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        #response-body { white-space: pre-wrap; word-break: break-word; font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace; font-size: 13px; line-height: 1.65; tab-size: 2; }
        #response-body .json-key { color: #881391; }
        .dark #response-body .json-key { color: #d183e8; }
        #response-body .json-string { color: #0b7500; }
        .dark #response-body .json-string { color: #6ee7b7; }
        #response-body .json-number { color: #994500; }
        .dark #response-body .json-number { color: #fbbf24; }
        #response-body .json-bool { color: #2563eb; }
        .dark #response-body .json-bool { color: #60a5fa; }
        #response-body .json-null { color: #94a3b8; font-style: italic; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        .sidebar-item { transition: all 0.15s ease; border-left: 2px solid transparent; }
        .sidebar-item:hover { background: rgba(99,102,241,0.07); border-left-color: #a5b4fc; }
        .sidebar-item.active { background: rgba(99,102,241,0.1); border-left-color: #6366f1; }
        .pill { @apply text-xs px-2 py-0.5 rounded font-medium; letter-spacing: 0.02em; }
        .spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.6s linear infinite; display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #response-preview { width: 100%; height: 100%; border: none; background: white; }
        .dark #response-preview { background: #1e293b; }
        .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(12px); background: #1e293b; color: white; padding: 10px 24px; border-radius: 10px; font-size: 13px; z-index: 999; opacity: 0; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); pointer-events: none; box-shadow: 0 8px 32px rgba(0,0,0,0.2); }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .method-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; padding-right: 24px; appearance: none; }
        .bg-noise { background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.015'/%3E%3C/svg%3E"); }
        input::placeholder, textarea::placeholder { color: #94a3b8; }
        .dark .method-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748b'/%3E%3C/svg%3E"); }
        .theme-btn { transition: all 0.2s ease; }
        .theme-btn:hover { transform: scale(1.1); }
    </style>
</head>
<body class="bg-surface-50 dark:bg-surface-900 text-surface-800 dark:text-surface-100 h-screen flex flex-col overflow-hidden selection:bg-brand-200 dark:selection:bg-brand-800 selection:text-brand-900 dark:selection:text-brand-200">

    <div id="toast" class="toast"></div>

    <!-- Top Bar -->
    <header class="bg-white dark:bg-surface-800 border-b border-surface-200 dark:border-surface-700 px-4 lg:px-5 py-2 flex items-center gap-3 shrink-0 shadow-sm z-10">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 rounded-xl flex items-center justify-center text-white text-xs font-extrabold shadow-sm shadow-brand-200 dark:shadow-brand-800">IT</div>
            <div>
                <span class="font-bold text-surface-800 dark:text-surface-100 text-sm tracking-tight">Incognito Testing</span>
                <span class="hidden sm:inline text-[10px] text-surface-400 dark:text-surface-500 font-medium ml-2 px-2 py-0.5 bg-surface-100 dark:bg-surface-700 rounded-full">v1.0</span>
            </div>
        </div>
        <div class="ml-auto flex items-center gap-2 text-xs">
            <button id="theme-toggle" onclick="toggleTheme()" class="theme-btn text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 px-2 py-1.5 rounded-lg transition flex items-center gap-1.5 font-medium" title="Toggle theme">
                <span id="theme-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </span>
            </button>
            <button onclick="saveCurrent()" class="text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                Save
            </button>
            <span id="status-bar" class="flex items-center gap-1.5 text-surface-400 dark:text-surface-500 px-2">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block shadow-sm shadow-green-200 dark:shadow-green-800"></span>
                <span class="hidden sm:inline">Ready</span>
            </span>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-60 bg-white dark:bg-surface-800 border-r border-surface-200 dark:border-surface-700 shrink-0 flex flex-col overflow-hidden hidden md:flex shadow-sm">
            <div class="flex border-b border-surface-100 dark:border-surface-700 text-xs font-medium">
                <button onclick="showSidebar('history')" id="sb-history-btn" class="flex-1 py-2.5 text-center hover:bg-surface-50 dark:hover:bg-surface-700 border-b-2 border-brand-500 text-brand-600 transition font-semibold">History</button>
                <button onclick="showSidebar('saved')" id="sb-saved-btn" class="flex-1 py-2.5 text-center hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-400 dark:text-surface-500 border-b-2 border-transparent transition font-medium">Saved</button>
            </div>
            <div id="sb-history" class="flex-1 overflow-y-auto p-2 space-y-0.5 text-xs"></div>
            <div id="sb-saved" class="flex-1 overflow-y-auto p-2 space-y-0.5 text-xs hidden"></div>
            <div class="p-2.5 border-t border-surface-100 dark:border-surface-700 text-[10px] text-surface-400 dark:text-surface-500 text-center bg-surface-50/50 dark:bg-surface-800/50">Stored in browser localStorage</div>
        </aside>

        <!-- Main -->
        <main class="flex-1 flex flex-col overflow-hidden bg-surface-50/50 dark:bg-surface-900/50">
            <!-- Request Tabs Bar -->
            <div class="bg-white dark:bg-surface-800 border-b border-surface-200 dark:border-surface-700 flex items-center px-2 shrink-0 overflow-x-auto" id="tabs-container">
                <div class="flex" id="tabs-list"></div>
                <button onclick="newTab()" class="px-2.5 py-1.5 text-surface-400 dark:text-surface-500 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 rounded-md ml-1 transition font-medium text-sm leading-none" title="New Tab">+</button>
            </div>

            <!-- Request Panel -->
            <section class="bg-white dark:bg-surface-800 border-b border-surface-200 dark:border-surface-700 p-3 lg:p-4 overflow-y-auto max-h-[45vh] space-y-3" id="request-panel">
                <!-- URL Bar -->
                <div class="flex gap-2">
                    <select id="method" class="method-select border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-2 text-xs font-bold w-auto min-w-[90px] focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-100 cursor-pointer">
                        <option class="text-green-600 dark:text-green-400">GET</option>
                        <option class="text-blue-600 dark:text-blue-400">POST</option>
                        <option class="text-orange-600 dark:text-orange-400">PUT</option>
                        <option class="text-yellow-600 dark:text-yellow-400">PATCH</option>
                        <option class="text-red-600 dark:text-red-400">DELETE</option>
                        <option class="text-surface-500 dark:text-surface-400">HEAD</option>
                        <option class="text-purple-600 dark:text-purple-400">OPTIONS</option>
                    </select>
                    <div class="flex-1 relative group">
                        <input id="url" type="text" placeholder="https://api.example.com/endpoint"
                               class="w-full border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 hover:bg-white dark:hover:bg-surface-600 transition-colors font-mono tracking-tight text-surface-800 dark:text-surface-100">
                    </div>
                    <button onclick="sendRequest()" class="bg-brand-600 dark:bg-brand-500 text-white px-6 py-2 rounded-lg text-xs font-semibold hover:bg-brand-500 dark:hover:bg-brand-400 active:scale-[0.97] transition-all flex items-center gap-2 shadow-sm shadow-brand-200 dark:shadow-brand-800 hover:shadow-brand-300/30">
                        <span id="send-icon">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                        <span id="send-text">Send</span>
                    </button>
                </div>

                <!-- Request Tabs -->
                <div class="flex gap-5 text-xs border-b border-surface-100 dark:border-surface-700">
                    <button class="tab-btn pb-1.5 font-semibold text-brand-600 dark:text-brand-400 border-b-2 border-brand-500 dark:border-brand-400" data-tab="params">Params</button>
                    <button class="tab-btn pb-1.5 font-medium text-surface-400 dark:text-surface-500 hover:text-surface-600 dark:hover:text-surface-300 border-b-2 border-transparent" data-tab="headers">Headers</button>
                    <button class="tab-btn pb-1.5 font-medium text-surface-400 dark:text-surface-500 hover:text-surface-600 dark:hover:text-surface-300 border-b-2 border-transparent" data-tab="body">Body</button>
                    <button class="tab-btn pb-1.5 font-medium text-surface-400 dark:text-surface-500 hover:text-surface-600 dark:hover:text-surface-300 border-b-2 border-transparent" data-tab="auth">Auth</button>
                </div>

                <div id="tab-params" class="tab-content space-y-1.5">
                    <div id="params-list"></div>
                    <button onclick="addParam()" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add param</button>
                </div>
                <div id="tab-headers" class="tab-content space-y-1.5 hidden">
                    <div id="headers-list"></div>
                    <button onclick="addHeader()" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add header</button>
                </div>
                <div id="tab-body" class="tab-content space-y-2 hidden">
                    <div class="flex gap-4 text-xs">
                        <label class="flex items-center gap-1.5 cursor-pointer group"><input type="radio" name="bodyType" value="none" checked onchange="toggleBodyType()" class="accent-brand-600 dark:accent-brand-400"> <span class="text-surface-700 dark:text-surface-300 group-hover:text-surface-800 dark:group-hover:text-surface-200">None</span></label>
                        <label class="flex items-center gap-1.5 cursor-pointer group"><input type="radio" name="bodyType" value="json" onchange="toggleBodyType()" class="accent-brand-600 dark:accent-brand-400"> <span class="text-surface-700 dark:text-surface-300 group-hover:text-surface-800 dark:group-hover:text-surface-200">JSON</span></label>
                        <label class="flex items-center gap-1.5 cursor-pointer group"><input type="radio" name="bodyType" value="form-data" onchange="toggleBodyType()" class="accent-brand-600 dark:accent-brand-400"> <span class="text-surface-700 dark:text-surface-300 group-hover:text-surface-800 dark:group-hover:text-surface-200">Form Data</span></label>
                    </div>
                    <div id="body-json" class="hidden">
                        <textarea id="json-body" rows="4" class="w-full border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-2.5 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors leading-relaxed" placeholder='{"key": "value"}'></textarea>
                    </div>
                    <div id="body-form" class="hidden space-y-1.5">
                        <div id="form-list"></div>
                        <button onclick="addFormField()" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add field</button>
                    </div>
                </div>
                <div id="tab-auth" class="tab-content space-y-2 hidden">
                    <select id="auth-type" onchange="toggleAuth()" class="border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-1.5 text-xs w-40 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-100">
                        <option value="none">No Auth</option>
                        <option value="bearer">Bearer Token</option>
                        <option value="basic">Basic Auth</option>
                        <option value="apikey">API Key</option>
                    </select>
                    <div id="auth-bearer" class="hidden"><input id="auth-bearer-token" placeholder="Token" class="w-full border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors"></div>
                    <div id="auth-basic" class="hidden flex gap-2"><input id="auth-username" placeholder="Username" class="flex-1 border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors"><input id="auth-password" type="password" placeholder="Password" class="flex-1 border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors"></div>
                    <div id="auth-apikey" class="hidden flex gap-2"><input id="auth-key-name" placeholder="Key name" value="X-API-Key" class="flex-1 border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors"><input id="auth-key-value" placeholder="Value" class="flex-1 border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors"></div>
                </div>
            </section>

            <!-- Response Panel -->
            <section class="flex-1 bg-white dark:bg-surface-800 overflow-hidden flex flex-col shadow-inner">
                <!-- Status Bar -->
                <div class="flex items-center justify-between px-4 lg:px-5 py-2.5 border-b border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800/80 shrink-0">
                    <div class="flex items-center gap-5 text-xs font-medium">
                        <span id="resp-status" class="text-surface-400 dark:text-surface-500">Status: <span class="text-surface-500 dark:text-surface-400">—</span></span>
                        <span id="resp-time" class="text-surface-400 dark:text-surface-500">Time: <span class="text-surface-500 dark:text-surface-400">—</span></span>
                        <span id="resp-size" class="text-surface-400 dark:text-surface-500">Size: <span class="text-surface-500 dark:text-surface-400">—</span></span>
                    </div>
                    <div class="flex gap-1 text-xs">
                        <button onclick="copyResponse()" class="text-surface-400 dark:text-surface-500 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2.5 py-1.5 rounded-md transition flex items-center gap-1.5 font-medium" title="Copy response">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy
                        </button>
                        <button onclick="clearResponse()" class="text-surface-400 dark:text-surface-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 px-2.5 py-1.5 rounded-md transition flex items-center gap-1.5 font-medium" title="Clear response">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Response Tab Bar + Format Toggle -->
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800/80 shrink-0 pr-4">
                    <div class="flex text-xs font-medium">
                        <button class="resp-tab-btn active px-4 py-1.5 text-brand-600 dark:text-brand-400 border-b-2 border-brand-500 dark:border-brand-400 font-semibold" data-resp-tab="body">Body</button>
                        <button class="resp-tab-btn px-4 py-1.5 text-surface-400 dark:text-surface-500 hover:text-surface-600 dark:hover:text-surface-300 border-b-2 border-transparent font-medium" data-resp-tab="headers">Headers</button>
                        <button class="resp-tab-btn px-4 py-1.5 text-surface-400 dark:text-surface-500 hover:text-surface-600 dark:hover:text-surface-300 border-b-2 border-transparent font-medium" data-resp-tab="cookies">Cookies</button>
                    </div>
                    <div id="resp-format-toggles" class="flex gap-1 text-xs bg-surface-100/80 dark:bg-surface-700/80 rounded-lg p-0.5 border border-surface-200/50 dark:border-surface-600/50">
                        <button class="resp-format-btn active px-3 py-1 rounded-md font-medium text-white" data-format="pretty">Pretty</button>
                        <button class="resp-format-btn px-3 py-1 rounded-md font-medium text-surface-600 dark:text-surface-300" data-format="raw">Raw</button>
                        <button class="resp-format-btn px-3 py-1 rounded-md font-medium text-surface-600 dark:text-surface-300" data-format="preview">Preview</button>
                    </div>
                </div>

                <!-- Response Content -->
                <div class="flex-1 overflow-hidden relative bg-noise" style="background-color: #fafbfc;">
                    <div id="resp-body" class="resp-content h-full overflow-auto p-4 lg:p-5 active"></div>
                    <div id="resp-headers" class="resp-content h-full overflow-auto p-4 lg:p-5 text-xs hidden"></div>
                    <div id="resp-cookies" class="resp-content h-full overflow-auto p-4 lg:p-5 text-xs hidden"></div>
                    <iframe id="resp-preview" class="resp-content h-full w-full hidden" sandbox="allow-same-origin"></iframe>
                </div>
            </section>
        </main>
    </div>

<script>
// ========= STATE =========
let tabs = [];
let activeTab = null;
let tabCounter = 0;
let activeRespFormat = 'pretty';

function defaultRequest() {
    return { method: 'GET', url: '', params: [], headers: [], bodyType: 'none', body: '', formData: [], auth: { type: 'none', bearer: '', username: '', password: '', keyName: 'X-API-Key', keyValue: '' } };
}

// ========= THEME =========
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('incognito-theme', isDark ? 'dark' : 'light');
    updateThemeIcon(isDark);
}

function updateThemeIcon(isDark) {
    const icon = document.getElementById('theme-icon');
    if (isDark) {
        icon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
    } else {
        icon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>';
    }
}

function initTheme() {
    const saved = localStorage.getItem('incognito-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = saved ? saved === 'dark' : prefersDark;
    document.documentElement.classList.toggle('dark', isDark);
    updateThemeIcon(isDark);
}

// ========= TABS =========
function newTab(data) {
    const id = ++tabCounter;
    const tab = { id, name: data?.name || 'Request ' + id, data: JSON.parse(JSON.stringify(data || defaultRequest())), response: null };
    tabs.push(tab);
    renderTabs();
    activateTab(id);
    return id;
}

function renderTabs() {
    const list = document.getElementById('tabs-list');
    list.innerHTML = tabs.map(t => `
        <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs border-r border-surface-100 dark:border-surface-700 cursor-pointer transition ${t.id === activeTab ? 'bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-semibold' : 'text-surface-500 dark:text-surface-400 hover:bg-surface-50 dark:hover:bg-surface-700 hover:text-surface-700 dark:hover:text-surface-200'}"
             onclick="activateTab(${t.id})">
            <span>${escHtml(t.name)}</span>
            <span onclick="event.stopPropagation(); closeTab(${t.id})" class="ml-0.5 text-surface-300 dark:text-surface-600 hover:text-red-500 dark:hover:text-red-400 text-sm leading-none rounded hover:bg-red-50 dark:hover:bg-red-900/30 px-0.5 transition">&times;</span>
        </div>
    `).join('');
}

function activateTab(id) {
    if (id === activeTab) return;
    activeTab = id;
    renderTabs();
    loadTabData();
    loadResponse();
}

function closeTab(id) {
    const idx = tabs.findIndex(t => t.id === id);
    if (tabs.length <= 1) return;
    tabs = tabs.filter(t => t.id !== id);
    if (activeTab === id) activateTab(tabs[Math.min(idx, tabs.length - 1)].id);
    else renderTabs();
}

function currentTab() { return tabs.find(t => t.id === activeTab); }

// ========= LOAD / SAVE TAB DATA =========
function loadTabData() {
    const d = currentTab()?.data;
    if (!d) return;
    document.getElementById('method').value = d.method;
    document.getElementById('url').value = d.url;

    renderKvList('params-list', d.params);
    renderKvList('headers-list', d.headers);
    renderKvList('form-list', d.formData);

    document.getElementById('json-body').value = d.body;

    const bt = d.bodyType || 'none';
    const radio = document.querySelector(`input[name="bodyType"][value="${bt}"]`);
    if (radio) radio.checked = true;
    toggleBodyType();

    document.getElementById('auth-type').value = d.auth?.type || 'none';
    document.getElementById('auth-bearer-token').value = d.auth?.bearer || '';
    document.getElementById('auth-username').value = d.auth?.username || '';
    document.getElementById('auth-password').value = d.auth?.password || '';
    document.getElementById('auth-key-name').value = d.auth?.keyName || 'X-API-Key';
    document.getElementById('auth-key-value').value = d.auth?.keyValue || '';
    toggleAuth();
}

function saveTabData() {
    const d = currentTab()?.data;
    if (!d) return;
    d.method = document.getElementById('method').value;
    d.url = document.getElementById('url').value;
    d.params = readKvList('params-list');
    d.headers = readKvList('headers-list');
    d.formData = readKvList('form-list');
    d.body = document.getElementById('json-body').value;
    d.bodyType = document.querySelector('input[name="bodyType"]:checked')?.value || 'none';
    d.auth = {
        type: document.getElementById('auth-type').value,
        bearer: document.getElementById('auth-bearer-token').value,
        username: document.getElementById('auth-username').value,
        password: document.getElementById('auth-password').value,
        keyName: document.getElementById('auth-key-name').value,
        keyValue: document.getElementById('auth-key-value').value,
    };
}

// ========= KV LIST HELPERS =========
function renderKvList(containerId, items) {
    const container = document.getElementById(containerId);
    container.innerHTML = (items || []).map((item, i) => `
        <div class="kv-row fade-in">
            <input value="${escHtml(item.key || '')}" placeholder="Key" oninput="saveTabData()" class="flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors">
            <input value="${escHtml(item.value || '')}" placeholder="Value" oninput="saveTabData()" class="flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors">
            <button onclick="removeKvRow(this)" class="text-red-300 dark:text-red-500 hover:text-red-500 dark:hover:text-red-400 text-sm px-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition leading-none">&times;</button>
        </div>
    `).join('');
}

function readKvList(containerId) {
    const rows = document.getElementById(containerId).querySelectorAll('.kv-row');
    return Array.from(rows).map(r => ({
        key: r.querySelector('input:first-child')?.value || '',
        value: r.querySelector('input:nth-child(2)')?.value || '',
    }));
}

function removeKvRow(btn) {
    btn.closest('.kv-row').remove();
    saveTabData();
}

function addParam() { addKvRow('params-list'); saveTabData(); }
function addHeader() { addKvRow('headers-list'); saveTabData(); }
function addFormField() { addKvRow('form-list'); saveTabData(); }

function addKvRow(containerId) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'kv-row fade-in';
    div.innerHTML = `<input placeholder="Key" oninput="saveTabData()" class="flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors"><input placeholder="Value" oninput="saveTabData()" class="flex-1 border border-surface-200 dark:border-surface-600 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors"><button onclick="removeKvRow(this)" class="text-red-300 dark:text-red-500 hover:text-red-500 dark:hover:text-red-400 text-sm px-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition leading-none">&times;</button>`;
    container.appendChild(div);
}

// ========= TAB TOGGLES =========
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('text-brand-600', 'dark:text-brand-400', 'border-brand-500', 'dark:border-brand-400', 'font-semibold');
            b.classList.add('text-surface-400', 'dark:text-surface-500', 'border-transparent', 'font-medium');
        });
        this.classList.remove('text-surface-400', 'dark:text-surface-500', 'border-transparent', 'font-medium');
        this.classList.add('text-brand-600', 'dark:text-brand-400', 'border-brand-500', 'dark:border-brand-400', 'font-semibold');
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById('tab-' + this.dataset.tab).classList.remove('hidden');
    });
});

// Response tab toggles
document.querySelectorAll('.resp-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.resp-tab-btn').forEach(b => {
            b.classList.remove('active', 'text-brand-600', 'dark:text-brand-400', 'border-brand-500', 'dark:border-brand-400');
            b.classList.add('text-surface-400', 'dark:text-surface-500', 'border-transparent');
        });
        this.classList.add('active', 'text-brand-600', 'dark:text-brand-400', 'border-brand-500', 'dark:border-brand-400');

        const tab = this.dataset.respTab;
        document.querySelectorAll('.resp-content').forEach(c => c.classList.add('hidden'));
        document.getElementById('resp-' + tab).classList.remove('hidden');

        document.getElementById('resp-format-toggles').classList.toggle('hidden', tab !== 'body');
        if (tab === 'body') applyRespFormat(activeRespFormat);
    });
});

// Response format toggles
document.querySelectorAll('.resp-format-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.resp-format-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeRespFormat = this.dataset.format;
        applyRespFormat(activeRespFormat);
    });
});

function applyRespFormat(format) {
    const r = currentTab()?.response;
    if (!r) return;

    const bodyEl = document.getElementById('resp-body');
    const previewEl = document.getElementById('resp-preview');

    bodyEl.classList.toggle('hidden', format === 'preview');
    previewEl.classList.toggle('hidden', format !== 'preview');

    if (format === 'pretty') {
        if (isJson(r.body)) {
            bodyEl.innerHTML = '<pre class="text-xs font-mono leading-relaxed">' + syntaxHighlight(JSON.stringify(JSON.parse(r.body), null, 2)) + '</pre>';
        } else {
            bodyEl.innerHTML = '<pre class="text-xs font-mono leading-relaxed whitespace-pre-wrap">' + escHtml(r.body || '(empty)') + '</pre>';
        }
    } else if (format === 'raw') {
        bodyEl.innerHTML = '<pre class="text-xs font-mono leading-relaxed whitespace-pre-wrap">' + escHtml(r.body || '(empty)') + '</pre>';
    } else if (format === 'preview') {
        try {
            previewEl.src = 'data:text/html;charset=utf-8,' + encodeURIComponent(r.body || '');
        } catch {
            previewEl.src = 'about:blank';
        }
    }
}

function toggleBodyType() {
    const val = document.querySelector('input[name="bodyType"]:checked')?.value;
    document.getElementById('body-json').classList.toggle('hidden', val !== 'json');
    document.getElementById('body-form').classList.toggle('hidden', val !== 'form-data');
    saveTabData();
}

function toggleAuth() {
    const val = document.getElementById('auth-type').value;
    ['auth-bearer', 'auth-basic', 'auth-apikey'].forEach(id => document.getElementById(id).classList.toggle('hidden', !id.includes(val)));
    saveTabData();
}

// ========= SEND REQUEST =========
async function sendRequest() {
    saveTabData();
    const d = currentTab()?.data;
    if (!d || !d.url) { showToast('Enter a URL first'); return; }

    const btn = document.getElementById('send-text');
    const icon = document.getElementById('send-icon');
    btn.textContent = 'Sending';
    icon.innerHTML = '<span class="spinner"></span>';

    const allHeaders = [...d.headers.filter(h => h.key)];
    const allParams = d.params.filter(p => p.key);

    let url = d.url;
    if (allParams.length) {
        const qs = allParams.map(p => encodeURIComponent(p.key) + '=' + encodeURIComponent(p.value)).join('&');
        url += (url.includes('?') ? '&' : '?') + qs;
    }

    if (d.auth?.type === 'bearer' && d.auth.bearer) allHeaders.push({ key: 'Authorization', value: 'Bearer ' + d.auth.bearer });
    if (d.auth?.type === 'basic' && d.auth.username) allHeaders.push({ key: 'Authorization', value: 'Basic ' + btoa(d.auth.username + ':' + (d.auth.password || '')) });
    if (d.auth?.type === 'apikey' && d.auth.keyName) allHeaders.push({ key: d.auth.keyName, value: d.auth.keyValue });

    try {
        const res = await fetch('/api-tester/proxy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' },
            body: JSON.stringify({ method: d.method, url, headers: allHeaders, body: d.body, bodyType: d.bodyType === 'json' ? 'json' : d.bodyType === 'form-data' ? 'form-data' : 'none', formData: d.formData }),
        });
        const json = await res.json();

        btn.textContent = 'Send';
        icon.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>';

        if (json.error) {
            showResponse({ status: 0, statusText: 'Error', headers: {}, body: json.error, size: 0, time: json.time || 0 });
            showToast('Error: ' + json.error);
            return;
        }

        showResponse(json);
        addHistory(currentTab().data, json);
    } catch (e) {
        btn.textContent = 'Send';
        icon.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>';
        showResponse({ status: 0, statusText: 'Error', headers: {}, body: e.message, size: 0, time: 0 });
        showToast('Network Error');
    }
}

// ========= SHOW RESPONSE =========
function showResponse(r) {
    const d = currentTab();
    if (d) d.response = r;

    const statusEl = document.getElementById('resp-status');
    const color = r.status >= 200 && r.status < 300 ? 'text-green-600 dark:text-green-400' : r.status >= 400 ? 'text-red-500 dark:text-red-400' : 'text-surface-500 dark:text-surface-400';
    statusEl.className = 'text-surface-400 dark:text-surface-500 text-xs font-medium';
    statusEl.innerHTML = `Status: <span class="${color} font-semibold">${r.status || '—'}</span> ${r.statusText || ''}`;
    document.getElementById('resp-time').innerHTML = `Time: <span class="text-surface-700 dark:text-surface-300 font-semibold">${r.time || 0}ms</span>`;
    document.getElementById('resp-size').innerHTML = `Size: <span class="text-surface-700 dark:text-surface-300 font-semibold">${formatSize(r.size || 0)}</span>`;

    const hEl = document.getElementById('resp-headers');
    if (r.headers && Object.keys(r.headers).length) {
        hEl.innerHTML = Object.entries(r.headers).map(([k, v]) =>
            `<div class="flex gap-2 py-1 border-b border-surface-100 dark:border-surface-700"><span class="font-medium text-surface-600 dark:text-surface-300 shrink-0 min-w-[200px]">${escHtml(k)}</span><span class="text-surface-500 dark:text-surface-400 break-all font-mono text-[11px]">${escHtml(v)}</span></div>`
        ).join('');
    } else {
        hEl.innerHTML = '<p class="text-surface-400 dark:text-surface-500 text-xs">No headers</p>';
    }

    document.getElementById('resp-cookies').innerHTML = r.headers?.['set-cookie']
        ? `<pre class="text-xs font-mono leading-relaxed">${escHtml(r.headers['set-cookie'])}</pre>`
        : '<p class="text-surface-400 dark:text-surface-500 text-xs">No cookies</p>';

    applyRespFormat(activeRespFormat);
}

function loadResponse() {
    const r = currentTab()?.response;
    if (r) showResponse(r);
    else clearResponse();
}

function clearResponse() {
    document.getElementById('resp-status').innerHTML = 'Status: <span class="text-surface-400 dark:text-surface-500">—</span>';
    document.getElementById('resp-time').innerHTML = 'Time: <span class="text-surface-400 dark:text-surface-500">—</span>';
    document.getElementById('resp-size').innerHTML = 'Size: <span class="text-surface-400 dark:text-surface-500">—</span>';
    document.getElementById('resp-body').innerHTML = '<div class="flex items-center justify-center h-full text-surface-300 dark:text-surface-600 text-sm font-medium">Send a request to see the response</div>';
    document.getElementById('resp-headers').innerHTML = '';
    document.getElementById('resp-cookies').innerHTML = '';
    document.getElementById('resp-preview').src = 'about:blank';
}

function copyResponse() {
    const body = document.getElementById('resp-body');
    if (!body.textContent.trim()) { showToast('Nothing to copy'); return; }
    navigator.clipboard?.writeText(body.textContent).then(() => showToast('Copied!')).catch(() => {});
}

function showToast(msg) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.classList.add('show');
    clearTimeout(el._timer);
    el._timer = setTimeout(() => el.classList.remove('show'), 2000);
}

// ========= SIDEBAR =========
function showSidebar(sb) {
    ['history', 'saved'].forEach(s => {
        const el = document.getElementById(`sb-${s}`);
        const btn = document.getElementById(`sb-${s}-btn`);
        if (el) el.classList.toggle('hidden', s !== sb);
        if (btn) {
            btn.classList.toggle('text-brand-600', s === sb);
            btn.classList.toggle('text-surface-400', s !== sb);
            btn.classList.toggle('border-brand-500', s === sb);
            btn.classList.toggle('border-transparent', s !== sb);
        }
    });
}

function addHistory(data, response) {
    let history = JSON.parse(localStorage.getItem('apiTesterHistory') || '[]');
    history.unshift({ method: data.method, url: data.url, status: response?.status, time: response?.time, ts: Date.now() });
    if (history.length > 50) history = history.slice(0, 50);
    localStorage.setItem('apiTesterHistory', JSON.stringify(history));
    renderHistory();
}

function renderHistory() {
    const history = JSON.parse(localStorage.getItem('apiTesterHistory') || '[]');
    const el = document.getElementById('sb-history');
    if (!history.length) { el.innerHTML = '<div class="flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>No requests yet</span></div>'; return; }
    el.innerHTML = history.map(h => `
        <div class="sidebar-item p-2 rounded-md cursor-pointer flex gap-2 items-start" onclick="loadHistory('${escHtml(h.url)}', '${h.method}')">
            <span class="pill ${methodColor(h.method)} shrink-0 mt-0.5 text-[10px]">${h.method}</span>
            <div class="min-w-0">
                <p class="truncate text-surface-700 dark:text-surface-300 text-xs">${escHtml(h.url)}</p>
                <span class="text-[10px] ${h.status >= 400 ? 'text-red-400' : 'text-green-500'} font-medium">${h.status || 'ERR'} &middot; ${h.time || '—'}ms</span>
            </div>
        </div>
    `).join('');
}

function loadHistory(url, method) {
    const d = currentTab()?.data;
    if (d) { d.url = url; d.method = method; loadTabData(); }
}

// ========= SAVE/LOAD COLLECTIONS =========
function saveCurrent() {
    saveTabData();
    const d = currentTab()?.data;
    if (!d || !d.url) { showToast('Nothing to save'); return; }
    const name = prompt('Request name:', currentTab()?.name || '');
    if (!name) return;
    let saved = JSON.parse(localStorage.getItem('apiTesterSaved') || '[]');
    saved.push({ name, data: JSON.parse(JSON.stringify(d)), ts: Date.now() });
    localStorage.setItem('apiTesterSaved', JSON.stringify(saved));
    renderSaved();
    showToast('Saved!');
}

function renderSaved() {
    const saved = JSON.parse(localStorage.getItem('apiTesterSaved') || '[]');
    const el = document.getElementById('sb-saved');
    if (!saved.length) { el.innerHTML = '<div class="flex flex-col items-center justify-center h-32 text-surface-300 dark:text-surface-600 text-xs gap-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg><span>No saved requests</span></div>'; return; }
    el.innerHTML = saved.map((s, i) => `
        <div class="sidebar-item p-2 rounded-md cursor-pointer flex gap-2" onclick="loadSaved(${i})">
            <span class="pill ${methodColor(s.data.method)} shrink-0 mt-0.5 text-[10px]">${s.data.method}</span>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-surface-700 dark:text-surface-300 truncate">${escHtml(s.name)}</p>
                <p class="text-[10px] text-surface-400 dark:text-surface-500 truncate">${escHtml(s.data.url)}</p>
            </div>
            <button onclick="event.stopPropagation(); deleteSaved(${i})" class="text-red-300 dark:text-red-500 hover:text-red-500 dark:hover:text-red-400 text-sm px-0.5 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition leading-none">&times;</button>
        </div>
    `).join('');
}

function loadSaved(idx) {
    let saved = JSON.parse(localStorage.getItem('apiTesterSaved') || '[]');
    if (saved[idx]) newTab({ ...saved[idx].data, name: saved[idx].name });
}

function deleteSaved(idx) {
    let saved = JSON.parse(localStorage.getItem('apiTesterSaved') || '[]');
    saved.splice(idx, 1);
    localStorage.setItem('apiTesterSaved', JSON.stringify(saved));
    renderSaved();
}

// ========= UTILITIES =========
function escHtml(s) { return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
function formatSize(bytes) { if (!bytes) return '0 B'; const u = ['B','KB','MB']; let i = 0; let s = bytes; while (s >= 1024 && i < 2) { s /= 1024; i++; } return (i ? s.toFixed(1) : s) + ' ' + u[i]; }
function methodColor(m) {
    return { GET: 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300', POST: 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300', PUT: 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300', PATCH: 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300', DELETE: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300', HEAD: 'bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300', OPTIONS: 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300' }[m] || 'bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300';
}

function isJson(str) {
    if (!str || typeof str !== 'string') return false;
    try { JSON.parse(str); return true; } catch { return false; }
}

function syntaxHighlight(json) {
    return json.replace(/("(?:[^"\\]|\\.)*")\s*:/g, '<span class="json-key">$1</span>:')
        .replace(/:(\s*)"((?:[^"\\]|\\.)*)"/g, ':<span class="json-string">"$2"</span>')
        .replace(/:\s*(\d+(?:\.\d+)?)/g, ':<span class="json-number">$1</span>')
        .replace(/:\s*(true|false)/g, ':<span class="json-bool">$1</span>')
        .replace(/:\s*(null)/g, ':<span class="json-null">$1</span>');
}

// ========= KEYBOARD SHORTCUTS =========
document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); sendRequest(); }
});

// ========= INIT =========
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    newTab();
    renderHistory();
    renderSaved();
    setInterval(() => { const d = currentTab()?.data; if (d && d.url) saveTabData(); }, 2000);
});
</script>
</body>
</html>
