
@section('content')
    <div class="container">
        <h1>{{ __('Welcome to Our Application :name', ['name' => 'Casper']) }}</h1>
        <p>{{ __('This is a demo page to showcase translations and Blade components.') }}</p>

        @if(session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @else
            <div class="alert alert-warning" role="alert">
                @lang('1You are currently not logged in.')
                @lang('2You are currently not logged in.')
            </div>
        @endif

        @lang('3You are currently not logged in.')

        <div class="buttons">
            <button type="primary">
                {{ __('Click Me') }}
            </button>
            <button type="secondary">
                {{ __('Another Button') }}
            </button>
            <button type="secondary">
                {{ trans_choice('countable', 2) }}
            </button>
            <button type="secondary">
                @choice('count', 2)
            </button>
        </div>

        <div class="content">
            @for($i = 0; $i < 5; $i++)
                <div>{{ __('Item #:count', ['count' => $i + 1]) }}</div>
            @endfor
        </div>

        @switch($user->status)
            @case('active')
                <div class="user-status">@lang('Active User')</div>
                @break

            @case('inactive')
                <div class="user-status">{{ __('Inactive User') }}</div>
                @break

            @default
                <div class="user-status">{{ __('Unknown Status') }}</div>
        @endswitch
    </div>
@endsection
