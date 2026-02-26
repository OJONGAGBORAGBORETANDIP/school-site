<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

/**
 * Policy for student-related actions.
 * Parents can only view report cards of their own children.
 */
class StudentPolicy
{
    /**
     * Determine whether the user can view the student's report card.
     * Parents: only their own children. Teachers/headteacher/admin: allowed.
     */
    public function viewReportCard(User $user, Student $student): bool
    {
        if ($user->isAdmin() || $user->isHeadteacher() || $user->isTeacher()) {
            return true;
        }

        if ($user->isParent() && $user->parentProfile) {
            return $user->parentProfile->students()->where('students.id', $student->id)->exists();
        }

        return false;
    }
}
