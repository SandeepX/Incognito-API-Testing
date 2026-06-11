<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Incognito Testing</title>
    @include('apitester.css')
</head>
<body class="bg-surface-50 dark:bg-surface-900 text-surface-800 dark:text-surface-100 h-screen flex flex-col overflow-hidden selection:bg-brand-200 dark:selection:bg-brand-800 selection:text-brand-900 dark:selection:text-brand-200">

    <div id="toast" class="toast"></div>

    <!-- Top Bar -->
    <header class="bg-white dark:bg-surface-800 border-b border-surface-200 dark:border-surface-700 px-4 lg:px-5 py-2 flex items-center gap-2 shrink-0 shadow-sm z-10">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 rounded-xl flex items-center justify-center text-white text-xs font-extrabold shadow-sm shadow-brand-200 dark:shadow-brand-800">IT</div>
            <span class="font-bold text-surface-800 dark:text-surface-100 text-sm tracking-tight hidden sm:inline">Incognito Testing</span>
        </div>

        <!-- Environment Selector -->
        <select id="env-select" class="border border-surface-200 dark:border-surface-600 rounded-lg px-2 py-1.5 text-[11px] focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-700 dark:text-surface-200 max-w-[140px] cursor-pointer" title="Environment"></select>

        <!-- Workspace Selector -->
        <select id="workspace-select" class="border border-surface-200 dark:border-surface-600 rounded-lg px-2 py-1.5 text-[11px] focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white dark:bg-surface-700 text-surface-700 dark:text-surface-200 max-w-[140px] cursor-pointer" title="Workspace"></select>

        <div class="ml-auto flex items-center gap-1.5 text-xs">
            <button onclick="manageEnvironments()" class="text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2 py-1.5 rounded-lg transition flex items-center gap-1 font-medium" title="Manage Environments">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="hidden sm:inline">Envs</span>
            </button>
            <button onclick="manageWorkspaces()" class="text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2 py-1.5 rounded-lg transition flex items-center gap-1 font-medium" title="Manage Workspaces">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="hidden sm:inline">Workspaces</span>
            </button>
            <button onclick="importCurl()" class="text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2 py-1.5 rounded-lg transition flex items-center gap-1 font-medium" title="Import from cURL">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                <span class="hidden sm:inline">Import</span>
            </button>
            <button onclick="exportCurl()" class="text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2 py-1.5 rounded-lg transition flex items-center gap-1 font-medium" title="Export as cURL">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 11l5-5 5 5M12 4v11"/></svg>
                <span class="hidden sm:inline">Export</span>
            </button>
            <button onclick="saveResp()" class="text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2 py-1.5 rounded-lg transition flex items-center gap-1 font-medium" title="Save Response">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                <span class="hidden sm:inline">Save Resp</span>
            </button>
            <button id="theme-toggle" onclick="toggleTheme()" class="theme-btn text-surface-400 dark:text-surface-400 hover:text-brand-600 dark:hover:text-brand-400 px-2 py-1.5 rounded-lg transition flex items-center font-medium" title="Toggle theme">
                <span id="theme-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </span>
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-60 bg-white dark:bg-surface-800 border-r border-surface-200 dark:border-surface-700 shrink-0 flex flex-col overflow-hidden hidden md:flex shadow-sm">
            <div class="flex border-b border-surface-100 dark:border-surface-700 text-xs font-medium">
                <button onclick="showSidebar('history')" id="sb-history-btn" class="flex-1 py-2.5 text-center hover:bg-surface-50 dark:hover:bg-surface-700 border-b-2 border-brand-500 text-brand-600 transition font-semibold">History</button>
                <button onclick="showSidebar('collections')" id="sb-collections-btn" class="flex-1 py-2.5 text-center hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-400 dark:text-surface-500 border-b-2 border-transparent transition font-medium">Collections</button>
                <button onclick="showSidebar('environments')" id="sb-environments-btn" class="flex-1 py-2.5 text-center hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-400 dark:text-surface-500 border-b-2 border-transparent transition font-medium">Environments</button>
            </div>
            <div id="sb-history" class="flex-1 overflow-y-auto text-xs"></div>
            <div id="sb-collections" class="flex-1 overflow-y-auto text-xs hidden"></div>
            <div id="sb-environments" class="flex-1 overflow-y-auto text-xs hidden"></div>
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
                    <div class="flex-1 relative">
                        <input id="url" type="text" placeholder="https://api.example.com/endpoint (use @{{variable}} for env vars)"
                               class="w-full border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 hover:bg-white dark:hover:bg-surface-600 transition-colors font-mono tracking-tight text-surface-800 dark:text-surface-100">
                        <div id="url-vars-preview" class="mt-1 text-[11px] font-mono leading-relaxed hidden"></div>
                    </div>
                    <button onclick="sendReq()" class="bg-brand-600 dark:bg-brand-500 text-white px-5 py-2 rounded-lg text-xs font-semibold hover:bg-brand-500 dark:hover:bg-brand-400 active:scale-[0.97] transition-all flex items-center gap-2 shadow-sm shadow-brand-200 dark:shadow-brand-800 hover:shadow-brand-300/30">
                        <span id="send-icon">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                        <span id="send-text">Send</span>
                    </button>
                    <button id="cancel-btn" onclick="cancelReq()" class="hidden bg-red-500 dark:bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-red-400 dark:hover:bg-red-500 transition-all flex items-center gap-2 shadow-sm" title="Cancel">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Cancel
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
                    <button onclick="addKvRow('params-list');saveTabData()" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add param</button>
                </div>
                <div id="tab-headers" class="tab-content space-y-1.5 hidden">
                    <div id="headers-list"></div>
                    <button onclick="addKvRow('headers-list');saveTabData()" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add header</button>
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
                        <button onclick="addKvRow('form-list');saveTabData()" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add field</button>
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
                        <span id="resp-status" class="text-surface-400 dark:text-surface-500">Status: <span class="text-surface-500 dark:text-surface-400">&mdash;</span></span>
                        <span id="resp-time" class="text-surface-400 dark:text-surface-500">Time: <span class="text-surface-500 dark:text-surface-400">&mdash;</span></span>
                        <span id="resp-size" class="text-surface-400 dark:text-surface-500">Size: <span class="text-surface-500 dark:text-surface-400">&mdash;</span></span>
                    </div>
                    <div class="flex gap-1 text-xs">
                        <button onclick="saveResp()" class="text-surface-400 dark:text-surface-500 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2.5 py-1.5 rounded-md transition flex items-center gap-1.5 font-medium" title="Save response">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                            Save
                        </button>
                        <button onclick="copyResp()" class="text-surface-400 dark:text-surface-500 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-2.5 py-1.5 rounded-md transition flex items-center gap-1.5 font-medium" title="Copy response">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy
                        </button>
                        <button onclick="clearResp()" class="text-surface-400 dark:text-surface-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 px-2.5 py-1.5 rounded-md transition flex items-center gap-1.5 font-medium" title="Clear response">
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
                <div class="flex-1 overflow-hidden relative bg-noise dark:bg-surface-900" style="background-color: #fafbfc;">
                    <div id="resp-body" class="resp-content h-full overflow-auto p-4 lg:p-5 active dark:text-surface-100 dark:bg-surface-900"></div>
                    <div id="resp-headers" class="resp-content h-full overflow-auto p-4 lg:p-5 text-xs hidden dark:text-surface-200 dark:bg-surface-900"></div>
                    <div id="resp-cookies" class="resp-content h-full overflow-auto p-4 lg:p-5 text-xs hidden dark:text-surface-200 dark:bg-surface-900"></div>
                    <iframe id="resp-preview" class="resp-content h-full w-full hidden" sandbox="allow-same-origin"></iframe>
                </div>
            </section>
        </main>
    </div>

    <!-- Import Curl Modal -->
    <div id="curl-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/60 hidden" onclick="if(event.target===this)closeCurlModal()">
        <div class="bg-white dark:bg-surface-800 rounded-xl shadow-xl border border-surface-200 dark:border-surface-700 w-full max-w-2xl mx-4 max-h-[80vh] flex flex-col" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-3 border-b border-surface-200 dark:border-surface-700">
                <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-100">Import from cURL</h3>
                <button onclick="closeCurlModal()" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 flex-1 overflow-auto">
                <p class="text-xs text-surface-500 mb-3">Paste a cURL command below. The URL, method, headers, body, and auth will be extracted.</p>
                <textarea id="curl-input" rows="8" class="w-full border border-surface-200 dark:border-surface-600 rounded-lg px-3 py-2.5 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-surface-50 dark:bg-surface-700 text-surface-800 dark:text-surface-100 transition-colors leading-relaxed" placeholder="curl -X POST https://api.example.com/endpoint \&#10;  -H &quot;Authorization: Bearer token123&quot; \&#10;  -H &quot;Content-Type: application/json&quot; \&#10;  -d '{&quot;key&quot;: &quot;value&quot;}'"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800/80 rounded-b-xl">
                <button onclick="closeCurlModal()" class="px-4 py-1.5 text-xs font-medium text-surface-600 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-700 rounded-lg transition">Cancel</button>
                <button onclick="parseAndImportCurl()" class="px-4 py-1.5 text-xs font-semibold text-white bg-brand-600 dark:bg-brand-500 hover:bg-brand-500 dark:hover:bg-brand-400 rounded-lg transition flex items-center gap-1.5">Import</button>
            </div>
        </div>
    </div>

    @include('apitester.script')
</body>
</html>
