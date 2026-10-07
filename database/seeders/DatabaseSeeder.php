<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Channel;
use App\Models\Entry;
use App\Models\Series;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [];
        foreach ([
            ['building-technology', 'Building & Technology', 'Useful tools, thoughtful systems, and learning by building.', 'active', '⌘'],
            ['creative-work', 'Creative Work', 'Visual ideas and hospitality experiences, made with care.', 'active', '◒'],
            ['places-experiences', 'Places & Experiences', 'Small observations from places visited and moments lived.', 'dormant', '◎'],
            ['making-practice', 'Making & Practice', 'The quiet joy of making things with your hands.', 'active', '✳'],
            ['learning-reflections', 'Learning & Reflections', 'Notes on what worked, what did not, and what comes next.', 'new', '✧'],
        ] as $i => [$slug,$name,$description,$activity,$icon]) {
            $branches[$slug] = Branch::withTrashed()->firstOrCreate(['demo_key' => $slug], ['slug' => $slug] + compact('name', 'description', 'activity', 'icon') + ['sort_order' => $i]);
        }
        $crochet = Branch::withTrashed()->firstOrCreate(['demo_key' => 'crochet'], ['slug' => 'crochet', 'name' => 'Crochet', 'description' => 'One stitch at a time. Projects, techniques and beginner-friendly guides.', 'parent_id' => $branches['making-practice']->id, 'activity' => 'active']);
        $life = Channel::withTrashed()->firstOrCreate(['demo_key' => 'life-of-pathi'], ['slug' => 'life-of-pathi', 'name' => 'Life of Pathi', 'introduction' => 'Stories, experiments, places and progress. A life in field notes.']);
        $guide = Channel::withTrashed()->firstOrCreate(['demo_key' => 'crochet-guide'], ['slug' => 'crochet-guide', 'name' => 'Crochet Guide', 'introduction' => 'A gentle place to begin. Learn a stitch, follow a project, make something yours.', 'accent' => '#a67b58']);
        if ($life->wasRecentlyCreated) {
            $life->branches()->syncWithoutDetaching(collect($branches)->pluck('id')->all());
        }
        if ($guide->wasRecentlyCreated) {
            $guide->branches()->syncWithoutDetaching([$crochet->id, $branches['making-practice']->id]);
        }
        $make = function ($slug, $title, $type, $branch, $channel, $extra = []) {
            $e = Entry::withTrashed()->firstOrCreate(['demo_key' => $slug], array_merge(['slug' => $slug, 'title' => $title, 'type' => $type, 'primary_branch_id' => $branch->id, 'publication' => 'published', 'visibility' => 'public', 'maturity' => 'growing', 'summary' => 'Demonstration content — an example to explore and replace with Pathi’s own work.', 'body' => "## Demonstration content\nThis entry illustrates the publishing workflow. It does not represent a client commission or a claimed achievement.\n\n## What I’m exploring\nA small, practical experiment, documented as it grows.\n\n## Lessons and next steps\nKeep the process visible. Replace this example with your own story.", 'published_at' => now(), 'meaningful_updated_at' => now()], $extra));
            if ($e->wasRecentlyCreated) {
                $e->branches()->syncWithoutDetaching([$branch->id]);
            }
            if ($e->wasRecentlyCreated) {
                $e->channels()->syncWithoutDetaching([$channel->id]);
            }

            return $e;
        };
        $bag = $make('everyday-crochet-bag', 'An everyday crochet bag', 'project', $crochet, $guide, ['category' => 'tutorial', 'difficulty' => 'beginner', 'technique' => 'single crochet', 'featured' => true, 'summary' => 'Demo project · From a ball of yarn to something you can carry. A beginner’s journey, one stitch at a time.', 'role' => 'Demonstration: pattern exploration and written tutorial']);
        $series = Series::withTrashed()->firstOrCreate(['demo_key' => 'crochet-bag-start-to-finish'], ['slug' => 'crochet-bag-start-to-finish', 'name' => 'Crochet bag: start to finish', 'description' => 'An ordered demonstration journey from materials to the finished result.']);
        foreach ([
            ['materials', 'Gather your materials', "## Before you begin\nThis is a demonstration guide. Choose a medium-weight yarn, a matching hook and a tapestry needle.\n\n## A small checklist\n- Yarn and hook\n- Scissors\n- Tapestry needle\n\n> Make a small swatch first. It helps you find a comfortable tension."],
            ['main-body', 'Making the main body', "## Finding a rhythm\nDemonstration instructions: practise a small rectangle in single crochet before beginning a bag.\n\n## Keep track\nCount stitches at the end of each row. Note your yarn, hook and sample size."],
            ['assembly', 'Bringing the pieces together', "## Lay it out\nDemonstration assembly notes. Lay the sample flat and check the edges before joining.\n\n## Join with care\nUse a tapestry needle and practise an even seam on a spare swatch."],
            ['finished-result', 'The finished result', "## A first finish\nDemonstration outcome. This record shows how a finished entry appears; no finished physical bag is claimed.\n\n## What comes next\nReplace these notes with your own photographs, pattern details and lessons."],
        ] as $i => [$slug,$title,$body]) {
            $make('crochet-bag-'.$slug, $title, 'article', $crochet, $guide, ['body' => $body, 'project_id' => $bag->id, 'series_id' => $series->id, 'sequence' => $i + 1, 'category' => 'tutorial', 'difficulty' => 'beginner', 'technique' => 'single crochet', 'maturity' => $i === 3 ? 'fruit' : 'growing']);
        }
        $tech = $make('a-small-digital-garden', 'A small digital garden', 'project', $branches['building-technology'], $life, ['category' => 'technology', 'portfolio' => true, 'featured' => true, 'sort_order' => 1, 'role' => 'Concept demonstration: application structure and interface design', 'summary' => 'Concept demo · Making room for connected stories, projects and learning.', 'case_study' => ['context' => 'A concept for a personal publishing space. This is demonstration content.', 'contribution' => 'Example: content modelling and interface exploration.', 'process' => 'A single relational application with a tree as an additional way to browse.', 'deliverables' => 'Example project hub and connected reading journey.', 'outcome' => 'No business metrics or client results are claimed.', 'lessons' => 'Show the work and its limitations clearly.', 'credits' => 'Demonstration content authored for this local application.']]);
        $creative = $make('a-table-worth-gathering-around', 'A table worth gathering around', 'project', $branches['creative-work'], $life, ['category' => 'creative', 'portfolio' => true, 'featured' => true, 'maturity' => 'fruit', 'sort_order' => 2, 'role' => 'Concept demonstration: visual direction and experience planning', 'summary' => 'Concept demo · An exploration of atmosphere, thoughtful details and welcoming spaces.', 'case_study' => ['context' => 'A hospitality concept, not a commissioned project.', 'contribution' => 'Example: visual references and experience planning.', 'process' => 'Explore a calm palette and intentional details.', 'deliverables' => 'A demonstration case-study structure.', 'outcome' => 'No event, client or commercial achievement is claimed.', 'lessons' => 'Replace with evidence and credits from your actual work.']]);
        $make('notes-on-learning-by-making', 'Notes on learning by making', 'article', $branches['learning-reflections'], $life, ['category' => 'reflection', 'summary' => 'Demo reflection · The small things you learn when you give yourself permission to begin.']);
        $article = Entry::withTrashed()->where('demo_key', 'crochet-bag-main-body')->first();
        $make('crochet-video-example', 'A stitch in motion', 'video', $crochet, $guide, ['project_id' => $bag->id, 'article_id' => $article->id, 'platform' => 'youtube', 'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'summary' => 'Video-block demonstration · Links to the Blender Foundation’s Big Buck Bunny, not a crochet lesson or a Pathi video.', 'body' => "## Video demonstration\nThis unrelated public film demonstrates a safe external video link. Replace it with your own tutorial URL. No social account is connected."]);
        $make('digital-garden-video-demo', 'A video alongside the project', 'video', $branches['building-technology'], $life, ['project_id' => $tech->id, 'platform' => 'youtube', 'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'summary' => 'Demo video record · An unrelated sample film demonstrates linked video presentation.', 'body' => "## External sample\nThe Blender Foundation’s Big Buck Bunny is an external sample film, not Pathi’s work. Replace this URL with your own project walkthrough."]);
        foreach (['draft' => 'draft', 'unlisted' => 'published', 'private' => 'published'] as $visibility => $publication) {
            $make('demo-'.$visibility, 'Demo '.$visibility.' entry', 'article', $branches['learning-reflections'], $life, ['visibility' => $visibility === 'draft' ? 'private' : $visibility, 'publication' => $publication, 'body' => 'This record demonstrates visibility rules.']);
        }
        Setting::firstOrCreate(['id' => 1], ['introduction' => 'A little corner of the internet for the things I build, the places I go, and everything I’m learning along the way.', 'availability' => 'Contact details have not been added yet. This local site contains clearly labelled demonstration content.', 'now_content' => "## A new garden\nDemonstration snapshot: exploring a connected home for projects, stories and practical guides.\n\n## One stitch at a time\nThe crochet example shows how a project can grow into a reading journey. Replace this snapshot with your actual current focus.", 'now_date' => now()->toDateString(), 'pathway_id' => $bag->id]);
    }
}
