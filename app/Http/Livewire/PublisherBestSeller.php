<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherBestSeller extends Component
{
    public $bestSellers;
    public function render()
    {
        $sql = "
            select r.name, count(rp.id) as sold
            from reviewer_purchases rp, reviewers r
            where r.id=rp.reviewer_id
            and r.user_id=" . Auth()->user()->id ."
            group by r.name
            order by count(rp.id) desc
            limit 10     
        ";
        $this->bestSellers = DB::select($sql);
        return <<<'blade'
        <div class="card" style='margin-top:10px;'>
            <div class="card-header">
                Best seller
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($bestSellers as $bs)
                    <li class="list-group-item">
                        {{ $bs->name }}
                        <div class="float-right">{{ $bs->sold }} {{ 1 < $bs->sold ? 'copies' : 'copy' }} sold</div>
                    </li>   
                    @endforeach   
                </ul>

            </div>
        </div>
        blade;
    }
}
