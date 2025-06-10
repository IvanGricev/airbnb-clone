<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseController extends Controller
{
    public function index()
    {
        try {
            $allowedTables = [
                'bookings' => 'Бронирования',
                'landlord_applications' => 'Заявки арендодателей',
                'support_messages' => 'Сообщения поддержки',
                'support_tickets' => 'Тикеты поддержки',
                'property_tag' => 'Теги объектов',
                'reviews' => 'Отзывы',
                'properties' => 'Объекты недвижимости'
            ];

            // PostgreSQL query to get only allowed tables
            $tables = collect(DB::select("
                SELECT table_name 
                FROM information_schema.tables 
                WHERE table_schema = 'public'
                AND table_name IN ('" . implode("','", array_keys($allowedTables)) . "')
                ORDER BY table_name
            "))->pluck('table_name')->map(function($table) use ($allowedTables) {
                return [
                    'name' => $table,
                    'display_name' => $allowedTables[$table]
                ];
            });
    
            return view('admin.database.index', compact('tables'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка таблиц', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->route('admin.dashboard')
                ->with('error', 'Не удалось загрузить список таблиц базы данных.');
        }
    }
    
    public function table($table = null)
    {
        try {
            if (!$table) {
                return redirect()->route('admin.database.index');
            }
        
            // Проверяем существование таблицы
            $tableExists = DB::select("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = ?
                )", [$table])[0]->exists;

            if (!$tableExists) {
                return redirect()->route('admin.database.index')
                    ->with('error', 'Таблица не найдена.');
            }

            $columns = DB::select("
                SELECT column_name, data_type, character_maximum_length
                FROM information_schema.columns
                WHERE table_schema = 'public'
                AND table_name = ?
                ORDER BY ordinal_position
            ", [$table]);

            $data = DB::table($table)->paginate(20); 

            return view('admin.database.table', compact('table', 'columns', 'data'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении данных таблицы', [
                'error' => $e->getMessage(),
                'table' => $table,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->route('admin.database.index')
                ->with('error', 'Не удалось загрузить данные таблицы.');
        }
    }    

    public function editRow(Request $request, $tableName, $id)
    {
        try {
            $row = DB::table($tableName)->where('id', $id)->first();
            if (!$row) {
                return redirect()->route('admin.database.table', ['table' => $tableName])
                    ->with('error', 'Запись не найдена.');
            }
            
            $columns = Schema::getColumnListing($tableName);
            return view('admin.database.edit', compact('tableName', 'row', 'columns'));
        } catch (\Exception $e) {
            Log::error('Ошибка при редактировании записи', [
                'error' => $e->getMessage(),
                'table' => $tableName,
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->route('admin.database.table', ['table' => $tableName])
                ->with('error', 'Не удалось загрузить данные для редактирования.');
        }
    }

    public function updateRow(Request $request, $tableName, $id)
    {
        try {
            $row = DB::table($tableName)->where('id', $id)->first();
            if (!$row) {
                return redirect()->route('admin.database.table', ['table' => $tableName])
                    ->with('error', 'Запись не найдена.');
            }

            $data = $request->except(['_token', '_method']);
            DB::table($tableName)->where('id', $id)->update($data);

            return redirect()->route('admin.database.table', ['table' => $tableName])
                ->with('success', 'Запись успешно обновлена.');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении записи', [
                'error' => $e->getMessage(),
                'table' => $tableName,
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->back()
                ->with('error', 'Не удалось обновить запись.')
                ->withInput();
        }
    }

    public function createRow(Request $request, $tableName)
    {
        try {
            // Проверяем существование таблицы
            $tableExists = DB::select("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = ?
                )", [$tableName])[0]->exists;

            if (!$tableExists) {
                return redirect()->route('admin.database.index')
                    ->with('error', 'Таблица не найдена.');
            }

            $columns = DB::select("
                SELECT column_name, data_type, character_maximum_length
                FROM information_schema.columns
                WHERE table_schema = 'public'
                AND table_name = ?
                ORDER BY ordinal_position
            ", [$tableName]);

            return view('admin.database.create', compact('tableName', 'columns'));
        } catch (\Exception $e) {
            Log::error('Ошибка при создании записи', [
                'error' => $e->getMessage(),
                'table' => $tableName,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->route('admin.database.index')
                ->with('error', 'Не удалось загрузить форму создания записи.');
        }
    }

    public function storeRow(Request $request, $tableName)
    {
        try {
            // Получаем информацию о колонках
            $columns = DB::select("
                SELECT column_name, data_type, character_maximum_length
                FROM information_schema.columns
                WHERE table_schema = 'public'
                AND table_name = ?
                ORDER BY ordinal_position
            ", [$tableName]);

            $constraints = [];
            foreach ($columns as $column) {
                if ($column->data_type === 'character varying' && $column->character_maximum_length) {
                    $constraints[$column->column_name] = (int)$column->character_maximum_length;
                }
            }
            
            $data = $request->except('_token');

            foreach ($data as $field => $value) {
                if (isset($constraints[$field]) && strlen($value) > $constraints[$field]) {
                    return redirect()->back()
                        ->with('error', "Значение поля '$field' превышает максимально допустимую длину ({$constraints[$field]} символов).")
                        ->withInput();
                }
            }

            DB::table($tableName)->insert($data);
            return redirect()->route('admin.database.table', ['table' => $tableName])
                ->with('success', 'Запись успешно добавлена.');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании записи', [
                'error' => $e->getMessage(),
                'table' => $tableName,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->back()
                ->with('error', 'Не удалось создать запись.')
                ->withInput();
        }
    }

    public function deleteRow(Request $request, $tableName, $id)
    {
        DB::table($tableName)->where('id', $id)->delete();
        return redirect()->route('admin.database.table', ['table' => $tableName])
                         ->with('success', 'Запись удалена.');
    }
}
