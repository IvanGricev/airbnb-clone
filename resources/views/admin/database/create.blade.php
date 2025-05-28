@extends('layouts.main')
@section('title', 'Добавить запись в таблицу ' . $tableName)
@section('content')
<div class="container mt-4">
    <h1>Добавить запись в таблицу: {{ $tableName }}</h1>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.database.store', $tableName) }}" method="POST">
        @csrf
        @foreach($columns as $column)
            @if(!in_array($column->column_name, ['id', 'created_at', 'updated_at']))
                <div class="mb-3">
                    <label for="{{ $column->column_name }}" class="form-label">
                        {{ ucfirst(str_replace('_', ' ', $column->column_name)) }}
                    </label>
                    
                    @php
                        $inputType = 'text';
                        switch($column->data_type) {
                            case 'integer':
                            case 'bigint':
                            case 'smallint':
                                $inputType = 'number';
                                break;
                            case 'boolean':
                                $inputType = 'checkbox';
                                break;
                            case 'date':
                                $inputType = 'date';
                                break;
                            case 'timestamp':
                            case 'timestamp with time zone':
                                $inputType = 'datetime-local';
                                break;
                            case 'text':
                                $inputType = 'textarea';
                                break;
                        }
                    @endphp

                    @if($inputType === 'textarea')
                        <textarea 
                            name="{{ $column->column_name }}" 
                            class="form-control" 
                            id="{{ $column->column_name }}"
                            rows="3"
                        >{{ old($column->column_name) }}</textarea>
                    @elseif($inputType === 'checkbox')
                        <input 
                            type="checkbox" 
                            name="{{ $column->column_name }}" 
                            class="form-check-input" 
                            id="{{ $column->column_name }}"
                            value="1"
                            {{ old($column->column_name) ? 'checked' : '' }}
                        >
                    @else
                        <input 
                            type="{{ $inputType }}" 
                            name="{{ $column->column_name }}" 
                            class="form-control" 
                            id="{{ $column->column_name }}"
                            value="{{ old($column->column_name) }}"
                            @if($column->character_maximum_length)
                                maxlength="{{ $column->character_maximum_length }}"
                            @endif
                        >
                    @endif

                    @error($column->column_name)
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            @endif
        @endforeach

        <div class="mb-3">
            <button type="submit" class="btn btn-success">Добавить</button>
            <a href="{{ route('admin.database.table', ['table' => $tableName]) }}" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
@endsection
