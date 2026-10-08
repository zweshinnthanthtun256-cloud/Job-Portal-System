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
    const navigation = allLinks.map(([href, label, Icon]) => <Link key={href} href={href} onClick={() => setMenuOpen(false)} className={`flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition ${isActive(href) ? 'bg-blue-600/15 text-blue-200 ring-1 ring-inset ring-blue-500/15' : 'text-slate-400 hover:bg-white/5 hover:text-slate-100'}`}><Icon size={17}/><span>{label}</span></Link>);

    return <div className="min-h-screen overflow-x-hidden bg-[#07111f] lg:grid lg:grid-cols-[236px_minmax(0,1fr)]">
        <a href="#main-content" className="skip-link">Skip to main content</a>
        <aside className="hidden border-r border-[#1e3a5f] bg-[#081426] p-5 lg:block"><Link href="/"><Brand/></Link><nav aria-label="Dashboard navigation" className="mt-8 grid gap-1.5">{navigation}</nav></aside>
        <main id="main-content" className="min-w-0">
            <header className="flex h-16 items-center justify-between border-b border-[#1e3a5f] bg-[#07111f]/90 px-4 backdrop-blur sm:px-6 lg:px-7">
                <div className="flex min-w-0 items-center gap-3"><button type="button" onClick={() => setMenuOpen(!menuOpen)} aria-label="Toggle dashboard navigation" aria-expanded={menuOpen} className="grid size-9 shrink-0 place-items-center rounded-lg border border-[#1e3a5f] lg:hidden">{menuOpen ? <X size={18}/> : <Menu size={18}/>}</button><div className="min-w-0"><div className="text-[.7rem] font-semibold uppercase tracking-[.16em] text-blue-400">{auth.user.role.replace('_', ' ')}</div><h1 className="truncate text-lg font-bold">{title}</h1></div></div>
                <div className="flex items-center gap-2"><Link href="/notifications" aria-label="Notifications" className="grid size-9 place-items-center rounded-lg border border-[#1e3a5f] text-slate-300 transition hover:border-blue-500/50 hover:text-white"><Bell size={17}/></Link><button type="button" onClick={() => router.post('/logout')} className="grid size-9 place-items-center rounded-lg border border-[#1e3a5f] text-slate-300 transition hover:border-blue-500/50 hover:text-white" aria-label="Log out"><LogOut size={17}/></button></div>
            </header>
            {menuOpen && <nav aria-label="Mobile dashboard navigation" className="grid gap-1 border-b border-[#1e3a5f] bg-[#081426] p-3 lg:hidden">{navigation}</nav>}
            <div className="mx-auto w-full max-w-[1440px] p-4 sm:p-6 lg:p-7">{flash?.success && <div role="status" className="mb-5 rounded-lg border border-green-700/50 bg-green-900/30 p-3.5 text-sm text-green-200">{flash.success}</div>}{children}</div>
        </main>
    </div>;
}
