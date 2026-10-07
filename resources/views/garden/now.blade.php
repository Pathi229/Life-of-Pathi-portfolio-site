@extends('garden.layout',['title'=>'Now'])
@section('content')<section class="page-header"><p class="eyebrow">A SNAPSHOT, NOT A SCHEDULE</p><h1>What’s growing<br><em>right now.</em></h1><p>Updated {{ $settings?->now_date?->format('d F Y')??'soon' }}.</p></section><section class="section prose narrow">{!! \Illuminate\Support\Str::markdown($settings?->now_content??'A new season is taking shape.',['html_input'=>'strip','allow_unsafe_links'=>false]) !!}</section>
@endsection
