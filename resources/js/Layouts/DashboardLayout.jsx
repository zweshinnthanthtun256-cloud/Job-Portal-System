import { Link, router, usePage } from '@inertiajs/react';
import { Bell, BriefcaseBusiness, LayoutDashboard, LogOut, Menu, X } from 'lucide-react';
import { useState } from 'react';
import Brand from '../Components/Brand';

export default function DashboardLayout({ children, title }) {
    const page = usePage();
    const { auth, flash } = page.props;
    const [menuOpen, setMenuOpen] = useState(false);
    const links = auth.user.role === 'employer'
        ? [['/employer/company', 'Company'], ['/employer/jobs', 'Manage jobs'], ['/employer/candidates', 'Candidates'], ['/interviews', 'Interviews'], ['/notifications', 'Notifications']]
        : auth.user.role === 'job_seeker'
            ? [['/profile', 'Profile'], ['/recommendations', 'Recommended jobs'], ['/applications', 'Applications'], ['/saved-jobs', 'Saved jobs'], ['/interviews', 'Interviews'], ['/notifications', 'Notifications'], ['/jobs', 'Browse jobs']]
            : [['/admin', 'Admin console'], ['/notifications', 'Notifications'], ['/jobs', 'Browse jobs']];
    const allLinks = [['/dashboard', 'Dashboard', LayoutDashboard], ...links.map(([href, label]) => [href, label, BriefcaseBusiness])];
    const isActive = (href) => href === '/dashboard' ? page.url === href : page.url.startsWith(href);
    const navigation = allLinks.map(([href, label, Icon]) => <Link key={href} href={href} onClick={() => setMenuOpen(false)} className={`flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-semibold transition ${isActive(href) ? 'bg-white/15 text-white ring-1 ring-inset ring-white/10' : 'text-purple-100/75 hover:bg-white/10 hover:text-white'}`}><Icon size={17}/><span>{label}</span></Link>);

    return <div className="min-h-screen overflow-x-hidden bg-[#f6f5f8] text-slate-900 lg:grid lg:grid-cols-[236px_minmax(0,1fr)]">
        <a href="#main-content" className="skip-link">Skip to main content</a>
        <aside className="hidden bg-gradient-to-b from-[#321446] to-[#59217e] p-5 lg:block"><Link href="/"><Brand inverse/></Link><p className="mt-3 text-xs text-purple-100/60">Myanmar careers workspace</p><nav aria-label="Dashboard navigation" className="mt-7 grid gap-1.5">{navigation}</nav></aside>
        <main id="main-content" className="min-w-0">
            <header className="flex h-17 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6 lg:px-7">
                <div className="flex min-w-0 items-center gap-3"><button type="button" onClick={() => setMenuOpen(!menuOpen)} aria-label="Toggle dashboard navigation" aria-expanded={menuOpen} className="grid size-9 shrink-0 place-items-center rounded-lg border border-slate-300 text-slate-700 lg:hidden">{menuOpen ? <X size={18}/> : <Menu size={18}/>}</button><div className="min-w-0"><div className="text-[.7rem] font-bold uppercase tracking-[.16em] text-[#7132a8]">{auth.user.role.replace('_', ' ')}</div><h1 className="truncate text-lg font-bold text-slate-900">{title}</h1></div></div>
                <div className="flex items-center gap-2"><Link href="/notifications" aria-label="Notifications" className="grid size-9 place-items-center rounded-lg border border-slate-300 text-slate-600 transition hover:border-purple-400 hover:text-[#7132a8]"><Bell size={17}/></Link><button type="button" onClick={() => router.post('/logout')} className="grid size-9 place-items-center rounded-lg border border-slate-300 text-slate-600 transition hover:border-purple-400 hover:text-[#7132a8]" aria-label="Log out"><LogOut size={17}/></button></div>
            </header>
            {menuOpen && <nav aria-label="Mobile dashboard navigation" className="grid gap-1 bg-[#42205b] p-3 lg:hidden">{navigation}</nav>}
            <div className="mx-auto w-full max-w-[1440px] p-4 sm:p-6 lg:p-7">{flash?.success && <div role="status" className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3.5 text-sm text-emerald-800">{flash.success}</div>}{children}</div>
        </main>
    </div>;
}
