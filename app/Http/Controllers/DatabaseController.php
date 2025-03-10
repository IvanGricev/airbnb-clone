<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    /**
     * Отображает список таблиц базы данных.
     */
    public function index()
    {
        $tables = collect(DB::select('SHOW TABLES'))
                    ->pluck('Tables_in_' . env('DB_DATABASE'))
                    ->all();
        return view('admin.database.index', compact('tables'));
    }

    /**
     * Отображает содержимое таблицы.
     */
    public function table($tableName)
    {
        $columns = DB::select("DESCRIBE $tableName");
        $data    = DB::table($tableName)->paginate(10);
        return view('admin.database.table', compact('tableName', 'columns', 'data'));
    }

    /**
     * Отображает форму редактирования строки таблицы.
     */
    public function editRow(Request $request, $tableName, $id)
    {
        $row = DB::table($tableName)->where('id', $id)->first();
        $columns = Schema::getColumnListing($tableName);
        return view('admin.database.edit', compact('tableName', 'row', 'columns'));
    }

    /**
     * Обновляет строку таблицы с проверкой ограничений по длине.
     */
    public function updateRow(Request $request, $tableName, $id)
    {
        $columnsInfo = DB::select("DESCRIBE $tableName");
        $constraints = [];
        foreach ($columnsInfo as $column) {
            if (preg_match('/^(varchar|char)\((\d+)\)/i', $column->Type, $matches)) {
                $constraints[$column->Field] = (int)$matches[2];
            }
        }

        $data = $request->except(['_token', '_method']);
        foreach ($data as $field => $value) {
            if (isset($constraints[$field]) && strlen($value) > $constraints[$field]) {
                return redirect()->back()->with('error', "Значение поля '$field' превышает максимально допустимую длину ({$constraints[$field]} символов).");
            }
        }

        DB::table($tableName)->where('id', $id)->update($data);
        return redirect()->route('admin.database.table', $tableName)->with('success', 'Запись обновлена.');
    }

    /**
     * Отображает форму создания новой записи.
     */
    public function createRow(Request $request, $tableName)
    {
        $columns = Schema::getColumnListing($tableName);
        return view('admin.database.create', compact('tableName', 'columns'));
    }

    /**
     * Сохраняет новую запись в таблице с проверкой ограничений по длине.
     */
    public function storeRow(Request $request, $tableName)
    {
        $columnsInfo = DB::select("DESCRIBE $tableName");
        $constraints = [];
        foreach ($columnsInfo as $column) {
            if (preg_match('/^(varchar|char)\((\d+)\)/i', $column->Type, $matches)) {
                $constraints[$column->Field] = (int)$matches[2];
            }
        }
        $data = $request->except('_token');
        foreach ($data as $field => $value) {
            if (isset($constraints[$field]) && strlen($value) > $constraints[$field]) {
                return redirect()->back()->with('error', "Значение поля '$field' превышает максимально допустимую длину ({$constraints[$field]} символов).");
            }
        }
        DB::table($tableName)->insert($data);
        return redirect()->route('admin.database.table', $tableName)->with('success', 'Запись добавлена.');
    }

    /**
     * Удаляет строку из таблицы.
     */
    public function deleteRow(Request $request, $tableName, $id)
    {
        DB::table($tableName)->where('id', $id)->delete();
        return redirect()->route('admin.database.table', $tableName)->with('success', 'Запись удалена.');
    }
}