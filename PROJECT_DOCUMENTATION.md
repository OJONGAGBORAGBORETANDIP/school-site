# Report Card Management System – Project Documentation

This document describes the structure, features, and implementation of the Report Card Management System – a Laravel application for managing students, terms, marks, report cards, and parent/teacher/headteacher access.

---

## 1. Technology Stack

- **PHP** ^8.2  
- **Laravel** ^12.0  
- **Filament** 5.x (admin panels)  
- **Livewire** (via Volt for auth; full Livewire for teacher marks/result review)  
- **Laravel Breeze** (auth scaffolding)  
- **Vite** (front-end build)  
- **MySQL** (database; configurable via `.env`)

---

## 2. User Roles and Access

The system uses a **role-based** model with four roles:

| Role         | Description                    | Access |
|-------------|--------------------------------|--------|
| **admin**   | Full system configuration      | Filament Admin panel (`/admin`) |
| **headteacher** | School head                   | Filament Headteacher panel (`/headteacher`) |
| **teacher** | Subject/class teachers        | Web dashboard + teacher routes (`/dashboard`, `/teacher/*`) |
| **parent**  | Parents/guardians              | Web dashboard only (`/dashboard`) |

- **Roles** are stored in `roles` and linked to users via `role_user`.  
- **User** model: `hasRole()`, `isAdmin()`, `isHeadteacher()`, `isTeacher()`, `isParent()`, `parentProfile()`, `teacherAssignments()`.  
- **Middleware**: `EnsureUserIsAdmin`, `EnsureUserIsTeacher`, `EnsureUserIsHeadteacher` restrict panels and routes by role.

---

## 3. Authentication and Parent Portal

- **Home** (`/`): Public welcome page; Log in / Register for parents and staff.  
- **Auth**: Laravel Breeze + Livewire Volt – Login, Register, Forgot Password, Reset Password, Email verification, Confirm Password.  
- **Profile** (`/profile`): Change password (current password + new password).  
- **Logout**: `POST /logout`; invalidates session and redirects to `/`.  
- **Parent login**: Parents use the same login; after login they are redirected to the dashboard and see their linked students (via `parent_student` and `ParentModel`).

---

## 4. Unified Dashboard (Teachers and Parents)

- **Route**: `GET /dashboard` (middleware: `auth`).  
- **Layout**: `resources/views/layouts/dashboard.blade.php` – sidebar + top nav + main content.  
- **Sidebar**:  
  - All: Dashboard, Profile, Log out.  
  - Teachers only: **Marks entry**, **Result review**.  
- **Content by role**:  
  - **Parent**: List of their children (students) linked via guardians.  
  - **Teacher**: Assigned classes and subjects; list of sections with students; link to class details per section.  
- **Class details** (`/teacher/class/{classSection}`): Teacher-only; shows class section, enrollments, terms; only if the teacher is assigned to that section.

---

## 5. Teacher Features (Livewire)

### 5.1 Marks Entry

- **Route**: `GET /teacher/marks-entry` (middleware: teacher).  
- **Component**: `App\Livewire\Teacher\MarksEntry`.  
- **View**: `resources/views/livewire/teacher/marks-entry.blade.php`.  
- **Behaviour**:  
  - Select **Class section**, **Subject**, **Term** (terms from current school year; **active term** listed first and labelled “(active – enter marks)”; others “(past – read only)”).  
  - Enter **CA** (Continuous Assessment) and **Exam** marks per student; **Total** = (CA × 40%) + (Exam × 60%); **Grade** and remark from `GradingScale`.  
  - **Editable** only when the selected term is the **active term** and the subject is not submitted; otherwise **read-only** (past term or submitted).  
  - Buttons: **Save as draft** and **Save** (both persist marks; submission to head teacher is done from Result Review).  
  - Validation: CA and exam 0–100; errors shown; save blocked for non-active term with a flash message.  
- **Pre-select** from query params: `class_section`, `term`, `subject` in URL (e.g. from Result Review “Marks entry” link).

### 5.2 Result Review

- **Route**: `GET /teacher/result-review` (middleware: teacher).  
- **Component**: `App\Livewire\Teacher\ResultReview`.  
- **View**: `resources/views/livewire/teacher/result-review.blade.php`.  
- **Behaviour**:  
  - Select **Class section** and **Term**.  
  - Table: Subject, Students with marks, Total students, Status (Draft/Submitted), **Actions**.  
  - Per row: **Marks entry** (link to marks-entry with class_section, term, subject) and **Submit finalized results to head teacher** (only for draft; with confirmation).  
