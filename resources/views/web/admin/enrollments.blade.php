@extends('web.admin.layout', ['title' => 'Enrollment Monitoring'])
@section('content')
<h1>Student Enrollment Monitoring</h1>
<p class="muted">Read-only ICT Admin view. Lecturers continue to manage student enrollment.</p>
<p><a href="{{ route('web.admin.dashboard') }}">Back to ICT Admin Dashboard</a></p>
<form method="GET" action="{{ route('web.admin.enrollments') }}" class="card form-grid">
<div class="field"><label>Search student, ID or unit code</label><input name="search" value="{{ $search }}" placeholder="Search"></div>
<div class="field"><label>Unit</label><select name="unit_id"><option value="">All units</option>
@foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string)$unitId === (string)$unit->id)>{{ $unit->unit_code }} - {{ $unit->unit_name }}</option>@endforeach
</select></div><div class="field"><button class="btn" type="submit">Filter</button></div>
</form>
<div class="section-title"><h2>Enrollment Records ({{ $enrollments->total() }})</h2></div>
@forelse($enrollments as $entry)
<div class="card" style="margin-bottom:12px">
<strong>{{ $entry->student_name }}</strong>
<div class="muted">Student ID: {{ $entry->dwu_id ?? 'Not recorded' }}</div>
<div>Unit: {{ $entry->unit_code }} - {{ $entry->unit_name }}</div>
<div>Status: {{ $entry->status }} | Enrolled: {{ $entry->enrollment_date }}</div>
<div>Enrolled by: {{ $entry->enrolled_by_name ?? 'Not recorded' }}</div>
@if((string)$entry->student_department_id !== (string)$entry->unit_department_id)
<p style="color:#b91c1c">Check: student and unit departments differ.</p>
@endif
@if($duplicatePairs->has($entry->student_id . ':' . $entry->unit_id))
<p style="color:#b91c1c">Check: multiple enrollments for this student and unit.</p>
@endif
</div>
@empty<div class="card">No enrollments match your filter.</div>@endforelse
{{ $enrollments->links() }}
@endsection
