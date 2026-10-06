import { Head, useForm } from "@inertiajs/react";
import PublicLayout from "../../Layouts/PublicLayout";

export default function VerifyEmail({ email }) {
    const form = useForm({});

    const resend = () => {
        form.post("/email/verification-notification", {
            preserveScroll: true,
        });
    };

    return (
        <PublicLayout>
            <Head title="Verify email" />
            <main className="container-wide grid min-h-[70vh] place-items-center">
                <div className="glass max-w-lg rounded-2xl p-9 text-center">
                    <h1 className="text-3xl font-bold">Verify your email</h1>
                    <p className="mt-3 leading-7 text-slate-400">
                        We sent a verification link to <strong className="text-white">{email}</strong>.
                        Open the link to activate your dashboard access.
                    </p>
                    <p className="mt-3 text-sm text-slate-500">
                        Check your spam folder if it does not appear within a few minutes.
                    </p>
                    <button
                        type="button"
                        onClick={resend}
                        disabled={form.processing}
                        className="btn btn-primary mt-6"
                    >
                        {form.processing ? "Sending…" : "Resend verification link"}
                    </button>
                </div>
            </main>
        </PublicLayout>
    );
}
