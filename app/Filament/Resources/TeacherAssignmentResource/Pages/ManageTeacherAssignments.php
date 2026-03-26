<?php

namespace App\Filament\Resources\TeacherAssignmentResource\Pages;

use App\Filament\Resources\TeacherAssignmentResource;
use App\Models\TeacherAssignment;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManageTeacherAssignments extends ManageRecords
{
    protected static string $resource = TeacherAssignmentResource::class;

    public function getDefaultActionSchemaResolver(Action $action): ?Closure
    {
        if ($action instanceof CreateAction) {
            return fn (Schema $schema): Schema => $schema
                ->columns(2)
                ->schema(TeacherAssignmentResource::getCreateFormComponents());
        }

        return parent::getDefaultActionSchemaResolver($action);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data): TeacherAssignment {
                    $subjectIds = array_values(array_unique(Arr::wrap($data['subject_ids'] ?? [])));
                    $attributes = Arr::except($data, ['subject_ids']);

                    if ($subjectIds === []) {
                        throw ValidationException::withMessages([
                            'subject_ids' => __('Select at least one subject.'),
                        ]);
                    }

                    return DB::transaction(function () use ($subjectIds, $attributes): TeacherAssignment {
                        $created = null;

                        foreach ($subjectIds as $subjectId) {
                            $duplicate = TeacherAssignment::query()
                                ->where('teacher_id', $attributes['teacher_id'])
                                ->where('class_section_id', $attributes['class_section_id'])
                                ->where('subject_id', $subjectId)
                                ->exists();

                            if ($duplicate) {
                                throw ValidationException::withMessages([
                                    'subject_ids' => __('This teacher is already assigned to this class for one of the selected subjects.'),
                                ]);
                            }

                            $created = TeacherAssignment::create([
                                ...$attributes,
                                'subject_id' => $subjectId,
                            ]);
                        }

                        return $created;
                    });
                }),
        ];
    }
}
