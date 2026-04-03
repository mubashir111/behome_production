<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaticPageController extends Controller
{
    public function index()
    {
        $pages = StaticPage::orderBy('slug')->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:static_pages,slug|regex:/^[a-z0-9\-]+$/',
            'content'          => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active'        => 'nullable|boolean',
        ]);

        StaticPage::create([
            'title'            => $data['title'],
            'slug'             => $data['slug'],
            'content'          => $data['content'] ?? null,
            'sections'         => [],
            'meta_title'       => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_active'        => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function destroy(StaticPage $page)
    {
        if ($page->is_system) {
            return back()->with('error', 'System pages cannot be deleted.');
        }
        $page->delete();
        return back()->with('success', 'Page deleted.');
    }

    public function edit(StaticPage $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, StaticPage $page)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'content'             => 'nullable|string',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string|max:500',
            'is_active'           => 'nullable|boolean',
            'team_image_file.*'   => 'nullable|image|max:3072',
        ]);

        // Build sections JSON from request depending on page slug
        $sections = $this->buildSections($request, $page->slug);

        $page->update([
            'title'            => $data['title'],
            'content'          => $data['content'] ?? null,
            'sections'         => $sections,
            'meta_title'       => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_active'        => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page updated successfully.');
    }

    private function buildSections(Request $request, string $slug): array
    {
        if ($slug === 'about') {
            $sections = [];

            $sections['hero'] = [
                'title'       => $request->input('hero_title', ''),
                'subtitle'    => $request->input('hero_subtitle', ''),
                'description' => $request->input('hero_description', ''),
            ];

            // Features
            $featNumbers = $request->input('feature_number', []);
            $featYears   = $request->input('feature_year', []);
            $featTitles  = $request->input('feature_title', []);
            $featDescs   = $request->input('feature_description', []);
            $features    = [];
            foreach ($featNumbers as $i => $num) {
                if (!empty($featTitles[$i])) {
                    $features[] = [
                        'number'      => $num,
                        'year'        => $featYears[$i] ?? '',
                        'title'       => $featTitles[$i],
                        'description' => $featDescs[$i] ?? '',
                    ];
                }
            }
            $sections['features'] = $features;

            // Stats
            $statLabels = $request->input('stat_label', []);
            $statValues = $request->input('stat_value', []);
            $stats      = [];
            foreach ($statLabels as $i => $label) {
                if (!empty($label)) {
                    $stats[] = ['label' => $label, 'value' => $statValues[$i] ?? ''];
                }
            }
            $sections['stats'] = $stats;

            // Team members
            $teamNames      = $request->input('team_name', []);
            $teamRoles      = $request->input('team_role', []);
            $teamImages     = $request->input('team_image', []);
            $teamImageFiles = $request->file('team_image_file', []);
            $teamDescs      = $request->input('team_description', []);
            $team           = [];
            foreach ($teamNames as $i => $name) {
                if (!empty($name)) {
                    $imageUrl = $teamImages[$i] ?? '';
                    if (!empty($teamImageFiles[$i])) {
                        $path     = $teamImageFiles[$i]->store('static-pages/team', 'public');
                        $imageUrl = Storage::url($path);
                    }
                    $team[] = [
                        'name'        => $name,
                        'role'        => $teamRoles[$i] ?? '',
                        'image'       => $imageUrl,
                        'description' => $teamDescs[$i] ?? '',
                    ];
                }
            }
            $sections['team'] = $team;

            return $sections;
        }

        if ($slug === 'contact') {
            $phones = array_filter(array_map('trim', explode("\n", $request->input('phones', ''))));
            $emails = array_filter(array_map('trim', explode("\n", $request->input('emails', ''))));
            return [
                'address'       => $request->input('address', ''),
                'phones'        => array_values($phones),
                'emails'        => array_values($emails),
                'careers_email' => $request->input('careers_email', ''),
                'map_query'     => $request->input('map_query', ''),
            ];
        }

        // Generic page — no structured sections
        return $request->input('sections', []) ?: [];
    }
}
