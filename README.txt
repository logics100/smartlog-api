SMARTLOG — LECTURER CLINICAL LOGBOOK MODULE

Replace/copy these files into C:\Users\USER 2026\Projects\smartlog-api preserving folders.

Files:
- app/Http/Controllers/Web/DashboardController.php
- routes/web.php
- resources/views/web/lecturer/dashboard.blade.php
- resources/views/web/lecturer/logbooks.blade.php
- resources/views/web/lecturer/logbook-create.blade.php
- resources/views/web/lecturer/logbook-manage.blade.php

Then run:
php -l app\Http\Controllers\Web\DashboardController.php
php artisan optimize:clear
php artisan view:cache
php artisan route:list --name=web.lecturer.logbooks

Expected: no PHP syntax error, Blade cache succeeds, and 12 Clinical Logbook routes appear.

Important behavior:
- Only LECTURER can use the module.
- Lecturer can only manage templates for units assigned through lecturer_units.
- HOD remains read-only. ICT Admin clinical responsibility is unchanged.
- No destructive delete operations are included. Items are activated/deactivated.
- Creating an item automatically creates the requested number of requirement slots.
- Assign to Enrolled Students only creates a logbook for ACTIVE enrollments that have no student_logbook yet. Existing logbooks are never replaced.
