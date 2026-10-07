@extends('garden.layout')
@section('content')<section class="page-header article-header">@if($preview)<p class="preview-banner">Private preview · expires after 20 minutes. Only administrators can open this link.</p>
@endif
<p class="eyebrow">{{ strtoupper($entry->type) }} · {{ strtoupper($entry->maturity) }}</p><h1>{{ $entry->title }}</h1><p>{{ $entry->summary }}</p><div class="entry-meta">By Pathi · {{ $entry->published_at?->format('d M Y')??'Draft' }} @if($entry->meaningful_updated_at) · Updated {{ $entry->meaningful_updated_at->format('d M Y') }}
@endif
</div><div class="collection-links">@if($entry->primaryBranch&&!$entry->primaryBranch->archived)<a href="{{ route('branch',$entry->primaryBranch->slug) }}">{{ $entry->primaryBranch->name }} ↗</a>
@endif
 @foreach($entry->channels as $channel)<a href="{{ route('channel',$channel->slug) }}">{{ $channel->name }} ↗</a>
@endforeach
 @if($entry->project?->publiclyAccessible())<a href="{{ route('entry',$entry->project->slug) }}">Back to {{ $entry->project->title }} →</a>
@endif
</div></section><div class="reading-layout"><aside><p class="eyebrow">ON THIS PAGE</p><nav id="contents" aria-label="Article contents"></nav>@if($entry->role)<p class="eyebrow">MY CONTRIBUTION</p><p>{{ $entry->role }}</p>
@endif
<a href="{{ route('explore') }}" data-back-to-explore>← Back to explore</a></aside><article class="prose" id="article-body">{!! \Illuminate\Support\Str::markdown($entry->body??'',['html_input'=>'strip','allow_unsafe_links'=>false]) !!}@foreach($entry->media as $image)<figure>@if(in_array(pathinfo($image->path,PATHINFO_EXTENSION),['jpg','jpeg','png','webp']))<img loading="lazy" src="{{ route('media',['media'=>$image->id,'entry'=>$entry->id]) }}" alt="{{ $image->alt }}"><figcaption>{{ $image->caption }} @if($image->credit) · {{ $image->credit }}
@endif
</figcaption>@else<a href="{{ route('media',['media'=>$image->id,'entry'=>$entry->id]) }}">Download {{ $image->name }} ↓</a>
@endif
</figure>
@endforeach
 @if($entry->video_url)<div class="video-context"><p class="eyebrow">WATCH THE VIDEO</p><h2>{{ $entry->title }}</h2><p>The written guide is here whenever you need it.</p><a class="button" href="{{ $entry->video_url }}" target="_blank" rel="noopener noreferrer">Watch on {{ ucfirst($entry->platform??'the platform') }} ↗</a></div>
@endif
 @if($entry->portfolio)@foreach($entry->case_study??[] as $label=>$text)<h2>{{ ucfirst($label) }}</h2><p>{{ $text }}</p>
@endforeach

@endif
 @if($entry->type==='project')<h2>Follow the project</h2><ol class="journey">@foreach($parts as $part)<li><a href="{{ route('entry',$part->slug) }}">{{ $part->title }} <span>{{ ucfirst($part->type) }} →</span></a></li>
@endforeach
</ol>
@endif
@if($videos->isNotEmpty())<h2>Related videos</h2>@foreach($videos as $video)<p><a href="{{ route('entry',$video->slug) }}">{{ $video->title }} ↗</a></p>
@endforeach

@endif
 @if($related->isNotEmpty())<h2>Connected projects</h2>@foreach($related as $project)<p><a href="{{ route('entry',$project->slug) }}">{{ $project->title }} ↗</a></p>
@endforeach

@endif
<div class="series-navigation">@if($previous)<a href="{{ route('entry',$previous->slug) }}">← Previous<br>{{ $previous->title }}</a>
@endif
 @if($next)<a href="{{ route('entry',$next->slug) }}">Next →<br>{{ $next->title }}</a>
@endif
</div>@if($entry->series_id)<a href="{{ route('series',$entry->series->slug) }}">View series: {{ $entry->series->name }} →</a>
@endif
</article></div>
@endsection
