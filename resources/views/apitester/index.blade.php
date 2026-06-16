<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Incognito Testing</title>
    @include('apitester.partials.css')
</head>
<body class="bg-surface-50 dark:bg-surface-900 text-surface-800 dark:text-surface-100 h-screen flex flex-col overflow-hidden selection:bg-brand-200 dark:selection:bg-brand-800 selection:text-brand-900 dark:selection:text-brand-200">

    <div id="toast" class="toast"></div>

    <!-- Top Bar - Postman Style -->
    <header class="bg-surface-800 dark:bg-surface-900 border-b border-surface-700 dark:border-surface-800 px-4 lg:px-5 py-2.5 flex items-center gap-3 shrink-0 shadow-lg z-10">
        <!-- Workspace/Collection Name -->
        <div class="flex items-center gap-2 text-surface-300 dark:text-surface-200">
            <span class="text-xs font-medium opacity-75">Workspace</span>
            <select id="workspace-select" class="bg-surface-700 dark:bg-surface-800 border border-surface-600 dark:border-surface-700 text-surface-100 text-xs px-2 py-1.5 rounded focus:outline-none focus:ring-2 focus:ring-blue-500/50 cursor-pointer font-medium hover:bg-surface-600 dark:hover:bg-surface-700 transition" title="Workspace"></select>
        </div>

        <!-- Request Tabs -->
        <div class="flex-1 flex items-center px-3 overflow-x-auto gap-1">
            <div id="tabs-list" class="flex gap-1 items-center"></div>
            <button onclick="promptNewTab()" class="px-3 py-1.5 text-surface-400 dark:text-surface-500 hover:text-blue-400 dark:hover:text-blue-400 hover:bg-surface-700 dark:hover:bg-surface-700 rounded text-sm leading-none transition font-medium flex items-center gap-1" title="New Tab">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.5 1.5H9.5V9.5H1.5V10.5H9.5V18.5H10.5V10.5H18.5V9.5H10.5V1.5Z"/></svg>
            </button>
        </div>

        <!-- Right Actions -->
        <div class="flex items-center gap-2 ml-auto">
            <!-- Environment Selector -->
            <div class="flex items-center gap-1.5">
                <select id="env-select" class="bg-surface-700 dark:bg-surface-800 border border-surface-600 dark:border-surface-700 text-surface-100 text-xs px-2 py-1.5 rounded focus:outline-none focus:ring-2 focus:ring-blue-500/50 cursor-pointer max-w-[120px] hover:bg-surface-600 dark:hover:bg-surface-700 transition" title="Environment"></select>
                <button onclick="manageEnvironments()" class="text-surface-400 hover:text-blue-400 p-1.5 rounded hover:bg-surface-700 transition" title="Manage Environments">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
            </div>

            <!-- User Menu -->
            <div class="flex items-center gap-1.5 border-l border-surface-700 pl-2 ml-2 relative">
                <button onclick="toggleUserMenu()" class="flex items-center gap-2 text-surface-300 hover:text-surface-100 hover:bg-surface-700 px-2 py-1.5 rounded transition" title="User menu">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-xs font-medium hidden lg:inline">{{ auth()->user()->name }}</span>
                    <svg class="w-3 h-3 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="user-menu-dropdown" class="absolute top-full right-0 mt-1 w-48 bg-surface-800 dark:bg-surface-800 border border-surface-700 rounded-lg shadow-xl z-50 hidden py-1">
                    <div class="px-3 py-2 border-b border-surface-700">
                        <p class="text-xs font-medium text-surface-100 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-surface-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="#" onclick="manageWorkspaceMembers()" class="flex items-center gap-2 px-3 py-2 text-xs text-surface-300 hover:bg-surface-700 hover:text-surface-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        Workspace Members
                    </a>
                    <a href="#" onclick="manageWorkspaces()" class="flex items-center gap-2 px-3 py-2 text-xs text-surface-300 hover:bg-surface-700 hover:text-surface-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Manage Workspaces
                    </a>
                    <div class="border-t border-surface-700 mt-1 pt-1">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 px-3 py-2 text-xs text-red-400 hover:bg-surface-700 hover:text-red-300 transition w-full text-left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- More Options -->
            <div class="flex items-center gap-1.5 border-l border-surface-700 pl-2 ml-2">
                <button onclick="saveResp()" class="text-surface-400 hover:text-blue-400 p-1.5 rounded hover:bg-surface-700 transition" title="Save Response">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                </button>
                <button id="theme-toggle" onclick="toggleTheme()" class="text-surface-400 hover:text-blue-400 p-1.5 rounded hover:bg-surface-700 transition" title="Toggle theme">
                    <span id="theme-icon"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg></span>
                </button>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar - Postman Style (Vertical Icon Bar) -->
        <aside class="w-20 bg-surface-800 dark:bg-surface-900 border-r border-surface-700 dark:border-surface-800 shrink-0 flex flex-col items-center py-4 gap-3 shadow-lg overflow-y-auto">
            <!-- Collections -->
            <button onclick="showSidebar('collections')" id="sb-collections-icon" class="sidebar-icon active" title="Collections">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="text-[10px] font-medium leading-tight mt-0.5">Collection</span>
            </button>
            
            <!-- Environments -->
            <button onclick="showSidebar('environments')" id="sb-environments-icon" class="sidebar-icon" title="Environments">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                <span class="text-[10px] font-medium leading-tight mt-0.5">Environment</span>
            </button>
            
            <!-- Flows -->
            <button onclick="showSidebar('flows')" id="sb-flows-icon" class="sidebar-icon" title="Flows">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span class="text-[10px] font-medium leading-tight mt-0.5">Flows</span>
            </button>
            
            <!-- History -->
            <button onclick="showSidebar('history')" id="sb-history-icon" class="sidebar-icon" title="History">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 2m6-11a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px] font-medium leading-tight mt-0.5">History</span>
            </button>
        </aside>

        <!-- Main Sidebar Panel -->
        <aside id="sidebar-panel" class="bg-white dark:bg-surface-850 border-r border-surface-200 dark:border-surface-800 shrink-0 flex flex-col overflow-hidden shadow-lg hidden">
            <!-- Header -->
            <div class="sidebar-panel-header">
                <h3 id="sidebar-title"></h3>
            </div>
            
            <!-- Content -->
            <div id="sb-history" class="flex-1 overflow-y-auto text-xs hidden"></div>
            <div id="sb-collections" class="flex-1 overflow-y-auto text-xs hidden text-surface-700 dark:text-surface-300"></div>
            <div id="sb-environments" class="flex-1 overflow-y-auto text-xs hidden"></div>
            <div id="sb-flows" class="flex-1 overflow-y-auto text-xs hidden flex items-center justify-center text-surface-500">
                <p class="text-center">Flows coming soon</p>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 flex flex-col overflow-hidden bg-surface-100 dark:bg-surface-900">
            <!-- Request Tabs Bar -->
            <div class="bg-surface-800 dark:bg-surface-850 border-b border-surface-700 dark:border-surface-800 px-4 py-1 shrink-0">
                <!-- Request Tabs (Method + URL from collections) -->
            </div>

            <!-- Request Panel -->
            <section class="bg-surface-800 dark:bg-surface-850 p-4 overflow-y-auto space-y-4 flex-shrink-0" id="request-panel">
                <!-- URL Bar with Method Dropdown -->
                <div class="flex gap-3 items-center">
                    <select id="method" class="method-select border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs font-bold w-auto min-w-[90px] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 cursor-pointer hover:bg-surface-600 transition">
                        <option class="text-green-400">GET</option>
                        <option class="text-yellow-400">POST</option>
                        <option class="text-orange-400">PUT</option>
                        <option class="text-yellow-300">PATCH</option>
                        <option class="text-red-400">DELETE</option>
                        <option class="text-surface-400">HEAD</option>
                        <option class="text-purple-400">OPTIONS</option>
                    </select>
                    <div class="flex-1 relative">
                        <div id="url" contenteditable="true" data-placeholder="https://api.example.com/endpoint"
                               class="w-full border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 hover:bg-surface-650 dark:hover:bg-surface-700 transition-colors font-mono tracking-tight text-surface-100 dark:text-surface-100 whitespace-nowrap overflow-x-auto overflow-y-hidden" style="outline:none;word-break:keep-all">
                        </div>
                    </div>
                    <button onclick="sendReq()" class="bg-blue-600 hover:bg-blue-500 active:scale-[0.97] text-white px-5 py-2 rounded text-xs font-semibold transition-all flex items-center gap-2 shadow-lg shadow-blue-900/30 shrink-0">
                        <span id="send-icon">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                        <span id="send-text">Send</span>
                    </button>
                    <button id="cancel-btn" onclick="cancelReq()" class="hidden bg-red-600 hover:bg-red-500 text-white px-3 py-2 rounded text-xs font-semibold transition-all flex items-center gap-1 shrink-0 shadow-lg" title="Cancel">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Request Tabs -->
                <div class="flex gap-1 text-xs border-b border-surface-700 dark:border-surface-700 pb-1 items-center">
                    <button class="tab-btn py-2 px-3 font-medium text-blue-400 border-b-2 border-blue-400" data-tab="params">Params</button>
                    <button class="tab-btn py-2 px-3 font-medium text-surface-400 hover:text-surface-300 border-b-2 border-transparent" data-tab="authorization">Authorization</button>
                    <button class="tab-btn py-2 px-3 font-medium text-surface-400 hover:text-surface-300 border-b-2 border-transparent" data-tab="headers">Headers</button>
                    <button class="tab-btn py-2 px-3 font-medium text-surface-400 hover:text-surface-300 border-b-2 border-transparent" data-tab="body">Body</button>
                    <button class="tab-btn py-2 px-3 font-medium text-surface-400 hover:text-surface-300 border-b-2 border-transparent" data-tab="cookies">Cookies</button>
                    <div class="ml-auto flex gap-1">
                        <button onclick="importCurl()" class="text-surface-400 hover:text-blue-400 p-1.5 rounded hover:bg-surface-700 transition" title="Import cURL">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                        </button>
                        <button onclick="exportCurl()" class="text-surface-400 hover:text-blue-400 p-1.5 rounded hover:bg-surface-700 transition" title="Export cURL">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 11l5-5 5 5M12 4v11"/></svg>
                        </button>
                    </div>
                </div>

                <div id="tab-params" class="tab-content space-y-2">
                    <div id="params-list"></div>
                    <button onclick="addKvRow('params-list');saveTabData()" class="text-xs text-blue-400 hover:text-blue-300 font-medium flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</button>
                </div>
                <div id="tab-authorization" class="tab-content space-y-2 hidden">
                    <select id="auth-type" onchange="toggleAuth()" class="border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs w-full focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 hover:bg-surface-600 transition">
                        <option value="none">No Auth</option>
                        <option value="bearer">Bearer Token</option>
                        <option value="basic">Basic Auth</option>
                        <option value="apikey">API Key</option>
                    </select>
                    <div id="auth-bearer" class="hidden"><input id="auth-bearer-token" placeholder="Token" class="w-full border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 transition-colors"></div>
                    <div id="auth-basic" class="hidden flex gap-2"><input id="auth-username" placeholder="Username" class="flex-1 border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 transition-colors"><input id="auth-password" type="password" placeholder="Password" class="flex-1 border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 transition-colors"></div>
                    <div id="auth-apikey" class="hidden flex gap-2"><input id="auth-key-name" placeholder="Key name" value="X-API-Key" class="flex-1 border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 transition-colors"><input id="auth-key-value" placeholder="Value" class="flex-1 border border-surface-600 dark:border-surface-700 rounded px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 transition-colors"></div>
                </div>
                <div id="tab-headers" class="tab-content space-y-2 hidden">
                    <div id="headers-list"></div>
                    <button onclick="addKvRow('headers-list');saveTabData()" class="text-xs text-blue-400 hover:text-blue-300 font-medium flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</button>
                </div>
                <div id="tab-cookies" class="tab-content space-y-2 hidden">
                    <div id="cookies-list"></div>
                    <button onclick="addKvRow('cookies-list');saveTabData()" class="text-xs text-blue-400 hover:text-blue-300 font-medium flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</button>
                </div>
                <div id="tab-body" class="tab-content space-y-2 hidden">
                    <div class="flex gap-4 text-xs">
                        <label class="flex items-center gap-1.5 cursor-pointer group"><input type="radio" name="bodyType" value="none" checked onchange="toggleBodyType()" class="accent-blue-500"> <span class="text-surface-300 group-hover:text-surface-100">None</span></label>
                        <label class="flex items-center gap-1.5 cursor-pointer group"><input type="radio" name="bodyType" value="form-data" onchange="toggleBodyType()" class="accent-blue-500"> <span class="text-surface-300 group-hover:text-surface-100">Form Data</span></label>
                        <label class="flex items-center gap-1.5 cursor-pointer group"><input type="radio" name="bodyType" value="raw" onchange="toggleBodyType()" class="accent-blue-500"> <span class="text-surface-300 group-hover:text-surface-100">Raw</span></label>
                        <label class="flex items-center gap-1.5 cursor-pointer group"><input type="radio" name="bodyType" value="json" onchange="toggleBodyType()" class="accent-blue-500"> <span class="text-surface-300 group-hover:text-surface-100">JSON</span></label>
                    </div>
                    <div id="body-raw" class="hidden">
                        <textarea id="raw-body" rows="10" class="body-textarea w-full border border-surface-600 dark:border-surface-700 rounded px-3 py-2.5 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 transition-colors leading-relaxed" placeholder="Plain text body"></textarea>
                    </div>
                    <div id="body-json" class="hidden">
                        <textarea id="json-body" rows="10" class="body-textarea w-full border border-surface-600 dark:border-surface-700 rounded px-3 py-2.5 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-surface-700 dark:bg-surface-750 text-surface-100 transition-colors leading-relaxed" placeholder='{"key": "value"}'></textarea>
                    </div>
                    <div id="body-form" class="hidden space-y-2">
                        <div id="form-list"></div>
                        <button onclick="addKvRow('form-list');saveTabData()" class="text-xs text-blue-400 hover:text-blue-300 font-medium flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</button>
                    </div>
                </div>

            </section>

            <!-- Resize Handle -->
            <div id="resp-resize-handle" class="resize-handle shrink-0"></div>

            <!-- Response Panel -->
            <section class="flex-1 bg-surface-800 dark:bg-surface-850 overflow-hidden flex flex-col" id="response-panel">
                <!-- Response Tabs -->
                <div class="flex items-center justify-between px-4 py-2 border-b border-surface-700 dark:border-surface-800 bg-surface-750 dark:bg-surface-800 shrink-0">
                    <div class="flex gap-1 text-xs font-medium">
                        <button class="resp-tab-btn active px-3 py-1.5 text-blue-400 border-b-2 border-blue-400 font-semibold" data-resp-tab="body">Body</button>
                        <button class="resp-tab-btn px-3 py-1.5 text-surface-400 hover:text-surface-300 border-b-2 border-transparent font-medium" data-resp-tab="headers">Headers</button>
                        <button class="resp-tab-btn px-3 py-1.5 text-surface-400 hover:text-surface-300 border-b-2 border-transparent font-medium" data-resp-tab="cookies">Cookies</button>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span id="resp-status" class="text-surface-400">Status: <span class="text-surface-300">&mdash;</span></span>
                        <span id="resp-time" class="text-surface-400">Time: <span class="text-surface-300">&mdash;</span></span>
                        <span id="resp-size" class="text-surface-400">Size: <span class="text-surface-300">&mdash;</span></span>
                        <div id="resp-format-toggles" class="flex gap-0.5 text-xs bg-surface-700 dark:bg-surface-750 rounded p-0.5 border border-surface-600 dark:border-surface-700 ml-3">
                            <button class="resp-format-btn active px-2 py-1 rounded font-medium text-white bg-blue-600" data-format="pretty">Pretty</button>
                            <button class="resp-format-btn px-2 py-1 rounded font-medium text-surface-400 hover:text-surface-300" data-format="raw">Raw</button>
                            <button class="resp-format-btn px-2 py-1 rounded font-medium text-surface-400 hover:text-surface-300" data-format="preview">Preview</button>
                        </div>
                        <div class="flex gap-1 border-l border-surface-700 pl-3">
                            <button onclick="saveResp()" class="text-surface-400 hover:text-blue-400 p-1 rounded hover:bg-surface-700 transition" title="Save">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                            </button>
                            <button onclick="copyResp()" class="text-surface-400 hover:text-blue-400 p-1 rounded hover:bg-surface-700 transition" title="Copy">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                            <button onclick="clearResp()" class="text-surface-400 hover:text-red-400 p-1 rounded hover:bg-red-900/30 transition" title="Clear">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Response Content -->
                <div class="flex-1 overflow-hidden relative">
                    <div id="resp-body" class="resp-content h-full overflow-auto p-4 active text-surface-100 dark:text-surface-100 text-xs font-mono">
                        <div class="flex items-center justify-center h-full text-center text-surface-400">
                            <div>
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <p class="text-sm">Click Send to get a response</p>
                            </div>
                        </div>
                    </div>
                    <div id="resp-headers" class="resp-content h-full overflow-auto p-4 text-xs hidden text-surface-100 dark:text-surface-100"></div>
                    <div id="resp-cookies" class="resp-content h-full overflow-auto p-4 text-xs hidden text-surface-100 dark:text-surface-100"></div>
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

    @include('apitester.partials.script')
</body>
</html>
