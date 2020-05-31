<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PublisherStatement extends Component
{
    public $rows;
    public $dateFrom;
    public $dateTo;
    public $add;
    public $sub;
    public $runningBalance = 0;

    public function mount()
    {
        $this->dateFrom = date('Y-m-01');
        $this->dateTo = date('Y-m-d');
    }

    private function defaultQuery()
    {
        $this->add = DB::table('transactions')->where('user_id', Auth()->user()->id)->whereDate('created_at', '<', date('Y-m-01'))->sum('add');
        $this->sub = DB::table('transactions')->where('user_id', Auth()->user()->id)->whereDate('created_at', '<', date('Y-m-01'))->sum('sub');             

        $this->rows = \App\Transaction::where('user_id', Auth()->user()->id)
            ->whereDate('created_at', '>=', date('Y-m-01'))
            ->whereDate('created_at', '<=', date('Y-m-d'))
            ->orderBy('created_at')
            ->get();
    }

    public function render()
    {
        if( !strtotime($this->dateFrom) || !strtotime($this->dateTo)) {
            $this->defaultQuery();
        } else {
            try {
                $this->rows = \App\Transaction::where('user_id', Auth()->user()->id)
                        ->whereDate('created_at', '>=', $this->dateFrom)
                        ->whereDate('created_at', '<=', $this->dateTo)
                        ->orderBy('created_at')
                        ->get();                    
                $this->add = DB::table('transactions')->where('user_id', Auth()->user()->id)->whereDate('created_at', '<', $this->dateFrom)->sum('add');
                $this->sub = DB::table('transactions')->where('user_id', Auth()->user()->id)->whereDate('created_at', '<', $this->dateFrom)->sum('sub');

            } catch (\Throwable $th) {
                $this->defaultQuery();
            }           
        }

        $this->runningBalance = $this->add - $this->sub;

        return <<<'blade'
            <div class='card'>
                <div class='card-header bg-primary text-white' style='padding-bottom:0.20rem;' >
                <div class='row'>
                    <div class='col-md'>
                        Statement of account 
                    </div>
                    <div class='col-md form-check form-check-inline'>
                        <input class='form-control form-control-sm' type='date' wire:model.lazy="dateFrom" />&nbsp;to&nbsp;
                        <input class='form-control form-control-sm' type='date' wire:model.lazy="dateTo" />
                    </div>
                </div>
                </div>
                <div class='card-body'>
                    <table class="table table-striped">
                        <thead class='thead-light'>
                            <tr>
                                <th scope="col">Date/Time</th>
                                <th scope="col">Type</th>
                                <th scope="col">Description</th>
                                <th scope="col">Add</th>
                                <th scope="col">Subtract</th>
                                <th scope="col">Balance</th>
                            </tr>
                        </thead>
                        <tbody>     
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Previous balance</td>
                                <td class='text-right'></td>
                                <td class='text-right'></td>
                                <td class='text-right'>{{ number_format($runningBalance,2) }}</td>
                            </tr>                       
                        @foreach($rows as $r)
                            @php 
                                $runningBalance += $r->add - $r->sub; 
                            @endphp
                            <tr>
                                <td>{{ $r->created_at }}</td>
                                <td>{{ Str::title($r->type) }}</td>
                                <td>{{ $r->description }}</td>
                                <td class='text-right'>{{ $r->add == 0 ? '' : number_format($r->add,2) }}</td>
                                <td class='text-right'>{{ $r->sub == 0 ? '' : number_format($r->sub,2) }}</td>
                                <td class='text-right'>{{ number_format($runningBalance,2) }}</td>
                            </tr>
                            
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        blade;
    }
}
