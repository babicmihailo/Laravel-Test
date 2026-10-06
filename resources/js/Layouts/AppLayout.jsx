import { Link, usePage } from '@inertiajs/react';

export default function AppLayout({ children }) {
    // auth.user is the logged-in user, or null for guests
    const { auth } = usePage().props;

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="border-b border-gray-200 bg-white">
                <div className="mx-auto flex h-16 max-w-5xl items-center justify-between px-4">
                    <Link
                        href={route('posts.index')}
                        className="text-lg font-semibold text-gray-800"
                    >
                        Blog
                    </Link>

                    <div className="flex items-center gap-4 text-sm text-gray-600">
                        {auth.user ? (
                            <>
                                <Link href={route('posts.create')}>New post</Link>
                                <Link href={route('profile.edit')}>{auth.user.name}</Link>
                                <Link method="post" href={route('logout')} as="button">
                                    Log out
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link href={route('login')}>Log in</Link>
                                <Link href={route('register')}>Register</Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            <main className="mx-auto max-w-5xl px-4 py-8">{children}</main>
        </div>
    );
}
