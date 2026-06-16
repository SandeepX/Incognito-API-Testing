@php
    $rd = $req['request_data'] ?? [];
    $method = strtoupper($req['method'] ?? $rd['method'] ?? 'GET');
    $url = $req['url'] ?? $rd['url'] ?? '';
    $headers = $rd['headers'] ?? [];
    $params = $rd['params'] ?? [];
    $cookies = $rd['cookies'] ?? [];
    $bodyType = $rd['bodyType'] ?? 'none';
    $body = $rd['body'] ?? '';
    $rawBody = $rd['rawBody'] ?? '';
    $auth = $rd['auth'] ?? ['type' => 'none'];

    // Determine raw and pretty versions of body content
    $bodyPretty = $body;
    $bodyRaw = $body;
    if ($bodyType === 'json' && !empty($body)) {
        $decoded = json_decode($body);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            $bodyPretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $bodyRaw = $body;
        }
    } elseif ($bodyType === 'raw' && !empty($rawBody)) {
        $decoded = json_decode($rawBody);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            $bodyPretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $bodyRaw = $rawBody;
        } else {
            $bodyPretty = $rawBody;
            $bodyRaw = $rawBody;
        }
    }
@endphp

<div class="docs-request-card" id="req-{{ $req['id'] }}">
    <div class="docs-request-header">
        <span class="docs-chevron">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </span>
        <span class="docs-method-badge {{ strtolower($method) }}">{{ $method }}</span>
        <span class="docs-req-name">{{ $req['name'] }}</span>
        <span class="docs-req-url">{{ $url }}</span>
    </div>
    <div class="docs-request-body">
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            <span style="font-size:11px;color:#64748b;background:rgba(255,255,255,0.03);padding:3px 10px;border-radius:4px">
                {{ strtoupper($method) }}
            </span>
            @if($auth['type'] !== 'none')
                <span style="font-size:11px;color:#a78bfa;background:rgba(167,139,250,0.1);padding:3px 10px;border-radius:4px">
                    {{ ucfirst($auth['type']) }} Auth
                </span>
            @endif
            @if($bodyType !== 'none')
                <span style="font-size:11px;color:#60a5fa;background:rgba(96,165,250,0.1);padding:3px 10px;border-radius:4px">
                    {{ ucfirst($bodyType) }}
                </span>
            @endif
        </div>

        <!-- Description -->
        @if(!empty($req['description']))
            <div class="docs-detail-section">
                <div class="docs-detail-label">Description</div>
                <div style="font-size:13px;line-height:1.7;color:#cbd5e1;padding:2px 0 8px 0">{{ nl2br(e($req['description'])) }}</div>
            </div>
        @endif

        <!-- URL -->
        <div class="docs-detail-section">
            <div class="docs-detail-label">Request URL</div>
            <div class="docs-code-block">{{ $method }} {{ $url }}</div>
        </div>

        <!-- Auth -->
        @if($auth['type'] !== 'none')
            <div class="docs-detail-section">
                <div class="docs-detail-label">Authorization</div>
                <table class="docs-detail-table">
                    <thead>
                        <tr><th style="width:120px">Type</th><th>Value</th></tr>
                    </thead>
                    <tbody>
                        @if($auth['type'] === 'bearer')
                            <tr><td>Bearer Token</td><td>{{ Str::limit($auth['bearer'] ?? '', 80) }}</td></tr>
                        @elseif($auth['type'] === 'basic')
                            <tr><td>Username</td><td>{{ $auth['username'] ?? '' }}</td></tr>
                            <tr><td>Password</td><td>••••••••</td></tr>
                        @elseif($auth['type'] === 'apikey')
                            <tr><td>Key Name</td><td>{{ $auth['keyName'] ?? 'X-API-Key' }}</td></tr>
                            <tr><td>Value</td><td>{{ $auth['keyValue'] ?? '' }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Query Params -->
        @if(!empty($params))
            <div class="docs-detail-section">
                <div class="docs-detail-label">Query Parameters</div>
                <table class="docs-detail-table">
                    <thead>
                        <tr><th>Name</th><th>Value</th></tr>
                    </thead>
                    <tbody>
                        @foreach($params as $p)
                            @if(!empty($p['key']))
                                <tr><td>{{ $p['key'] }}</td><td>{{ $p['value'] ?? '' }}</td></tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Headers -->
        @if(!empty($headers))
            <div class="docs-detail-section">
                <div class="docs-detail-label">Headers</div>
                <table class="docs-detail-table">
                    <thead>
                        <tr><th>Name</th><th>Value</th></tr>
                    </thead>
                    <tbody>
                        @foreach($headers as $h)
                            @if(!empty($h['key']))
                                <tr><td>{{ $h['key'] }}</td><td>{{ $h['value'] ?? '' }}</td></tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Body -->
        @if($bodyType !== 'none')
            <div class="docs-detail-section">
                <div class="docs-detail-label" style="display:flex;align-items:center;gap:8px;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;color:#64748b">
                    <span>Request Body ({{ ucfirst($bodyType) }})</span>
                    @if($bodyType === 'json' || ($bodyType === 'raw' && !empty($rawBody) && json_decode($rawBody) !== null))
                        <span class="docs-view-toggles" style="margin-left:auto">
                            <button class="docs-view-btn active" data-view="pretty" onclick="toggleBodyView(this,'pretty','body-{{ $req['id'] }}')" style="padding:2px 8px;border-radius:3px;font-size:10px;font-weight:500;border:1px solid transparent;cursor:pointer;background:rgba(59,130,246,0.2);color:#60a5fa">Pretty</button>
                            <button class="docs-view-btn" data-view="raw" onclick="toggleBodyView(this,'raw','body-{{ $req['id'] }}')" style="padding:2px 8px;border-radius:3px;font-size:10px;font-weight:500;border:1px solid transparent;cursor:pointer;background:transparent;color:#64748b">Raw</button>
                        </span>
                    @endif
                </div>
                @if($bodyType === 'json' && !empty($body))
                    <div class="docs-code-block docs-body-content" id="body-{{ $req['id'] }}" data-pretty="{{ e($bodyPretty) }}" data-raw="{{ e($bodyRaw) }}">{{ $bodyPretty }}</div>
                @elseif($bodyType === 'raw' && !empty($rawBody))
                    @php $isJson = json_decode($rawBody) !== null; @endphp
                    <div class="docs-code-block docs-body-content" id="body-{{ $req['id'] }}" data-pretty="{{ e($bodyPretty) }}" data-raw="{{ e($bodyRaw) }}">{{ $isJson ? $bodyPretty : $rawBody }}</div>
                @elseif($bodyType === 'form-data' && !empty($rd['formData']))
                    <table class="docs-detail-table">
                        <thead><tr><th>Name</th><th>Value</th></tr></thead>
                        <tbody>
                            @foreach($rd['formData'] as $f)
                                @if(!empty($f['key']))
                                    <tr><td>{{ $f['key'] }}</td><td>{{ $f['value'] ?? '' }}</td></tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <span class="docs-empty">No body content</span>
                @endif
            </div>
        @endif

        <!-- Cookies -->
        @if(!empty($cookies))
            <div class="docs-detail-section">
                <div class="docs-detail-label">Cookies</div>
                <table class="docs-detail-table">
                    <thead><tr><th>Name</th><th>Value</th></tr></thead>
                    <tbody>
                        @foreach($cookies as $c)
                            @if(!empty($c['key']))
                                <tr><td>{{ $c['key'] }}</td><td>{{ $c['value'] ?? '' }}</td></tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Examples -->
        @if(!empty($req['examples']))
            <div class="docs-detail-section">
                <div class="docs-detail-label">Examples ({{ count($req['examples']) }})</div>
                @foreach($req['examples'] as $exIdx => $ex)
                    @php
                        $exBody = $ex['body'] ?? '';
                        $exDecoded = json_decode($exBody);
                        $exIsJson = json_last_error() === JSON_ERROR_NONE && $exDecoded !== null;
                        $exPretty = $exIsJson ? json_encode($exDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $exBody;
                        $exRaw = $exBody;
                        $exId = $req['id'].'-ex-'.$exIdx;
                    @endphp
                    <div style="background:#0f172a;border:1px solid #334155;border-radius:8px;margin-bottom:10px;overflow:hidden">
                        <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid #334155;font-size:11px;color:#94a3b8;background:rgba(255,255,255,0.02)">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16"/></svg>
                            <span style="font-weight:600;color:#f1f5f9">{{ $ex['name'] }}</span>
                            @if(!empty($ex['status']))
                                <span style="margin-left:auto;padding:2px 8px;border-radius:4px;font-weight:600;background:{{ $ex['status'] >= 200 && $ex['status'] < 300 ? 'rgba(52,211,153,0.15)' : 'rgba(248,113,113,0.15)' }};color:{{ $ex['status'] >= 200 && $ex['status'] < 300 ? '#34d399' : '#f87171' }}">
                                    Status: {{ $ex['status'] }}
                                </span>
                            @endif
                            @if($exIsJson)
                                <span class="docs-view-toggles">
                                    <button class="docs-view-btn active" data-view="pretty" onclick="toggleBodyView(this,'pretty','{{ $exId }}')" style="padding:2px 8px;border-radius:3px;font-size:10px;font-weight:500;border:1px solid transparent;cursor:pointer;background:rgba(59,130,246,0.2);color:#60a5fa">Pretty</button>
                                    <button class="docs-view-btn" data-view="raw" onclick="toggleBodyView(this,'raw','{{ $exId }}')" style="padding:2px 8px;border-radius:3px;font-size:10px;font-weight:500;border:1px solid transparent;cursor:pointer;background:transparent;color:#64748b">Raw</button>
                                </span>
                            @endif
                        </div>
                        @if(!empty($exBody))
                            <div class="docs-code-block docs-body-content" id="{{ $exId }}" data-pretty="{{ e($exPretty) }}" data-raw="{{ e($exRaw) }}" style="border:none;border-radius:0">{{ $exIsJson ? $exPretty : $exBody }}</div>
                        @else
                            <div style="padding:12px;font-size:11px;color:#64748b;font-style:italic">No response body</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if(empty($url) && empty($headers) && empty($params) && $bodyType === 'none' && $auth['type'] === 'none' && empty($req['description']) && empty($req['examples']))
            <span class="docs-empty">No request details saved for this endpoint.</span>
        @endif
    </div>
</div>
