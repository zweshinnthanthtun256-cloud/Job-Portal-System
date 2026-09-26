import { Head } from '@inertiajs/react';
import JobCard from '../../Components/JobCard';
import DashboardLayout from '../../Layouts/DashboardLayout';
export default function Index({jobs}){return <DashboardLayout title="Saved jobs"><Head title="Saved jobs"/>{jobs.data.length?<div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">{jobs.data.map(job=><JobCard key={job.id} job={job}/>)}</div>:<div className="glass rounded-2xl p-12 text-center text-slate-400">You haven’t saved any jobs yet.</div>}</DashboardLayout>}
