<?php

use App\Http\Middleware\EnsureUserIsTeacher;
use App\Livewire\Teacher\MarksEntry;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Single dashboard: same layout (sidebar + nav + main) for all; content by role (teacher / parent).
Route::get('dashboard', function () {
    $user = auth()->user();
    $parentStudents = collect();
    $assignedSections = collect();
    $assignedSubjects = collect();
    $sectionsWithStudents = [];
    $currentYear = SchoolYear::where('is_current', true)->first();

    if ($user->isParent() && $user->parentProfile) {
        $parentStudents = $user->parentProfile->students;
    }

    if ($user->isTeacher()) {
        $assignments = $user->teacherAssignments()->with(['classSection.schoolClass', 'subject'])->get();
        $assignedSections = $assignments->map(fn ($a) => $a->classSection)->unique('id')->values();
        $assignedSubjects = $assignments->map(fn ($a) => $a->subject)->unique('id')->values();
        foreach ($assignedSections as $section) {
            $query = $section->enrollments()->with('student')->where('is_active', true);
            if ($currentYear) {
                $query->where('school_year_id', $currentYear->id);
            }
            $sectionsWithStudents[$section->id] = $query->get()->pluck('student');
        }
    }

    return view('dashboard', [
        'parentStudents' => $parentStudents,
        'assignedSections' => $assignedSections,
        'assignedSubjects' => $assignedSubjects,
        'sectionsWithStudents' => $sectionsWithStudents,
        'currentYear' => $currentYear,
    ]);
})->middleware(['auth'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('profile/password', function () {
    $validated = request()->validate([
        'current_password' => ['required', 'current_password'],
        'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
    ]);
    auth()->user()->update(['password' => bcrypt($validated['password'])]);
    return back()->with('status', 'password-updated');
})->middleware(['auth'])->name('password.update');

Route::post('logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware(['auth'])->name('logout');

require __DIR__.'/auth.php';

// Teacher-only routes (sidebar shows "Marks entry" only for teachers)
Route::middleware([EnsureUserIsTeacher::class])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/marks-entry', MarksEntry::class)->name('marks-entry');

        Route::get('/class/{classSection}', function (App\Models\ClassSection $classSection) {
            $user = auth()->user();
            $assigned = $user->teacherAssignments()->where('class_section_id', $classSection->id)->exists();
            if (!$assigned) {
                abort(403, 'You are not assigned to this class.');
            }
            $classSection->load(['schoolClass', 'enrollments' => fn ($q) => $q->where('is_active', true)->with(['student', 'schoolYear'])]);
            $currentYear = SchoolYear::where('is_current', true)->first();
            $terms = $currentYear ? $currentYear->terms()->orderBy('number')->get() : collect();
            $enrollmentsByYear = $classSection->enrollments->groupBy('school_year_id');
            return view('teacher.class-details', [
                'section' => $classSection,
                'currentYear' => $currentYear,
                'terms' => $terms,
                'enrollmentsByYear' => $enrollmentsByYear,
            ]);
        })->name('class-details');
    });
