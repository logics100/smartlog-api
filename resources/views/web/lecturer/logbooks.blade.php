<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Clinical Logbooks | SmartLog</title><style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f4f7f5;color:#26352f}.top{background:#005f3c;color:#fff;padding:18px 28px;display:flex;justify-content:space-between;align-items:center}.top a{color:#fff;text-decoration:none}.wrap{max-width:1200px;margin:28px auto;padding:0 20px}.nav{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 22px}.nav a{background:#fff;border:1px solid #dfe8e3;color:#075d3d;padding:10px 14px;border-radius:8px;text-decoration:none}.nav a.active{background:#075d3d;color:#fff}.card{background:#fff;border:1px solid #e1e8e4;border-radius:12px;padding:20px;margin-bottom:18px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}.btn{display:inline-block;border:0;border-radius:8px;padding:10px 14px;background:#006b45;color:#fff;text-decoration:none;cursor:pointer;font-weight:700}.btn.secondary{background:#edf5f1;color:#075d3d}.btn.warn{background:#8a5a00}.field{margin-bottom:13px}.field label{display:block;font-size:13px;font-weight:700;margin-bottom:6px}.field input,.field select,.field textarea{width:100%;padding:10px;border:1px solid #ccd8d1;border-radius:7px;background:#fff}.muted{color:#6f7d76}.ok{background:#e8f6ef;border:1px solid #bfe5d1;padding:12px;border-radius:8px;margin-bottom:16px}.err{background:#fff0f0;border:1px solid #efc2c2;padding:12px;border-radius:8px;margin-bottom:16px}.badge{display:inline-block;padding:5px 9px;border-radius:14px;background:#edf5f1;color:#075d3d;font-size:12px;font-weight:700}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:11px;border-bottom:1px solid #e8eeeb;vertical-align:top}h1,h2,h3{color:#174331}.top h1{color:#fff;margin:0;font-size:22px}@media(max-width:700px){.top{align-items:flex-start;gap:12px;flex-direction:column}.wrap{margin-top:18px}table{font-size:13px}}

/* SMARTLOG LECTURER SIDEBAR */
body {
    padding-left: 260px;
}

.lecturer-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 260px;
    background: #005f3c;
    color: white;
    padding: 28px 20px;
    z-index: 1000;
}

.lecturer-brand {
    padding: 5px 10px 28px;
    border-bottom: 1px solid rgba(255,255,255,.15);
}

.lecturer-brand h1 {
    color: white;
    margin: 0 0 5px;
    font-size: 28px;
}

.lecturer-brand p {
    color: #cce8dc;
    margin: 0;
    font-size: 13px;
}

.lecturer-role {
    display: inline-block;
    margin-top: 12px;
    padding: 6px 11px;
    border-radius: 20px;
    background: rgba(255,255,255,.14);
    font-size: 12px;
    font-weight: bold;
}

.lecturer-nav {
    margin-top: 28px;
}

.lecturer-nav a {
    display: block;
    text-decoration: none;
    color: #e5f5ee;
    padding: 13px 14px;
    border-radius: 8px;
    margin-bottom: 7px;
    font-size: 14px;
}

.lecturer-nav a:hover,
.lecturer-nav a.active {
    background: rgba(255,255,255,.14);
    color: white;
}

body > .top {
    background: white;
    color: #26352f;
    border-bottom: 1px solid #e1e8e4;
    padding: 16px 30px;
}

body > .top h1 {
    color: #174331;
}

body > .top small {
    color: #7a8982;
}

body > .top a {
    color: #006b45;
}

.wrap {
    max-width: none;
    margin: 28px 30px;
    padding: 0;
}

.wrap > .nav {
    display: none;
}

@media(max-width:700px) {
    body {
        padding-left: 0;
        padding-top: 0;
    }

    .lecturer-sidebar {
        position: relative;
        width: 100%;
        bottom: auto;
    }

    body > .top {
        padding: 16px 20px;
    }

    .wrap {
        margin: 18px 20px;
    }
}
</style>
<style id="smartlog-stable-sidebar">
/* Keep Lecturer navigation in exactly the same position on every page */
.sidebar,
.lecturer-sidebar {
    width: 260px !important;
    padding: 28px 20px !important;
}

.brand,
.lecturer-brand {
    padding: 5px 10px 28px !important;
}

.navigation,
.lecturer-nav {
    margin-top: 28px !important;
}

.navigation a,
.lecturer-nav a {
    display: block !important;
    width: 100% !important;
    height: 42px !important;
    padding: 13px 14px !important;
    margin: 0 0 7px 0 !important;
    border-radius: 8px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 14px !important;
    font-weight: normal !important;
    line-height: 16px !important;
    text-decoration: none !important;
    box-sizing: border-box !important;
}

.navigation a.active,
.lecturer-nav a.active {
    font-weight: normal !important;
}

.role-badge,
.lecturer-role {
    height: 25px !important;
    line-height: 13px !important;
}

@media (max-width: 650px) {
    .sidebar,
    .lecturer-sidebar {
        width: 100% !important;
    }
}
</style>



