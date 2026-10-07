@php $settings=\App\Models\Setting::first(); $pageTitle=$title??($entry->seo_title??$entry->title??'Still growing.'); $description=$entry->seo_description??$entry->summary??$settings?->introduction??'Work, learning and life, connected in a growing digital garden.';
@endphp

<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $pageTitle }} · {{ $settings?->name??'Life of Pathi' }}</title><meta name="description" content="{{ $description }}"><link rel="canonical" href="{{ url()->current() }}"><meta property="og:title" content="{{ $pageTitle }}"><meta property="og:description" content="{{ $description }}"><meta property="og:type" content="{{ isset($entry)&&$entry->type==='article'?'article':'website' }}"><meta property="og:url" content="{{ url()->current() }}"><link rel="stylesheet" href="{{ asset('css/sinhala-font.css') }}">@vite(['resources/css/app.css','resources/js/app.js'])</head><body @class(['woodland-home'=>request()->routeIs('home'),'reading-page'=>isset($entry),'work-page'=>request()->routeIs('work'),'blogs-page'=>request()->routeIs('blogs')])>
<a class="skip" href="#main">Skip to content</a>
<div class="site-forest" aria-hidden="true">@include('garden.scenery')</div>
<header class="header">
<a class="brand" href="{{ route('home') }}">{{ $settings?->name??'Life of Pathi' }}</a>
<span class="header-note">{{ $settings?->tagline??'Still growing.' }}</span>
<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-navigation">Explore the woodland <span aria-hidden="true">☰</span></button>
<nav id="main-navigation" aria-label="Main navigation">@foreach(['explore'=>'Explore','work'=>'Work','blogs'=>'Blogs & Guides','channels'=>'Channels','now'=>'Now','contact'=>'Contact'] as $route=>$label)<a @class(['current'=>request()->routeIs($route)]) @if(request()->routeIs($route)) aria-current="page" @endif href="{{ route($route) }}">{{ $label }}</a>@endforeach</nav>
</header>
<main id="main">@yield('content')</main><footer><a class="brand" href="/">✳ {{ $settings?->name??'Life of Pathi' }}</a><p>A collection of work, practice, and things along the way.</p><span>By Pathi · Still growing.</span><a href="{{ route('contact') }}">Let’s connect ↗</a></footer>
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>isset($entry)&&$entry->type==='article'?'Article':'WebSite','name'=>$pageTitle,'url'=>url()->current(),'author'=>['@type'=>'Person','name'=>'Pathi']], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script></body></html>
