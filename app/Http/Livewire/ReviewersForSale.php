<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ReviewersForSale extends Component
{
    public $reviewers;
    public $search;
    public $category;

    public function render()
    {
        $this->reviewers = \App\Reviewer::where('status', 'active')
            ->when(3 <= strlen($this->search), function ($query) {
                return $query->where('name', 'like', "%$this->search%");
            })
            ->when('' != strlen($this->category), function ($query) {
                return $query->where('category', 'like', "%$this->category%");
            })
            ->whereRaw("id NOT IN (SELECT reviewer_id FROM reviewer_purchases WHERE user_id=" . Auth()->user()->id . " AND status='success')")
            ->with('publisher')
            ->get();

        return <<<'blade'
    
        <div class="row justify-content-center">
            <div class="col-md">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class='row'>
                            <div class='col-md-4'>
                                <h4>Reviewers available for sale</h4>
                            </div>
                            <div class='col-md-8'>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <input type='text' wire:model.debounce.250ms="search" class='form-control' placeholder='Search' />
                                    </div>
                                    <div class='col-md-6'>
                                        <select class='form-control'  wire:model="category" >
                                            <option value=''>All</option>
                                            <option value='accounting'>Accounting</option>
                                            <option value='engineering'>Engineering</option>
                                            <option value='civil-service'>Civil Service</option>
                                            <option value='entrance-exam'>Entrance Exams</option>
                                            <option value='nursing'>Nursing</option>
                                            <option value='medicine'>Medicine</option>
                                            <option value='education'>Education</option>
                                            <option value='law'>Law</option>
                                            <option value='others'>Others</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body horizontal-scroll">
                    @if(0 < count($reviewers))
                        @foreach($reviewers as $index => $r)
                            @if(0==$r->sellingPrice() || 100<=$r->sellingPrice())
                                <div class="card shadow-sm  rounded-lg">
                                    <img src="{{ env('AWS_S3_URL') . $r->cover_photo }}" class="card-img-top" alt="...">
                                    <div class="card-body wrapword">
                                        <p class='name'>{{ $r->name }} by <span class='text-muted'>{{ $r->publisher->display_name }}</span></p>
                                        <p class='selling-price'>                                    
                                            @if(0>=$r->sellingPrice())
                                                <button class='btn btn-danger btn-block' onclick="buyNow( {{ $r->id }}, {{ $r->sellingPrice() }} )">Get this for free!</button>
                                            @else
                                                <button class='btn btn-danger btn-block' onclick="buyNow( {{ $r->id }}, {{ $r->sellingPrice() }} )">Buy Now {{ number_format($r->sellingPrice(), 2) }}</button>
                                            @endif
                                        </p> 
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="alert alert-secondary" role="alert">
                            {{ __('No reviewers for this creteria was found') }}
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div> 
    
        <div class="modal fade" id="paymongoCardDetailModal" tabindex="-1" role="dialog" aria-labelledby="paymongoCardDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">              
                        Payments processed by
                        <a href='https://paymongo.com/' target='paymongo'>
                            <img width='150' src='https://lares-reviewers.s3-ap-southeast-1.amazonaws.com/common/paymongo_logo.png' alt='Paymongo logo' />
                        </a>
                        
                        <hr/>   

                        <div class="alert alert-danger" role="alert" style='display:none;'></div>

                        <div class='form-group'>
                            <label>Card #</label>
                            <input class='form-control' type='number' maxlength='16' name='card_number'/>
                        </div>

                        <div class='row'>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Expiry month</label>                                    
                                    <select class='form-control' name='exp_month'>
                                        <option value='01'>01</option>
                                        <option value='02'>02</option>
                                        <option value='03'>03</option>
                                        <option value='04'>04</option>
                                        <option value='05'>05</option>
                                        <option value='06'>06</option>
                                        <option value='07'>07</option>
                                        <option value='08'>08</option>
                                        <option value='09'>09</option>
                                        <option value='10'>10</option>
                                        <option value='11'>11</option>
                                        <option value='12'>12</option>
                                    </select>
                                </div>
                            </div>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Expiry year</label>
                                    <select class='form-control' name='exp_year'>
                                    @for ($i = 2020; $i <=2030; $i++)
                                        <option value='{{ $i }}'>{{ $i }}</option>
                                    @endfor
                                    </select>
                                </div>
                            </div>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>CVC</label>
                                    <input class='form-control' type='number' maxlength='4' name='cvc'/>
                                </div>
                            </div>
                        </div>

                        <div class='row' style='display:none;'>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Address line 1</label>
                                    <input class='form-control' type='text' maxlength='256' name='line1'/>
                                </div>
                            </div>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Address line 2</label>
                                    <input class='form-control' type='text' maxlength='256' name='line2'/>
                                </div>
                            </div>
                        </div>

                        <div class='row' style='display:none;'>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>City</label>
                                    <input class='form-control' type='text' maxlength='50' name='city'/>
                                </div>
                            </div>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>State</label>
                                    <input class='form-control' type='text' maxlength='50' name='state'/>
                                </div>
                            </div>                            
                        </div>

                        <div class='row' style='display:none;'>                            
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Postal code</label>
                                    <input class='form-control' type='text' maxlength='10' name='postal_code'/>
                                </div>
                            </div>                            
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Country code</label>
                                    <input class='form-control' type='text' maxlength='2' name='country_code' value='PH's/>
                                </div>
                            </div>
                        </div>

                        <div class='form-group'>
                            <label>Name on card</label>
                            <input class='form-control' type='text' maxlength='256' name='name'/>
                        </div>

                        <div class='row'>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Email</label>
                                    <input class='form-control' type='email' maxlength='50' name='email'/>
                                </div>
                            </div>
                            <div class='col-md'>
                                <div class='form-group'>
                                    <label>Phone</label>
                                    <input class='form-control' type='tel' maxlength='20' name='phone'/>
                                </div>
                            </div>
                        </div>

                    </div>      
                    <div class='modal-footer'>
                        <input name='processBtn' type='button' onclick='processPayment()' class="btn btn-success btn-block" value='Process payment' />
                        
                        <div style='display: none; margin-left: 50%; margin-right: 50%;' class='spinner'>                        
                            <div class="spinner-border text-success" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class='modal fade' id='3DAuthModal' tabindex="-1" role="dialog" aria-labelledby="3DAuthModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">     
                        <iframe id='3dauth' height='500' width='600' allowfullscreen=true style='border: 0px'></iframe>   
                    </div>
                    <div class='modal-footer'>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>            
            clientKey = '';
            paymentMethodId = '';
            reviewerId = '';
            paymentIntentId = '';

            document.addEventListener('DOMContentLoaded', function(){

                $('#3DAuthModal').on('hidden.bs.modal', function (e) {
                    axios.get(
                        'https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '?client_key=' + clientKey,
                        {
                          headers: {
                            // Base64 encoded public PayMongo API key.
                            Authorization: `Basic ${window.btoa('{{ env('PAYMONGO_PUBLIC_KEY') }}')}`
                          }
                        }
                      ).then(function(response) {
                        var paymentIntent = response.data.data;
                        var paymentIntentStatus = paymentIntent.attributes.status;
                
                        if (paymentIntentStatus === 'succeeded') {
                            // You already received your customer's payment. You can show a success message from this condition.
                            confirmPayment();

                        } else if(paymentIntentStatus === 'awaiting_payment_method') {
                            // The PaymentIntent encountered a processing error. You can refer to paymentIntent.attributes.last_payment_error to check the error and render the appropriate error message.
                            $('#paymongoCardDetailModal div.alert').hide();
                            $('#paymongoCardDetailModal').modal('show');
                            displayError('An error occured while processing your payment. Try again later');

                          
                        }
                      }).catch(function (e) {
                        $('#paymongoCardDetailModal input[name=processBtn]').show();
                        displayError(e);
                      });
                });  
            });

            function populateFields()
            {
                // $('#paymongoCardDetailModal input[name=line1]').val( getCookie('line1') );
                // $('#paymongoCardDetailModal input[name=line2]').val( getCookie('line2') );
                // $('#paymongoCardDetailModal input[name=city]').val( getCookie('city') );
                // $('#paymongoCardDetailModal input[name=state]').val( getCookie('state') );
                // $('#paymongoCardDetailModal input[name=postal_code]').val( getCookie('postal_code') );
                // $('#paymongoCardDetailModal input[name=country_code]').val( getCookie('country_code') );
                // $('#paymongoCardDetailModal input[name=name]').val( getCookie('name') );
                // $('#paymongoCardDetailModal input[name=email]').val( getCookie('email') );
                // $('#paymongoCardDetailModal input[name=phone]').val( getCookie('phone') );                
            }

            function saveFieldValues()
            {
                // setCookie('line1', $('#paymongoCardDetailModal input[name=line1]'));
                // setCookie('line2', $('#paymongoCardDetailModal input[name=line2]'));
                // setCookie('city', $('#paymongoCardDetailModal input[name=city]'));
                // setCookie('state', $('#paymongoCardDetailModal input[name=state]'));
                // setCookie('postal_code', $('#paymongoCardDetailModal input[name=postal_code]'));
                // setCookie('country_code', $('#paymongoCardDetailModal input[name=country_code]'));
                // setCookie('name', $('#paymongoCardDetailModal input[name=name]'));
                // setCookie('email', $('#paymongoCardDetailModal input[name=email]'));
                // setCookie('phone', $('#paymongoCardDetailModal input[name=phone]'));
            }

            function buyNow(r, price)
            {
                reviewerId = r;

                if (0==price) {
                    window.location = '/paymongo/buy-reviewer/' + reviewerId;
                    return;                        
                }

                $('#paymongoCardDetailModal div.alert').hide();
                $('#paymongoCardDetailModal').modal('show');
                populateFields();
            }

            function processPayment()
            {
                saveFieldValues();
                $('#paymongoCardDetailModal div.alert').hide();
                $('#paymongoCardDetailModal input[name=processBtn]').hide();
                $('#paymongoCardDetailModal div.spinner').show();

                // get clientKey                
                axios.get('paymongo/buy-reviewer/' + reviewerId)
                    .then(function(resp){
                        clientKey = resp.data;   
                        createPaymentMethod();            
                    }).catch(function (e) {
                        $('#paymongoCardDetailModal input[name=processBtn]').show();
                        $('#paymongoCardDetailModal div.spinner').hide();
                        displayError(e);
                    });
            }

            function createPaymentMethod()
            {
                // create paymentMethod
                axios.post(
                    'https://api.paymongo.com/v1/payment_methods',
                    {
                      data: {
                        attributes: {
                          type: 'card',
                          details: {
                            card_number: $('#paymongoCardDetailModal input[name=card_number]').val(),
                            exp_month: parseInt($('#paymongoCardDetailModal select[name=exp_month]').val()),
                            exp_year: parseInt($('#paymongoCardDetailModal select[name=exp_year]').val()),
                            cvc: $('#paymongoCardDetailModal input[name=cvc]').val(),
                          },
                          billing: {
                            address: {
                                line1: $('#paymongoCardDetailModal input[name=line1]').val(),
                                line2: $('#paymongoCardDetailModal input[name=line2]').val(),
                                city: $('#paymongoCardDetailModal input[name=city]').val(),
                                state: $('#paymongoCardDetailModal input[name=state]').val(),
                                postal_code: $('#paymongoCardDetailModal input[name=postal_code]').val(),
                                country: $('#paymongoCardDetailModal input[name=country_code]').val(),
                            },
                            name: $('#paymongoCardDetailModal input[name=name]').val(),
                            email: $('#paymongoCardDetailModal input[name=email]').val(),
                            phone: $('#paymongoCardDetailModal input[name=phone]').val(),
                          },
                          
                        }
                      }
                    },
                    {
                      headers: {Authorization: `Basic ${window.btoa('{{ env('PAYMONGO_PUBLIC_KEY') }}')}`}
                    }
                  ).then(function(resp) {
                    paymentMethodId = resp.data.data.id;
                    attachPaymentMethod();
                  }).catch(function (e) {
                    $('#paymongoCardDetailModal input[name=processBtn]').show();
                    $('#paymongoCardDetailModal div.spinner').hide();
                    displayError(e);
                  });  
            }

            function attachPaymentMethod()
            {                
                // Get the payment intent id from the client key
                paymentIntentId = clientKey.split('_client')[0];

                axios.post(
                    'https://api.paymongo.com/v1/payment_intents/' + paymentIntentId + '/attach',
                    {
                      data: {
                        attributes: {
                          client_key: clientKey,
                          payment_method: paymentMethodId
                        }
                      }
                    },
                    {
                      headers: {Authorization: `Basic ${window.btoa('{{ env('PAYMONGO_PUBLIC_KEY') }}')}`}
                    }
                  ).then(function(response) {
                    
                    $('#paymongoCardDetailModal input[name=processBtn]').show();
                    $('#paymongoCardDetailModal div.spinner').hide();
                    
                    var paymentIntent = response.data.data;
                    var paymentIntentStatus = paymentIntent.attributes.status;
                    
                    if (paymentIntentStatus === 'awaiting_next_action') {
                      // render your modal for 3D Secure Authentication since next_action has a value. 
                      // You can access the next action via paymentIntent.attributes.next_action.
                      $('#3DAuthModal iframe').attr('src', paymentIntent.attributes.next_action.redirect.url);
                      $('#3DAuthModal').modal('show');
                      $('#3DAuthModal').modal('handleUpdate');
                      $('#paymongoCardDetailModal').modal('hide');

                    } else if (paymentIntentStatus === 'succeeded') {
                      // You already received your customer's payment. You can show a success message from this condition.
                      confirmPayment();

                    } else if(paymentIntentStatus === 'awaiting_payment_method') {
                        // The PaymentIntent encountered a processing error. You can refer to paymentIntent.attributes.last_payment_error to check the error and render the appropriate error message.// The PaymentIntent encountered a processing error. 
                        // You can refer to paymentIntent.attributes.last_payment_error to check the error and render the appropriate error message.
                        displayError(paymentIntent.attributes.last_payment_error);                      
                    }

                  }).catch(function (e) {
                    $('#paymongoCardDetailModal input[name=processBtn]').show();
                    $('#paymongoCardDetailModal div.spinner').hide();
                    displayError(e);
                  });
            }

            function confirmPayment()
            {
                axios.get('/paymongo/confirm-payment/' + clientKey)
                .then(function(resp){
                    location.reload();
                }).catch(function(e){
                    location.reload();
                });
            }

            function displayError(e)
            {
                var msg = e;
                if ('string' != typeof e) {
                    if (undefined != e.response.data.errors) {
                        msg = '<ul>';
                        for(var x=0; x<e.response.data.errors.length; x++){
                            msg += `<li>${e.response.data.errors[x].detail}</li>`
                        }
                        msg += '</ul>';
                    }   
                }
             
                $('#paymongoCardDetailModal div.alert').show();
                $('#paymongoCardDetailModal div.alert').html(msg);
            }

                    
        </script>

        blade;
    }
}
