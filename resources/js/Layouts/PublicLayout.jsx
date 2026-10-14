import { Link, router, usePage } from '@inertiajs/react';
import { BriefcaseBusiness, Menu, X } from 'lucide-react';
import { useState } from 'react';
import Brand from '../Components/Brand';

export default function PublicLayout({ children }) {
    const { auth, flash } = usePage().props;
    const [open, setOpen] = useState(false);
    const close = () => setOpen(false);

    return <div className="page-shell">
        <a href="#main-content" className="skip-link">Skip to main content</a>
        <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
            <nav className="container-wide flex min-h-17 items-center justify-between gap-6">
                <Link href="/" onClick={close}><Brand/></Link>
                <div className="hidden items-center gap-6 text-sm font-medium text-slate-700 lg:flex">
                    <Link href="/jobs" className="transition hover:text-[#7132a8]">Find Jobs</Link>
                    <Link href="/#companies" className="transition hover:text-[#7132a8]">Companies</Link>
                    <Link href="/#career" className="transition hover:text-[#7132a8]">Career Resources</Link>
                </div>
                <div className="hidden items-center gap-2 lg:flex">
                    {auth.user ? <><Link href="/dashboard" className="btn btn-secondary">Dashboard</Link><button className="btn btn-primary" onClick={() => router.post('/logout')}>Log out</button></> : <><Link href="/login" className="btn btn-secondary">Log in</Link><Link href="/register" className="btn btn-primary">Register</Link></>}
                    <Link href={auth.user?.role === 'employer' ? '/employer/jobs' : '/register'} className="ml-1 hidden min-h-11 items-center gap-2 rounded-md border border-[#7132a8] px-4 text-sm font-bold text-[#7132a8] xl:inline-flex"><BriefcaseBusiness size={16}/> Post Jobs</Link>
                </div>
                <button type="button" aria-label="Toggle navigation" aria-expanded={open} className="grid size-10 place-items-center rounded-lg border border-slate-300 text-slate-700 lg:hidden" onClick={() => setOpen(!open)}>{open ? <X size={20}/> : <Menu size={20}/>}</button>
            </nav>
            {open && <nav aria-label="Mobile navigation" className="container-wide grid gap-1 border-t border-slate-200 py-3 text-sm font-medium text-slate-700 lg:hidden">
                <Link href="/jobs" className="rounded-lg px-3 py-2.5 hover:bg-purple-50" onClick={close}>Find Jobs</Link><Link href="/#companies" className="rounded-lg px-3 py-2.5 hover:bg-purple-50" onClick={close}>Companies</Link>
                {auth.user ? <><Link href="/dashboard" className="rounded-lg px-3 py-2.5 hover:bg-purple-50" onClick={close}>Dashboard</Link><button className="rounded-lg px-3 py-2.5 text-left hover:bg-purple-50" onClick={() => router.post('/logout')}>Log out</button></> : <><Link href="/login" className="rounded-lg px-3 py-2.5 hover:bg-purple-50" onClick={close}>Log in</Link><Link href="/register" className="rounded-lg bg-[#7132a8] px-3 py-2.5 text-white" onClick={close}>Register</Link></>}
            </nav>}
        </header>
        {flash?.success && <div role="status" className="container-wide mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3.5 text-sm text-emerald-800">{flash.success}</div>}
        {children}
        <footer className="border-t border-slate-200 bg-white">
            <div className="container-wide grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div><Brand/><p className="mt-4 max-w-sm text-sm leading-6 text-slate-500">Helping people across Myanmar find better careers and helping employers meet the right talent.</p></div>
                <div><h2 className="font-bold text-slate-900">Job Seekers</h2><div className="mt-3 grid gap-2 text-sm text-slate-500"><Link href="/jobs">Browse jobs</Link><Link href="/register">Create an account</Link><Link href="/login">Manage applications</Link></div></div>
                <div><h2 className="font-bold text-slate-900">Employers</h2><div className="mt-3 grid gap-2 text-sm text-slate-500"><Link href="/register">Post a job</Link><Link href="/login">Manage candidates</Link><Link href="/#companies">Company profiles</Link></div></div>
                <div><h2 className="font-bold text-slate-900">JobSphere</h2><div className="mt-3 grid gap-2 text-sm text-slate-500"><Link href="/">About us</Link><Link href="/#career">Career resources</Link><Link href="/login">Help centre</Link></div></div>
            </div>
            <div className="border-t border-slate-200 py-4 text-center text-xs text-slate-500">© {new Date().getFullYear()} JobSphere Myanmar. All rights reserved.</div>
        </footer>
    </div>;
}
