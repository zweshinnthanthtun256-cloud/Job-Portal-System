import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, BookOpen, Building2, MapPin, Search, ShieldCheck, Users } from 'lucide-react';
import { useState } from 'react';
import JobCard from '../Components/JobCard';
import PublicLayout from '../Layouts/PublicLayout';

export default function Home({ featuredJobs, featuredCompanies = [], categories = [], stats }) {
    const [q, setQ] = useState('');
    const [location, setLocation] = useState('');
    const [category, setCategory] = useState('');
    const submit = (e) => { e.preventDefault(); router.get('/jobs', { q, location, category }); };

    return <PublicLayout>
        <Head title="Myanmar jobs and career opportunities" />
        <main id="main-content">
            <section className="relative overflow-hidden bg-gradient-to-r from-[#321446] via-[#5f2785] to-[#7640a3] text-white">
                <div className="absolute inset-0 opacity-20" style={{backgroundImage:'radial-gradient(circle at 18% 20%, white 0 1px, transparent 1.5px)',backgroundSize:'24px 24px'}}/>
                <div className="container-wide relative flex min-h-[430px] items-center py-14 sm:py-20">
                    <div className="w-full">
                        <p className="text-sm font-bold uppercase tracking-[.22em] text-purple-200">Myanmar's career marketplace</p>
                        <h1 className="mt-3 max-w-4xl text-4xl font-black leading-tight sm:text-5xl">Build your next chapter with the right opportunity.</h1>
                        <p className="mt-4 max-w-2xl text-base leading-7 text-purple-100 sm:text-lg">Explore trusted employers, discover jobs that fit your skills, and manage every application in one place.</p>
                        <form onSubmit={submit} className="mt-8 grid overflow-hidden rounded-lg bg-white p-2 shadow-2xl md:grid-cols-[minmax(0,1.35fr)_minmax(0,.8fr)_minmax(0,.8fr)_auto]">
                            <label className="flex items-center border-b border-slate-200 px-3 md:border-b-0 md:border-r"><Search size={18} className="shrink-0 text-[#7132a8]"/><span className="sr-only">Job title or keyword</span><input className="h-12 w-full border-0 bg-transparent px-3 text-slate-900 outline-none" value={q} onChange={e => setQ(e.target.value)} placeholder="Job title, skill or company"/></label>
                            <label className="flex items-center border-b border-slate-200 px-3 md:border-b-0 md:border-r"><MapPin size={18} className="shrink-0 text-[#7132a8]"/><span className="sr-only">Location</span><input className="h-12 w-full border-0 bg-transparent px-3 text-slate-900 outline-none" value={location} onChange={e => setLocation(e.target.value)} placeholder="Location"/></label>
                            <label><span className="sr-only">Category</span><select className="h-12 w-full border-0 bg-white px-4 text-slate-700 outline-none" value={category} onChange={e => setCategory(e.target.value)}><option value="">All categories</option>{categories.map(item => <option key={item.id} value={item.slug}>{item.name}</option>)}</select></label>
                            <button className="btn btn-primary min-h-12 rounded-md px-7"><Search size={18}/> Find Jobs</button>
                        </form>
                        <div className="mt-7 flex flex-wrap gap-x-8 gap-y-3 text-sm text-purple-100">{Object.entries(stats).map(([key, value]) => <div key={key}><strong className="mr-1.5 text-xl text-white">{value.toLocaleString()}</strong><span className="capitalize">{key}</span></div>)}</div>
                    </div>
                </div>
            </section>

            {!!featuredCompanies.length && <section id="companies" className="border-b border-slate-200 bg-white"><div className="container-wide py-8"><h2 className="text-center text-lg font-bold text-slate-800">Top employers hiring on JobSphere</h2><div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">{featuredCompanies.map(company => <Link href={`/companies/${company.slug}`} key={company.id} className="flex min-h-24 flex-col items-center justify-center rounded-lg border border-slate-200 p-3 text-center transition hover:border-purple-300 hover:shadow-md">{company.logo_path ? <img src={`/companies/${company.slug}/assets/logo`} alt="" className="size-10 rounded-md object-contain"/> : <span className="grid size-10 place-items-center rounded-md bg-purple-50 font-black text-[#7132a8]">{company.name[0]}</span>}<strong className="mt-2 line-clamp-2 text-xs text-slate-700">{company.name}</strong></Link>)}</div></div></section>}

            <section className="container-wide py-12 sm:py-16">
                <div className="flex flex-wrap items-end justify-between gap-4"><div><p className="text-sm font-bold uppercase tracking-[.18em] text-[#7132a8]">New opportunities</p><h2 className="mt-1 text-3xl font-black text-slate-900">Featured jobs</h2><p className="mt-2 text-slate-500">Fresh openings from employers across Myanmar.</p></div><Link href="/jobs" className="font-bold text-[#7132a8]">Browse all jobs <ArrowRight size={16} className="inline"/></Link></div>
                {featuredJobs.length ? <div className="mt-7 grid gap-4 md:grid-cols-2 lg:grid-cols-3">{featuredJobs.map(job => <JobCard key={job.id} job={job}/>)}</div> : <div className="glass mt-7 rounded-xl p-10 text-center text-slate-500">New opportunities are being prepared. Check back soon.</div>}
            </section>

            {!!categories.length && <section className="bg-white py-12 sm:py-16"><div className="container-wide"><div className="text-center"><p className="text-sm font-bold uppercase tracking-[.18em] text-[#7132a8]">Explore by field</p><h2 className="mt-1 text-3xl font-black">Popular job categories</h2></div><div className="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">{categories.map(category => <Link href={`/jobs?category=${category.slug}`} key={category.id} className="flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-purple-300 hover:shadow-md"><span className="grid size-11 place-items-center rounded-lg bg-purple-50 text-[#7132a8]"><Building2 size={20}/></span><span><strong className="block text-slate-900">{category.name}</strong><small className="text-slate-500">{category.jobs_count} open {category.jobs_count === 1 ? 'role' : 'roles'}</small></span></Link>)}</div></div></section>}

            <section id="career" className="container-wide grid gap-5 py-12 sm:py-16 md:grid-cols-3"><div className="glass rounded-xl p-6"><ShieldCheck className="text-[#7132a8]"/><h2 className="mt-4 text-lg font-bold">Trusted opportunities</h2><p className="mt-2 text-sm leading-6 text-slate-500">View company profiles and apply through a secure account.</p></div><div className="glass rounded-xl p-6"><Users className="text-[#7132a8]"/><h2 className="mt-4 text-lg font-bold">One professional profile</h2><p className="mt-2 text-sm leading-6 text-slate-500">Showcase your skills, experience, education and resume.</p></div><div className="glass rounded-xl p-6"><BookOpen className="text-[#7132a8]"/><h2 className="mt-4 text-lg font-bold">Stay in control</h2><p className="mt-2 text-sm leading-6 text-slate-500">Track applications, interviews and saved jobs from your dashboard.</p></div></section>

            <section className="bg-[#3a174f] py-12 text-white"><div className="container-wide flex flex-col items-start justify-between gap-5 md:flex-row md:items-center"><div><h2 className="text-2xl font-black">Ready to move your career forward?</h2><p className="mt-2 text-purple-100">Create your free JobSphere profile and start applying today.</p></div><Link href="/register" className="btn bg-white px-6 text-[#5f248f] shadow-lg">Create free account <ArrowRight size={17}/></Link></div></section>
        </main>
    </PublicLayout>;
}