- **Submission**: Per subject via `subject_reports.submitted_at`; marks entry becomes read-only for that subject once submitted.

---

## 6. Active Term (Head Teacher)

- **Purpose**: Only one term is “active” at a time; teachers can **enter/edit marks only for the active term**; other terms are **read-only** for viewing.  
- **Database**: `terms.is_active` (boolean); migration `2025_02_20_000002_add_is_active_to_terms_table.php`.  
- **Model**: `Term` has `is_active` in fillable/casts and `scopeActive()`.  
- **Head teacher**: In the **Headteacher Filament panel**, the **Terms** resource (`App\Filament\Headteacher\Resources\TermResource`) lists all terms with an **Active** badge and a row action **“Set as active term”**. Clicking it sets that term as active and all others as inactive.  
- **Teacher marks entry**: Uses `Term::where('is_active', true)` to determine `canEdit`; save methods reject saves when the selected term is not the active term.

---

## 7. Filament Panels

### 7.1 Admin Panel (`/admin`)

- **Provider**: `App\Providers\Filament\AdminPanelProvider`.  
- **Middleware**: `EnsureUserIsAdmin`.  
- **Resources** (discovered from `App\Filament\Resources`):  
  - **User Management**: Users (with roles), Teacher assignments.  
  - **School Structure**: School years, Terms, School classes, Class sections, Subjects.  
  - **Student Management**: Students, Enrollments.  
- **Pages**: Dashboard.  
- **Purpose**: Configure school years, terms, classes, sections, subjects, users, roles, teacher assignments, students, enrollments.

### 7.2 Headteacher Panel (`/headteacher`)

- **Provider**: `App\Providers\Filament\HeadteacherPanelProvider`.  
- **Middleware**: `EnsureUserIsHeadteacher`.  
- **Resources** (discovered from `App\Filament\Headteacher\Resources`):  
  - **Terms**: List terms; **Set as active term** action per row.  
  - **Report Cards**: Term reports (list, edit – headteacher remark, approval).  
  - **Students**: List/view students.  
  - **Attendance**: List attendance records.  
- **Pages**: Headteacher Dashboard, Performance Analytics.  
- **Widgets**: Report card stats, Class performance.  

### 7.3 Teacher Panel (Filament)

- **Teacher**-specific Filament pages exist under `App\Filament\Teacher` (e.g. TeacherDashboard, MarksEntry, AttendanceEntry, MyClasses) but the main teacher workflow uses the **web dashboard** and **Livewire** marks entry/result review routes described above.

---

## 8. Student and Guardians

### 8.1 Student Resource (Admin)

- **Resource**: `App\Filament\Resources\StudentResource`.  
- **Form**: Personal information, Admission information, **Guardians** section.  
- **Guardians**: Three optional dropdowns – **Father**, **Mother**, **Other Guardian** – each selecting a parent from the `parents` table. On create/edit, selections are synced to the `parent_student` pivot with `relationship` = `father` / `mother` / `guardian`.  
- **Pivot**: `parent_student` has `parent_id`, `student_id`, `relationship` (nullable string); migration `2025_02_20_100000_add_relationship_to_parent_student_table.php`.  
- **Models**: `Student::parents()` and `ParentModel::students()` use `withPivot('relationship')` and explicit pivot keys (`student_id`, `parent_id`) so Laravel does not assume `parent_model_id`.

### 8.2 Parent–Student Relationship Fix

- **Issue**: Laravel inferred pivot column `parent_model_id` from the class name `ParentModel`, while the table has `parent_id`.  
- **Fix**: In `Student` and `ParentModel`, the `belongsToMany` calls specify the pivot foreign keys:  
  - `Student::parents()`: `'student_id', 'parent_id'`.  
  - `ParentModel::students()`: `'parent_id', 'student_id'`.  
- This resolves “Unknown column 'parent_student.parent_model_id'” when parents log in and their students are loaded.

---

## 9. Database Overview

### 9.1 Core Tables

