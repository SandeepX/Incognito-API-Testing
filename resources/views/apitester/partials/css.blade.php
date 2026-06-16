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
                    surface: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 650: '#3f4857', 700: '#334155', 750: '#2d3748', 800: '#1e293b', 850: '#1a202c', 900: '#0f172a' }
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
    
    /* Sidebar Icon Styling */
    .sidebar-icon { 
        width: 64px; min-height: 52px; 
        display: flex; flex-direction: column; align-items: center; justify-content: center; 
        border-radius: 8px; 
        color: #94a3b8; 
        cursor: pointer; 
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        padding: 4px 2px;
        gap: 1px;
    }
    .sidebar-icon:hover { 
        color: #3b82f6; 
        background: rgba(59, 130, 246, 0.08);
    }
    .dark .sidebar-icon:hover { 
        color: #60a5fa; 
        background: rgba(96, 165, 250, 0.1);
    }
    .sidebar-icon.active { 
        color: #3b82f6; 
        background: rgba(59, 130, 246, 0.1);
        border-left-color: #3b82f6;
    }
    .dark .sidebar-icon.active { 
        color: #60a5fa; 
        background: rgba(96, 165, 250, 0.15);
        border-left-color: #60a5fa;
    }

    /* Tab Styling */
    .tab { 
        display: inline-flex; align-items: center; gap: 6px; 
        padding: 6px 12px; margin: 0 2px;
        background: #f1f5f9; 
        color: #475569; 
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
        border-right: 1px solid #e2e8f0;
        transition: all 0.15s;
    }
    .dark .tab { 
        background: #334155; 
        color: #cbd5e1;
        border-right-color: #475569;
    }
    .tab:hover { background: #e2e8f0; }
    .dark .tab:hover { background: #475569; }
    .tab.active { 
        background: #3b82f6; 
        color: white;
    }
    .tab .close-tab {
        margin-left: 4px;
        opacity: 0.6;
        cursor: pointer;
        font-size: 14px;
    }
    .tab .close-tab:hover { opacity: 1; }

    /* Response Content */
    #response-body { white-space: pre-wrap; word-break: break-word; font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace; font-size: 13px; line-height: 1.65; tab-size: 2; color: #e2e8f0; }
    #response-body pre { color: #e2e8f0; }
    #response-body .json-key { color: #d183e8; }
    #response-body .json-string { color: #6ee7b7; }
    #response-body .json-number { color: #fbbf24; }
    #response-body .json-bool { color: #60a5fa; }
    #response-body .json-null { color: #94a3b8; font-style: italic; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #475569; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #64748b; }

    /* Sidebar Items */
    .sidebar-item { transition: all 0.15s ease; border-left: 2px solid transparent; cursor: pointer; }
    .sidebar-item:hover { background: rgba(59, 130, 246, 0.06); border-left-color: #3b82f6; }
    .dark .sidebar-item:hover { background: rgba(96, 165, 250, 0.07); }
    .sidebar-item.active { background: rgba(59, 130, 246, 0.08); border-left-color: #3b82f6; }
    .dark .sidebar-item.active { background: rgba(96, 165, 250, 0.1); }

    /* Drag & Drop */
    .drag-handle { display: inline-flex; align-items: center; justify-content: center; }
    .drag-handle:active { cursor: grabbing; }
    .drag-handle.dragging { opacity: 0.4; }
    .drag-over {
        background: rgba(59, 130, 246, 0.1) !important;
        border-radius: 6px;
        outline: 2px dashed #3b82f6;
        outline-offset: -2px;
    }
    .dark .drag-over {
        background: rgba(96, 165, 250, 0.15) !important;
    }
    .collection-drop-zone.drag-over {
        border-color: #3b82f6 !important;
        background: rgba(59, 130, 246, 0.06) !important;
        color: #2563eb !important;
    }
    .dark .collection-drop-zone.drag-over {
        background: rgba(96, 165, 250, 0.08) !important;
        color: #60a5fa !important;
    }

    /* Method Select */
    .method-select { 
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748b'/%3E%3C/svg%3E"); 
        background-repeat: no-repeat; 
        background-position: right 8px center; 
        padding-right: 24px; 
        appearance: none; 
    }

    /* Placeholder */
    input::placeholder, textarea::placeholder { color: #9ca3af; }
    .dark input::placeholder, .dark textarea::placeholder { color: #64748b; }

    /* Tree Items - Postman Style */
    .tree-item { 
        padding: 6px 12px; 
        font-size: 12px; 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        border-radius: 4px; 
        cursor: pointer; 
        transition: all 0.15s;
        color: #475569;
    }
    .dark .tree-item { 
        color: #cbd5e1;
    }
    .tree-item:hover { 
        background: rgba(59, 130, 246, 0.06); 
        color: #1e293b;
    }
    .dark .tree-item:hover { 
        background: rgba(96, 165, 250, 0.07); 
        color: #e2e8f0;
    }
    .tree-item .toggle { 
        width: 14px; height: 14px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 8px; 
        color: #94a3b8; 
        transition: transform 0.15s; 
    }
    .dark .tree-item .toggle { 
        color: #64748b;
    }
    .tree-item .toggle.open { transform: rotate(90deg); }
    .tree-children { padding-left: 20px; }

    /* ========= Bulk Selection Mode ========= */
    .sel-checkbox {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        border: 2px solid #cbd5e1;
        background: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.12s ease;
        margin-right: 2px;
    }
    .dark .sel-checkbox {
        border-color: #475569;
        background: transparent;
    }
    .sel-checkbox:hover {
        border-color: #3b82f6;
        background: rgba(59,130,246,0.1);
    }
    .dark .sel-checkbox:hover {
        background: rgba(96,165,250,0.12);
    }
    .sel-checkbox.checked {
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .sel-checkbox.checked::after {
        content: '';
        width: 5px;
        height: 9px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        margin-top: -1px;
    }
    .sel-highlight {
        background: rgba(59,130,246,0.08) !important;
        border-left-color: #3b82f6 !important;
    }
    .dark .sel-highlight {
        background: rgba(96,165,250,0.12) !important;
        border-left-color: #60a5fa !important;
    }
    /* Bulk action toolbar */
    #bulk-action-bar {
        display: none;
        padding: 6px 10px;
        border-bottom: 1px solid #e2e8f0;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
    }
    .dark #bulk-action-bar {
        border-bottom-color: #334155;
        background: rgba(255,255,255,0.02);
    }
    #bulk-action-bar.show {
        display: flex;
    }
    #bulk-action-bar .bulk-count {
        font-size: 11px;
        font-weight: 600;
        color: #1e293b;
        margin-right: auto;
    }
    .dark #bulk-action-bar .bulk-count {
        color: #e2e8f0;
    }
    .bulk-btn {
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.12s ease;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .bulk-btn.move {
        background: #e0e7ff;
        color: #3730a3;
    }
    .dark .bulk-btn.move {
        background: rgba(99,102,241,0.15);
        color: #818cf8;
    }
    .bulk-btn.move:hover {
        background: #c7d2fe;
    }
    .dark .bulk-btn.move:hover {
        background: rgba(99,102,241,0.25);
    }
    .bulk-btn.delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .dark .bulk-btn.delete {
        background: rgba(239,68,68,0.15);
        color: #f87171;
    }
    .bulk-btn.delete:hover {
        background: #fecaca;
    }
    .dark .bulk-btn.delete:hover {
        background: rgba(239,68,68,0.25);
    }
    .bulk-btn.deselect {
        background: #f1f5f9;
        color: #64748b;
    }
    .dark .bulk-btn.deselect {
        background: rgba(255,255,255,0.04);
        color: #94a3b8;
    }
    .bulk-btn.deselect:hover {
        background: #e2e8f0;
    }
    .dark .bulk-btn.deselect:hover {
        background: rgba(255,255,255,0.08);
    }
    .tree-request .sel-checkbox {
        margin-right: 4px;
        margin-left: -2px;
    }
    .folder-header .sel-checkbox {
        margin-right: 2px;
    }

    /* Postman-style folder headers */
    .folder-header {
        padding: 7px 8px 7px 6px;
        margin: 1px 4px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.12s ease;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 4px;
        user-select: none;
        border-left: 2px solid transparent;
    }
    .dark .folder-header {
        color: #cbd5e1;
    }
    .folder-header:hover {
        background: rgba(59, 130, 246, 0.06);
        color: #1e293b;
        border-left-color: #3b82f6;
    }
    .dark .folder-header:hover {
        background: rgba(96, 165, 250, 0.07);
        color: #e2e8f0;
    }
    .folder-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: #d97706;
    }
    .dark .folder-icon {
        color: #fbbf24;
    }
    .folder-toggle {
        width: 14px;
        text-align: center;
        flex-shrink: 0;
        font-size: 8px;
        color: #64748b;
        transition: transform 0.15s ease;
    }
    .folder-toggle.open {
        transform: rotate(90deg);
    }

    /* Postman-style request items in tree */
    .tree-request {
        padding: 6px 8px 6px 10px;
        margin: 1px 4px 1px 8px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.12s ease;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 7px;
        border-left: 2px solid transparent;
    }
    .dark .tree-request {
        color: #cbd5e1;
    }
    .tree-request:hover {
        background: rgba(59, 130, 246, 0.06);
        color: #1e293b;
        border-left-color: #3b82f6;
    }
    .dark .tree-request:hover {
        background: rgba(96, 165, 250, 0.07);
        color: #e2e8f0;
    }
    .tree-request .req-method-pill {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 3px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        min-width: 40px;
        text-align: center;
        flex-shrink: 0;
    }
    .tree-request .req-name {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 12px;
    }

    /* Drag & Drop Ghost Element */
    .drag-ghost {
        opacity: 0.85;
        transform: scale(1.02);
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        border: 2px solid #3b82f6 !important;
        border-radius: 6px;
        background: #ffffff !important;
        z-index: 100;
    }
    .dark .drag-ghost {
        background: #1e293b !important;
    }
    .drag-ghost * {
        pointer-events: none;
    }

    /* Drop Zone Indicators */
    .drop-indicator {
        height: 3px;
        background: #3b82f6;
        border-radius: 2px;
        margin: 2px 8px;
        animation: pulse-drop 1s infinite;
    }
    .dark .drop-indicator {
        background: #60a5fa;
    }
    @keyframes pulse-drop {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }

    /* Same-level reorder indicators */
    .reorder-drop-top {
        position: relative;
    }
    .reorder-drop-top::before {
        content: '';
        position: absolute;
        top: -3px;
        left: 0;
        right: 0;
        height: 4px;
        background: #3b82f6;
        border-radius: 2px;
        z-index: 10;
        box-shadow: 0 0 8px rgba(59,130,246,0.5);
        pointer-events: none;
    }
    .reorder-drop-bottom {
        position: relative;
    }
    .reorder-drop-bottom::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        right: 0;
        height: 4px;
        background: #3b82f6;
        border-radius: 2px;
        z-index: 10;
        box-shadow: 0 0 8px rgba(59,130,246,0.5);
        pointer-events: none;
    }

    /* Reorder target highlight */
    .reorder-target {
        background: rgba(59, 130, 246, 0.08) !important;
        outline: 1px dashed #3b82f6;
        outline-offset: -1px;
    }
    .dark .reorder-target {
        background: rgba(96, 165, 250, 0.12) !important;
    }

    /* Hover Toolbar for collections */
    .coll-hover-toolbar {
        display: flex;
        gap: 2px;
        opacity: 0;
        transition: opacity 0.12s ease;
    }
    .collection-item:hover .coll-hover-toolbar,
    .folder-header:hover .coll-hover-toolbar,
    .tree-request:hover .coll-hover-toolbar {
        opacity: 1;
    }
    .coll-hover-btn {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.12s ease;
        font-size: 12px;
    }
    .dark .coll-hover-btn {
        color: #64748b;
    }
    .coll-hover-btn:hover {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }
    .dark .coll-hover-btn:hover {
        background: rgba(96, 165, 250, 0.15);
        color: #60a5fa;
    }
    .coll-hover-btn.danger:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }
    .dark .coll-hover-btn.danger:hover {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    /* Environment Variables */
    .env-var-row { 
        display: flex; 
        gap: 6px; 
        align-items: center; 
        margin-bottom: 8px; 
    }
    .env-var-row input[type="text"] { flex: 1; }

    /* Body Textareas */
    .body-textarea {
        resize: vertical;
        min-height: 120px;
        max-height: 600px;
    }
    .body-textarea::-webkit-resizer {
        border-width: 8px;
        border-style: solid;
        border-color: transparent #3b82f6 #3b82f6 transparent;
        border-radius: 0 0 4px 0;
    }

    /* Collection Items - Postman Style */
    .collection-item { 
        padding: 10px 12px; 
        font-size: 13px; 
        cursor: pointer; 
        border-radius: 6px; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        transition: all 0.15s ease;
        color: #334155;
        margin: 2px 6px;
        border-left: 3px solid transparent;
        font-weight: 500;
    }
    .dark .collection-item { 
        color: #e2e8f0;
    }
    .collection-item:hover { 
        background: rgba(59, 130, 246, 0.06); 
        color: #1e293b;
        border-left-color: #3b82f6;
    }
    .dark .collection-item:hover { 
        background: rgba(96, 165, 250, 0.08); 
        color: #f1f5f9;
    }
    .collection-item .coll-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
    }
    .collection-item .coll-info .coll-name {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.3;
    }
    .dark .collection-item .coll-info .coll-name {
        color: #f1f5f9;
    }
    .collection-item .coll-info .coll-meta {
        font-size: 10px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 6px;
        line-height: 1.2;
    }
    .dark .collection-item .coll-info .coll-meta {
        color: #64748b;
    }
    .collection-item .coll-info .coll-meta .coll-count {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 600;
    }
    .dark .collection-item .coll-info .coll-meta .coll-count {
        background: rgba(96, 165, 250, 0.12);
        color: #60a5fa;
    }
    .collection-item .coll-info .coll-meta .coll-folder-count {
        background: rgba(217, 119, 6, 0.1);
        color: #d97706;
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 600;
    }
    .dark .collection-item .coll-info .coll-meta .coll-folder-count {
        background: rgba(251, 191, 36, 0.12);
        color: #fbbf24;
    }

    /* Postman-style collection icons */
    .coll-icon {
        width: 34px;
        height: 34px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 15px;
        font-weight: 700;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .coll-icon.collection {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }
    .coll-icon.api {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }
    .coll-icon.documentation {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
    }

    /* Sidebar Panel - Postman Style */
    #sidebar-panel {
        width: 300px;
    }
    .sidebar-panel-header {
        padding: 12px 14px 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .sidebar-panel-header {
        border-bottom-color: #334155;
    }
    .sidebar-panel-header h3 {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        letter-spacing: -0.02em;
    }
    .dark .sidebar-panel-header h3 {
        color: #f1f5f9;
    }
    .collections-search {
        margin: 8px 10px;
        position: relative;
    }
    .collections-search input {
        width: 100%;
        padding: 7px 10px 7px 30px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
        background: #ffffff;
        color: #1e293b;
        outline: none;
        transition: all 0.15s ease;
        box-sizing: border-box;
    }
    .dark .collections-search input {
        border-color: #334155;
        background: rgba(255,255,255,0.04);
        color: #e2e8f0;
    }
    .collections-search input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
    }
    .collections-search input::placeholder {
        color: #9ca3af;
    }
    .dark .collections-search input::placeholder {
        color: #64748b;
    }
    .collections-search .search-icon {
        position: absolute;
        left: 9px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }
    .dark .collections-search .search-icon {
        color: #64748b;
    }

    /* Collection Action Bar */
    .coll-action-bar {
        display: flex;
        gap: 4px;
        padding: 6px 10px;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .coll-action-bar {
        border-bottom-color: #334155;
    }
    .coll-action-btn {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 5px;
        background: #f1f5f9;
        color: #475569;
    }
    .dark .coll-action-btn {
        background: rgba(255,255,255,0.05);
        color: #cbd5e1;
    }
    .coll-action-btn:hover {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }
    .dark .coll-action-btn:hover {
        background: rgba(96, 165, 250, 0.15);
        color: #60a5fa;
    }
    .coll-action-btn.primary {
        background: #3b82f6;
        color: white;
    }
    .coll-action-btn.primary:hover {
        background: #2563eb;
    }

    /* Badges */
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

    /* Variable Chips */
    .var-chip { display: inline-flex; align-items: center; gap: 3px; padding: 0 5px; border-radius: 4px; font-size: 10px; font-weight: 600; cursor: help; transition: all 0.15s; }
    .var-chip-name { background: #1e3a5f; color: #93c5fd; border: 1px solid #1e40af; }
    .var-chip-resolved { background: #14532d; color: #86efac; border: 1px solid #166534; }
    .var-chip-missing { background: #450a0a; color: #fca5a5; border: 1px solid #7f1d1d; }

    /* Tooltips */
    .var-tooltip:hover::after { 
        content: attr(data-value); 
        position: absolute; 
        bottom: calc(100% + 6px); 
        left: 50%; 
        transform: translateX(-50%); 
        background: #1e293b; 
        color: #e2e8f0; 
        padding: 4px 10px; 
        border-radius: 6px; 
        font-size: 11px; 
        white-space: nowrap; 
        z-index: 50; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.4); 
        pointer-events: none; 
        font-weight: 400; 
    }

    /* Variable badges - blue highlighted chips */
    .var-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; cursor: help; letter-spacing: 0.02em; }
    .var-badge-found { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
    .dark .var-badge-found { background: #1e3a5f; color: #93c5fd; border-color: #1e40af; }
    .var-badge-missing { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
    .dark .var-badge-missing { background: #450a0a; color: #fca5a5; border-color: #7f1d1d; }

    /* Input field highlighting when it contains @{{variables}} */
    .has-vars {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important;
    }
    .dark .has-vars {
        border-color: #60a5fa !important;
        box-shadow: 0 0 0 3px rgba(96,165,250,0.15) !important;
    }
    .kv-row input.has-vars {
        border-color: #3b82f6 !important;
    }
    .dark .kv-row input.has-vars {
        border-color: #60a5fa !important;
    }
    /* Field var badge container */
    .field-var-badge {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
        margin-top: 3px;
        margin-bottom: 2px;
    }

    /* URL Field Placeholder */
    #url:empty:before { content: attr(data-placeholder); color: #64748b; pointer-events: none; }

    /* Toast */
    .toast { 
        position: fixed; 
        bottom: 24px; 
        left: 50%; 
        transform: translateX(-50%) translateY(12px); 
        background: #1e293b; 
        color: white; 
        padding: 10px 24px; 
        border-radius: 8px; 
        font-size: 13px; 
        z-index: 999; 
        opacity: 0; 
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1); 
        pointer-events: none; 
        box-shadow: 0 8px 32px rgba(0,0,0,0.3); 
    }
    .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }



    /* Resize Handle */
    .resize-handle {
        height: 10px;
        cursor: ns-resize;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 5;
        user-select: none;
    }
    .resize-handle::before {
        content: '';
        position: absolute;
        inset: 3px 0;
        border-top: 1px solid #cbd5e1;
        pointer-events: none;
        transition: border-color 0.15s;
    }
    .dark .resize-handle::before { border-top-color: #475569; }
    .resize-handle:hover::before { border-top-color: #3b82f6; }
    .resize-handle.dragging::before { border-top-color: #3b82f6; border-top-width: 2px; }

    /* ========= SweetAlert2 Dark Theme Overrides ========= */
    .dark .swal2-popup {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important;
    }
    .dark .swal2-title {
        color: #f1f5f9 !important;
        font-size: 16px !important;
        font-weight: 600 !important;
    }
    .dark .swal2-html-container {
        color: #cbd5e1 !important;
    }
    .dark .swal2-close {
        color: #94a3b8 !important;
    }
    .dark .swal2-close:hover {
        color: #f1f5f9 !important;
    }
    .dark .swal2-input,
    .dark .swal2-textarea,
    .dark .swal2-select {
        background: rgba(255,255,255,0.04) !important;
        border: 1px solid rgba(255,255,255,0.08) !important;
        color: #e2e8f0 !important;
        box-shadow: none !important;
    }
    .dark .swal2-input:focus,
    .dark .swal2-textarea:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important;
    }
    .dark .swal2-input::placeholder,
    .dark .swal2-textarea::placeholder {
        color: rgba(255,255,255,0.25) !important;
    }
    .dark .swal2-validation-message {
        background: rgba(239,68,68,0.1) !important;
        color: #fca5a5 !important;
        border-color: rgba(239,68,68,0.2) !important;
    }
    .dark .swal2-confirm.swal2-styled {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        padding: 8px 24px !important;
        border-radius: 8px !important;
        box-shadow: none !important;
    }
    .dark .swal2-confirm.swal2-styled:hover {
        background: linear-gradient(135deg, #60a5fa, #3b82f6) !important;
        transform: translateY(-1px);
        box-shadow: 0 8px 25px -5px rgba(59,130,246,0.4) !important;
    }
    .dark .swal2-cancel.swal2-styled {
        background: rgba(255,255,255,0.06) !important;
        color: #e2e8f0 !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        padding: 8px 24px !important;
        border-radius: 8px !important;
        border: 1px solid rgba(255,255,255,0.08) !important;
        box-shadow: none !important;
    }
    .dark .swal2-cancel.swal2-styled:hover {
        background: rgba(255,255,255,0.1) !important;
    }
    .dark .swal2-icon.swal2-warning {
        border-color: rgba(251,191,36,0.3) !important;
        color: #fbbf24 !important;
    }
    .dark .swal2-icon.swal2-error {
        border-color: rgba(248,113,113,0.3) !important;
        color: #f87171 !important;
    }
    .dark .swal2-icon.swal2-success {
        border-color: rgba(110,231,183,0.3) !important;
        color: #6ee7b7 !important;
    }
    .dark .swal2-icon.swal2-info {
        border-color: rgba(96,165,250,0.3) !important;
        color: #60a5fa !important;
    }
    .dark .swal2-icon.swal2-question {
        border-color: rgba(167,139,250,0.3) !important;
        color: #a78bfa !important;
    }
    .swal2-popup {
        border-radius: 16px !important;
        padding: 12px !important;
    }
    .swal2-title {
        font-size: 16px !important;
        font-weight: 600 !important;
    }
    .swal2-html-container {
        font-size: 13px !important;
        line-height: 1.5 !important;
    }
    .swal2-actions {
        margin-top: 8px !important;
    }

    /* ========= Modal Content Styles (light + dark aware) ========= */
    .modal-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        background: #f8fafc;
        transition: all 0.15s ease;
    }
    .dark .modal-card {
        border-color: #334155;
        background: rgba(255,255,255,0.03);
    }
    .modal-input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 12px;
        background: #fff;
        color: #334155;
        box-sizing: border-box;
        transition: all 0.15s ease;
    }
    .dark .modal-input {
        background: rgba(255,255,255,0.04);
        border-color: rgba(255,255,255,0.08);
        color: #e2e8f0;
    }
    .dark .modal-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        outline: none;
    }
    .modal-input::placeholder {
        color: #94a3b8;
    }
    .dark .modal-input::placeholder {
        color: rgba(255,255,255,0.25);
    }
    .modal-label {
        font-size: 12px;
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
        color: #475569;
    }
    .dark .modal-label {
        color: #94a3b8;
    }
    .modal-text-secondary {
        font-size: 11px;
        color: #64748b;
    }
    .dark .modal-text-secondary {
        color: #94a3b8;
    }
    .modal-divider {
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .modal-divider {
        border-color: #334155;
    }
    .modal-section-title {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
    }
    .dark .modal-section-title {
        color: #cbd5e1;
    }
    .modal-member-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .modal-member-row {
        border-color: #334155;
    }
    .modal-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 11px;
        font-weight: bold;
        flex-shrink: 0;
    }
    .modal-role-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 500;
    }
    .modal-role-badge.owner {
        background: #fef3c7;
        color: #92400e;
    }
    .dark .modal-role-badge.owner {
        background: rgba(251,191,36,0.15);
        color: #fbbf24;
    }
    .modal-role-badge.admin {
        background: #e0e7ff;
        color: #3730a3;
    }
    .dark .modal-role-badge.admin {
        background: rgba(99,102,241,0.15);
        color: #818cf8;
    }
    .modal-role-badge.member {
        background: #dcfce7;
        color: #166534;
    }
    .dark .modal-role-badge.member {
        background: rgba(74,222,128,0.12);
        color: #6ee7b7;
    }
    .modal-btn-primary {
        padding: 6px 12px;
        background: #4f46e5;
        color: white;
        border-radius: 6px;
        font-size: 11px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.15s ease;
    }
    .modal-btn-primary:hover {
        background: #6366f1;
    }
    .modal-btn-sm {
        padding: 2px 8px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 10px;
        font-weight: 500;
        transition: all 0.15s ease;
    }
    .modal-btn-copy {
        background: #e0e7ff;
        color: #3730a3;
    }
    .dark .modal-btn-copy {
        background: rgba(99,102,241,0.15);
        color: #818cf8;
    }
    .modal-btn-copy:hover {
        background: #c7d2fe;
    }
    .dark .modal-btn-copy:hover {
        background: rgba(99,102,241,0.25);
    }
    .modal-btn-danger {
        color: #ef4444;
        font-size: 16px;
        border: none;
        background: none;
        cursor: pointer;
        padding: 0 2px;
        line-height: 1;
    }
    .modal-btn-danger:hover {
        color: #dc2626;
    }
    .dark .modal-btn-danger {
        color: #f87171;
    }
    .dark .modal-btn-danger:hover {
        color: #fca5a5;
    }
    .modal-add-btn {
        font-size: 11px;
        color: #4f46e5;
        border: none;
        background: none;
        cursor: pointer;
        transition: color 0.15s ease;
    }
    .dark .modal-add-btn {
        color: #818cf8;
    }
    .modal-add-btn:hover {
        color: #6366f1;
    }
</style>
