import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';

export default function Index({ posts }) {
    return (
        <AppLayout>
            <Head title="Posts" />

            <h1 className="mb-6 text-2xl font-bold text-gray-800">Posts</h1>

            {posts.data.length === 0 && (
                <p className="text-gray-500">No posts yet.</p>
            )}

            <div className="space-y-4">
                {posts.data.map((post) => (
                    <article key={post.id} className="rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="text-xl font-semibold text-gray-900">
                            <Link href={route('posts.show', post.id)}>{post.title}</Link>
                        </h2>

                        <p className="mt-1 text-sm text-gray-500">
                            by {post.user.name} on{' '}
                            {new Date(post.created_at).toLocaleDateString()}
                        </p>

                        <p className="mt-3 text-gray-700">
                            {post.content.slice(0, 150)}
                        </p>
                    </article>
                ))}
            </div>

            <div className="mt-6 flex justify-between text-sm">
                {posts.prev_page_url ? (
                    <Link href={posts.prev_page_url}>&larr; Previous</Link>
                ) : (
                    <span />
                )}

                {posts.next_page_url && (
                    <Link href={posts.next_page_url}>Next &rarr;</Link>
                )}
            </div>
        </AppLayout>
    );
}
