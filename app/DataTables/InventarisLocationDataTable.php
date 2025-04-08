<?php

namespace App\DataTables;

use App\Models\InventarisLocation;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class InventarisLocationDataTable extends DataTable
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
            $deleteUrl = route('inventaris.location.delete', $item->id);
            $csrf = csrf_field();
            $method = method_field('DELETE');
            $buttons = '';

            if (hasPermission('EDIT_LOCATION_INVENTARIS')) {
                $buttons .= '
                    <button type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditLocationInventaris"
                        data-id="' . $item->id . '"
                        data-location="' . e($item->location) . '"
                        class="btn btn-warning">
                        <i class="fas fa-edit"></i> 
                    </button>
                ';
            }

            if (hasPermission('DELETE_LOCATION_INVENTARIS')) {
                $buttons .= '
                    <form action="' . $deleteUrl . '" method="post" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus lokasi ini?\');">
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
                </div>
            ';
        })
        
            ->rawColumns(['action']) // Penting untuk memastikan HTML pada kolom aksi tidak di-escape.
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(InventarisLocation $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('inventarislocation-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(0 , 'asc')
                    ->selectStyleSingle()
                    ->parameters([
                        'dom' => '<"datatable-toolbar d-flex flex-wrap justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-end align-items-center"p>',
                        'pageLength' => 10,
                        'buttons' => array_filter([
                            hasPermission('CREATE_LOCATION_INVENTARIS') ? [
                                'text' => '<i class="fas fa-plus"></i> Tambah Lokasi',
                                'className' => 'btn btn-primary',
                                'action' => 'function ( e, dt, node, config ) {
                                    $(node).attr("data-bs-toggle", "modal");
                                    $(node).attr("data-bs-target", "#modalCreateLocationInventaris");
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
        $hasAction = hasPermission(['EDIT_LOCATION_INVENTARIS', 'DELETE_LOCATION_INVENTARIS']);
        if (!$hasAction) {
            return [
                Column::make('location')->title('Lokasi'),
            ];
        }
        return [
            Column::make('location')->title('Lokasi'),
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
        return 'InventarisLocation_' . date('YmdHis');
    }
}
