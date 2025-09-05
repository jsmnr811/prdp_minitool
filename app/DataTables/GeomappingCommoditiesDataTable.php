<?php

namespace App\DataTables;

use App\Models\Commodity;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\HtmlString;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GeomappingCommoditiesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('icon', function (Commodity $commodity) {
                $iconFile = $commodity->icon && file_exists(public_path('icons/commodities/' . basename($commodity->icon)))
                    ? basename($commodity->icon)
                    : 'default.png';

                $imagePath = asset('icons/commodities/' . $iconFile);
                return '<img src="' . $imagePath . '" alt="' . e($commodity->name) . '" style="width:40px; height:auto; border-radius:4px;">';
            })
            ->addColumn('actions', fn(Commodity $commodity) => $this->actions($commodity))
            ->addColumn('status', fn($commodity) => $commodity->is_blocked ? new HtmlString('<span class="badge bg-danger">Blocked</span>') : new HtmlString('<span class="badge bg-success">Active</span>'))
            ->rawColumns(['icon', 'actions'])
            ->setRowId('id');
    }

    //Get the query source of dataTable.
    public function query(Commodity $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'asc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('model-table')
            ->addTableClass('table table-hover')
            ->columns($this->getColumns())
            ->orderBy(1)
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')
                ->width('5%'),
            Column::computed('icon')
                ->exportable(false)
                ->printable(false)
                ->width('10%'),
            Column::make('name')
                ->searchable(true),
            Column::make('abbr')->title('Abbreviation')
                ->searchable(true),
            Column::make('status')->title('Status')->searchable(true)->width('5%'),

            Column::computed('actions')
                ->exportable(false)
                ->printable(false)
                ->width('10%')
                ->addClass('text-left'), // keep actions centered
        ];
    }



    public function actions(Commodity $commodity)
    {
        $isBlocked = $commodity->is_blocked;
        $commodityId = $commodity->id;

        $blockAction = $isBlocked
            ? '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { commodity: ' . $commodityId . ' })">Unblock</a>'
            : '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { commodity: ' . $commodityId . ' })">Block</a>';


        $html = <<<HTML
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="actionsMenuButton$commodityId" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu" aria-labelledby="actionsMenuButton$commodityId">
                    <li>
                        <a class="dropdown-item" href="#" onclick="Livewire.dispatch('editGeomappingCommodity', { commodity: $commodityId })">
                            Edit Commodity
                        </a>
                    </li>
                    <li>
                                $blockAction
                            </li>
                </ul>
            </div>
            HTML;

        return new HtmlString($html);
    }


    protected function filename(): string
    {
        return 'GeomappingCommodities_' . date('YmdHis');
    }
}
