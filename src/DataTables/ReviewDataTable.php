<?php

namespace budisteikul\toursdk\DataTables;

use budisteikul\toursdk\Models\Review;
use budisteikul\coresdk\Helpers\GeneralHelper;
use budisteikul\toursdk\Helpers\ReviewHelper;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

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
                ->addColumn('review', function($id){
                    $review = '';

                    $review .= '<b>'. $id->user .'</b> '. GeneralHelper::dateFormat($id->date,4) .'<br />';
                    $review .= 'Review of : <b>'. $id->product->name .'</b><br />';
                    $review .= ReviewHelper::star($id->rating) .'<br />';
                    if($id->title!="") $review .= '<b>'. $id->title .'</b><br />';
                    $review .= Str::words($id->text, 15) .'<br />';
                    if($id->link=="")
                    {
                        $review .= $id->channel->name;
                    }
                    else
                    {
                        $review .= '<a class="text-decoration-none" href="'. $id->link .'" target="_blank">'.$id->channel->name.'</a>';
                    }
                    return $review;
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
                ->rawColumns(['action','channel_id','review']);
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
            ["name" => "review", "title" => "Review", "data" => "review", "orderable" => false],
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
