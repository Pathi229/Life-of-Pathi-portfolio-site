@extends('garden.layout')
@section('content')<section class="hero"><div class="hero-copy"><p class="eyebrow"><span class="tiny-leaf">✳</span> WORK · LEARNING · LIFE</p><h1>Still<br><em>growing.</em><span class="hero-dot">✳</span></h1><p class="hero-intro">{{ $settings?->introduction??'A little corner of the internet for the things I build, the places I go, and everything I’m learning along the way.' }}</p><div class="actions"><a class="button" href="#garden">Explore the tree <span>↓</span></a><a class="text-link" href="{{ route('work') }}">View selected work ↗</a></div><div class="hero-foot"><span class="status-dot"></span> A living collection. Always a work in progress.</div></div><div class="hero-illustration"><span class="journal-stamp">LIFE OF PATHI<br><b>FIELD NOTES / 01</b></span><svg viewBox="0 0 400 440" aria-hidden="true"><g fill="none" stroke="#4d6c4f" stroke-linecap="round"><path d="M191 414 C208 352 182 280 208 227 C236 168 213 119 234 52" stroke-width="4"/><path d="M204 338 Q144 278 94 255 M202 292 Q266 271 309 203 M215 217 Q167 181 139 135 M228 167 Q281 135 305 85 M210 252 Q249 230 266 169" stroke-width="3"/></g>@foreach([[94,255,-20],[121,270,35],[139,135,60],[164,159,35],[234,52,-65],[229,96,-30],[305,85,-35],[282,126,-45],[266,169,-65],[309,203,-35],[283,243,-40],[206,370,-70]] as [$x,$y,$a])<ellipse cx="{{ $x }}" cy="{{ $y }}" rx="32" ry="12" fill="#738669" opacity=".8" transform="rotate({{ $a }} {{ $x }} {{ $y }})"/>
@endforeach
<circle cx="221" cy="274" r="10" fill="#bb8258"/><circle cx="184" cy="217" r="8" fill="#bb8258"/><path d="M120 417 Q195 399 276 418" stroke="#b8baa7" fill="none"/></svg><div class="handwritten">Room to become.</div><span class="illustration-index">NOT A STRAIGHT LINE.<br>NEVER STOPPED GROWING.</span></div></section>
<section id="garden" class="section garden-section"><div class="section-heading"><div><p class="eyebrow">THE GARDEN</p><h2>Many interests.<br>One growing story.</h2></div><p>Some branches are new. Some are bearing fruit.<br>They all belong here.</p></div>@include('garden.tree')</section>
<section class="section"><div class="section-heading"><div><p class="eyebrow">THINGS BROUGHT TO LIFE</p><h2>Selected work</h2></div><a class="text-link" href="{{ route('work') }}">All selected work ↗</a></div><div class="card-grid">@forelse($selected as $item)@include('garden.card')@empty<p>Selected work will appear here as it’s published.</p>
@endforelse
</div></section>
<section class="section growing-section"><div class="section-heading"><div><p class="eyebrow">IN THE MAKING</p><h2>Currently growing</h2></div><a href="{{ route('now') }}" class="text-link">What I’m doing now ↗</a></div><div class="card-grid">@foreach($growing as $item)@include('garden.card')
@endforeach
</div></section>
<section class="section"><div class="section-heading"><div><p class="eyebrow">RECENT FIELD NOTES</p><h2>Fresh from the garden</h2></div><a class="text-link" href="{{ route('blogs') }}">Blogs & Guides ↗</a></div><div class="notes-list">@foreach($recent as $item)<a href="{{ route('entry',$item->slug) }}"><span>{{ $item->meaningful_updated_at?->format('d M Y') }}</span><h3>{{ $item->title }}</h3><small>{{ ucfirst($item->type) }}</small><b>↗</b></a>
@endforeach
</div></section>
@if($pathway=\App\Models\Entry::discoverable()->find($settings?->pathway_id))<section class="pathway"><p class="eyebrow">A PATH TO FOLLOW</p><h2>{{ $pathway->title }}</h2><p>{{ $pathway->summary }}</p><a class="button" href="{{ route('entry',$pathway->slug) }}">Start the journey →</a></section>
@endif

<section class="contact-banner"><p class="eyebrow">GOOD THINGS START WITH A CONVERSATION</p><h2>Have something in mind?</h2><a class="text-link" href="{{ route('contact') }}">Let’s make it grow ↗</a></section>
@endsection