- **users** – name, email, password; roles via **role_user**.  
- **roles** – name (admin, headteacher, teacher, parent), label.  
- **school_years** – name, dates, `is_current`.  
- **terms** – school_year_id, number, name, dates, **is_active**, results_published_at.  
- **school_classes** – name, code, level.  
- **class_sections** – school_class_id, name, label, class_teacher_id.  
- **subjects** – name, code, min/max level, is_compulsory.  
- **teacher_assignments** – teacher_id (users.id), class_section_id, subject_id.  
- **students** – personal and admission data.  
- **parents** – user_id, first_name, last_name, phone, email, relationship.  
- **parent_student** – parent_id, student_id, **relationship** (father/mother/guardian), timestamps.  
- **enrollments** – student_id, school_year_id, class_section_id, class_level, is_active.  
- **term_reports** – enrollment_id, term_id, average, position, class_average, class_size, class_teacher_remark, headteacher_remark, is_approved_by_headteacher; optional submitted_at.  
- **subject_reports** – term_report_id, subject_id, ca_mark, exam_mark, total_mark, grade, remark, teacher_comment; **submitted_at** (submission to head teacher).  
- **grading_scales** – min_mark, max_mark, grade, remark.  
- **attendances** – enrollment_id, term_id, date, status (present/absent/late).  
- **behaviour_ratings**, **promotion_decisions** – as in migrations.

### 9.2 Migrations (in order)

- `0001_01_01_000000_create_users_table`  
- `0001_01_01_000001_create_cache_table`, `0001_01_01_000002_create_jobs_table`  
- `2025_01_01_000100_create_roles_and_school_structure_tables` (roles, school_years, terms, classes, sections, subjects, teacher_assignments)  
- `2025_01_01_000200_create_students_parents_and_enrollments_tables` (students, parents, parent_student, enrollments)  
- `2025_01_01_000300_create_assessment_and_attendance_tables` (grading_scales, attendances, term_reports, subject_reports, behaviour_ratings, promotion_decisions)  
- `2025_02_20_000001_add_submitted_at_to_subject_reports_table`  
- `2025_02_20_000002_add_is_active_to_terms_table`  
- `2025_02_20_000000_add_submitted_at_to_term_reports_table` (if used)  
- `2025_02_20_100000_add_relationship_to_parent_student_table`

---

## 10. Grading and Marks

- **Formula**: Total = (CA × 40%) + (Exam × 60%); CA and exam are out of 100.  
- **Grades**: From `grading_scales` by total mark; `GradingScale::getGradeForMark()`.  
- **Marks entry**: Stored in `subject_reports` (ca_mark, exam_mark, total_mark, grade, remark, teacher_comment).  
- **Flow**: Teacher enters marks (draft) → Result Review → Submit finalized results per subject → Head teacher can view/approve term reports and add headteacher remark.

---

## 11. Key Files Reference

| Area              | Path |
|-------------------|------|
| Routes            | `routes/web.php`, `routes/auth.php` |
| Dashboard layout  | `resources/views/layouts/dashboard.blade.php` |
| Teacher marks     | `app/Livewire/Teacher/MarksEntry.php`, `resources/views/livewire/teacher/marks-entry.blade.php` |
| Teacher result review | `app/Livewire/Teacher/ResultReview.php`, `resources/views/livewire/teacher/result-review.blade.php` |
| Admin Filament    | `app/Providers/Filament/AdminPanelProvider.php`, `app/Filament/Resources/*` |
| Headteacher Filament | `app/Providers/Filament/HeadteacherPanelProvider.php`, `app/Filament/Headteacher/*` |
| Active term (Terms resource) | `app/Filament/Headteacher/Resources/TermResource.php` |
| Student guardians | `app/Filament/Resources/StudentResource.php`, CreateStudent/EditStudent pages |
| Models            | `app/Models/*` (User, Student, ParentModel, Term, Subject, ClassSection, TeacherAssignment, Enrollment, TermReport, SubjectReport, GradingScale, etc.) |
| Middleware        | `app/Http/Middleware/EnsureUserIsAdmin.php`, `EnsureUserIsTeacher.php`, `EnsureUserIsHeadteacher.php` |

---

## 12. Running the Project

1. **Environment**: Copy `.env.example` to `.env`, set `APP_KEY`, `DB_*` (e.g. MySQL).  
2. **Dependencies**: `composer install`, `npm install`.  
3. **Migrations**: `php artisan migrate`.  
4. **Seeders** (optional): Run role, user, school structure, student, parent, and grading seeders as needed.  
5. **Dev server**: `php artisan serve`; optionally `npm run dev` for Vite.  
6. **Access**:  
   - `/` – welcome; login/register.  
   - `/dashboard` – teacher/parent dashboard (after login).  
   - `/admin` – Filament admin (admin role).  
   - `/headteacher` – Filament headteacher (headteacher role).  
   - `/teacher/marks-entry`, `/teacher/result-review` – teacher-only (teacher role).

---

This document reflects the state of the project as implemented, including roles, auth, unified dashboard, teacher marks entry and result review, active term handling, Filament admin/headteacher panels, student guardians with Father/Mother/Other Guardian, and the parent–student pivot fix.
