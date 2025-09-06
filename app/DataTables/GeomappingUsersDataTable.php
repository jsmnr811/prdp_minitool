<?php

namespace App\DataTables;

use App\Models\GeomappingUser;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\HtmlString;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
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
                            <div><span>Region:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->region->abbr}</span></div>
                             <div><span>Province:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->province->abbr}</span></div>
                             <div><span>Office:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->office}</span></div>
                             <div><span>Designation:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->designation}</span></div>
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
                            <div><span>Email:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->email}</span></div>
                             <div><span>Contact #:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->contact_number}</span></div>
                 HTML;
                return new HtmlString($html);
            })->filterColumn('contact_info', function ($query, $keyword) {
                $query->where('email', 'like', "%$keyword%")->orWhere('contact_number', 'like', "%$keyword%");
            })
            ->addColumn('attendance_days', function (GeomappingUser $geomappingUser) {
                $days = $geomappingUser->attendance_days
                    ? explode(',', $geomappingUser->attendance_days)
                    : [];

                $badges = collect($days)->map(function ($day) {
                    return "<span class='badge bg-info text-dark me-1'>" . trim($day) . "</span>";
                })->implode(' ');

                return new HtmlString($badges);
            })
            ->addColumn('gropup_info', function (GeomappingUser $geomappingUser) {
                $liveInClass = $geomappingUser->is_livein ? 'bg-success text-white' : 'bg-danger text-white';
                $liveInText  = $geomappingUser->is_livein ? 'Yes' : 'No';

                $html = <<<HTML
                            <div><span>Group #:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->group_number}</span></div>
                            <div><span>Table #:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->table_number}</span></div>
                            <div>
                                <span>Is Live In:</span>
                                <span class="badge ms-2 badge-sm {$liveInClass}">{$liveInText}</span>
                            </div>
                            <div><span>Room #:</span><span class="badge ms-2 badge-sm bg-light text-dark">{$geomappingUser->room_assignment}</span></div>
                        HTML;

                return new HtmlString($html);
            })
            ->filterColumn('gropup_info', function ($query, $keyword) {
                $query->where('group_number', 'like', "%$keyword%")
                    ->orWhere('table_number', 'like', "%$keyword%")
                    ->orWhere('room_assignment', 'like', "%$keyword%")
                    ->orWhere(function ($q) use ($keyword) {
                        if (stripos('yes', $keyword) !== false) {
                            $q->where('is_livein', 1);
                        } elseif (stripos('no', $keyword) !== false) {
                            $q->where('is_livein', 0);
                        }
                    });
            })

            ->addColumn('is_verified', function (GeomappingUser $geomappingUser) {
                $class = $geomappingUser->is_verified ? 'success' : 'danger';
                $label = $geomappingUser->is_verified ? 'Verified' : 'Not Verified';
                $html = <<<HTML
                            <span role="button" style="cursor: pointer" onclick="Livewire.dispatch('updateVerified', { user: {$geomappingUser->id} })" class="badge badge-sm bg-{$class}">{$label}</span>
                 HTML;
                return new HtmlString($html);
            })
            ->addColumn('status', fn($geomappingUser) => $geomappingUser->is_blocked ? new HtmlString('<span class="badge bg-danger">Blocked</span>') : new HtmlString('<span class="badge bg-success">Active</span>'))
            ->addColumn('name_role', function (GeomappingUser $user) {
                if ($user->role == 2) {
                    $roleLabel = '<span class="badge bg-secondary badge-sm">Participant</span>';
                } elseif ($user->role == 1 && $user->is_iplan == 1) {
                    $roleLabel = '<span class="badge bg-success badge-sm">I-PLAN Administrator</span>';
                } elseif ($user->role == 1) {
                    $roleLabel = '<span class="badge bg-primary badge-sm">Administrator</span>';
                } else {
                    $roleLabel = '<span class="badge bg-warning badge-sm">Unknown Role</span>';
                }
                $html = <<<HTML
                            <div>
                                <div>{$user->name}</div>
                                <div class="mt-1">{$roleLabel}</div>
                            </div>
                        HTML;
                return new HtmlString($html);
            })
            ->filterColumn('name_role', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $keywordLower = strtolower($keyword);

                    $q->where('name', 'like', "%$keyword%");

                    if (strpos('participant', $keywordLower) !== false) {
                        $q->orWhere('role', 2);
                    }
                    if (strpos('administrator', $keywordLower) !== false) {
                        $q->orWhere('role', 1);
                    }
                    if (strpos('i-plan', $keywordLower) !== false || strpos('iplan', $keywordLower) !== false) {
                        $q->orWhere(function ($q2) {
                            $q2->where('role', 1)->where('is_iplan', 1);
                        });
                    }
                });
            })
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
        $query->orderBy('id', 'asc');
        return $query;
    }

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
            ->parameters([
                'pagingType' => 'simple_numbers',
                'lengthChange' => true,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language' => [
                    'paginate' => [
                        'previous' => '&laquo;',
                        'next' => '&raquo;',
                    ]
                ],
                'initComplete' => 'function () {
                    const table = this.api();
                    $("#region_select").on("change", function() {
                        table.ajax.reload();
                    });
                }',
            ]);
    }


    public function getColumns(): array
    {
        return [
            Column::make('id')->width('5%')->addClass('text-center'),
            Column::make('firstname')->visible(false),

            Column::make('name_role')->title('Name')->searchable(true)->width('20%'),
            Column::make('region')->title('Office')->searchable(true)->width('15%'),
            Column::make('contact_info')->title('Contact Info')->searchable(true)->width('15%'),
            Column::make('gropup_info')->title('Group Info')->searchable(true)->width('15%'),
            Column::make('attendance_days')->title('Attendance')->width('10%'),
            Column::make('is_verified')->title('Is Verified')->width('5%'),
            Column::make('status')->title('Status')->searchable(true)->width('5%'),
            Column::computed('actions')->exportable(false)->printable(false)->width('10%')->addClass('text-center'),
        ];
    }


    public function actions(GeomappingUser $geomappingUser)
    {
        $isBlocked = $geomappingUser->is_blocked;
        $userId = $geomappingUser->id;

        $blockAction = $isBlocked
            ? '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { user: ' . $userId . ' })">Unblock</a>'
            : '<a class="dropdown-item text-danger" href="#" onclick="Livewire.dispatch(\'confirmUpdateBlockStatus\', { user: ' . $userId . ' })">Block</a>';

        $mailIdAction = '';
        // if ($geomappingUser->group_number !== null && $geomappingUser->table_number !== null) {
        $mailIdAction = '
            <li>
                <a class="dropdown-item" href="#" onclick="Livewire.dispatch(\'confirmSendGeomappingUserId\', { user: ' . $userId . ' })">
                    Mail ID
                </a>
            </li>';
        // }

        $html = <<<HTML
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="actionsMenuButton{$userId}" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="actionsMenuButton{$userId}">
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
