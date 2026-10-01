@extends('web.admin.layout',['title'=>'Units']) @section('content')<h1>Units</h1><div class="grid"><div class="card"><div class="muted">Total</div><div class="num">{{ $summary['total'] }}</div></div><div class="card"><div class="muted">Active</div><div class="num">{{ $summary['active'] }}</div></div><div class="card"><div class="muted">Logbook Units</div><div class="num">{{ $summary['logbook'] }}</div></div></div>
<div class="section-title"><h2>Add Unit</h2></div><form class="card form-grid" method="POST" action="{{ route('web.admin.units.store') }}">@csrf<div class="field"><label>Unit Code</label><input name="unit_code" required></div><div class="field"><label>Unit Name</label><input name="unit_name" required></div><div class="field"><label>Department</label><select name="department_id" required>@foreach($departments->where('is_active',1) as $d)<option value="{{ $d->id }}">{{ $d->department_code }}</option>@endforeach</select></div><div class="field"><label>Year Level</label><select name="year_level_id" required>@foreach($yearLevels as $y)<option value="{{ $y->id }}">{{ $y->year_name }}</option>@endforeach</select></div><div class="field"><label>Semester</label><select name="semester_id" required>@foreach($semesters as $s)<option value="{{ $s->id }}">{{ $s->semester_name }}</option>@endforeach</select></div><div class="field"><label>Requires Logbook</label><select name="requires_logbook"><option value="1">Yes</option><option value="0">No</option></select></div><div class="field"><label>Status</label><select name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div><div class="field" style="justify-content:end"><button class="btn">Add Unit</button></div></form>
<div class="section-title"><h2>Manage Units</h2></div>@foreach($units as $u)<form class="card" style="margin-bottom:12px" method="POST" action="{{ route('web.admin.units.update',$u->id) }}">@csrf @method('PUT')<div class="form-grid"><div class="field"><label>Code</label><input name="unit_code" value="{{ $u->unit_code }}" required></div><div class="field"><label>Name</label><input name="unit_name" value="{{ $u->unit_name }}" required></div><div class="field"><label>Department</label><select name="department_id">@foreach($departments as $d)<option value="{{ $d->id }}" @selected($u->department_id==$d->id)>{{ $d->department_code }}</option>@endforeach</select></div><div class="field"><label>Year</label><select name="year_level_id">@foreach($yearLevels as $y)<option value="{{ $y->id }}" @selected($u->year_level_id==$y->id)>{{ $y->year_name }}</option>@endforeach</select></div><div class="field"><label>Semester</label><select name="semester_id">@foreach($semesters as $s)<option value="{{ $s->id }}" @selected($u->semester_id==$s->id)>{{ $s->semester_name }}</option>@endforeach</select></div><div class="field"><label>Logbook</label><select name="requires_logbook"><option value="1" @selected($u->requires_logbook)>Yes</option><option value="0" @selected(!$u->requires_logbook)>No</option></select></div><div class="field"><label>Status</label><select name="is_active"><option value="1" @selected($u->is_active)>Active</option><option value="0" @selected(!$u->is_active)>Inactive</option></select></div><div><div class="muted">Enrollments {{ $u->enrollments }} · Logbooks {{ $u->logbooks }}</div><button class="btn small" style="margin-top:9px">Save</button></div></div></form>@endforeach 
<div class="section-title">
    <h2>Assign Lecturers to Units</h2>
</div>

@if ($errors->has('assignment') || $errors->has('lecturer_id'))
    <div class="card" style="color:#b91c1c;margin-bottom:15px">
        {{ $errors->first('assignment') ?: $errors->first('lecturer_id') }}
    </div>
@endif

@if (session('success'))
    <div class="card" style="color:#15803d;margin-bottom:15px">
        {{ session('success') }}
    </div>
@endif

@foreach ($units as $unit)
    <div class="card" style="margin-bottom:15px">

        <h3>{{ $unit->unit_code }} - {{ $unit->unit_name }}</h3>

        <p class="muted">Assigned lecturers:</p>

        @forelse ($unitAssignments->get($unit->id, collect()) as $assigned)
            <div style="display:flex;align-items:center;gap:12px;margin:8px 0;flex-wrap:wrap">
                <span>{{ $assigned->name }}</span>
                <form method="POST" action="{{ route('web.admin.units.unassign') }}"
                      onsubmit="return confirm('Unassign this lecturer from the unit? Existing student records will remain.');">
                    @csrf
                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                    <input type="hidden" name="lecturer_id" value="{{ $assigned->lecturer_id }}">
                    <button type="submit" class="btn small">Unassign</button>
                </form>
            </div>
        @empty
            <p class="muted">No lecturer assigned yet.</p>
        @endforelse

        @if ($unit->is_active)
            <form method="POST"
                  action="{{ route('web.admin.units.assign') }}"
                  class="form-grid">

                @csrf

                <input type="hidden"
                       name="unit_id"
                       value="{{ $unit->id }}">

                <div class="field">
                    <label>Select Lecturer</label>

                    <select name="lecturer_id" required>
                        <option value="">Choose lecturer</option>

                        @foreach ($lecturers->where('department_id', $unit->department_id) as $lecturer)
                            <option value="{{ $lecturer->id }}">
                                {{ $lecturer->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="field" style="justify-content:end">
                    <button class="btn" type="submit">
                        Assign Lecturer
                    </button>
                </div>

            </form>
        @endif

    </div>
@endforeach
@endsection