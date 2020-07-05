@extends('layout')

@section('title', __('actions.create') . ' Budget')

@section('body')
    <div class="wrapper my-3">
        <h2>{{ __('actions.create')}} Budget</h2>
        <div class="box mt-3">
            <div class="box__section">
                @if (session()->has('message'))
                    <div class="mb-2">{{ session('message') }}</div>
                @endif
                <form method="POST" action="/budgets">
                    {{ csrf_field() }}
                    <div class="input input--small">
                        <label>Tag</label>
                        <select name="tag_id">
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        @include('partials.validation_error', ['payload' => 'tag_id'])
                    </div>
                    <div class="input input--small">
                        <label>Period</label>
                        <select name="period">
                            <option value="yearly">Yearly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="biweekly">Bi-weekly</option>
                            <option value="weekly">Weekly</option>
                            <option value="daily">Daily</option>
                        </select>
                        @include('partials.validation_error', ['payload' => 'period'])
                    </div>
                    <div class="input input--small">
                        <label>Amount</label>
                        <input type="text" name="amount" />
                        @include('partials.validation_error', ['payload' => 'amount'])
                    </div>
                    <button>Create</button>
                </form>
            </div>
        </div>
    </div>
@endsection
