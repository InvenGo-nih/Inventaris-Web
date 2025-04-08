<?php

namespace App\DataTables;

use App\Models\Borrow;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BorrowDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('status', function ($item) {
                $class = $item->status === 'Dikembalikan'
                    ? 'badge bg-label-success'
                    : 'badge bg-label-warning';

                return '<span class="' . $class . '">' . $item->status . '</span>';
            })
            ->addColumn('date_borrow', function ($item) {
                return \Carbon\Carbon::parse($item->date_borrow)->format('d/m/Y');
            })
            ->addColumn('date_back', function ($item) {
                return \Carbon\Carbon::parse($item->date_back)->format('d/m/Y');
            })
            ->filterColumn('date_borrow', function ($query, $keyword) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $keyword)->format('Y-m-d');
                    $query->whereDate('borrows.date_borrow', $date);
                } catch (\Exception $e) {
                    // abaikan jika format tidak valid
                }
            })
            ->filterColumn('date_back', function ($query, $keyword) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $keyword)->format('Y-m-d');
                    $query->whereDate('borrows.date_back', $date);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('borrows.status', 'like', "%{$keyword}%");
            })
            ->addColumn('img_borrow', function ($item) {
                if ($item->img_borrow) {
                    $url = "https://vtgompvryxqxirylucui.supabase.co/storage/v1/object/public/invengo/upload/{$item->img_borrow}";
                    return '<img src="' . $url . '" alt="img_borrow" height="100">';
                }
                return '-';
            })
            ->addColumn('inventaris_name', function ($item) {
                // Menambahkan kolom inventaris_name dengan data dari relasi role.
                return $item->inventaris_name;
            })
            ->filterColumn('inventaris_name', function ($query, $keyword) {
                // Menambahkan filter pencarian untuk kolom inventaris_name.
                $query->where('inventaris.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('inventaris_name', function ($query, $order) {
                // Menambahkan sorting untuk kolom role_name.
                $query->orderBy('inventaris.name', $order);
            })
            ->addColumn('action', function ($item) {
                // Menambahkan kolom aksi dengan tombol edit dan delete.
                $editUrl = route('borrow.form', $item->id);
                $deleteUrl = route('borrow.delete', $item->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');
                $buttons = '';

                if (hasPermission('EDIT_BORROW')) {
                    $buttons .= '
                        <a href="' . $editUrl . '" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>';
                }
            
                if (hasPermission('DELETE_BORROW')) {
                    $buttons .= '
                        <form action="' . $deleteUrl . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus pengguna ini?\');">
                            ' . $csrf . '
                            ' . $method . '
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>';
                }
            
                if ($buttons == '') {
                    return null; // tidak menampilkan kolom action
                }
            
                return '<div class="d-flex justify-content-center gap-2">' . $buttons . '</div>';
            })
            ->rawColumns(['img_borrow', 'status', 'action']) // 👈 penting: agar <img>, status dan <form> tidak di-escape
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Borrow $model): QueryBuilder
    {
        return $model->newQuery()
        ->select('borrows.*', 'inventaris.name as inventaris_name')
        ->leftJoin('inventaris', 'borrows.inventaris_id', '=', 'inventaris.id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('borrow-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(0 , 'asc')
                    ->selectStyleSingle()
                    ->parameters([
                        'dom' => '<"datatable-toolbar d-flex flex-wrap justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-end align-items-center"p>',
                        'pageLength' => 10,
                        'buttons' => array_filter([
                            hasPermission('CREATE_BORROW') ? [
                                'text' => '<i class="fas fa-plus"></i> Tambah Peminjam',
                                'className' => 'btn btn-primary',
                                'action' => 'function ( e, dt, node, config ) {
                                    window.location.href = "' . route('borrow.form') . '";
                                }',
                            ] : null,
                        ]),
                        'responsive' => true,
                        'autoWidth' => false,
                        'language' => ['url' => asset('js/id.json')]
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        // Cek permission dulu
        $hasAction = hasPermission(['EDIT_BORROW', 'DELETE_BORROW']);
        if (!$hasAction) {
            return [
                Column::computed('inventaris_name')
                ->title('Nama Barang') // opsional: ubah header jadi 'Role'
                ->searchable(true)   // 👈 WAJIB untuk bisa disearch
                ->orderable(true)   // 👈 WAJIB untuk bisa diurutkan
                ->addClass('white-space text-nowrap'),   
                Column::make('borrow_by')->title('Peminjam')->addClass('white-space text-nowrap'),
                Column::make('date_borrow')->title('Tanggal Pinjam')->addClass('white-space text-nowrap text-end'),
                Column::make('date_back')->title('Tanggal Pengembalian')->addClass('white-space text-nowrap text-end'),
                Column::make('status')->addClass('white-space text-nowrap'),
                Column::make('img_borrow')->title('Foto')->addClass('white-space text-nowrap'),
            ];
        }
        return [
            Column::computed('inventaris_name')
            ->title('Nama Barang') // opsional: ubah header jadi 'Role'
            ->searchable(true)   // 👈 WAJIB untuk bisa disearch
            ->orderable(true)   // 👈 WAJIB untuk bisa diurutkan
            ->addClass('white-space text-nowrap'),   
            Column::make('borrow_by')->title('Peminjam')->addClass('white-space text-nowrap'),
            Column::make('date_borrow')->title('Tanggal Pinjam')->addClass('white-space text-nowrap text-end'),
            Column::make('date_back')->title('Tanggal Pengembalian')->addClass('white-space text-nowrap text-end'),
            Column::make('status')->addClass('white-space text-nowrap'),
            Column::make('img_borrow')->title('Foto')->addClass('white-space text-nowrap'),
            Column::computed('action')
                  ->title('Aksi')
                  ->width(60)
                  ->addClass('text-center white-space text-nowrap'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Borrow_' . date('YmdHis');
    }
}
