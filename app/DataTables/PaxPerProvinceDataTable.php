<?php

namespace App\DataTables;

use App\Models\Province;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PaxPerProvinceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Province> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'Province.action')
            ->setRowId('id')
            ->addColumn('region', function (Province $province) {
                return $province->region->name;
            })

            ->addColumn('govCount', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'Governor');
            })
            ->addColumn('spcomCount', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'SP Committee on Agriculture');
            })
            ->addColumn('ppdoCount', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'PPDO');
            })
            ->addColumn('paoCount', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'Provincial Agriculturist');
            })
            ->addColumn('vetCount', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'Provincial Veterenarian');
            })
            ->addColumn('ppmiuCount', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'PPMIU Head');
            })
               ->addColumn('govCountVerified', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'Governor',true);
            })
            ->addColumn('spcomCountVerified', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'SP Committee on Agriculture',true);
            })
            ->addColumn('ppdoCountVerified', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'PPDO',true);
            })
            ->addColumn('paoCountVerified', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'Provincial Agriculturist',true);
            })
            ->addColumn('vetCountVerified', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'Provincial Veterenarian',true);
            })
            ->addColumn('ppmiuCountVerified', function (Province $province) {
                return $province->paxCount('Provincial Local Government Units', 'PPMIU Head',true);
            })
        ;
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Province>
     */
    public function query(Province $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('Province-table')
            ->addTableClass('table align-middle table-row-dashed fs-7 mb-0 dataTable no-footer align-center')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->lengthMenu([50, 100, 200, 500])
            ->selectStyleSingle()
            ->buttons([
                // Button::make('excel'),
                // Button::make('csv'),
                // Button::make('pdf'),
                // Button::make('print'),
                // Button::make('reset'),
                // Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('region'),
            Column::make('name'),
            Column::make('govCount')->title('Provincial Governor')->sortable(true),
            Column::make('spcomCount')->title('SP Committee on Agriculture')->sortable(true),
            Column::make('ppdoCount')->title('Provincial Provincial Director of Operations')->sortable(true),
            Column::make('paoCount')->title('Provincial Agriculturist')->sortable(true),
            Column::make('vetCount')->title('Provincial Veterenarian')->sortable(true),
            Column::make('ppmiuCount')->title('PPMIU Head')->sortable(true),
                    Column::make('govCountVerified')->title('Provincial Governor')->sortable(true),
            Column::make('spcomCountVerified')->title('SP Committee on Agriculture')->sortable(true),
            Column::make('ppdoCountVerified')->title('Provincial Provincial Director of Operations')->sortable(true),
            Column::make('paoCountVerified')->title('Provincial Agriculturist')->sortable(true),
            Column::make('vetCountVerified')->title('Provincial Veterenarian')->sortable(true),
            Column::make('ppmiuCountVerified')->title('PPMIU Head')->sortable(true),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Province_' . date('YmdHis');
    }
}
