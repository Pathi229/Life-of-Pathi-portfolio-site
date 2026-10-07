@php
$roots=$branches->filter(fn($b)=>!$b->parent_id||!$branches->contains('id',$b->parent_id))->values();
$mobilePositions=[[33,27],[67,25],[31,49],[69,50],[41,39],[62,40],[37,64],[65,65]];
$positions=[[22,29],[77,26],[13,48],[86,49],[33,40],[66,40],[26,64],[74,65]];
$treePool=$treeEntries??collect();
@endphp
<div class="tree-layout" data-tree>
<div class="tree-scene" data-woodland-scene>
<div class="scene-depth scene-back" data-depth="0.12" aria-hidden="true">@include('garden.scenery')</div>
<div class="scene-light" data-depth="0.2" aria-hidden="true"></div>
<div class="scene-mist" data-depth="0.35" aria-hidden="true"></div>
<div class="tree-art" data-depth="0.5">
<img class="portfolio-tree" src="{{ asset('images/woodland/portfolio-tree-1536.webp') }}" srcset="{{ asset('images/woodland/portfolio-tree-768.webp') }} 768w, {{ asset('images/woodland/portfolio-tree-1536.webp') }} 1536w" sizes="(max-width: 700px) 115vw, 100vw" width="1536" height="1024" alt="An immense moss-covered tree with sculptural branches and a broad olive canopy." fetchpriority="high">
<svg class="branch-tracery" viewBox="0 0 1000 667" aria-hidden="true"><defs><filter id="branch-glow"><feGaussianBlur stdDeviation="3"/></filter></defs>@foreach($roots as $i=>$branch)@php [$x,$y]=$positions[$i%8]; @endphp<path data-branch-glow="{{ $branch->id }}" d="M500 565 C{{ $x<50?440:570 }} 420 {{ $x*10+($x<50?100:-100) }} {{ $y*6.67+60 }} {{ $x*10 }} {{ $y*6.67 }}"/>@endforeach</svg>
@foreach($roots as $i=>$branch)
@php [$x,$y]=$positions[$i%8]; $ids=$branch->subtreeIds(); $leaves=$treePool->filter(fn($e)=>in_array($e->primary_branch_id,$ids)||$e->branches->pluck('id')->intersect($ids)->isNotEmpty()); @endphp
<div class="tree-growth" data-tree-group="{{ intdiv($i,8) }}" @if($i>=8) hidden @endif>
<button type="button" class="tree-marker {{ $branch->activity==='dormant'?'is-dormant':'' }}" style="--node-x:{{ $x }}%;--node-y:{{ $y }}%;--mobile-x:{{ $mobilePositions[$i%8][0] }}%;--mobile-y:{{ $mobilePositions[$i%8][1] }}%" data-tree-select="{{ $branch->id }}" aria-expanded="false" aria-controls="branch-{{ $branch->id }}"><span class="node-light" aria-hidden="true"></span><span class="node-label">{{ $branch->name }}</span><span class="node-hint">{{ \Illuminate\Support\Str::limit($branch->description,90) }}<b>Open this branch →</b></span></button>
@foreach($leaves->where('type','project')->take(2)->values() as $j=>$leaf)<a class="project-fruit {{ $leaf->maturity==='fruit'?'is-fruit':'is-growing' }}" style="--node-x:{{ $x+($x<50?7:-7)+$j*3 }}%;--node-y:{{ $y+9+$j*5 }}%" href="{{ route('entry',$leaf->slug) }}" aria-label="{{ $leaf->title }} · {{ $leaf->maturity==='fruit'?'Completed outcome':'Growing project' }}"><span aria-hidden="true"></span><span class="fruit-hint">{{ $leaf->title }} ↗</span></a>@endforeach
</div>
@endforeach
</div>
<svg class="foreground-plants" data-depth="0.85" viewBox="0 0 1440 300" preserveAspectRatio="none" aria-hidden="true"><defs><g id="woodland-fern"><path d="M0 290 Q40 150 100 20" fill="none" stroke="currentColor" stroke-width="4"/>@foreach(range(0,8) as $i)<path d="M{{ 12+$i*9 }} {{ 250-$i*25 }} Q{{ -55+$i*10 }} {{ 190-$i*20 }} {{ -25+$i*10 }} {{ 165-$i*20 }} Q{{ 10+$i*9 }} {{ 180-$i*20 }} {{ 12+$i*9 }} {{ 250-$i*25 }} M{{ 12+$i*9 }} {{ 250-$i*25 }} Q{{ 105+$i*4 }} {{ 220-$i*25 }} {{ 130+$i*3 }} {{ 180-$i*20 }} Q{{ 55+$i*7 }} {{ 160-$i*20 }} {{ 12+$i*9 }} {{ 250-$i*25 }}"/>@endforeach</g></defs><g fill="currentColor"><use href="#woodland-fern" x="0"/><use href="#woodland-fern" x="120" transform="rotate(-20 140 290)"/><use href="#woodland-fern" transform="translate(1440 0) scale(-1 1)"/><use href="#woodland-fern" transform="translate(1270 50) scale(-.8 .8)"/></g></svg>
<div class="fireflies" aria-hidden="true">@foreach(range(1,5) as $i)<i style="--fly-x:{{ 10+$i*15 }}%;--fly-y:{{ 38+($i%3)*15 }}%;--fly-delay:{{ -$i*2 }}s"></i>@endforeach</div>
<div class="tree-scene-note"><span>ROOTED IN CURIOSITY</span><p>Touch a branch. Follow its story.</p></div>
</div>
@if($roots->count()>8)<div class="tree-pages" aria-label="Tree branch groups">@foreach($roots->chunk(8) as $group=>$chunk)<button type="button" data-tree-page="{{ $group }}" aria-pressed="{{ $group===0?'true':'false' }}">Branches {{ $group*8+1 }}–{{ $group*8+$chunk->count() }}</button>@endforeach</div>@endif
<div class="branch-preview" data-preview-shell hidden><div class="preview-heading"><p class="eyebrow">YOU FOUND A PATH</p><button type="button" data-close-branch aria-label="Close branch preview">Close ×</button></div>
@foreach($roots as $branch)@php $ids=$branch->subtreeIds();$leaves=$treePool->filter(fn($e)=>in_array($e->primary_branch_id,$ids)||$e->branches->pluck('id')->intersect($ids)->isNotEmpty()); @endphp
<section class="branch-detail" id="branch-{{ $branch->id }}" aria-label="{{ $branch->name }} preview" hidden><div><p class="eyebrow">{{ ucfirst($branch->activity) }} · {{ $leaves->count() }} things to discover</p><h2>{{ $branch->name }}</h2><p>{{ $branch->description }}</p><a class="button" href="{{ route('branch',$branch->slug) }}">Explore this branch →</a></div><div class="branch-paths">@foreach($branches->where('parent_id',$branch->id) as $child)<a href="{{ route('branch',$child->slug) }}">{{ $child->name }} <small>A smaller branch ↗</small></a>@endforeach @foreach($leaves->take(3) as $leaf)<a href="{{ route('entry',$leaf->slug) }}">{{ $leaf->title }} <small>{{ ucfirst($leaf->type) }} · {{ ucfirst($leaf->maturity) }} ↗</small></a>@endforeach @foreach($branch->channels as $channel)<a href="{{ route('channel',$channel->slug) }}">{{ $channel->name }} <small>Follow the channel ↗</small></a>@endforeach</div></section>
@endforeach
</div>
<details class="branch-index"><summary>Explore without the illustration <span>All {{ $branches->count() }} branches ↓</span></summary><div class="branch-controls">@forelse($branches as $branch)<a href="{{ route('branch',$branch->slug) }}"><span>{{ $branch->name }}</span><small>{{ ucfirst($branch->activity) }}{{ $branch->parent_id?' · Smaller branch':'' }}</small><p>{{ $branch->description }}</p></a>@empty<p>The first branches will appear here as they are published.</p>@endforelse</div><a class="text-link" href="{{ route('explore',array_merge(request()->query(),['view'=>'list'])) }}">Browse everything as a list →</a></details>
<noscript><p class="tree-noscript">Open “Explore without the illustration” above to follow every branch.</p></noscript>
</div>
