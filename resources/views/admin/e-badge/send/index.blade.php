@extends('layouts.app')

@section('title', 'Send E-Badges')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="card-title">Send E-Badges</h1>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.e-badge.send.index') }}">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
                    <div>
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Category }}" {{ ($selectedCategory ?? '') === $category->Category ? 'selected' : '' }}>
                                    {{ $category->Category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="RegID / Name / Email / Company / Mobile">
                    </div>
                </div>
                <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.e-badge.send.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            @if(!empty($selectedCategory) && !empty($selectedBadgeSize))
                <div style="margin-top:10px;font-size:12px;color:#475569;">
                    Resolved e-badge PDF size:
                    <strong>{{ $selectedBadgeSize['width_px'] }}px × {{ $selectedBadgeSize['height_px'] }}px</strong>
                    (source:
                    <strong>{{ $selectedBadgeSize['source'] === 'background_image' ? 'uploaded background image' : 'category fallback size' }}</strong>)
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Users</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.e-badge.send.bulk') }}">
                @csrf
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
                <input type="hidden" name="search" value="{{ $search }}">

                <div style="margin-bottom:10px;">
                    <button type="submit" class="btn btn-primary">Send Email To Selected</button>
                    <button type="submit" formaction="{{ route('admin.e-badge.send.bulk-whatsapp') }}" class="btn btn-secondary">Send WhatsApp To Selected</button>
                    @if(!empty($selectedCategory))
                        <small style="display:block;margin-top:6px;color:#6b7280;">If none selected, all filtered users of selected category will be processed.</small>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-users"></th>
                            <th>RegID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><input type="checkbox" name="selected_user_ids[]" class="row-user-checkbox" value="{{ $user->id }}"></td>
                                <td>{{ $user->RegID }}</td>
                                <td>{{ $user->Name }}</td>
                                <td>{{ $user->Category }}</td>
                                <td>{{ $user->Email ?: '-' }}</td>
                                <td>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                        <a
                                            href="{{ route('admin.e-badge.send.preview', $user->id) }}"
                                            target="_blank"
                                            class="btn btn-secondary"
                                            style="padding:6px 10px;font-size:12px;text-decoration:none;"
                                        >
                                            View PDF
                                        </a>
                                        <button
                                            type="submit"
                                            name="user_detail_id"
                                            value="{{ $user->id }}"
                                            formaction="{{ route('admin.e-badge.send.whatsapp') }}"
                                            class="btn btn-secondary"
                                            style="padding:6px 10px;font-size:12px;"
                                        >
                                            Send WhatsApp
                                        </button>
                                        <button
                                            type="submit"
                                            name="user_detail_id"
                                            value="{{ $user->id }}"
                                            formaction="{{ route('admin.e-badge.send.user') }}"
                                            class="btn btn-primary"
                                            style="padding:6px 10px;font-size:12px;"
                                        >
                                            Send Email
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;">No users found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div style="margin-top:20px; display:flex; flex-direction:column; align-items:flex-end;">
                {{-- CDP Pagination --}}
                <div class="cdp" actpage="{{ $users->currentPage() }}">
                    {{-- Prev button (child 1) --}}
                    <a href="{{ $users->onFirstPage() ? '#' : $users->previousPageUrl() }}" class="cdp_i">prev</a>
                    {{-- Page number links (child 2 … lastPage+1) --}}
                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="cdp_i">{{ $page }}</a>
                    @endforeach
                    {{-- Next button (last child) --}}
                    <a href="{{ $users->hasMorePages() ? $users->nextPageUrl() : '#' }}" class="cdp_i">next</a>
                </div>
                {{-- Pagination Info --}}
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                    Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Recent Send Logs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>RegID</th>
                        <th>Email</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Sent At</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentLogs as $log)
                        <tr>
                            <td>{{ $log->regid }}</td>
                            <td>{{ $log->email }}</td>
                            <td>{{ $log->category }}</td>
                            <td>{{ $log->status }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($log->message, 80) }}</td>
                            <td>{{ optional($log->sent_at)->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;">No e-badge logs yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes cdp-in {
        from { transform: scale(1.5); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }

    .cdp {
        position: relative;
        text-align: right;
        padding: 8px 0;
        font-size: 0;
        z-index: 6;
        animation: cdp-in 400ms ease both;
    }

    .cdp_i {
        font-size: 13px;
        font-family: 'Comfortaa', sans-serif;
        font-weight: 700;
        letter-spacing: .03em;
        text-decoration: none;
        text-transform: uppercase;
        text-align: center;
        transition: background 250ms, color 250ms, box-shadow 200ms, transform 150ms;
        display: none;
        margin: 0 3px 6px;
        height: 36px;
        min-width: 36px;
        border-radius: 36px;
        border: 2px solid #3b82f6;
        line-height: 32px;
        padding: 0;
        color: #3b82f6;
    }

    .cdp_i:first-child,
    .cdp_i:last-child {
        padding: 0 16px;
        margin: 0 6px 6px;
    }

    /* Always show first page, last page, and next button */
    .cdp_i:last-child,
    .cdp_i:nth-child(2),
    .cdp_i:nth-last-child(2) {
        display: inline-block;
    }

    .cdp_i:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
    }

    /* Show prev button only when not on first page */
    .cdp:not([actpage="1"]) .cdp_i:nth-child(1) {
        display: inline-block;
    }

    /* ── Per-page rules (actpage 1–80) ─────────────────────────── */
    @for ($i = 1; $i <= 80; $i++)
    /* actpage={{ $i }} */
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i - 2 }}):not(:first-child):not(:nth-child(2)) {
        display: inline-block;
        pointer-events: none;
        color: transparent;
        border-color: transparent;
        width: 50px;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i - 2 }}):not(:first-child):not(:nth-child(2))::after {
        content: '...';
        color: #3b82f6;
        font-size: 22px;
        margin-left: -6px;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i - 1 }}):not(:first-child) {
        display: inline-block;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i }}):not(:first-child) {
        display: inline-block;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i + 1 }}) {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: #fff;
        display: inline-block;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.35);
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i + 1 }}) + .cdp_i:last-child {
        display: none !important;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i + 2 }}):not(:last-child) {
        display: inline-block;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i + 3 }}):not(:last-child) {
        display: inline-block;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i + 4 }}):not(:last-child):not(:nth-last-child(2)) {
        display: inline-block;
        pointer-events: none;
        color: transparent;
        border-color: transparent;
        width: 50px;
    }
    .cdp[actpage="{{ $i }}"] .cdp_i:nth-child({{ $i + 4 }}):not(:last-child):not(:nth-last-child(2))::after {
        content: '...';
        color: #3b82f6;
        font-size: 22px;
        margin-left: -6px;
    }
    @endfor
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all-users');
    if (!selectAll) return;

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.row-user-checkbox').forEach(function (cb) {
            cb.checked = selectAll.checked;
        });
    });
});
</script>
@endpush
