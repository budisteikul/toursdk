<?php

namespace budisteikul\toursdk\DataTables;

use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\ProductHelper;
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
                    $invoice = '';
                    $invoice .= '<b><a class="text-decoration-none" href="/snippets/pdf/invoice/'. $id->session_id .'/Invoice-'. $id->confirmation_code .'.pdf" target="_blank">'. $id->confirmation_code .'</a> - INVOICE</b> <br />';
                    $invoice .= ' <b>Channel :</b> '.$id->booking_channel.' <br />';

                    $name = $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','firstName')->first()->answer .' '. $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','lastName')->first()->answer;
                
                    $email = $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer;
                    $phone = $id->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','phoneNumber')->first()->answer;

                    if($name!='') $invoice .= ' <b>Name :</b> '.$name.' <br />';
                    if($email!='') $invoice .= ' <b>Email :</b> '.$email.' <br />';
                    if($phone!='') $invoice .= ' <b>Phone :</b> '.$phone.' <br />';

                    if($id->booking_status=="CONFIRMED")
                    {
                        $invoice .= ' <b>Status :</b> <span class="badge badge-success">'.$id->booking_status.'</span> <br />';
                    }
                    else
                    {
                        $invoice .= ' <b>Status :</b> <span class="badge badge-danger">'.$id->booking_status.'</span> <br />';
                    }
                    

                    $product = '';
                    foreach($id->shoppingcart_products()->get() as $shoppingcart_product)
                    {
                        $product .= '<b><a class="text-decoration-none" href="/snippets/pdf/ticket/'. $id->session_id .'/Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf" target="_blank">'. $shoppingcart_product->product_confirmation_code .'</a> - '.$shoppingcart_product->title.'</b> <br />';
                        if($shoppingcart_product->rate!="") $product .= $shoppingcart_product->rate .' <br />';
                        $product .= ProductHelper::datetotext($shoppingcart_product->date) .' <br />';
                        foreach($shoppingcart_product->shoppingcart_rates()->get() as $shoppingcart_rate)
                        {
                            if($shoppingcart_rate->type=="product")
                            {
                                $product .= $shoppingcart_rate->qty .' '. $shoppingcart_rate->unit_price .'<br>';
                            }
                            elseif($shoppingcart_rate->type=="pickup")
                            {
                                $product .= $shoppingcart_rate->title .'<br>';
                            }
                        }

                        //$product = '';
                        foreach($id->shoppingcart_questions()->where('booking_id',$shoppingcart_product->booking_id)->whereNotNull('label')->get() as $shoppingcart_question)
                        {
                            $product .= $shoppingcart_question->label .' : '. $shoppingcart_question->answer .'<br>';
                        }
                        $product .= '<br>';
                    }

                    return $invoice .'<hr>'. $product;
                })
                
                ->addColumn('payment', function ($id){
                	if(isset($id->shoppingcart_payment->payment_status))
                	{
                   	 	switch($id->shoppingcart_payment->payment_status)
                    	{
                        	case 1:
                            	return '
                <div class="btn-toolbar justify-content-end">
                    <div class="btn-group mr-2 mb-2" role="group">
                        
                        <button id="void-'.$id->id.'" type="button" onClick="STATUS(\''.$id->id.'\',\'void\'); return false;" class="btn btn-sm btn-warning payment"><i class="fa fa-ban"></i> Void</button>
                        <button id="capture-'.$id->id.'" type="button" onClick="STATUS(\''. $id->id .'\',\'capture\')" class="btn btn-sm btn-primary payment"><i class="fa fa-money-check"></i> Capture</button>
                        
                    </div>
                </div>';
                        	break;
                        	default:
                            	return BookingHelper::payment_status($id->shoppingcart_payment->payment_status);
                       
                    	}
                	}
                    return BookingHelper::payment_status(0);
                    //$paymentStatus = 0;
                    //$shoppingcart_payment = $id->shoppingcart_payment->first();
                    //if(isset($shoppingcart_payment)) $paymentStatus = $shoppingcart_payment->payment_status;
                    //return BookingHelper::payment_status($paymentStatus);
                })
                ->addColumn('action', function ($id) {
                if(isset($id->shoppingcart_payment->payment_status))
                {
                    if($id->shoppingcart_payment->payment_status==1)
                    {
                        return '';
                    }
                }

                if($id->booking_status=='CANCELED')
                {
                    $button_cancel = '';
                }
                else
                {
                    $button_cancel = '<button id="btn-edit" type="button" onClick="CANCEL(\''.$id->id.'\'); return false;" class="btn btn-sm btn-success"><i class="fa fa-ban"></i> Cancel This Booking</button>';
                }
                

                return '
                <div class="btn-toolbar justify-content-end">
                    <div class="btn-group mr-2 mb-2" role="group">
                        
                        '.$button_cancel.'
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
        return $model->where('booking_status','CONFIRMED')->orWhere('booking_status','CANCELED')->newQuery();
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
