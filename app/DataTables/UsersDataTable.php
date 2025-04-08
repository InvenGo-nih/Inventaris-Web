<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('role_name', function ($user) {
                // Menambahkan kolom role_name dengan data dari relasi role.
                return $user->role_name;
            })
            ->filterColumn('role_name', function ($query, $keyword) {
                // Menambahkan filter pencarian untuk kolom role_name.
                $query->where('roles.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('role_name', function ($query, $order) {
                // Menambahkan sorting untuk kolom role_name.
                $query->orderBy('roles.name', $order);
            })
            ->addColumn('action', function ($user) {
                // Menambahkan kolom aksi dengan tombol edit dan delete.
                $editUrl = route('users.form', $user->id);
                $deleteUrl = route('users.destroy', $user->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');
                $buttons = '';
                if (hasPermission('EDIT_USERS')) {
                    $buttons .= '
                        <a href="' . $editUrl . '" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>';
                }

                if (hasPermission('DELETE_USERS')) {
                    $buttons .= '                        
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus pengguna ini?\');">
                            ' . $csrf . '
                            ' . $method . '
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    ';
                }
                return '
                    <div class="d-flex justify-content-center gap-2">
                        ' . $buttons . '
                    </div>';
            })
            ->rawColumns(['action']) // Penting untuk memastikan HTML pada kolom aksi tidak di-escape.
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
        ->select('users.*', 'roles.name as role_name')
        ->leftJoin('roles', 'users.role_id', '=', 'roles.id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('users-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    // ->dom('Bfrtip')
                    ->orderBy(0 , 'asc')
                    ->selectStyleSingle()
                    ->parameters([
                        'dom' => '<"datatable-toolbar d-flex flex-wrap justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-end align-items-center"p>',
                        'pageLength' => 10,
                        'buttons' => array_filter([
                            hasPermission('CREATE_USERS') ? [
                                'text' => '<i class="fas fa-plus"></i> Tambah Pengguna',
                                'className' => 'btn btn-primary',
                                'action' => 'function ( e, dt, node, config ) {
                                    window.location.href = "' . route('users.form') . '";
                                }',
                            ] : null,
                        ]),
                        'responsive' => true,
                        'autoWidth' => false,
                        'language' => ['url' => asset('js/id.json')]
                    ]);
    }
    // Keterangan untuk DataTables:
    // l = Length dropdown (Show entries)

    // i = Info ("Showing 1 to 10 of x entries")

    // p = Pagination

    // f = Search

    // B = Button

    // r = Processing

    // t = Table

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $hasActions = hasPermission(['EDIT_USERS', 'DELETE_USERS']);
        if (!$hasActions) {
            return [
                Column::make('name')
                    ->title('Nama'), // Mengubah header kolom menjadi 'Nama'.
                Column::make('email'),
                Column::computed('role_name')
                    ->title('Jabatan') // opsional: ubah header jadi 'Role'
                    ->searchable(true)   // 👈 WAJIB untuk bisa disearch
                    ->orderable(true),   // 👈 WAJIB untuk bisa diurutkan
            ];
        }
        return [
            Column::make('name')
                ->title('Nama'), // Mengubah header kolom menjadi 'Nama'.
            Column::make('email'),
            Column::computed('role_name')
                ->title('Jabatan') // opsional: ubah header jadi 'Role'
                ->searchable(true)   // 👈 WAJIB untuk bisa disearch
                ->orderable(true),   // 👈 WAJIB untuk bisa diurutkan
            Column::computed('action')
                  ->title('Aksi')
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
