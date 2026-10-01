<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Units | SmartLog</title>

<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:Arial,Helvetica,sans-serif;background:#f4f7f5;color:#26352f}
.app{min-height:100vh;display:flex}.sidebar{width:260px;min-height:100vh;background:#005f3c;color:#fff;padding:28px 20px;position:fixed;overflow-y:auto}
.brand{padding:5px 10px 28px;border-bottom:1px solid rgba(255,255,255,.15)}.brand h1{font-size:28px;margin-bottom:5px}.brand p{color:#cce8dc;font-size:13px}
.badge{display:inline-block;margin-top:10px;padding:6px 11px;border-radius:20px;background:rgba(255,255,255,.14);font-size:11px;font-weight:bold}.badge.white{background:#fff;color:#005f3c}
.nav{margin-top:28px}.nav a{display:block;text-decoration:none;color:#e5f5ee;padding:13px 14px;border-radius:8px;margin-bottom:7px;font-size:14px}.nav a:hover,.nav a.active{background:rgba(255,255,255,.14);color:#fff}
.sidebar-user{margin-top:30px;padding:18px 10px 5px;border-top:1px solid rgba(255,255,255,.15)}.sidebar-user strong,.sidebar-user span{display:block}.sidebar-user span{color:#cce8dc;font-size:12px;margin:5px 0 14px}
.logout{width:100%;border:1px solid rgba(255,255,255,.35);background:transparent;color:#fff;padding:10px;border-radius:7px;cursor:pointer;font-weight:bold}
.main{margin-left:260px;width:calc(100% - 260px);min-height:100vh}.topbar{background:#fff;padding:22px 32px;border-bottom:1px solid #e1e8e4;display:flex;justify-content:space-between;gap:15px;align-items:center}
.topbar h2{color:#005f3c;font-size:23px}.topbar p{margin-top:5px;color:#718078;font-size:13px}.role{background:#edf7f2;color:#005f3c;padding:9px 14px;border-radius:20px;font-size:12px;font-weight:bold}
.content{padding:30px 32px 45px}.notice{background:#e9f5ef;border-left:4px solid #008653;padding:15px 18px;border-radius:7px;margin-bottom:24px;color:#315c49;font-size:14px;line-height:1.5}
.cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;margin-bottom:26px}.card,.panel{background:#fff;border:1px solid #e2e9e5;border-radius:12px;padding:21px;box-shadow:0 3px 12px rgba(0,0,0,.035)}
.card .label{color:#718078;font-size:13px;margin-bottom:10px}.card .value{font-size:30px;font-weight:bold;color:#005f3c}.card .desc{color:#8b9791;font-size:12px;margin-top:7px}
.panel{margin-bottom:24px}.panel h3{color:#005f3c;margin-bottom:16px}.grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.stat{display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid #edf1ef;font-size:14px}.stat:last-child{border-bottom:0}.stat span{color:#65736c}
.progress{height:10px;background:#e8eeeb;border-radius:20px;overflow:hidden}.bar{height:100%;background:#008653;border-radius:20px}
.filters{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}.filters input,.filters select{padding:10px 12px;border:1px solid #ccd8d2;border-radius:7px;min-width:190px}.btn{display:inline-block;text-decoration:none;border:0;background:#005f3c;color:#fff;padding:10px 14px;border-radius:7px;font-weight:bold;cursor:pointer}.btn.secondary{background:#eef4f1;color:#005f3c;border:1px solid #d6e2dc}
.table-wrap{overflow-x:auto;background:#fff;border:1px solid #e2e9e5;border-radius:12px}table{width:100%;border-collapse:collapse;min-width:850px}th,td{padding:14px 15px;border-bottom:1px solid #edf1ef;text-align:left;font-size:13px;vertical-align:top}th{background:#f7faf8;color:#52625a}td strong{color:#26352f}.muted{color:#718078;font-size:12px}.empty{text-align:center;padding:28px;color:#718078}
.footer{margin-top:35px;padding-top:20px;border-top:1px solid #dfe7e3;color:#87938d;font-size:12px;text-align:center}
@media(max-width:1000px){.cards{grid-template-columns:repeat(2,1fr)}.grid2{grid-template-columns:1fr}}@media(max-width:800px){.app{display:block}.sidebar{position:static;width:100%;min-height:auto}.main{margin-left:0;width:100%}.cards{grid-template-columns:1fr}.topbar{align-items:flex-start;flex-direction:column}.content{padding:22px 18px 35px}}
</style>

@include('web.shared.professional-theme')
@include('web.shared.hod-modern-ui')

</head>
<body>
<div class="app">
<aside class="sidebar">
<div class="brand"><h1>SmartLog</h1><p>DWU Clinical Logbook System</p><span class="badge">Head of Department</span><br><span class="badge white">READ-ONLY ACCESS</span></div>
<nav class="nav"><a href="{{ route('web.hod.dashboard') }}">Dashboard</a>
<a href="{{ route('web.hod.students') }}">Students</a>
<a href="{{ route('web.hod.lecturers') }}">Teaching Staff</a>
<a href="{{ route('web.hod.units') }}" class="active">Units</a>
<a href="{{ route('web.hod.year-levels') }}">Year Levels</a></nav>
<div class="sidebar-user"><strong>{{ $user->name ?? 'HOD' }}</strong><span>{{ $user->dwu_id ?? 'DWU' }}</span>
<form method="POST" action="{{ route('web.logout') }}">@csrf<button class="logout" type="submit">Logout</button></form></div>
</aside>
<main class="main"><header class="topbar"><div><h2>Units</h2><p>Department unit and logbook progress monitoring</p></div><div class="role">Department Monitoring</div></header>
<div class="content"><div class="notice"><strong>Read-Only Access:</strong> This page is for department monitoring only. HODs cannot approve verifications, enroll students, assign units or modify clinical records.</div>

<div class="panel"><h3>Units</h3><form class="filters" method="GET" action="{{ route('web.hod.units') }}"><input name="search" value="{{ $search ?? '' }}" placeholder="Search unit code or name"><select name="year_level_id"><option value="">All Year Levels</option>@foreach($yearLevels ?? [] as $y)<option value="{{ $y->id }}" @selected(($selectedYearLevelId ?? null)==$y->id)>{{ $y->year_name }}</option>@endforeach</select><button class="btn">Filter</button><a class="btn secondary" href="{{ route('web.hod.units') }}">Clear</a></form></div>
<div class="table-wrap"><table><thead><tr><th>Code</th><th>Unit</th><th>Year</th><th>Lecturers</th><th>Students</th><th>Logbooks</th><th>Completed</th><th>Average</th><th>Action</th></tr></thead><tbody>
@forelse($units as $u) @php($p=max(0,min(100,(float)($u->average_completion_percentage??0))))
<tr><td><strong>{{ $u->unit_code }}</strong></td><td>{{ $u->unit_name }}</td><td>{{ $u->year_name ?? '—' }}</td><td>{{ $u->lecturer_count ?? 0 }}</td><td>{{ $u->enrolled_student_count ?? 0 }}</td><td>{{ $u->logbook_count ?? 0 }}</td><td>{{ $u->completed_logbook_count ?? 0 }}</td><td>{{ number_format($p,1) }}%<div class="progress"><div class="bar" style="width:{{ $p }}%"></div></div></td><td><a class="btn" href="{{ route('web.hod.unit-progress.show',$u->id) }}">View Progress</a></td></tr>
@empty<tr><td colspan="9" class="empty">No units found.</td></tr>@endforelse
</tbody></table></div>

<div class="footer">DWU Smart Clinical Logbook System - HOD Read-Only Portal</div></div></main>
</div></body></html>