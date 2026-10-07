@extends('garden.layout',['title'=>'Contact'])
@section('content')<section class="page-header"><p class="eyebrow">WORK · COLLABORATION · A HELLO</p><h1>Let’s make<br><em>something grow.</em></h1><p>{{ $settings?->availability??'Contact details will appear here when Pathi adds them.' }}</p>@if($settings?->contact_email)<a class="button" href="mailto:{{ $settings->contact_email }}">{{ $settings->contact_email }} ↗</a>
@endif
</section><section class="section collection-links">@foreach($settings?->social_links??[] as $label=>$url)@if(str_starts_with($url,'https://'))<a href="{{ $url }}" rel="noopener noreferrer">{{ $label }} ↗</a>
@endif

@endforeach
</section>
@endsection
