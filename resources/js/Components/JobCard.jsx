import { Link } from '@inertiajs/react';
import { Building2, Clock3, MapPin } from 'lucide-react';

const label = (value = '') => value.replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase());

export default function JobCard({ job }) {
    return <Link href={`/jobs/${job.slug}`} className="group block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-lg">
        <div className="flex gap-3">
            <div className="grid size-12 shrink-0 place-items-center rounded-lg border border-purple-100 bg-purple-50 text-[#7132a8]"><Building2 size={21}/></div>
            <div className="min-w-0"><h3 className="truncate text-base font-bold text-slate-900 group-hover:text-[#7132a8]">{job.title}</h3><p className="mt-1 truncate text-sm text-slate-500">{job.company?.name}</p></div>
        </div>
        <div className="mt-4 grid gap-2 text-sm text-slate-500"><span><MapPin size={14} className="mr-1.5 inline text-purple-500"/>{job.city || job.country || 'Flexible location'}</span><span><Clock3 size={14} className="mr-1.5 inline text-purple-500"/>{label(job.employment_type)} · {label(job.work_arrangement)}</span></div>
        <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">{job.salary_visible && job.salary_min ? <p className="font-bold text-[#7132a8]">{job.salary_currency} {Number(job.salary_min).toLocaleString()}{job.salary_max ? `–${Number(job.salary_max).toLocaleString()}` : '+'}</p> : <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Salary negotiable</p>}<span className="text-sm font-bold text-[#7132a8]">View job →</span></div>
    </Link>;
}
