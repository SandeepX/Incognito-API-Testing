<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    .kv-row { display: flex; gap: 6px; align-items: center; }
    .kv-row input { flex: 1; }
    .fade-in { animation: fadeIn 0.2s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
    #response-body { white-space: pre-wrap; word-break: break-word; font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace; font-size: 13px; line-height: 1.65; tab-size: 2; color: #1e293b; }
    .dark #response-body { color: #e2e8f0; }
    .dark #response-body pre { color: #e2e8f0; }
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
    .sidebar-item { transition: all 0.15s ease; border-left: 2px solid transparent; cursor: pointer; }
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
    .tree-item { padding: 4px 8px 4px 12px; font-size: 12px; display: flex; align-items: center; gap: 6px; border-radius: 6px; cursor: pointer; transition: all 0.15s; }
    .tree-item:hover { background: rgba(99,102,241,0.07); }
    .tree-item .toggle { width: 14px; height: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 8px; color: #94a3b8; transition: transform 0.15s; }
    .tree-item .toggle.open { transform: rotate(90deg); }
    .tree-children { padding-left: 20px; }
    .env-var-row { display: flex; gap: 6px; align-items: center; margin-bottom: 4px; }
    .env-var-row input[type="text"] { flex: 1; }
    .collection-item { padding: 4px 8px; font-size: 12px; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 6px; transition: background 0.15s; }
    .collection-item:hover { background: rgba(99,102,241,0.07); }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .var-chip { display: inline-flex; align-items: center; gap: 3px; padding: 0 5px; border-radius: 4px; font-size: 10px; font-weight: 600; cursor: help; transition: all 0.15s; }
    .var-chip-name { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .dark .var-chip-name { background: #1e3a5f; color: #93c5fd; border-color: #1e40af; }
    .var-chip-resolved { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .dark .var-chip-resolved { background: #14532d; color: #86efac; border-color: #166534; }
    .var-chip-missing { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .dark .var-chip-missing { background: #450a0a; color: #fca5a5; border-color: #7f1d1d; }
    .var-tooltip { position: relative; }
    .var-tooltip:hover::after { content: attr(data-value); position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: #1e293b; color: #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 11px; white-space: nowrap; z-index: 50; box-shadow: 0 4px 12px rgba(0,0,0,0.2); pointer-events: none; font-weight: 400; }
    .dark .var-tooltip:hover::after { background: #f1f5f9; color: #1e293b; }
    .var-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 500; cursor: help; }
    .var-badge-found { background: #dbeafe; color: #1d4ed8; }
    .dark .var-badge-found { background: #1e3a5f; color: #93c5fd; }
    .var-badge-missing { background: #fee2e2; color: #dc2626; }
    .dark .var-badge-missing { background: #450a0a; color: #fca5a5; }
    .field-var-badge { display: inline-flex; gap: 3px; margin-left: 4px; flex-shrink: 0; }
</style>
