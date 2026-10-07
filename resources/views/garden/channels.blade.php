@extends('garden.layout',['title'=>'Channels'])
@section('content')<section class="page-header"><p class="eyebrow">DIFFERENT PATHS, SHARED CURIOSITY</p><h1>Channels</h1><p>Collections with their own focus. All part of the same growing story.</p></section><section class="section"><div class="channel-grid">@foreach($channels as $channel)<a class="channel-card" style="--collection-accent: {{ preg_match('/^#[0-9a-f]{6}$/i',$channel->accent)?$channel->accent:'#3e6753' }}" href="{{ route('channel',$channel->slug) }}">@if($channel->cover)<img class="channel-cover" loading="lazy" src="{{ route('media',$channel->cover->id) }}" alt="{{ $channel->cover->alt }}">@else<span class="channel-symbol">✳</span>
@endif
<p class="eyebrow">{{ $channel->entries_count }} PUBLISHED ENTRIES</p><h2>{{ $channel->name }}</h2><p>{{ $channel->introduction }}</p><span class="text-link">Explore the collection ↗</span></a>
@endforeach
</div></section>
@endsection
