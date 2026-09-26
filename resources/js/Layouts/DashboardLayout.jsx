import {Link,router,usePage} from '@inertiajs/react';
import {Bell,BriefcaseBusiness,LayoutDashboard,LogOut} from 'lucide-react';
import Brand from '../Components/Brand';

export default function DashboardLayout({children,title}){
 const {auth,flash}=usePage().props;
 const links=auth.user.role==='employer'
  ?[['/employer/company','Company'],['/employer/jobs','Manage jobs'],['/employer/candidates','Candidates'],['/interviews','Interviews'],['/notifications','Notifications']]
  :auth.user.role==='job_seeker'
   ?[['/profile','Profile'],['/recommendations','Recommended jobs'],['/applications','Applications'],['/saved-jobs','Saved jobs'],['/interviews','Interviews'],['/notifications','Notifications'],['/jobs','Browse jobs']]
   :[['/admin','Admin console'],['/notifications','Notifications'],['/jobs','Browse jobs']];
 const navLinks=<><Link href="/dashboard" className="flex shrink-0 items-center gap-3 rounded-xl bg-blue-600/15 px-4 py-3 text-blue-200"><LayoutDashboard size={18}/> Dashboard</Link>{links.map(([href,label])=><Link key={href} href={href} className="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-slate-400 hover:bg-white/5"><BriefcaseBusiness size={18}/>{label}</Link>)}</>;
 return <div className="min-h-screen bg-[#07111f] lg:grid lg:grid-cols-[260px_1fr]">
  <a href="#main-content" className="skip-link">Skip to main content</a>
  <aside className="hidden border-r border-[#1e3a5f] bg-[#081426] p-6 lg:block"><Link href="/"><Brand/></Link><nav aria-label="Dashboard navigation" className="mt-10 grid gap-2 text-sm">{navLinks}</nav></aside>
  <main id="main-content">
   <header className="flex h-20 items-center justify-between border-b border-[#1e3a5f] px-5 lg:px-8"><div><div className="text-xs uppercase tracking-[.2em] text-blue-400">{auth.user.role.replace('_',' ')}</div><h1 className="text-xl font-bold">{title}</h1></div><div className="flex items-center gap-3"><Link href="/notifications" aria-label="Notifications" className="grid size-10 place-items-center rounded-xl border border-[#1e3a5f]"><Bell size={18}/></Link><button onClick={()=>router.post('/logout')} className="grid size-10 place-items-center rounded-xl border border-[#1e3a5f]" aria-label="Log out"><LogOut size={18}/></button></div></header>
   <nav aria-label="Mobile dashboard navigation" className="flex gap-2 overflow-x-auto border-b border-[#1e3a5f] p-3 text-sm lg:hidden">{navLinks}</nav>
   <div className="p-5 lg:p-8">{flash?.success&&<div role="status" className="mb-5 rounded-xl border border-green-700/50 bg-green-900/30 p-4 text-green-200">{flash.success}</div>}{children}</div>
  </main>
 </div>
}
