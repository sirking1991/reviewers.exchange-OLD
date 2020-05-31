<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherAccountBalance extends Component
{
    public $balance;
    public function render()
    {
        $add = DB::table('transactions')->where('user_id', Auth()->user()->id)->sum('add');
        $sub = DB::table('transactions')->where('user_id', Auth()->user()->id)->sum('sub');

        $this->balance = $add - $sub;
        return <<<'blade'
            <div class="card shadow-sm rounded-lg">
                <div class="card-header text-center">
                    Account balance
                </div>
                <div class="card-body text-center">
                    <h1>{{ number_format($balance,2) }}</h1>
                    @if(env('MINIMUM_BALANCE_FOR_WITHDRAWAL', 500)<$balance)
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#withdrawalModal">Withdraw fund</button>
                    @else
                        <span class='text-muted'>Minimum balance for withdrawal: {{ env('MINIMUM_BALANCE_FOR_WITHDRAWAL', 500) }}</span>
                    @endif
                </div>
            </div>

            <div class='modal' tabindex="-1" role="dialog" id='withdrawalModal'>
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            Request fund withdrawal
                            <div class="float-right">
                                <button type="button" class="btn btn-sm btn-success" onclick="requestWithdrawal()">Make withdrawal request</button>
                                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class='amount'>
                                <label for="amount">Amount: <span class='amount'>{{ $balance }}</span></label>
                                <input type="range" class="custom-range" min="500" max="{{ $balance }}" step="50" id="amount">
                            </div>
                            <div class="alert alert-success" role="alert">
                                Your fund withdrawal reuqest has been submitted.
                            </div>
                            <div class="alert alert-danger" role="alert">
                                An error occured while processing your request.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                var amount = document.getElementById('amount');
                amount.value = {{ $balance }};
                amount.addEventListener('change', function(e){
                    console.log(amount.value);
                    $('span.amount').html(amount.value);
                });

                document.addEventListener('DOMContentLoaded', function(){
                    $('#withdrawalModal').on('show.bs.modal', function (e) {
                        $('#withdrawalModal button.btn-success').show();
                        $('#withdrawalModal div.modal-body div.amount').show()
                        $('#withdrawalModal div.modal-body .alert-success').hide()
                        $('#withdrawalModal div.modal-body .alert-danger').hide();
                    }) 
                });

                function requestWithdrawal()
                {
                    axios.post('/publisher/request-fund-withdrawal', {amount: amount.value})
                        .then(function(resp){
                            $('#withdrawalModal button.btn-success').hide();
                            $('#withdrawalModal div.modal-body div.amount').hide()
                            $('#withdrawalModal div.modal-body .alert-success').show()
                        })
                        .catch(function(error){
                            $('#withdrawalModal button.btn-success').hide();
                            $('#withdrawalModal div.modal-body div.amount').hide();
                            $('#withdrawalModal div.modal-body .alert-danger').show();
                            $('#withdrawalModal div.modal-body .alert-danger').html(error);
                        });
                }

            </script>
        blade;
    }
}
