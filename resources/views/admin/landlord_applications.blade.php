@extends('layouts.main')

@section('title', 'Заявки на роль арендодателя')

@section('content')
<link rel="stylesheet" href="{{ url('/css/landlord_applications.admin.css') }}">

<div class="landlord-applications">
    <div class="applications-header">
        <h1>Заявки на роль арендодателя</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($applications->isEmpty())
        <p>Нет новых заявок.</p>
    @else
        <div class="applications-grid">
            @foreach($applications as $application)
                <div class="application-card modern-card">
                    <div class="application-image-block">
                        <img src="{{ asset('images/user-placeholder.svg') }}" alt="User avatar" class="application-user-image">
                    </div>
                    <div class="application-card-content">
                        <div class="application-title">{{ $application->user->name }}</div>
                        <div class="application-email">{{ $application->user->email }}</div>
                        <div class="application-message">{{ $application->message }}</div>
                        <div class="application-dates">
                            <div>Дата подачи: <b>{{ $application->created_at->format('d.m.Y H:i') }}</b></div>
                        </div>
                        <div class="application-status application-status-pending">На рассмотрении</div>
                        <div class="application-actions-wide">
                            <form action="{{ route('admin.landlord.applications.approve', $application) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-wide btn-approve">Одобрить</button>
                            </form>
                            <form action="{{ route('admin.landlord.applications.reject', $application) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-wide btn-reject">Отклонить</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="pagination-container" style="margin-top: 32px; display: flex; justify-content: center;">
            {{ $applications->links() }}
        </div>
    @endif
</div>
@endsection
