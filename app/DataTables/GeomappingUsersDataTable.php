<?php

namespace App\DataTables;

use App\Models\GeomappingUser;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\HtmlString;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class GeomappingUsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('actions', fn($geomappingUser) => $this->actions($geomappingUser))
            ->addColumn('registration', function (GeomappingUser $geomappingUser) {
                return date('F d, Y', strtotime($geomappingUser->created_at));
            })->filterColumn('registration', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(created_at, '%M %d, %Y') like ?", ["%$keyword%"]);
            })->addColumn('region', function (GeomappingUser $geomappingUser) {
                $html = <<<HTML
                            <div><span>Region:</span><span class="badge ms-2 badge-sm bg-primary">{$geomappingUser->region->name}</span></div>
                             <div><span>Province:</span><span class="badge ms-2 badge-sm bg-info">{$geomappingUser->province->name}</span></div>
                             <div><span>Office:</span><span class="badge ms-2 badge-sm bg-success">{$geomappingUser->office}</span></div>
                             <div><span>Designation:</span><span class="badge ms-2 badge-sm bg-success">{$geomappingUser->designation}</span></div>
                 HTML;
                return new HtmlString($html);
            })->filterColumn('region', function ($query, $keyword) {
                $query->whereHas('region', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                })->orWhereHas('province', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                })->orWhere('office', 'like', "%$keyword%")->orWhere('designation', 'like', "%$keyword%");
            })
            ->addColumn('contact_info', function (GeomappingUser $geomappingUser) {
                $html = <<<HTML
                            <div><span>Email:</span><span class="badge ms-2 badge-sm bg-primary">{$geomappingUser->email}</span></div>
                             <div><span>Contact #:</span><span class="badge ms-2 badge-sm bg-info">{$geomappingUser->contact_number}</span></div>
                 HTML;
                return new HtmlString($html);
            })->filterColumn('contact_info', function ($query, $keyword) {
                $query->where('email', 'like', "%$keyword%")->orWhere('contact_number', 'like', "%$keyword%");
            })
            ->addColumn('gropup_info', function (GeomappingUser $geomappingUser) {
                $html = <<<HTML
                            <div><span>Group #:</span><span class="badge ms-2 badge-sm bg-primary">{$geomappingUser->group_number}</span></div>
                             <div><span>Table #:</span><span class="badge ms-2 badge-sm bg-info">{$geomappingUser->table_number}</span></div>
                 HTML;
                return new HtmlString($html);
            })->filterColumn('gropup_info', function ($query, $keyword) {
                $query->where('group_number', 'like', "%$keyword%")->orWhere('table_number', 'like', "%$keyword%");
            })
            ->addColumn('status', fn($geomappingUser) => $geomappingUser->is_blocked ? new HtmlString('<span class="badge bg-danger">Blocked</span>') : new HtmlString('<span class="badge bg-success">Active</span>'))

            ->setRowId('id');
    }


    //Get the query source of dataTable.
    public function query(GeomappingUser $model): QueryBuilder
    {
        $query = $model->newQuery();

        if (request()->filled('region_select')) {
            if (strtolower(request()->get('region_select')) != 'all') {
                $query->where('region_id', request()->get('region_select'));
            }
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('model-table')
            ->addTableClass('table table-hover')
            ->columns($this->getColumns())
            ->minifiedAjax('', null, [
                'region_select' => 'function() { return $("#region_select").val(); }',
            ])
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
            ])
            ->parameters([
                'initComplete' => 'function () {
                const table = this.api();
                $("#region_select").on("change", function() {
                    table.ajax.reload();
                    console.log("Region selected:", $(this).val());
                });
            }',
            ]);
    }



    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('firstname')->visible(false),
            Column::make('name')->searchable(true)->width('30%'),
            Column::make('region')->title('Office')->searchable(true)->width('20%'),
            Column::make('contact_info')->title('Contact Info')->searchable(true)->width('20%'),
            Column::make('gropup_info')->title('Group Info')->searchable(true)->width('15%'),
            Column::make('status')->title('Status')->searchable(true)->width('5%'),
            // Column::make('group_numnber')->searchable(true),
            // Column::make('registration')->title('Registered')->searchable(true),
            Column::computed('actions')->exportable(false)->printable(false)->width('8%')->addClass('text-center'),
        ];
    }

    public function actions(GeomappingUser $geomappingUser)
    {
        $isBlocked = $geomappingUser->is_blocked;
        $userId = $geomappingUser->id;

        // Block / Unblock link
        $blockAction = $isBlocked
            ? '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { user: ' . $userId . ' })">Unblock</a>'
            : '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { user: ' . $userId . ' })">Block</a>';

        // Mail ID link only if group_number & table_number exist
        $mailIdAction = '';
        if ($geomappingUser->group_number !== null && $geomappingUser->table_number !== null) {
            $mailIdAction = '
            <li>
                <a class="dropdown-item" href="#" onclick="Livewire.dispatch(\'confirmSendGeomappingUserId\', { user: ' . $userId . ' })">
                    Mail ID
                </a>
            </li>';
        }

        // Build full dropdown HTML
        $html = <<<HTML
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="actionsMenuButton{$userId}" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="actionsMenuButton{$userId}">
                            <!-- Edit Profile -->
                            <li>
                                <a class="dropdown-item" href="#" onclick="Livewire.dispatch('editGeomappingUser', { user: $userId })">
                                    Edit Profile
                                </a>
                            </li>
                            $mailIdAction
                            <li>
                                <a class="dropdown-item" href="#" onclick="Livewire.dispatch('assignGeomappingUser', { user: $userId })">
                                    Assign
                                </a>
                            </li>
                            <!-- Block / Unblock -->
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
        return 'GeomappingUsers_' . date('YmdHis');
    }
}
