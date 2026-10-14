import { Head, router } from "@inertiajs/react";
import DashboardLayout from "../../Layouts/DashboardLayout";

export default function Index({ notifications }) {
    return (
        <DashboardLayout title="Notifications">
            <Head title="Notifications" />
            <section>
                <div className="mb-4 text-right">
                    <button
                        onClick={() => router.post("/notifications/read-all")}
                        className="btn btn-secondary"
                    >
                        Mark all read
                    </button>
                </div>
                <div className="glass overflow-hidden rounded-2xl">
                    {notifications.data.map((notification) => (
                        <button
                            key={notification.id}
                            onClick={() =>
                                router.patch(`/notifications/${notification.id}`)
                            }
                            className={`block w-full border-b border-slate-200 p-5 text-left ${notification.read_at ? "opacity-60" : "bg-purple-50"}`}
                        >
                            <strong>{notification.data.title || "Update"}</strong>
                            <p className="mt-1 text-sm text-slate-500">
                                {notification.data.message}
                            </p>
                        </button>
                    ))}
                    {!notifications.data.length && (
                        <div className="p-12 text-center text-slate-500">
                            You’re all caught up.
                        </div>
                    )}
                </div>
            </section>
        </DashboardLayout>
    );
}
