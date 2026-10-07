<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Resume;
use App\Models\Skill;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user()->load(['profile', 'educations', 'experiences', 'skills', 'resumes', 'languages', 'certifications']);

        $checks = [
            filled($user->profile?->headline), filled($user->profile?->summary), filled($user->profile?->city),
            filled($user->profile?->current_position), filled($user->profile?->photo_path), $user->skills->isNotEmpty(),
            $user->educations->isNotEmpty(), $user->experiences->isNotEmpty(), $user->resumes->isNotEmpty(),
            $user->languages->isNotEmpty(),
        ];

        return Inertia::render('Profile/Edit', [
            'profile' => $user,
            'skillLibrary' => Skill::orderBy('name')->get(['id', 'name']),
            'completionScore' => (int) round(collect($checks)->filter()->count() / count($checks) * 100),
        ]);
    }

    public function update(UpdateProfileRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $data, $logger) {
            $user = $request->user();
            $user->update(['first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'name' => $data['first_name'].' '.$data['last_name']]);
            $profileData = collect($data)->except(['first_name', 'last_name'])->all();
            $user->profile()->updateOrCreate([], $profileData);
            $logger->log('profile.updated', $user, null, $profileData);
        });

        return back()->with('success', 'Profile updated.');
    }

    public function education(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['institution' => ['required', 'string', 'max:180'], 'degree' => ['required', 'string', 'max:150'], 'field' => ['nullable', 'string', 'max:150'], 'start_year' => ['required', 'integer', 'min:1950', 'max:'.date('Y')], 'end_year' => ['nullable', 'integer', 'gte:start_year'], 'currently_studying' => ['boolean'], 'description' => ['nullable', 'string', 'max:2000']]);
        $education = $request->user()->educations()->create($data);
        $logger->log('profile.education_added', $education);

        return back()->with('success', 'Education added.');
    }

    public function destroyEducation(Request $request, Education $education, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($education->user_id === $request->user()->id, 403);
        $logger->log('profile.education_removed', $education);
        $education->delete();

        return back()->with('success', 'Education removed.');
    }

    public function experience(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['company' => ['required', 'string', 'max:180'], 'position' => ['required', 'string', 'max:160'], 'employment_type' => ['nullable', 'string', 'max:50'], 'start_date' => ['required', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'currently_working' => ['boolean'], 'responsibilities' => ['nullable', 'string', 'max:3000']]);
        $experience = $request->user()->experiences()->create($data);
        $logger->log('profile.experience_added', $experience);

        return back()->with('success', 'Experience added.');
    }

    public function destroyExperience(Request $request, Experience $experience, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($experience->user_id === $request->user()->id, 403);
        $logger->log('profile.experience_removed', $experience);
        $experience->delete();

        return back()->with('success', 'Experience removed.');
    }

    public function skills(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['skills' => ['array', 'max:30'], 'skills.*' => ['integer', 'exists:skills,id']]);
        $request->user()->skills()->sync(collect($data['skills'] ?? [])->mapWithKeys(fn ($id) => [$id => ['proficiency' => 'intermediate']]));
        $logger->log('profile.skills_updated', $request->user(), null, ['skill_ids' => $data['skills'] ?? []]);

        return back()->with('success', 'Skills updated.');
    }

    public function addSkill(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/[\pL\pN]/u'],
        ]);
        $name = preg_replace('/\s+/u', ' ', trim($data['name']));
        $slug = Str::slug($name) ?: 'skill';

        $skill = Skill::query()->whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();

        if (! $skill) {
            if (Skill::where('slug', $slug)->exists()) {
                $slug .= '-'.substr(sha1(Str::lower($name)), 0, 8);
            }

            $skill = Skill::create(['name' => $name, 'slug' => $slug]);
        }

        $request->user()->skills()->syncWithoutDetaching([
            $skill->id => ['proficiency' => 'intermediate'],
        ]);
        $logger->log('profile.skill_added', $request->user(), null, ['skill_id' => $skill->id]);

        return back()->with('success', 'Skill added to your profile.');
    }

    public function resume(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $request->validate(['resume' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:5120']]);
        $file = $request->file('resume');
        $path = $file->store('resumes/'.$request->user()->id, 'local');
        $request->user()->resumes()->update(['is_primary' => false]);
        $resume = $request->user()->resumes()->create(['original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize(), 'is_primary' => true]);
        $logger->log('profile.resume_uploaded', $resume);

        return back()->with('success', 'Resume uploaded securely.');
    }

    public function download(Request $request, Resume $resume): StreamedResponse
    {
        $owner = $resume->user_id === $request->user()->id;
        $employer = $request->user()->role === 'employer' && $request->user()->companies()->whereHas('jobs.applications', fn ($q) => $q->where('user_id', $resume->user_id))->exists();
        abort_unless($owner || $employer || $request->user()->isAdmin(), 403);
        abort_unless(Storage::disk('local')->exists($resume->path), 404);

        return Storage::disk('local')->download($resume->path, $resume->original_name);
    }

    public function destroyResume(Request $request, Resume $resume, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($resume->user_id === $request->user()->id, 403);
        $logger->log('profile.resume_removed', $resume);
        Storage::disk('local')->delete($resume->path);
        $resume->delete();

        return back()->with('success', 'Resume deleted.');
    }

    public function language(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'proficiency' => ['required', 'in:basic,conversational,professional,native']]);
        $language = $request->user()->languages()->create($data);
        $logger->log('profile.language_added', $language);

        return back()->with('success', 'Language added.');
    }

    public function destroyLanguage(Request $request, Language $language, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($language->user_id === $request->user()->id, 403);
        $logger->log('profile.language_removed', $language);
        $language->delete();

        return back()->with('success', 'Language removed.');
    }

    public function certification(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:160'], 'issuer' => ['required', 'string', 'max:160'], 'issued_at' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'], 'credential_url' => ['nullable', 'url', 'max:500']]);
        $certification = $request->user()->certifications()->create($data);
        $logger->log('profile.certification_added', $certification);

        return back()->with('success', 'Certification added.');
    }

    public function destroyCertification(Request $request, Certification $certification, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($certification->user_id === $request->user()->id, 403);
        $logger->log('profile.certification_removed', $certification);
        $certification->delete();

        return back()->with('success', 'Certification removed.');
    }

    public function photo(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $request->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        $profile = $request->user()->profile()->firstOrCreate();
        if ($profile->photo_path) {
            Storage::disk('public')->delete($profile->photo_path);
        }
        $profile->update(['photo_path' => $request->file('photo')->store('profile-photos/'.$request->user()->id, 'public')]);
        $logger->log('profile.photo_updated', $request->user());

        return back()->with('success', 'Profile photo updated.');
    }
}