<style id="smartlog-nav-final-fix">
.navigation a,
.lecturer-nav a {
    display: block !important;
    width: 100% !important;
    height: 42px !important;
    padding: 13px 14px !important;
    margin: 0 0 7px 0 !important;
    border-radius: 8px !important;
    background: transparent !important;
    color: #e5f5ee !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 14px !important;
    font-weight: normal !important;
    line-height: 16px !important;
    text-decoration: none !important;
    border: none !important;
}

.navigation a:hover,
.lecturer-nav a:hover {
    background: rgba(255,255,255,0.08) !important;
    color: white !important;
}

.navigation a.active,
.lecturer-nav a.active {
    background: rgba(255,255,255,0.14) !important;
    color: white !important;
    font-weight: normal !important;
}
</style>
<style id="smartlog-unified-navigation">

.smartlog-main-nav {
    margin-top: 28px !important;
    width: 100% !important;
}

.smartlog-main-nav a {
    display: flex !important;
    align-items: center !important;

    width: 100% !important;
    height: 42px !important;

    margin: 0 0 7px 0 !important;
    padding: 0 14px !important;

    box-sizing: border-box !important;

    border: 0 !important;
    border-radius: 8px !important;

    background: transparent !important;
    color: #e5f5ee !important;

    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 1 !important;

    text-decoration: none !important;
}

.smartlog-main-nav a:hover {
    background: rgba(255,255,255,0.08) !important;
    color: #ffffff !important;
}

.smartlog-main-nav a.active {
    background: rgba(255,255,255,0.14) !important;
    color: #ffffff !important;
    font-weight: 400 !important;
}

.sidebar .smartlog-main-nav,
.lecturer-sidebar .smartlog-main-nav {
    margin-top: 28px !important;
}
</style>
@include('web.shared.professional-theme')
@include('web.shared.lecturer-modern-ui')
</head><body>
<aside class="lecturer-sidebar">
    <div class="lecturer-brand">
        <h1>SmartLog</h1>
        <p>DWU Clinical Logbook</p>
        <span class="lecturer-role">LECTURER</span>
    </div>

    <nav class="smartlog-main-nav">
    <a href="{{ route('web.lecturer.dashboard') }}"
       class="{{ request()->routeIs('web.lecturer.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('web.lecturer.units') }}"
       class="{{ request()->routeIs('web.lecturer.units*') ? 'active' : '' }}">
        My Units
    </a>

    <a href="{{ route('web.lecturer.students') }}"
       class="{{ request()->routeIs('web.lecturer.students*') ? 'active' : '' }}">
        Students
    </a>

    <a href="{{ route('web.lecturer.student-progress') }}"
       class="{{ request()->routeIs('web.lecturer.student-progress*') ? 'active' : '' }}">
        Student Progress
    </a>

    <a href="{{ route('web.lecturer.verifications') }}"
       class="{{ request()->routeIs('web.lecturer.verifications*') ? 'active' : '' }}">
        Pending Verifications
    </a>

    <a href="{{ route('web.lecturer.logbooks') }}"
       class="{{ request()->routeIs('web.lecturer.logbooks*') ? 'active' : '' }}">
        Clinical Logbooks
    </a>
</nav>
</aside>
<header class="top"><div><h1>SmartLog</h1><small>DWU Clinical Logbook - Lecturer Portal</small></div><div>{{ $user->name }} - <a href="{{ route('web.lecturer.dashboard') }}">Dashboard</a></div></header><main class="wrap"><div class="nav"><a href="{{ route('web.lecturer.dashboard') }}">Dashboard</a><a href="{{ route('web.lecturer.units') }}">My Units</a><a href="{{ route('web.lecturer.students') }}">Students</a><a href="{{ route('web.lecturer.student-progress') }}">Student Progress</a><a href="{{ route('web.lecturer.verifications') }}">Pending Verifications</a><a class="active" href="{{ route('web.lecturer.logbooks') }}">Clinical Logbooks</a></div>
@if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
@if($errors->any())<div class="err"><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div style="display:flex;justify-content:space-between;gap:15px;align-items:center;margin-bottom:18px"><div><h2 style="margin:0 0 5px">Clinical Logbooks</h2><p class="muted" style="margin:0">Create and manage digital clinical logbooks only for units assigned to you.</p></div><a class="btn" href="{{ route('web.lecturer.logbooks.create') }}">+ Create Logbook</a></div>
<div class="card"><table><thead><tr><th>Unit</th><th>Template</th><th>Structure</th><th>Students</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($templates as $t)<tr><td><strong>{{ $t->unit_code }}</strong><br><span class="muted">{{ $t->unit_name }}</span></td><td><strong>{{ $t->template_name }}</strong><br><span class="muted">Minimum {{ number_format((float)$t->minimum_completion_percentage,1) }}%</span></td><td>{{ $t->sections_count }} sections<br>{{ $t->items_count }} items - {{ $t->requirements_count }} requirements</td><td>{{ $t->student_logbooks_count }}</td><td><span class="badge">{{ $t->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td><td><a class="btn secondary" href="{{ route('web.lecturer.logbooks.manage',['templateId'=>$t->id]) }}">Manage</a></td></tr>
@empty<tr><td colspan="6">No clinical logbook templates have been created for your assigned units.</td></tr>@endforelse
</tbody></table></div></main></body></html>





