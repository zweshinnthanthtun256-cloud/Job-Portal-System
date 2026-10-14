import { BriefcaseBusiness } from 'lucide-react';

export default function Brand({ inverse = false }) {
    return <div className={`flex items-center gap-2.5 text-xl font-black tracking-tight ${inverse ? 'text-white' : 'text-[#2d173d]'}`}>
        <span className="grid size-9 place-items-center rounded-lg bg-[#7132a8] text-white shadow-md shadow-purple-900/20"><BriefcaseBusiness size={19}/></span>
        <span>Job<span className={inverse ? 'text-purple-200' : 'text-[#7132a8]'}>Sphere</span></span>
    </div>;
}
