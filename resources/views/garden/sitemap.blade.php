{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">@foreach(['home','explore','work','blogs','channels','now','contact'] as $page)<url><loc>{{ route($page) }}</loc></url>
@endforeach
 @foreach($entries as $entry)<url><loc>{{ route('entry',$entry->slug) }}</loc><lastmod>{{ ($entry->meaningful_updated_at??$entry->updated_at)->toAtomString() }}</lastmod></url>
@endforeach
 @foreach($branches as $branch)<url><loc>{{ route('branch',$branch->slug) }}</loc></url>
@endforeach
 @foreach($channels as $channel)<url><loc>{{ route('channel',$channel->slug) }}</loc></url>
@endforeach
</urlset>
