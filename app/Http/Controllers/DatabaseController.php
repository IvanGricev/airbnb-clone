<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function index()
    {
        $tables = collect(DB::select('SHOW TABLES'))
            ->pluck('Tables_in_' . env('DB_DATABASE'))
            ->all();
        return view('admin.database.index', compact('tables'));
    }

    public function table($tableName)
    {
        $columns = DB::select("DESCRIBE $tableName");
        $data    = DB::table($tableName)->get();
        return view('admin.database.table', compact('tableName', 'columns', 'data'));
    }

    public function editRow(Request $request, $tableName, $id)
    {
        // Получаем данные строки
        $row = DB::table($tableName)->where('id', $id)->first();
        $columns = Schema::getColumnListing($tableName);
        return view('admin.database.edit', compact('tableName', 'row', 'columns'));
    }

    public function updateRow(Request $request, $tableName, $id)
    {
        // Получаем информацию о колонках для проверки ограничений
        $columnsInfo = DB::select("DESCRIBE $tableName");
        $constraints = [];
        foreach ($columnsInfo as $column) {
            // Если тип поля varchar или char, извлекаем максимальную длину
            if (preg_match('/^(varchar|char)\((\d+)\)/i', $column->Type, $matches)) {
                $constraints[$column->Field] = (int)$matches[2];
            }
        }
        
        // Извлекаем данные из запроса, исключая служебные поля
        $data = $request->except(['_token', '_method']);

        // Проверяем каждое поле на превышение длины
        foreach ($data as $field => $value) {
            if (isset($constraints[$field]) && strlen($value) > $constraints[$field]) {
                return redirect()->back()->with('error', "Значение поля '$field' превышает максимально допустимую длину ({$constraints[$field]} символов).");
            }
        }

        DB::table($tableName)->where('id', $id)->update($data);
        return redirect()->route('admin.database.table', $tableName)
                         ->with('success', 'Запись обновлена.');
    }

    public function createRow(Request $request, $tableName)
    {
        // Показ формы создания новой записи
        $columns = Schema::getColumnListing($tableName);
        return view('admin.database.create', compact('tableName', 'columns'));
    }

    public function storeRow(Request $request, $tableName)
    {
        // Получаем информацию о колонках для проверки
        $columnsInfo = DB::select("DESCRIBE $tableName");
        $constraints = [];
        foreach ($columnsInfo as $column) {
            if (preg_match('/^(varchar|char)\((\d+)\)/i', $column->Type, $matches)) {
                $constraints[$column->Field] = (int)$matches[2];
            }
        }
        
        // Извлекаем данные из запроса, исключая _token
        $data = $request->except('_token');

        // Проверяем длину для каждого поля, если для него установлено ограничение
        foreach ($data as $field => $value) {
            if (isset($constraints[$field]) && strlen($value) > $constraints[$field]) {
                return redirect()->back()->with('error', "Значение поля '$field' превышает максимально допустимую длину ({$constraints[$field]} символов).");
            }
        }

        DB::table($tableName)->insert($data);
        return redirect()->route('admin.database.table', $tableName)
                         ->with('success', 'Запись добавлена.');
    }

    public function deleteRow(Request $request, $tableName, $id)
    {
        // Удаляем запись
        DB::table($tableName)->where('id', $id)->delete();
        return redirect()->route('admin.database.table', $tableName)
                         ->with('success', 'Запись удалена.');
    }
}
