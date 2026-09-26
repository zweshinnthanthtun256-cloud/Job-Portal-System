import { Link, router, usePage } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useState } from 'react';
import Brand from '../Components/Brand';

export default function PublicLayout({ children }) {
    const { auth, flash } = usePage().props; const [open, setOpen] = useState(false);
    return <div className="page-shell"><header className="sticky top-0 z-30 border-b border-blue-950/80 bg-[#07111f]/85 backdrop-blur-xl"><nav className="container-wide flex h-18 items-center justify-between"><Link href="/"><Brand/></Link><div className="hidden items-center gap-7 text-sm text-slate-300 md:flex"><Link href="/jobs" className="hover:text-white">Find jobs</Link>{auth.user ? <><Link href="/dashboard" className="hover:text-white">Dashboard</Link><button className="btn btn-secondary py-2" onClick={() => router.post('/logout')}>Log out</button></> : <><Link href="/login" className="hover:text-white">Log in</Link><Link href="/register" className="btn btn-primary py-2">Get started</Link></>}</div><button aria-label="Toggle navigation" className="md:hidden" onClick={() => setOpen(!open)}>{open ? <X/> : <Menu/>}</button></nav>{open && <div className="container-wide grid gap-3 border-t border-blue-950 py-4 md:hidden"><Link href="/jobs">Find jobs</Link>{auth.user ? <Link href="/dashboard">Dashboard</Link> : <><Link href="/login">Log in</Link><Link href="/register">Register</Link></>}</div>}</header>{flash?.success && <div className="container-wide mt-4 rounded-xl border border-green-700/50 bg-green-900/30 p-4 text-green-200">{flash.success}</div>}{children}</div>;
}
