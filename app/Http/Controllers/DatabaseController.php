<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function index()
    {
        $tables = collect(DB::select('SHOW TABLES'))->pluck('Tables_in_' . env('DB_DATABASE'))->all();
        return view('admin.database.index', compact('tables'));
    }

    public function table($tableName)
    {
        $columns = DB::select("DESCRIBE $tableName");
        $data = DB::table($tableName)->get();
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
        // Обновляем данные строки
        $data = $request->except(['_token', '_method']);
        DB::table($tableName)->where('id', $id)->update($data);
        return redirect()->route('admin.database.table', $tableName)->with('success', 'Запись обновлена.');
    }

    public function createRow(Request $request, $tableName)
    {
        // Показ формы создания новой записи
        $columns = Schema::getColumnListing($tableName);
        return view('admin.database.create', compact('tableName', 'columns'));
    }

    public function storeRow(Request $request, $tableName)
    {
        // Сохраняем новую запись
        $data = $request->except('_token');
        DB::table($tableName)->insert($data);
        return redirect()->route('admin.database.table', $tableName)->with('success', 'Запись добавлена.');
    }

    public function deleteRow(Request $request, $tableName, $id)
    {
        // Удаляем запись
        DB::table($tableName)->where('id', $id)->delete();
        return redirect()->route('admin.database.table', $tableName)->with('success', 'Запись удалена.');
    }
}
