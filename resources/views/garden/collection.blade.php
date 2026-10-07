@extends('garden.layout')
@section('content')<section class="page-header" @isset($collection) style="border-top:3px solid {{ preg_match('/^#[0-9a-f]{6}$/i',$collection->accent)?$collection->accent:'#3e6753' }}" @endisset><p class="eyebrow">A PATH THROUGH THE GARDEN @isset($status) · {{ strtoupper($status) }}
@endisset
</p><h1>{{ $title }}</h1><p>{{ $intro }}</p>@isset($collection)@if($collection->cover)<img class="collection-cover" src="{{ route('media',$collection->cover->id) }}" alt="{{ $collection->cover->alt }}">
@endif
<a class="text-link" href="{{ route('explore',['channel'=>$collection->id]) }}">Search and filter this collection →</a>@foreach($collection->social_links??[] as $label=>$url)@if(str_starts_with($url,'https://'))<a class="text-link" href="{{ $url }}" rel="noopener noreferrer">{{ $label }} ↗</a>
@endif

@endforeach

@endisset
</section><section class="section"><div class="collection-links">@foreach($children as $branch)<a href="{{ route('branch',$branch->slug) }}">{{ $branch->name }} →</a>
@endforeach
 @foreach($channels as $channel)<a href="{{ route('channel',$channel->slug) }}">{{ $channel->name }} ↗</a>
@endforeach
</div>@isset($series)
@if($series->isNotEmpty())<div class="reading-journeys"><p class="eyebrow">READING JOURNEYS</p>@foreach($series as $journey)<a class="text-link" href="{{ route('series',$journey->slug) }}">{{ $journey->name }} →</a>
@endforeach
</div>
@endif
@endisset
<div class="card-grid">@forelse($entries as $item)@include('garden.card')@empty<p>This branch is waiting for its next story.</p>
@endforelse
</div>@if(method_exists($entries,'links')){{ $entries->links() }}
@endif
</section>
@endsection
