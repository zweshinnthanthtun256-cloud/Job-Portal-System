<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function show(Company $company): Response
    {
        abort_if(in_array($company->verification_status, ['rejected', 'suspended'], true), 404);
        $company->load(['jobs' => fn ($q) => $q->published()->latest('published_at')->limit(12)]);

        return Inertia::render('Companies/Show', ['company' => $company]);
    }

    public function asset(Company $company, string $type)
    {
        abort_unless(in_array($type, ['logo', 'cover'], true), 404);
        $path = $company->{$type.'_path'};
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    public function mine(Request $request): RedirectResponse
    {
        $company = $request->user()->companies()->firstOrFail();

        return redirect()->route('companies.edit', $company);
    }

    public function edit(Request $request, Company $company): Response
    {
        $this->authorizeMember($request, $company);

        return Inertia::render('Employer/Company/Edit', ['company' => $company->load('users:id,name,email')]);
    }

    public function update(Request $request, Company $company, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeAdmin($request, $company);
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'industry' => ['nullable', 'string', 'max:120'], 'size' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email'], 'website' => ['nullable', 'url'], 'phone' => ['nullable', 'string', 'max:30'], 'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'], 'address' => ['nullable', 'string', 'max:255'], 'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')], 'description' => ['nullable', 'string', 'max:5000'], 'culture' => ['nullable', 'string', 'max:5000'], 'linkedin_url' => ['nullable', 'url'], 'benefits' => ['nullable', 'array', 'max:30'], 'benefits.*' => ['string', 'max:120']]);
        $old = $company->only(array_keys($data));
        $company->update($data);
        $logger->log('company.updated', $company, $old, $data);

        return back()->with('success', 'Company profile updated.');
    }

    public function media(Request $request, Company $company, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeAdmin($request, $company);
        $data = $request->validate(['type' => ['required', 'in:logo,cover'], 'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);
        $column = $data['type'].'_path';
        if ($company->{$column}) {
            Storage::disk('public')->delete($company->{$column});
        }
        $company->update([$column => $request->file('image')->store('companies/'.$company->id, 'public')]);
        $logger->log('company.'.$data['type'].'_updated', $company);

        return back()->with('success', ucfirst($data['type']).' updated.');
    }

    public function addMember(Request $request, Company $company, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeAdmin($request, $company);
        $data = $request->validate(['email' => ['required', 'email', 'exists:users,email'], 'role' => ['required', 'in:company_admin,recruiter']]);
        $user = User::where('email', $data['email'])->firstOrFail();
        abort_if($user->role !== 'employer', 422, 'Only employer accounts can join a company.');
        $company->users()->syncWithoutDetaching([$user->id => ['role' => $data['role']]]);
        $logger->log('company.member_added', $company, null, ['user_id' => $user->id, 'role' => $data['role']]);

        return back()->with('success', 'Company member added.');
    }

    public function removeMember(Request $request, Company $company, User $user, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeAdmin($request, $company);
        $role = $company->users()->whereKey($user->id)->first()?->pivot->role;
        abort_if($role === 'owner', 422, 'The company owner cannot be removed.');
        $logger->log('company.member_removed', $company, ['user_id' => $user->id, 'role' => $role]);
        $company->users()->detach($user);

        return back()->with('success', 'Company member removed.');
    }

    private function authorizeMember(Request $request, Company $company): void
    {
        abort_unless($request->user()->belongsToCompany($company->id), 403);
    }

    private function authorizeAdmin(Request $request, Company $company): void
    {
        $role = $request->user()->companies()->whereKey($company->id)->first()?->pivot->role;
        abort_unless(in_array($role, ['owner', 'company_admin'], true), 403);
    }
}
