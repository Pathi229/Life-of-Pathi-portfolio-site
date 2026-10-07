@extends('garden.layout')
@section('content')<section class="page-header"><p class="eyebrow">LIFE OF PATHI / {{ strtoupper($title) }}</p><h1>{{ $title }}</h1><p>{{ request()->routeIs('work')?'A considered collection of projects, contributions and lessons.':'Follow your curiosity through work, stories and things in progress.' }}</p></section><section class="section browse"><form method="get" class="filters"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search the garden" aria-label="Search"><select name="branch" aria-label="Topic"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch')==$branch->id)>{{ $branch->name }}</option>
@endforeach
</select><select name="maturity" aria-label="Maturity"><option value="">All stages</option>@foreach(['seed','growing','fruit'] as $value)<option value="{{ $value }}" @selected(request('maturity')===$value)>{{ $value }}</option>
@endforeach
</select>@if(!request()->routeIs('work','blogs'))<select name="type" aria-label="Content type"><option value="">All content</option>@foreach(['project','article','video'] as $value)<option value="{{ $value }}" @selected(request('type')===$value)>{{ $value }}</option>
@endforeach
</select>
@endif
<select name="channel" aria-label="Channel"><option value="">All channels</option>@foreach($channels as $channel)<option value="{{ $channel->id }}" @selected(request('channel')==$channel->id)>{{ $channel->name }}</option>
@endforeach
</select><select name="category" aria-label="Collection or work category"><option value="">All collections</option>@foreach(['technology','creative','tutorial','story','reflection','update'] as $value)<option value="{{ $value }}" @selected(request('category')===$value)>{{ ucfirst($value) }}</option>
@endforeach
</select><select name="difficulty" aria-label="Difficulty"><option value="">Any difficulty</option>@foreach(['beginner','intermediate','advanced'] as $value)<option value="{{ $value }}" @selected(request('difficulty')===$value)>{{ $value }}</option>
@endforeach
</select><input name="technique" value="{{ request('technique') }}" placeholder="Technique" aria-label="Technique"><input type="hidden" name="view" value="{{ request('view','list') }}"><button class="button">Find content →</button><a href="{{ url()->current() }}">Reset filters</a></form><div class="browse-toolbar"><span>{{ $entries->total() }} things to discover</span><div><a @class(['active'=>request('view')==='tree']) href="{{ request()->fullUrlWithQuery(['view'=>'tree']) }}">Tree</a><a @class(['active'=>request('view','list')==='list']) href="{{ request()->fullUrlWithQuery(['view'=>'list']) }}">List</a></div></div>@if(request('view')==='tree')@include('garden.tree')<h2>Matching content</h2>
@endif
<div class="card-grid">@forelse($entries as $item)@include('garden.card')@empty<div class="empty"><h2>Nothing on this path yet.</h2><p>Try another branch or clear a filter.</p><a href="{{ url()->current() }}">Reset filters →</a></div>
@endforelse
</div>{{ $entries->links() }}@if(request()->routeIs('work')&&$settings?->cv_media_id)<a class="text-link" href="{{ route('media',$settings->cv_media_id) }}">Download CV ↓</a>
@endif
</section>
@endsection
