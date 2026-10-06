import { Head, router, useForm } from "@inertiajs/react";
import DashboardLayout from "../../Layouts/DashboardLayout";
const Select = ({ value, options, onChange, label = "Status" }) => (
    <label>
        <span className="sr-only">{label}</span>
        <select
            className="field max-w-44 py-2"
            value={value}
            onChange={(e) => onChange(e.target.value)}
        >
            {options.map((x) => (
                <option key={x} value={x}>
                    {x}
                </option>
            ))}
        </select>
    </label>
);
function Library({ title, items, path }) {
    const f = useForm({ name: "" });
    return (
        <section className="glass rounded-2xl p-5">
            <h2 className="text-xl font-bold">{title}</h2>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    f.post(`/admin/${path}`, { onSuccess: () => f.reset() });
                }}
                className="mt-4 flex gap-2"
            >
                <label className="grow">
                    <span className="sr-only">New {title.toLowerCase()}</span>
                    <input
                        className="field"
                        value={f.data.name}
                        onChange={(e) => f.setData("name", e.target.value)}
                        placeholder={`New ${title.toLowerCase()}`}
                    />
                </label>
                <button className="btn btn-primary">Add</button>
            </form>
            <div className="mt-4 flex flex-wrap gap-2">
                {items.map((x) => (
                    <span
                        key={x.id}
                        className="rounded-full bg-white/5 px-3 py-2 text-sm"
                    >
                        {x.name}{" "}
                        <button
                            aria-label={`Delete ${x.name}`}
                            onClick={() =>
                                router.delete(`/admin/${path}/${x.id}`)
                            }
                            className="ml-2 text-red-300"
                        >
                            ×
                        </button>
                    </span>
                ))}
            </div>
        </section>
    );
}
function Analytics({ analytics }) {
    const max = Math.max(1, ...analytics.jobs, ...analytics.applications);
    return (
        <>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                {Object.entries(analytics.totals).map(([k, v]) => (
                    <div className="glass rounded-2xl p-5" key={k}>
                        <p className="text-sm text-slate-400">
                            {k.replaceAll("_", " ")}
                        </p>
                        <strong className="mt-2 block text-3xl">{v}</strong>
                    </div>
                ))}
            </div>
            <section className="glass rounded-2xl p-5">
                <h2 className="text-xl font-bold">14-day activity</h2>
                <div
                    className="mt-6 flex h-44 items-end gap-2"
                    role="img"
                    aria-label="Jobs and applications created during the last 14 days"
                >
                    {analytics.labels.map((label, i) => (
                        <div
                            key={label}
                            className="flex h-full flex-1 items-end gap-1"
                            title={`${label}: ${analytics.jobs[i]} jobs, ${analytics.applications[i]} applications`}
                        >
                            <span
                                className="w-1/2 rounded-t bg-blue-500"
                                style={{
                                    height: `${Math.max(3, (analytics.jobs[i] / max) * 100)}%`,
                                }}
                            />
                            <span
                                className="w-1/2 rounded-t bg-emerald-500"
                                style={{
                                    height: `${Math.max(3, (analytics.applications[i] / max) * 100)}%`,
                                }}
                            />
                        </div>
                    ))}
                </div>
                <div className="mt-3 flex gap-5 text-xs text-slate-400">
                    <span>● Blue: jobs</span>
                    <span>● Green: applications</span>
                </div>
            </section>
        </>
    );
}
function Settings({ settings }) {
    const f = useForm({
        site_name: settings.site_name || "JobSphere",
        support_email: settings.support_email || "support@jobsphere.test",
        maintenance_message: settings.maintenance_message || "",
        applications_enabled: settings.applications_enabled !== "0",
    });
    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                f.put("/admin/settings");
            }}
            className="glass rounded-2xl p-5"
        >
            <h2 className="text-xl font-bold">Platform settings</h2>
            <div className="mt-4 grid gap-4 md:grid-cols-2">
                <label>
                    <span className="label">Site name</span>
                    <input
                        className="field"
                        value={f.data.site_name}
                        onChange={(e) => f.setData("site_name", e.target.value)}
                    />
                </label>
                <label>
                    <span className="label">Support email</span>
                    <input
                        type="email"
                        className="field"
                        value={f.data.support_email}
                        onChange={(e) =>
                            f.setData("support_email", e.target.value)
                        }
                    />
                </label>
            </div>
            <label className="mt-4 block">
                <span className="label">Maintenance message</span>
                <textarea
                    className="field"
                    value={f.data.maintenance_message}
                    onChange={(e) =>
                        f.setData("maintenance_message", e.target.value)
                    }
                />
            </label>
            <label className="mt-4 flex gap-2">
                <input
                    type="checkbox"
                    checked={f.data.applications_enabled}
                    onChange={(e) =>
                        f.setData("applications_enabled", e.target.checked)
                    }
                />{" "}
                Applications enabled
            </label>
            <button className="btn btn-primary mt-4">Save settings</button>
        </form>
    );
}
export default function Index({
    users,
    companies,
    jobs,
    reports,
    audits,
    categories,
    skills,
    analytics,
    settings,
}) {
    return (
        <DashboardLayout title="Administration">
            <Head title="Administration" />
            <div className="grid gap-7">
                <Analytics analytics={analytics} />
                <Settings settings={settings} />
                <div className="grid gap-7 lg:grid-cols-2">
                    <Library
                        title="Categories"
                        items={categories}
                        path="categories"
                    />
                    <Library title="Skills" items={skills} path="skills" />
                </div>
                <section className="glass overflow-x-auto rounded-2xl p-5">
                    <h2 className="text-xl font-bold">Users</h2>
                    <table className="mt-4 w-full min-w-[650px]">
                        <thead>
                            <tr className="text-left text-sm text-slate-400">
                                <th className="p-3">User</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((u) => (
                                <tr
                                    key={u.id}
                                    className="border-t border-[#1e3a5f]"
                                >
                                    <td className="p-3">
                                        {u.name}
                                        <div className="text-xs text-slate-400">
                                            {u.email}
                                        </div>
                                    </td>
                                    <td>{u.role}</td>
                                    <td>
                                        <Select
                                            value={u.status}
                                            options={["active", "suspended"]}
                                            onChange={(status) =>
                                                router.patch(
                                                    `/admin/users/${u.id}`,
                                                    { status },
                                                )
                                            }
                                        />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </section>
                <section className="glass overflow-x-auto rounded-2xl p-5">
                    <h2 className="text-xl font-bold">Companies</h2>
                    {companies.data.map((c) => (
                        <div
                            key={c.id}
                            className="flex items-center justify-between border-t border-[#1e3a5f] py-4"
                        >
                            <span>
                                {c.name}
                                <small className="block text-slate-400">
                                    {c.industry}
                                </small>
                            </span>
                            <Select
                                value={c.verification_status}
                                options={[
                                    "pending",
                                    "verified",
                                    "rejected",
                                    "suspended",
                                ]}
                                onChange={(verification_status) =>
                                    router.patch(`/admin/companies/${c.id}`, {
                                        verification_status,
                                    })
                                }
                            />
                        </div>
                    ))}
                </section>
                <section className="glass rounded-2xl p-5">
                    <h2 className="text-xl font-bold">Jobs</h2>
                    {jobs.data.map((j) => (
                        <div
                            key={j.id}
                            className="flex items-center justify-between border-t border-[#1e3a5f] py-4"
                        >
                            <span>
                                {j.title} ·{" "}
                                <span className="text-slate-400">
                                    {j.company.name}
                                </span>
                            </span>
                            <Select
                                value={j.status}
                                options={[
                                    "published",
                                    "paused",
                                    "closed",
                                    "archived",
                                ]}
                                onChange={(status) =>
                                    router.patch(`/admin/jobs/${j.slug}`, {
                                        status,
                                    })
                                }
                            />
                        </div>
                    ))}
                </section>
                <section className="glass rounded-2xl p-5">
                    <h2 className="text-xl font-bold">Reports</h2>
                    {reports.data.map((r) => (
                        <form
                            key={r.id}
                            onSubmit={(e) => {
                                e.preventDefault();
                                const d = new FormData(e.currentTarget);
                                router.patch(
                                    `/admin/reports/${r.id}`,
                                    Object.fromEntries(d),
                                );
                            }}
                            className="border-t border-[#1e3a5f] py-4"
                        >
                            <div className="flex flex-wrap justify-between gap-3">
                                <div>
                                    <strong>{r.reason}</strong>
                                    <p className="text-sm text-slate-400">
                                        Reported by{" "}
                                        {r.reporter?.name || "Unknown"} ·{" "}
                                        {new Date(
                                            r.created_at,
                                        ).toLocaleString()}
                                    </p>
                                    <p className="mt-2 text-sm">
                                        {r.details || "No additional details."}
                                    </p>
                                </div>
                                <select
                                    name="status"
                                    defaultValue={r.status}
                                    className="field max-w-44"
                                >
                                    <option>reviewing</option>
                                    <option>resolved</option>
                                    <option>dismissed</option>
                                </select>
                            </div>
                            <label className="mt-3 block">
                                <span className="label">Resolution notes</span>
                                <textarea
                                    name="resolution"
                                    defaultValue={r.resolution || ""}
                                    className="field"
                                />
                            </label>
                            <button className="btn btn-secondary mt-3">
                                Update report
                            </button>
                        </form>
                    ))}
                </section>
                <section className="glass rounded-2xl p-5">
                    <h2 className="text-xl font-bold">Audit trail</h2>
                    {audits.map((a) => (
                        <div
                            key={a.id}
                            className="border-t border-[#1e3a5f] py-3 text-sm"
                        >
                            <strong>{a.event}</strong>
                            <span className="ml-3 text-slate-400">
                                {a.user?.name || "System"} ·{" "}
                                {new Date(a.created_at).toLocaleString()} ·{" "}
                                {a.ip_address}
                            </span>
                        </div>
                    ))}
                </section>
            </div>
        </DashboardLayout>
    );
}
