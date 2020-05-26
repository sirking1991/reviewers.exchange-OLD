<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\User;
use Illuminate\Support\Facades\Auth;

class PublisherSettings extends Component
{
    public $displayName;
    public $depositoryBank;
    public $accountNmbr;
    public $accountName;

    public function mount()
    {
        $this->displayName = Auth()->user()->display_name;
        $this->depositoryBank = Auth()->user()->depository_bank;
        $this->accountNmbr = Auth()->user()->account_nmbr;
        $this->accountName = Auth()->user()->account_name;
    }

    public function saveSettings()
    {
        $user = User::find(Auth()->user()->id);
        $user->display_name = $this->displayName ?? '';
        $user->depository_bank = $this->depositoryBank ?? '';
        $user->account_nmbr = $this->accountNmbr ?? '';
        $user->account_name = $this->accountName ?? '';
        $user->save();

        session()->flash('status', 'Settings saved');
    }

    public function render()
    {
        return <<<'blade'
            <div>
                @include('layouts.alert')                      

                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        Settings
                        <button class="btn btn-sm btn-primary float-right" wire:click="saveSettings">Save</button>
                    </div>
                    <div class='card-body'>                    
                        <div class='row'>
                            <div class='col-md'>
                                <div class="form-group">
                                    <label for="displayName">Display name</label>
                                    <input type="text" class="form-control" id="displayName" aria-describedby="displayNameHelp" placeholder="eg. The Great Publisher" wire:model.lazy='displayName'>
                                    <small id="displayNameHelp" class="form-text text-muted">This will identify the publisher of the reviewers.</small>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-md-4'>
                                <div class="form-group">
                                    <label for="depositoryBank">Bank</label>
                                    <select class='form-control' wire:model='depositoryBank'>
                                        <option value='bdo'>Banco de Oro</option>
                                        <option value='bpi'>Bank of Philippine Islands</option>                                        
                                        <option value='eastwest'>Eastwest Bank</option>
                                        <option value='metrobank'>Metrobank</option>                                        
                                    </select>
                                    <small id="depositoryBankHelp" class="form-text text-muted">This is where your fund will be deposited.</small>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class="form-group">
                                    <label for="accountNmbr">Account #</label>
                                    <input type="text" class="form-control" id="accountNmbr" aria-describedby="accountNmbrHelp" placeholder="eg. 1234567890" wire:model.lazy='accountNmbr'>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class="form-group">
                                    <label for="accountName">Account name</label>
                                    <input type="text" class="form-control" id="accountName" aria-describedby="accountNameHelp" placeholder="eg. Juan Dela Cruz" wire:model.lazy='accountName'>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        blade;
    }
}
