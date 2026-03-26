<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManageEnrollments extends ManageRecords
{
    protected static string $resource = EnrollmentResource::class;

    public function getDefaultActionSchemaResolver(Action $action): ?Closure
    {
        if ($action instanceof CreateAction) {
            return fn (Schema $schema): Schema => $schema
                ->columns(2)
                ->schema(EnrollmentResource::getCreateFormComponents());
        }

        return parent::getDefaultActionSchemaResolver($action);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data): Enrollment {
                    $studentIds = array_values(array_unique(Arr::wrap($data['student_ids'] ?? [])));
                    $attributes = Arr::except($data, ['student_ids']);

                    if ($studentIds === []) {
                        throw ValidationException::withMessages([
                            'student_ids' => __('Select at least one student.'),
                        ]);
                    }

                    return DB::transaction(function () use ($studentIds, $attributes): Enrollment {
                        $created = null;

                        foreach ($studentIds as $studentId) {
                            $created = Enrollment::create([
                                ...$attributes,
                                'student_id' => $studentId,
                            ]);
                        }

                        return $created;
                    });
                }),
        ];
    }
}
