@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <strong>What is Reviewers.Exchange?</strong><br/><br/>
            <div class="row">
                <div class="col-md-6">
                    For students & reviewers:
                    <ul>
                        <li>Reviewers & students can choose from hundreds of learning materials</li>
                        <li>A sophisticated practice exam generator ensures that questionnaires are randomly selected and answers are displayed randomly</li>
                        <li>Track your progress of understanding a particular subject</li>
                        <li>Take practice exams with your friends, wherever they are</li>
                        <li>Earn stars for each correct answer and use it as a badge of honor</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    For authors & publishers
                    <ul>
                        <li>Reach more reviewers & students</li>
                        <li>Lowers your publication cost</li>
                        <li>Lessen the risk of piracy to your copyrighted materials</li>
                        <li>Track the details of your sales</li>
                        <li>Easily withdraw funds</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row">
                             <div class="col-md-12">
                                 <div class="form-group">
                                     <label for="email" class=" col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                                     <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                     @error('email')
                                         <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                     @enderror
                                 </div>
                             </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="password" class="col-form-label text-md-right">{{ __('Password') }}</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
<!--
                        <div class="row">
                            <div class="col-md-12">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>

                            </div>
                        </div>
-->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-block">
                                    {{ __('Login') }}
                                </button>
                                <br/>
                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
