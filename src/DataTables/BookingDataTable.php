<?php

namespace budisteikul\toursdk\DataTables;

use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Helpers\BookingHelper;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BookingDataTable extends DataTable
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
                ->addColumn('invoice', function ($id){
                    $value = '';
                    $value .= '<b><a class="text-decoration-none" href="/snippets/pdf/invoice/'. $id->confirmation_code .'" target="_blank">'. $id->confirmation_code .'</a> - INVOICE</b> <br />';
                    $value .= ' Channel : '.$id->booking_channel.' <br />';

                    $name = $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','firstName')->first()->answer .' '. $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','lastName')->first()->answer;
                
                    $email = $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer;
                    $phone = $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','phoneNumber')->first()->answer;

                    if($name!='') $value .= ' Name : '.$name.' <br />';
                    if($email!='') $value .= ' Email : '.$email.' <br />';
                    if($phone!='') $value .= ' Phone : '.$phone.' <br />';

                    return $value;
                })
                ->addColumn('product', function ($id){
                    $value = '';
                    foreach($id->shoppingcart_products()->get() as $shoppingcart_product)
                    {
                        $value .= '<b><a class="text-decoration-none" href="/snippets/pdf/ticket/'. $shoppingcart_product->product_confirmation_code .'" target="_blank">'. $shoppingcart_product->product_confirmation_code .'</a> - '.$shoppingcart_product->title.'</b> <br />';
                        if($shoppingcart_product->rate!="") $value .= $shoppingcart_product->rate .' <br />';
                        $value .= BookingHelper::datetotext($shoppingcart_product->date) .' <br />';
                        foreach($shoppingcart_product->shoppingcart_rates()->get() as $shoppingcart_rate)
                        {
                            if($shoppingcart_rate->type=="product")
                            {
                                $value .= $shoppingcart_rate->qty .' '. $shoppingcart_rate->unit_price .'<br>';
                            }
                            elseif($shoppingcart_rate->type=="pickup")
                            {
                                $value .= $shoppingcart_rate->title .'<br>';
                            }
                        }

                        //$value = '';
                        foreach($id->shoppingcart_questions()->where('booking_id',$shoppingcart_product->booking_id)->whereNotNull('label')->get() as $shoppingcart_question)
                        {
                            $value .= $shoppingcart_question->label .' : '. $shoppingcart_question->answer .'<br>';
                        }
                        $value .= '<br>';
                    }
                    return $value;
                })
                ->addColumn('payment', function ($id){
                    $paymentStatus = 0;
                    $shoppingcart_payment = $id->shoppingcart_payment->first();
                    if(isset($shoppingcart_payment)) $paymentStatus = $shoppingcart_payment->paymentStatus;

                    switch($paymentStatus)
                    {
                        case 1:
                            $paymentStatus = "AUTHORIZED";
                        break;
                        case 2:
                            $paymentStatus = "CAPTURED";
                        break;
                        case 3:
                            $paymentStatus = "VOIDED";
                        break;
                        default:
                            $paymentStatus = "NOT AVAILABLE";
                    }
                    return $paymentStatus;
                })
                ->addColumn('action', function ($id) {
                return '
                <div class="btn-toolbar justify-content-end">
                    <div class="btn-group mr-2 mb-2" role="group">
                        
                        <!-- button id="btn-edit" type="button" onClick="EDIT(\''.$id->id.'\'); return false;" class="btn btn-sm btn-success"><i class="fa fa-ban"></i> Cancel This Booking</button -->
                        <button id="btn-del" type="button" onClick="DELETE(\''. $id->id .'\')" class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> Delete</button>
                        
                    </div>
                </div>';
                })
                ->rawColumns(['action','invoice','product','payment']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\BookingDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Shoppingcart $model)
    {
        return $model->where('booking_status','CONFIRMED')->orWhere('booking_status','CANCELLED')->newQuery();
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
                    ->addAction(['title' => '','class' => 'text-center'])
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
            ["name" => "invoice", "title" => "Invoice", "data" => "invoice", "orderable" => false],
            ["name" => "product", "title" => "Product", "data" => "product", "orderable" => false],
            ["name" => "booking_status", "title" => "Booking Status", "data" => "booking_status", "orderable" => false],
            ["name" => "payment", "title" => "Payment", "data" => "payment", "orderable" => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Booking_' . date('YmdHis');
    }
}
