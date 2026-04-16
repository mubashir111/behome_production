<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;

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
            'slug'                => $page->is_system ? 'nullable' : 'required|string|max:255|regex:/^[a-z0-9\-]+$/|unique:static_pages,slug,' . $page->id,
            'content'             => 'nullable|string',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string|max:500',
            'is_active'           => 'nullable|boolean',
            'team_image_file.*'   => 'nullable|image|max:3072',
        ]);

        // Build sections JSON from request depending on page slug
        $sections = $this->buildSections($request, $page->slug);

        // Handle team image uploads specifically if present
        if ($page->slug === 'about' && $request->hasFile('team_image_file')) {
            $files = $request->file('team_image_file');
            foreach ($files as $index => $file) {
                if ($file && isset($sections['team'][$index])) {
                    $path = $file->store('pages', 'public');
                    $sections['team'][$index]['image'] = '/storage/' . $path;
                }
            }
        }

        $updateData = [
            'title'            => $data['title'],
            'content'          => $data['content'] ?? null,
            'sections'         => $sections,
            'meta_title'       => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_active'        => $request->boolean('is_active'),
        ];

        // Only update slug if it's NOT a system page
        if (!$page->is_system && isset($data['slug'])) {
            $updateData['slug'] = $data['slug'];
        }

        $page->update($updateData);

        \App\Models\AdminNotification::record('info', 'Page Updated', "Static page '{$page->title}' was modified by " . (auth()->user()->name ?? 'Admin'));

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page updated successfully.');
    }

    private function buildSections(Request $request, string $slug): array
    {
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

        if ($slug === 'about') {
            // Build features array from parallel arrays
            $numbers      = $request->input('feature_number', []);
            $years        = $request->input('feature_year', []);
            $featureTitles = $request->input('feature_title', []);
            $featureDescs  = $request->input('feature_desc', []);
            $features = [];
            foreach ($numbers as $i => $num) {
                if (empty($num) && empty($featureTitles[$i] ?? '')) continue;
                $features[] = [
                    'number'      => $num,
                    'year'        => $years[$i] ?? '',
                    'title'       => $featureTitles[$i] ?? '',
                    'description' => $featureDescs[$i] ?? '',
                ];
            }

            // Build stats array
            $statValues = $request->input('stat_value', []);
            $statLabels = $request->input('stat_label', []);
            $stats = [];
            foreach ($statValues as $i => $val) {
                if (empty($val)) continue;
                $stats[] = ['value' => $val, 'label' => $statLabels[$i] ?? ''];
            }

            // Build team array
            $teamNames  = $request->input('team_name', []);
            $teamRoles  = $request->input('team_role', []);
            $teamImages = $request->input('team_image', []);
            $teamDescs  = $request->input('team_desc', []);
            $team = [];
            foreach ($teamNames as $i => $name) {
                if (empty($name)) continue;
                $team[] = [
                    'name'        => $name,
                    'role'        => $teamRoles[$i] ?? '',
                    'image'       => $teamImages[$i] ?? '',
                    'description' => $teamDescs[$i] ?? '',
                ];
            }

            return [
                'hero' => [
                    'title'       => $request->input('hero_title', ''),
                    'subtitle'    => $request->input('hero_subtitle', ''),
                    'description' => $request->input('hero_description', ''),
                ],
                'features' => $features,
                'stats'    => $stats,
                'team'     => $team,
            ];
        }

        // Generic page — no structured sections
        return $request->input('sections', []) ?: [];
    }
}
