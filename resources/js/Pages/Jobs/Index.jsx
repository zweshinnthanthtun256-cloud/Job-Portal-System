import { Head, router } from '@inertiajs/react';
import { Search, SlidersHorizontal } from 'lucide-react';
import { useState } from 'react';
import JobCard from '../../Components/JobCard';
import PublicLayout from '../../Layouts/PublicLayout';

export default function Index({ jobs, filters, categories }) {
    const [data, setData] = useState({ q: filters.q || '', location: filters.location || '', category: filters.category || '', work_arrangement: filters.work_arrangement || '' });
    const set = (key, value) => setData({ ...data, [key]: value });
    const submit = (event) => { event.preventDefault(); router.get('/jobs', data, { preserveState: true }); };
    return <PublicLayout><Head title="Find jobs"/><main id="main-content">
        <section className="bg-gradient-to-r from-[#351649] to-[#6f2da8] py-10 text-white"><div className="container-wide"><p className="text-sm font-bold uppercase tracking-[.18em] text-purple-200">Job search</p><h1 className="mt-1 text-3xl font-black sm:text-4xl">Find a job that fits you</h1><p className="mt-2 text-purple-100">Search opportunities from employers across Myanmar.</p></div></section>
        <div className="container-wide py-8 sm:py-10">
            <form onSubmit={submit} className="glass -mt-14 grid gap-3 rounded-xl p-4 md:grid-cols-2 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                <input className="field" aria-label="Job title or keyword" placeholder="Job title, skill or company" value={data.q} onChange={e => set('q', e.target.value)}/><input className="field" aria-label="Location" placeholder="Location" value={data.location} onChange={e => set('location', e.target.value)}/><select className="field" aria-label="Job category" value={data.category} onChange={e => set('category', e.target.value)}><option value="">All categories</option>{categories.map(item => <option value={item.slug} key={item.slug}>{item.name}</option>)}</select><select className="field" aria-label="Work arrangement" value={data.work_arrangement} onChange={e => set('work_arrangement', e.target.value)}><option value="">Any work type</option><option value="remote">Remote</option><option value="hybrid">Hybrid</option><option value="on_site">On-site</option></select><button className="btn btn-primary"><Search size={18}/> Search</button>
            </form>
            <div className="mt-7 flex items-center justify-between"><p className="text-slate-600"><strong className="text-slate-900">{jobs.total}</strong> jobs found</p><SlidersHorizontal className="text-slate-500"/></div>
            {jobs.data.length ? <div className="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-3">{jobs.data.map(job => <JobCard job={job} key={job.id}/>)}</div> : <div className="glass mt-5 rounded-xl p-10 text-center"><h2 className="text-xl font-bold">No jobs match those filters</h2><p className="mt-2 text-slate-500">Try a broader keyword or clear a filter.</p></div>}
            <div className="mt-8 flex flex-wrap gap-2">{jobs.links.map((link, index) => <button key={index} disabled={!link.url} onClick={() => link.url && router.get(link.url)} className={`rounded-md border px-3 py-2 text-sm ${link.active ? 'border-[#7132a8] bg-[#7132a8] text-white' : 'border-slate-300 bg-white text-slate-600'}`} dangerouslySetInnerHTML={{ __html: link.label }}/>)}</div>
        </div>
    </main></PublicLayout>;
}
