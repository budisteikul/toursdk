<?php

namespace budisteikul\toursdk\DataTables;

use budisteikul\toursdk\Models\Review;
use budisteikul\coresdk\Helpers\GeneralHelper;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ReviewDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables($query)
                ->addIndexColumn()
                ->editColumn('date', function($id){
                    return GeneralHelper::dateFormat($id->date,4);
                })
                ->editColumn('product_id', function($id){
                    return $id->product->name;
                })
                ->editColumn('channel_id', function($id){
                    if($id->link=="")
                    {
                        $channel = $id->channel->name;
                    }
                    else
                    {
                        $channel = '<a class="text-decoration-none" href="'. $id->link .'" target="_blank">'.$id->channel->name.'</a>';
                    }
                    return $channel;
                })
                ->addColumn('action', function ($id) {
                return '
                <div class="btn-toolbar justify-content-end">
                    <div class="btn-group mr-2 mb-2" role="group">
                        
                        <button id="btn-edit" type="button" onClick="EDIT(\''.$id->id.'\'); return false;" class="btn btn-sm btn-success"><i class="fa fa-edit"></i> Edit</button>
                        <button id="btn-del" type="button" onClick="DELETE(\''. $id->id .'\')" class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> Delete</button>
                        
                    </div>
                </div>';
                })
                ->rawColumns(['action','channel_id']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ReviewDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Review $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->addAction(['title' => '','width' => '300px','class' => 'text-center'])
                    ->parameters([
                        'language' => [
                            'paginate' => [
                                'previous'=>'<i class="fa fa-step-backward"></i>',
                                'next'=>'<i class="fa fa-step-forward"></i>',
                                'first'=>'<i class="fa fa-fast-backward"></i>',
                                'last'=>'<i class="fa fa-fast-forward"></i>'
                                ]
                            ],
                        'pagingType' => 'full_numbers',
                        'responsive' => true,
                        'order' => [0,'desc']
                    ])
                    ->ajax('/'.request()->path());
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            ["name" => "created_at", "title" => "created_at", "data" => "created_at", "orderable" => true, "visible" => false,'searchable' => false],
            ["name" => "DT_RowIndex", "title" => "No", "data" => "DT_RowIndex", "orderable" => false, "render" => null,'searchable' => false, 'width' => '30px'],
            ["name" => "user", "title" => "User", "data" => "user", "orderable" => false, "orderable" => false],
            ["name" => "text", "title" => "Text", "data" => "text", "orderable" => false],
            ["name" => "rating", "title" => "Rating", "data" => "rating", "orderable" => false],
            ["name" => "date", "title" => "Date", "data" => "date", "orderable" => false],
            ["name" => "channel_id", "title" => "Channel", "data" => "channel_id", "orderable" => false],
            ["name" => "product_id", "title" => "Product", "data" => "product_id", "orderable" => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Review_' . date('YmdHis');
    }
}
