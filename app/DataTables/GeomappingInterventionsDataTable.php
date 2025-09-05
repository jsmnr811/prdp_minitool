<?php

namespace App\DataTables;

use App\Models\Intervention;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\HtmlString;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GeomappingInterventionsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('actions', fn(Intervention $intervention) => $this->actions($intervention))
            ->addColumn('status', fn($intervention) => $intervention->is_blocked ? new HtmlString('<span class="badge bg-danger">Blocked</span>') : new HtmlString('<span class="badge bg-success">Active</span>'))
            ->rawColumns(['actions'])
            ->setRowId('id');
    }

    //Get the query source of dataTable.
    public function query(Intervention $model): QueryBuilder
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
            Column::make('name')
                ->searchable(true),
            Column::make('status')->title('Status')->searchable(true)->width('5%'),
            Column::computed('actions')
                ->exportable(false)
                ->printable(false)
                ->width('10%')
                ->addClass('text-left'), 
        ];
    }



    public function actions(Intervention $intervention)
    {
        $isBlocked = $intervention->is_blocked;
        $interventionId = $intervention->id;

        $blockAction = $isBlocked
            ? '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { intervention: ' . $interventionId . ' })">Unblock</a>'
            : '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { intervention: ' . $interventionId . ' })">Block</a>';


        $html = <<<HTML
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="actionsMenuButton$interventionId" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu" aria-labelledby="actionsMenuButton$interventionId">
                    <li>
                        <a class="dropdown-item" href="#" onclick="Livewire.dispatch('editGeomappingIntervention', { intervention: $interventionId })">
                            Edit Intervention
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
        return 'GeomappingInterventions_' . date('YmdHis');
    }
}
