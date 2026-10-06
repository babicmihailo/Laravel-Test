import AppLayout from '@/Layouts/AppLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        content: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('posts.store'));
    }

    return (
        <AppLayout>
            <Head title="New post" />

            <h1 className="mb-6 text-2xl font-bold text-gray-800">New post</h1>

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

                <PrimaryButton disabled={processing}>Save</PrimaryButton>
            </form>
        </AppLayout>
    );
}
