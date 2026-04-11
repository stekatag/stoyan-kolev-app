<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type Props = {
    title: string;
    description: string;
    inviteState: string;
    email: string | null;
    emailLocked: boolean;
    submitUrl: string | null;
};

const props = defineProps<Props>();

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const stateCopy = computed(() => {
    switch (props.inviteState) {
        case 'consumed':
            return {
                title: 'Invitation already used',
                description: 'This admin invitation has already been consumed.',
            };
        case 'email_unavailable':
            return {
                title: 'Email unavailable',
                description: 'That email can no longer be used to create an invited admin account.',
            };
        case 'invalid':
            return {
                title: 'Invitation unavailable',
                description: 'The invitation link is invalid or expired.',
            };
        default:
            return {
                title: props.title,
                description: props.description,
            };
    }
});

const submit = (): void => {
    if (props.submitUrl === null) {
        return;
    }

    form.post(props.submitUrl, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="stateCopy.title" />

    <div class="space-y-6">
        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-semibold text-stone-950">
                {{ stateCopy.title }}
            </h1>
            <p class="text-sm leading-6 text-stone-600">
                {{ stateCopy.description }}
            </p>
        </div>

        <form
            v-if="inviteState === 'valid'"
            class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-700">Email</label>
                <input
                    :value="email ?? ''"
                    type="email"
                    class="w-full rounded-xl border border-stone-300 bg-stone-100 px-4 py-3 text-sm text-stone-700"
                    :readonly="emailLocked"
                />
            </div>

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-stone-700">Name</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm text-stone-900"
                    autocomplete="name"
                />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-stone-700">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm text-stone-900"
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-stone-700">
                    Confirm password
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm text-stone-900"
                    autocomplete="new-password"
                />
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-stone-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"
                :disabled="form.processing"
            >
                Finish admin setup
            </button>
        </form>

        <div
            v-else
            class="rounded-2xl border border-stone-200 bg-white px-6 py-5 text-center text-sm text-stone-600 shadow-sm"
        >
            Request a fresh invitation from an existing admin if you still need access.
        </div>
    </div>
</template>