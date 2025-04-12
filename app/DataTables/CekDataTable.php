<?php

namespace App\DataTables;

use App\Models\Cek;
use App\Models\Inventaris;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CekDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($item) {
                // Menambahkan kolom aksi dengan tombol edit dan delete.
                $editUrl = route('cek.cek', $item->id);
                $buttons = '';

                if (hasPermission('PROCESS_CEK')) {
                    $buttons .= '
                        <a href="' . $editUrl . '" class="btn btn-info">
                            Periksa
                        </a>';
                }
            
                if ($buttons == '') {
                    return null; // tidak menampilkan kolom action
                }
            
                return '<div class="d-flex justify-content-center gap-2">' . $buttons . '</div>';
            })
            ->rawColumns(['action']) // 👈 penting: agar <img>, status dan <form> tidak di-escape
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Inventaris $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('cek-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(2, 'asc')
                    ->selectStyleSingle()
                    ->parameters([
                        'dom' => '<"datatable-toolbar d-flex flex-wrap justify-content-end align-items-center mb-3"f>rt<"d-flex justify-content-end align-items-center"p>',
                        'pageLength' => 10,
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
        $hasAction = hasPermission(['PROCESS_CEK']);
        if (!$hasAction) {
            return [
                Column::make('name')->title('Nama Barang')->addClass('white-space text-nowrap'),
                Column::make('specification')->title('Spesifikasi')->addClass('white-space text-nowrap'),
                Column::make('serial_number')->title('Nomor Seri')->addClass('white-space text-nowrap'),
                Column::make('condition')->title('Kondisi')->addClass('white-space text-nowrap'),
                Column::make('broken_description')->title('Keterangan Rusak')->addClass('white-space text-nowrap'),
            ];
        }
        return [
            Column::make('name')->title('Nama Barang')->addClass('white-space text-nowrap'),
            Column::make('specification')->title('Spesifikasi')->addClass('white-space text-nowrap'),
            Column::make('serial_number')->title('Nomor Seri')->addClass('white-space text-nowrap'),
            Column::make('condition')->title('Kondisi')->addClass('white-space text-nowrap'),
            Column::make('broken_description')->title('Keterangan Rusak')->addClass('white-space text-nowrap'),
            Column::computed('action')
                  ->title('Aksi')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Cek_' . date('YmdHis');
    }
}
