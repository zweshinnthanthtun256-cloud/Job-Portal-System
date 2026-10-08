import { Head, Link, router } from "@inertiajs/react";
import { ArrowRight, Search, Sparkles, Users } from "lucide-react";
import { useState } from "react";
import JobCard from "../Components/JobCard";
import PublicLayout from "../Layouts/PublicLayout";
export default function Home({ featuredJobs, stats }) {
    const [q, setQ] = useState("");
    const [location, setLocation] = useState("");
    const submit = (e) => {
        e.preventDefault();
        router.get("/jobs", { q, location }, { preserveState: true });
    };
    return (
        <PublicLayout>
            <Head title="Find the right job. Hire the right talent." />
            <main>
                <section className="container-wide grid items-center gap-10 py-14 sm:py-16 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,.95fr)] lg:py-20">
                    <div>
                        <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-500/20 bg-blue-500/10 px-3.5 py-1.5 text-sm text-blue-200">
                            <Sparkles size={16} /> The modern way to move your
                            career forward
                        </div>
                        <h1 className="max-w-3xl text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                            Your Career{" "}
                            <span className="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                                Starts Here
                            </span>
                        </h1>
                        <p className="mt-5 max-w-2xl text-base leading-7 text-slate-400 sm:text-lg">
                            Discover opportunities from teams looking for talent
                            like you. Search with confidence and apply in
                            minutes.
                        </p>
                        <form
                            onSubmit={submit}
                            className="glass mt-7 grid gap-3 rounded-2xl p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]"
                        >
                            <label className="relative">
                                
                                <input
                                    className="field pl-11"
                                    value={q}
                                    onChange={(e) => setQ(e.target.value)}
                                    placeholder="Job title or keyword"
                                />
                            </label>
                            <input
                                className="field"
                                value={location}
                                onChange={(e) => setLocation(e.target.value)}
                                placeholder="City or country"
                            />
                            <button className="btn btn-primary">
                                Search jobs <ArrowRight size={17} />
                            </button>
                        </form>
                        <div className="mt-7 flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-400">
                            {Object.entries(stats).map(([key, value]) => (
                                <div key={key}>
                                    <strong className="block text-xl text-white">
                                        {value.toLocaleString()}
                                    </strong>
                                    {key}
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="glass relative hidden min-h-[400px] overflow-hidden rounded-3xl p-6 lg:block">
                        <div className="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-cyan-400/5" />
                        <div className="relative rounded-2xl border border-blue-400/20 bg-[#0d1b2a] p-5">
                            <div className="flex items-center gap-4">
                                <div className="grid size-14 place-items-center rounded-2xl bg-blue-600">
                                    <Users />
                                </div>
                                <div>
                                    <p className="font-bold">
                                        Teams are hiring now
                                    </p>
                                    <p className="text-sm text-slate-400">
                                        Build your profile and get discovered
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div className="absolute bottom-10 left-8 right-8 rounded-2xl border border-cyan-400/20 bg-[#10233a] p-5 shadow-2xl">
                            <p className="text-sm text-cyan-300">
                                JobSphere match
                            </p>
                            <p className="mt-2 text-2xl font-bold">
                                Skills meet opportunity.
                            </p>
                            <div className="mt-5 h-2 overflow-hidden rounded-full bg-slate-800">
                                <div className="h-full w-[82%] rounded-full bg-gradient-to-r from-blue-500 to-cyan-400" />
                            </div>
                        </div>
                    </div>
                </section>
                <section className="container-wide pb-16 sm:pb-20">
                    <div className="mb-8 flex items-end justify-between">
                        <div>
                            <p className="text-sm font-bold uppercase tracking-[.2em] text-blue-400">
                                Fresh opportunities
                            </p>
                            <h2 className="mt-2 text-3xl font-bold">
                                Featured jobs
                            </h2>
                        </div>
                        <Link href="/jobs" className="text-blue-300">
                            View all →
                        </Link>
                    </div>
                    {featuredJobs.length ? (
                        <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                            {featuredJobs.map((job) => (
                                <JobCard key={job.id} job={job} />
                            ))}
                        </div>
                    ) : (
                        <div className="glass rounded-2xl p-10 text-center text-slate-400">
                            New opportunities are being prepared. Check back
                            soon.
                        </div>
                    )}
                </section>
            </main>
        </PublicLayout>
    );
}
