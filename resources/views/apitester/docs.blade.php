<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $collection->name }} Documentation - Incognito Testing</title>
    @include('apitester.partials.css')
    <style>
        /* ========= Docs Page Styles ========= */
        .docs-sidebar {
            width: 280px;
            background: #1e293b;
            border-right: 1px solid #334155;
            overflow-y: auto;
            flex-shrink: 0;
        }
        .docs-main {
            flex: 1;
            overflow-y: auto;
            background: #0f172a;
        }
        .docs-header {
            background: linear-gradient(135deg, #1e3a5f, #1e293b);
            border-bottom: 1px solid #334155;
            padding: 32px 40px;
        }
        .docs-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 4px;
        }
        .docs-header p {
            font-size: 13px;
            color: #94a3b8;
        }
        .docs-section {
            padding: 24px 40px;
            border-bottom: 1px solid #1e293b;
        }
        .docs-section:last-child {
            border-bottom: none;
        }
        .docs-folder-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            margin: 0 -12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #cbd5e1;
            cursor: default;
        }
        .docs-folder-header .folder-icon {
            color: #fbbf24;
            flex-shrink: 0;
        }
        .docs-request-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            margin-bottom: 16px;
            overflow: hidden;
            transition: border-color 0.15s ease;
        }
        .docs-request-card:hover {
            border-color: #475569;
        }
        .docs-request-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid #334155;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease;
        }
        .docs-request-header:hover {
            background: rgba(255,255,255,0.02);
        }
        .docs-method-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 4px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            flex-shrink: 0;
            min-width: 60px;
            text-align: center;
        }
        .docs-method-badge.get { background: rgba(52,211,153,0.15); color: #34d399; }
        .docs-method-badge.post { background: rgba(96,165,250,0.15); color: #60a5fa; }
        .docs-method-badge.put { background: rgba(251,146,60,0.15); color: #fb923c; }
        .docs-method-badge.patch { background: rgba(250,204,21,0.15); color: #facc15; }
        .docs-method-badge.delete { background: rgba(248,113,113,0.15); color: #f87171; }
        .docs-method-badge.head { background: rgba(148,163,184,0.15); color: #94a3b8; }
        .docs-method-badge.options { background: rgba(167,139,250,0.15); color: #a78bfa; }
        .docs-req-url {
            font-size: 13px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            color: #e2e8f0;
            word-break: break-all;
            flex: 1;
        }
        .docs-req-name {
            font-size: 13px;
            font-weight: 500;
            color: #f1f5f9;
            flex-shrink: 0;
            max-width: 240px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .docs-request-body {
            padding: 18px;
            display: none;
        }
        .docs-request-body.open {
            display: block;
        }
        .docs-chevron {
            color: #64748b;
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .docs-chevron.open {
            transform: rotate(90deg);
        }
        .docs-detail-section {
            margin-bottom: 16px;
        }
        .docs-detail-section:last-child {
            margin-bottom: 0;
        }
        .docs-detail-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .docs-detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .docs-detail-table th {
            text-align: left;
            padding: 6px 10px;
            background: rgba(255,255,255,0.03);
            color: #94a3b8;
            font-weight: 500;
            font-size: 11px;
            border-bottom: 1px solid #334155;
        }
        .docs-detail-table td {
            padding: 6px 10px;
            color: #cbd5e1;
            border-bottom: 1px solid rgba(51,65,85,0.5);
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 11px;
        }
        .docs-detail-table tr:last-child td {
            border-bottom: none;
        }
        .docs-code-block {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 12px 14px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 11px;
            line-height: 1.6;
            color: #e2e8f0;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .docs-empty {
            color: #64748b;
            font-size: 12px;
            font-style: italic;
        }
        .docs-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            border: 1px solid #334155;
            background: #1e293b;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .docs-toggle-btn:hover {
            background: #334155;
            color: #e2e8f0;
        }
        .docs-toggle-btn.active {
            background: rgba(59,130,246,0.15);
            border-color: rgba(59,130,246,0.3);
            color: #60a5fa;
        }
        .docs-view-btn {
            transition: all 0.15s ease;
        }
        .docs-view-btn:hover:not(.active) {
            background: rgba(255,255,255,0.05) !important;
            color: #94a3b8 !important;
        }
        .docs-nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            font-size: 12px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.12s ease;
            border-left: 2px solid transparent;
        }
        .docs-nav-item:hover {
            background: rgba(255,255,255,0.03);
            color: #e2e8f0;
        }
        .docs-nav-item.active {
            background: rgba(59,130,246,0.08);
            color: #60a5fa;
            border-left-color: #3b82f6;
        }
        .docs-nav-folder {
            padding: 10px 16px 4px;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .docs-nav-req {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 16px 5px 28px;
            font-size: 12px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.12s ease;
            border-left: 2px solid transparent;
        }
        .docs-nav-req:hover {
            background: rgba(255,255,255,0.03);
            color: #e2e8f0;
        }
        .docs-nav-req.active {
            background: rgba(59,130,246,0.08);
            color: #60a5fa;
            border-left-color: #3b82f6;
        }
        .docs-nav-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .docs-nav-dot.get { background: #34d399; }
        .docs-nav-dot.post { background: #60a5fa; }
        .docs-nav-dot.put { background: #fb923c; }
        .docs-nav-dot.patch { background: #facc15; }
        .docs-nav-dot.delete { background: #f87171; }
        .docs-nav-dot.head { background: #94a3b8; }
        .docs-nav-dot.options { background: #a78bfa; }

        .docs-sidebar-title {
            padding: 16px 16px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #f1f5f9;
            border-bottom: 1px solid #334155;
        }
        .docs-sidebar-subtitle {
            padding: 4px 16px 12px;
            font-size: 11px;
            color: #64748b;
        }
        .print-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 50;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 25px -5px rgba(59,130,246,0.4);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px -5px rgba(59,130,246,0.5);
        }
        .print-btn:active {
            transform: translateY(0);
        }
        @media print {
            .docs-sidebar, .print-btn, .docs-request-header {
                display: none !important;
            }
            .docs-request-body {
                display: block !important;
            }
            .docs-main {
                overflow: visible !important;
            }
            body {
                background: white !important;
            }
            .docs-header {
                background: #f8fafc !important;
                border-bottom: 2px solid #e2e8f0 !important;
            }
            .docs-header h1 { color: #1e293b !important; }
            .docs-header p { color: #64748b !important; }
            .docs-request-card {
                border: 1px solid #e2e8f0 !important;
                break-inside: avoid !important;
            }
            .docs-detail-table td, .docs-detail-table th {
                color: #334155 !important;
            }
            .docs-code-block {
                background: #f8fafc !important;
                color: #334155 !important;
            }
        }
    </style>
</head>
<body class="bg-surface-900 text-surface-100 h-screen flex overflow-hidden selection:bg-blue-500/30">
    <!-- Sidebar -->
    <aside class="docs-sidebar">
        <div class="docs-sidebar-title">{{ $collection->name }}</div>
        <div class="docs-sidebar-subtitle">{{ count($collection->allItems) }} endpoints</div>

        <div style="padding: 0 12px; margin-bottom: 8px">
            <a href="/" style="display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:6px;font-size:11px;color:#94a3b8;text-decoration:none;transition:all 0.12s;border:1px solid #334155" onmouseover="this.style.background='#334155';this.style.color='#e2e8f0'" onmouseout="this.style.background='transparent';this.style.color='#94a3b8'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to App
            </a>
        </div>

        <nav>
            @foreach($tree as $node)
                @if($node['type'] === 'folder')
                    <div class="docs-nav-folder">
                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        {{ $node['name'] }}
                    </div>
                    @if(!empty($node['children']))
                        @foreach($node['children'] as $child)
                            <div class="docs-nav-req" onclick="scrollToReq('req-{{ $child['id'] }}')">
                                <span class="docs-nav-dot {{ strtolower($child['method'] ?? 'GET') }}"></span>
                                {{ $child['name'] }}
                            </div>
                        @endforeach
                    @endif
                @else
                    <div class="docs-nav-req" onclick="scrollToReq('req-{{ $node['id'] }}')">
                        <span class="docs-nav-dot {{ strtolower($node['method'] ?? 'GET') }}"></span>
                        {{ $node['name'] }}
                    </div>
                @endif
            @endforeach
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="docs-main">
        <!-- Header -->
        <div class="docs-header">
            <div style="display:flex;align-items:flex-start;justify-content:space-between">
                <div>
                    <h1>{{ $collection->name }}</h1>
                    <p>Collection documentation &middot; {{ count($collection->allItems) }} {{ Str::plural('endpoint', count($collection->allItems)) }}</p>
                </div>
                <button onclick="copyDocsLink()" class="docs-toggle-btn" title="Copy documentation link" style="flex-shrink:0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Copy Link
                </button>
            </div>
        </div>

        <!-- Requests -->
        <div class="p-0">
            @forelse($tree as $node)
                @if($node['type'] === 'folder')
                    <div class="docs-section" style="padding-bottom: 12px">
                        <div class="docs-folder-header">
                            <svg class="w-4 h-4 folder-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            {{ $node['name'] }}
                        </div>
                        <div style="margin-top: 12px">
                            @foreach($node['children'] ?? [] as $child)
                                @include('apitester.partials.docs-request', ['req' => $child])
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="docs-section">
                        @include('apitester.partials.docs-request', ['req' => $node])
                    </div>
                @endif
            @empty
                <div class="docs-section">
                    <div style="text-align:center;padding:60px 20px;color:#64748b">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p style="font-size:14px;font-weight:500;margin-bottom:4px">No endpoints yet</p>
                        <p style="font-size:12px">Add requests to this collection to see them here.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div style="padding: 20px 40px;border-top:1px solid #1e293b;text-align:center;font-size:11px;color:#475569">
            Generated by Incognito Testing &middot; {{ now()->format('F j, Y') }}
        </div>
    </main>

    <!-- Print Button -->
    <button class="print-btn" onclick="window.print()">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print / PDF
    </button>

    <script>
        function scrollToReq(id) {
            const el = document.getElementById(id);
            if (el) {
                // Close all open bodies first
                document.querySelectorAll('.docs-request-body.open').forEach(b => {
                    if (!el.querySelector('.docs-request-body') || !el.contains(b)) {
                        b.classList.remove('open');
                        b.closest('.docs-request-card')?.querySelector('.docs-chevron')?.classList.remove('open');
                    }
                });
                // Open this request
                const body = el.querySelector('.docs-request-body');
                const chevron = el.querySelector('.docs-chevron');
                if (body) body.classList.add('open');
                if (chevron) chevron.classList.add('open');
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Toggle request body expand/collapse
        document.addEventListener('click', function(e) {
            const header = e.target.closest('.docs-request-header');
            if (header) {
                const body = header.nextElementSibling;
                const chevron = header.querySelector('.docs-chevron');
                if (body) {
                    body.classList.toggle('open');
                    if (chevron) chevron.classList.toggle('open');
                }
            }
        });

        function copyDocsLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.querySelector('button[onclick="copyDocsLink()"]');
                if (btn) {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
                    btn.style.borderColor = 'rgba(52,211,153,0.3)';
                    btn.style.color = '#34d399';
                    setTimeout(() => {
                        btn.innerHTML = orig;
                        btn.style.borderColor = '';
                        btn.style.color = '';
                    }, 2000);
                }
            }).catch(() => {
                // Fallback: select the URL text
                const input = document.createElement('input');
                input.value = url;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
            });
        }

        // Toggle between Raw and Pretty view for body content
        function toggleBodyView(btn, view, id) {
            const toggles = btn.closest('.docs-view-toggles');
            if (toggles) {
                toggles.querySelectorAll('.docs-view-btn').forEach(b => {
                    b.style.background = 'transparent';
                    b.style.color = '#64748b';
                });
                btn.style.background = 'rgba(59,130,246,0.2)';
                btn.style.color = '#60a5fa';
            }
            const el = document.getElementById(id);
            if (el) {
                el.textContent = el.getAttribute('data-' + view);
            }
        }

        // Auto-open first request
        document.addEventListener('DOMContentLoaded', function() {
            const firstBody = document.querySelector('.docs-request-body');
            const firstChevron = document.querySelector('.docs-chevron');
            if (firstBody) firstBody.classList.add('open');
            if (firstChevron) firstChevron.classList.add('open');
        });
    </script>
</body>
</html>
