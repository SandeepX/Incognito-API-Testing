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
        width: 40px; height: 40px; 
        display: flex; align-items: center; justify-content: center; 
        border-radius: 8px; 
        color: #94a3b8; 
        cursor: pointer; 
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    .sidebar-icon:hover { 
        color: #60a5fa; 
        background: rgba(96, 165, 250, 0.1);
    }
    .sidebar-icon.active { 
        color: #60a5fa; 
        background: rgba(96, 165, 250, 0.15);
        border-left-color: #60a5fa;
    }

    /* Tab Styling */
    .tab { 
        display: inline-flex; align-items: center; gap: 6px; 
        padding: 6px 12px; margin: 0 2px;
        background: #334155; 
        color: #cbd5e1; 
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
        border-right: 1px solid #475569;
        transition: all 0.15s;
    }
    .tab:hover { background: #475569; }
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
    .sidebar-item:hover { background: rgba(96, 165, 250, 0.07); border-left-color: #3b82f6; }
    .sidebar-item.active { background: rgba(96, 165, 250, 0.1); border-left-color: #3b82f6; }

    /* Method Select */
    .method-select { 
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748b'/%3E%3C/svg%3E"); 
        background-repeat: no-repeat; 
        background-position: right 8px center; 
        padding-right: 24px; 
        appearance: none; 
    }

    /* Placeholder */
    input::placeholder, textarea::placeholder { color: #64748b; }

    /* Tree Items */
    .tree-item { 
        padding: 6px 12px; 
        font-size: 12px; 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        border-radius: 4px; 
        cursor: pointer; 
        transition: all 0.15s;
        color: #cbd5e1;
    }
    .tree-item:hover { 
        background: rgba(96, 165, 250, 0.07); 
        color: #e2e8f0;
    }
    .tree-item .toggle { 
        width: 14px; height: 14px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 8px; 
        color: #64748b; 
        transition: transform 0.15s; 
    }
    .tree-item .toggle.open { transform: rotate(90deg); }
    .tree-children { padding-left: 20px; }

    /* Environment Variables */
    .env-var-row { 
        display: flex; 
        gap: 6px; 
        align-items: center; 
        margin-bottom: 8px; 
    }
    .env-var-row input[type="text"] { flex: 1; }

    /* Collection Items */
    .collection-item { 
        padding: 6px 12px; 
        font-size: 12px; 
        cursor: pointer; 
        border-radius: 4px; 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        transition: background 0.15s;
        color: #cbd5e1;
    }
    .collection-item:hover { 
        background: rgba(96, 165, 250, 0.07); 
        color: #e2e8f0;
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

    /* Badges */
    .var-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 500; cursor: help; }
    .var-badge-found { background: #1e3a5f; color: #93c5fd; }
    .var-badge-missing { background: #450a0a; color: #fca5a5; }

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
