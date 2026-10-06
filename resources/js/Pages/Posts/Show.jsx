import AppLayout from '@/Layouts/AppLayout';
import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Show({ post, comments, can }) {
    const { data, setData, post: send, processing, errors, reset } = useForm({
        comment: '',
    });

    function deletePost() {
        if (confirm('Delete this post?')) {
            router.delete(route('posts.destroy', post.id));
        }
    }

    function addComment(e) {
        e.preventDefault();
        send(route('comments.store', post.id), {
            onSuccess: () => reset(),
        });
    }

    function deleteComment(id) {
        if (confirm('Delete this comment?')) {
            router.delete(route('comments.destroy', id));
        }
    }

    return (
        <AppLayout>
            <Head title={post.title} />

            <article className="rounded-lg bg-white p-6 shadow-sm">
                <h1 className="text-2xl font-bold text-gray-900">{post.title}</h1>

                <p className="mt-1 text-sm text-gray-500">
                    by {post.user.name} on{' '}
                    {new Date(post.created_at).toLocaleDateString()}
                </p>

                <p className="mt-4 whitespace-pre-line text-gray-700">{post.content}</p>

                {(can.update || can.delete) && (
                    <div className="mt-6 flex items-center gap-4">
                        {can.update && (
                            <Link href={route('posts.edit', post.id)} className="text-sm text-gray-600">
                                Edit
                            </Link>
                        )}
                        {can.delete && (
                            <DangerButton onClick={deletePost}>Delete</DangerButton>
                        )}
                    </div>
                )}
            </article>

            <h2 className="mb-3 mt-8 text-lg font-semibold text-gray-800">
                Comments ({comments.length})
            </h2>

            <form onSubmit={addComment} className="mb-4 space-y-3 rounded-lg bg-white p-4 shadow-sm">
                <textarea
                    rows="3"
                    placeholder="Write a comment..."
                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    value={data.comment}
                    onChange={(e) => setData('comment', e.target.value)}
                />
                <InputError message={errors.comment} />
                <PrimaryButton disabled={processing}>Add comment</PrimaryButton>
            </form>

            <div className="space-y-3">
                {comments.map((comment) => (
                    <div key={comment.id} className="rounded-lg bg-white p-4 shadow-sm">
                        <p className="text-sm text-gray-500">
                            {comment.author} on{' '}
                            {new Date(comment.created_at).toLocaleDateString()}
                        </p>
                        <p className="mt-1 text-gray-700">{comment.comment}</p>

                        {comment.can_delete && (
                            <button
                                onClick={() => deleteComment(comment.id)}
                                className="mt-2 text-sm text-red-600"
                            >
                                Delete
                            </button>
                        )}
                    </div>
                ))}
            </div>
        </AppLayout>
    );
}
