@extends('layouts.parent')

@section('title', 'My Dashboard')

@section('content')
    <div class="space-y-6">
        <p class="text-gray-600">
            Welcome to the Parent Portal. Here you can view information about your children and their report cards.
        </p>

        @if($students->isEmpty())
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-amber-800">
                <p class="font-medium">No children linked yet</p>
                <p class="mt-1 text-sm">
                    Contact the school to link your account to your child(ren). Once linked, you will see them here and can view report cards.
                </p>
            </div>
        @else
            <div>
                <h3 class="text-base font-semibold text-gray-900 mb-3">My children</h3>
                <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white overflow-hidden">
                    @foreach($students as $student)
                        <li class="px-4 py-4 flex items-center justify-between hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</p>
                                @if($student->admission_number)
                                    <p class="text-sm text-gray-500">Admission #{{ $student->admission_number }}</p>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                {{-- Link to report cards can be added later --}}
                                <span class="text-emerald-600">View report cards</span> (coming soon)
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
