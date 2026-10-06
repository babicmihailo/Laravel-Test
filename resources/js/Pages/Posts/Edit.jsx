import AppLayout from '@/Layouts/AppLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ post }) {
    const { data, setData, put, processing, errors } = useForm({
        title: post.title,
        content: post.content,
    });

    function submit(e) {
        e.preventDefault();
        put(route('posts.update', post.id));
    }

    return (
        <AppLayout>
            <Head title="Edit post" />

            <h1 className="mb-6 text-2xl font-bold text-gray-800">Edit post</h1>

            <form onSubmit={submit} className="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                <div>
                    <InputLabel htmlFor="title" value="Title" />
                    <TextInput
                        id="title"
                        className="mt-1 block w-full"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                    />
                    <InputError message={errors.title} className="mt-1" />
                </div>

                <div>
                    <InputLabel htmlFor="content" value="Content" />
                    <textarea
                        id="content"
                        rows="8"
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        value={data.content}
                        onChange={(e) => setData('content', e.target.value)}
                    />
                    <InputError message={errors.content} className="mt-1" />
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Update</PrimaryButton>
                    <Link href={route('posts.show', post.id)} className="text-sm text-gray-600">
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
