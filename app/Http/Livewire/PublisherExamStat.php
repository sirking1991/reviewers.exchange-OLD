<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherExamStat extends Component
{
    public $stats;
    public function render()
    {
        $sql = "
            select r.name, count(er.id) exams, sum(er.questions) questions, sum(er.correct_answers) correct_answers
            from exam_results er, reviewers r
            where r.id=er.reviewer_id
            and r.user_id=" . Auth()->user()->id ."
            group by r.id
            order by count(er.id) desc     
        ";
        $this->stats = DB::select($sql);        
        return <<<'blade'
        <div class="card" style='margin-top:10px;'>
            <div class="card-header">
                Practice exam statistics
            </div>
            <div class="card-body">
                @foreach($stats as $s)
                <div class="alert alert-light" role="alert"">
                    {{ $s->name }}
                    <br/>
                    <div class="text-muted">
                        <span>{{ $s->exams }} practice exam taken. Average score is {{ number_format(($s->correct_answers / $s->questions) * 100,2) }}%</span>
                    </div>
                </div>
                @endforeach                
            </div>
        </div>
        blade;
    }
}
