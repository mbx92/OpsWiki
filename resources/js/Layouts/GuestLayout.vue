<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-[#f8f9fa] px-4 py-12">
        <!-- Logo -->
        <div class="mb-8">
            <Link href="/" class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-[8px] bg-[#111111]">
                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span style="font-family: 'Manrope', 'Inter', sans-serif; font-weight: 700;" class="text-[15px] text-[#111111]">Wiki</span>
            </Link>
        </div>

        <!-- Card -->
        <div class="w-full max-w-[400px] rounded-[12px] border border-[#e5e7eb] bg-white p-8 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
            <slot />
        </div>
    </div>
</template>
